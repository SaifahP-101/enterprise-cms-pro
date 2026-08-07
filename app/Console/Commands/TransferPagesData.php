<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Page;
use App\Models\Tag;
use Carbon\Carbon;
use DOMDocument;

class TransferPagesData extends Command
{
    /**
     * The name and signature of the console command.
     * เปิดใช้งาน Option --fresh สำหรับเคลียร์ข้อมูลเก่า
     *
     * @var string
     */
    protected $signature = 'cms:transfer-pages {--fresh : ล้างข้อมูลหน้าเพจเดิมในระบบใหม่ทิ้งก่อนดึงข้อมูล}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer pages data, extract base64, create tags, and copy files to Enterprise CMS (Static Pages)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 1. ตรวจสอบการล้างข้อมูลเก่า (ถ้ามี Flag --fresh)
        if ($this->option('fresh')) {
            $this->info('🧹 Cleaning up old Pages records in DB2...');
            
            Schema::disableForeignKeyConstraints();
            
            // หมายเหตุ: ตรวจสอบตารางที่เชื่อมโยงกับ pages ในระบบใหม่ เช่น page_tag หรือ page_galleries 
            // หากระบบใหม่มีการใช้ pivot table สำหรับ pages ให้เคลียร์ด้วย (ในที่นี้เคลียร์เฉพาะตารางหลักก่อน)
            if (Schema::hasTable('page_tag')) {
                DB::table('page_tag')->truncate();
            }
            if (Schema::hasTable('page_galleries')) {
                DB::table('page_galleries')->truncate();
            }

            Page::truncate(); // ล้างตาราง pages หลัก
            
            Schema::enableForeignKeyConstraints();
            
            $this->info('✅ Clean up completed!');
        }

        $this->info('🚀 Starting Data, Tags, Base64 & Physical Files Transfer for Pages...');

        // 2. กำหนด Path ต้นทางและปลายทาง
        // ⚠️ หมายเหตุ: ปรับโฟลเดอร์ 'old/images/page' ให้ตรงกับระบบเก่า
        $oldBasePathPDF     = public_path('old/images/page/pdf');
        $oldBasePathAPP     = public_path('old/app'); 
        
        $newSecurePdfPath = storage_path('app/secure_docs');
        $inlineImagePath  = storage_path('app/public/pages/inline_images'); // โฟลเดอร์รูปภาพแทรกในเพจ

        // สร้าง Directory ปลายทางหากยังไม่มี
        File::ensureDirectoryExists($newSecurePdfPath, 0755, true);
        File::ensureDirectoryExists($inlineImagePath, 0755, true);
 
        // 3. ดึงข้อมูลจากฐานข้อมูลเก่า DB 1 (ตาราง pages)
        $oldRecords = DB::connection('mysql_old')->table('pages')->get();
        
        if ($oldRecords->isEmpty()) {
            $this->warn('⚠️ No records found in pages table.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($oldRecords));

        foreach ($oldRecords as $old) {
            // 4. จัดการเนื้อหาหลักจากไฟล์ .text แปลงเป็น LongText และสกัด Base64 Image
            $bodyContent = '';
            if (!empty($old->page_file_text)) { 
                $textPath = $oldBasePathAPP . '/' . $old->page_file_text;
                if (File::exists($textPath)) {
                    $bodyContent = File::get($textPath);
                    
                    // สกัดรูปภาพ Base64 เป็นไฟล์ Physical เพื่อให้ Database เบาที่สุด
                    if (!empty($bodyContent) && strpos($bodyContent, 'data:image') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML(mb_convert_encoding($bodyContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                        libxml_clear_errors();

                        $images = $dom->getElementsByTagName('img');

                        foreach ($images as $img) {
                            $src = $img->getAttribute('src');

                            if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
                                $base64Data = substr($src, strpos($src, ',') + 1);
                                $type = strtolower($type[1]);
                                
                                if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                    $type = 'jpg';
                                }

                                $imageName = 'inline_' . Str::random(10) . '_' . time() . '.' . $type;
                                $imagePath = 'pages/inline_images/' . $imageName;

                                // บันทึกไฟล์ลง Storage
                                Storage::disk('public')->put($imagePath, base64_decode($base64Data));

                                // สลับ Base64 เป็น URL
                                $img->removeAttribute('src');
                                $img->setAttribute('src', asset('storage/' . $imagePath));
                            }
                        }
                        $bodyContent = $dom->saveHTML();
                    }
                }
            }

            // 5. คัดลอกไฟล์เอกสาร PDF ลง Secure Storage
            $pdfPathValue = null;
            if (!empty($old->file_pdf)) {
                $sourcePdf = $oldBasePathPDF . '/' . $old->file_pdf;
                if (File::exists($sourcePdf)) {
                    File::copy($sourcePdf, $newSecurePdfPath . '/' . $old->file_pdf);
                    $pdfPathValue = 'secure_docs/' . $old->file_pdf;
                }
            }

            // 6. จัดการ Slug ให้รองรับภาษาไทย และไม่ซ้ำกัน
            $slug = !empty($old->page_slug) ? $old->page_slug : null;
            if (!$slug) {
                $cleanTitle = preg_replace('/[^A-Za-z0-9ก-๙\s]/u', '', strip_tags($old->page_title));
                $thaiSlug = preg_replace('/\s+/u', '-', trim($cleanTitle));
                $slug = mb_substr($thaiSlug ?: 'page', 0, 100) . '-page-' . $old->id . '-' . Str::random(4);
            }

            // 7. บันทึกข้อมูลลงตาราง Pages 
            // ⚠️ หมายเหตุ: ตาราง pages ไม่มีฟิลด์ category_id และ cover_image อ้างอิงตาม Schema ใหม่[cite: 2]
            $page = Page::create([
                'title'            => strip_tags($old->page_title), // ป้องกัน XSS
                'slug'             => $slug,
                'body'             => $bodyContent,
                'secure_pdf_path'  => $pdfPathValue,
                'view_count'       => $old->count_view ?? 0,
                'is_active'        => (isset($old->page_status) && $old->page_status == 1) ? 1 : 0,
                'meta_title'       => $old->page_meta_title ?? strip_tags($old->page_title),
                'meta_description' => $old->page_meta_description ?? strip_tags($old->page_intro ?? ''),
                'created_at'       => $old->created_at ?? now(),
                'updated_at'       => $old->updated_at ?? now(),
            ]);

            // 8. ดึงฟิลด์ย่อย (Non-main fields) มาสร้างเป็น Tags อัตโนมัติ (หากโมเดล Page รองรับ)
            $tagIdsToSync = [];
            $tagFields = [
                'คีย์เวิร์ด'  => $old->page_meta_keyword,
            ];

            foreach ($tagFields as $prefix => $value) {
                if (!empty($value)) {
                    $items = explode(',', $value);
                    foreach ($items as $item) {
                        $item = trim($item);
                        if (!empty($item)) {
                            $tagName = "{$prefix} : {$item}";
                            
                            $cleanTag = preg_replace('/[^A-Za-z0-9ก-๙\s\-]/u', '', $tagName);
                            $tagSlug = preg_replace('/\s+/u', '-', trim($cleanTag));
                            
                            // จำกัดความยาว Slug เพื่อป้องกัน MySQL ตัดคำออโต้แล้วซ้ำกัน
                            $tagSlug = mb_substr($tagSlug, 0, 100); 

                            $tag = Tag::where('name', $tagName)->first();
                            
                            if (!$tag) {
                                if (Tag::where('slug', $tagSlug)->exists()) {
                                    $tagSlug = $tagSlug . '-' . Str::random(4);
                                }
                                $tag = Tag::create([
                                    'slug' => $tagSlug ?: 'tag-' . Str::random(6),
                                    'name' => $tagName
                                ]);
                            }
                            $tagIdsToSync[] = $tag->id;
                        }
                    }
                }
            }

            // Sync Tags เข้ากับ Page (ตรวจสอบก่อนว่า Model Page มีฟังก์ชัน tags() หรือไม่)
            if (count($tagIdsToSync) > 0 && method_exists($page, 'tags')) {
                $page->tags()->syncWithoutDetaching($tagIdsToSync);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Pages Data, Tags, Base64 Extraction, and Files Migration Completed!');
        return 0;
    }
}