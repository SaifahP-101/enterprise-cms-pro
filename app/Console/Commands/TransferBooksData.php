<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Content;
use App\Models\ContentGallery;
use App\Models\Tag;
use Carbon\Carbon;

class TransferBooksData extends Command
{
    /**
     * The name and signature of the console command.
     * เพิ่ม Option --fresh สำหรับเคลียร์ข้อมูลเก่าเฉพาะหมวดหมู่ 5 (หนังสือและวารสาร)
     *
     * @var string
     */
    protected $signature = 'cms:transfer-books {--fresh : ล้างข้อมูลหนังสือและวารสารสำนักฯ เดิมในระบบใหม่ทิ้งก่อนดึงข้อมูล}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer books data, transform non-main fields to tags, and copy physical files to Enterprise CMS (Category 5)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 1. ตรวจสอบและดำเนินการล้างข้อมูลเก่าในหมวดหมู่ที่ 5 (ถ้ามี Flag --fresh)
        
        // 2. กำหนด Path ต้นทางและปลายทาง (อิงจากระบบเดิม)
        $oldBasePath        = public_path('old/images/book');
        $oldBasePathPDF     = public_path('old/images/book/pdf');
        $oldBasePathGallery = public_path('old/images/book/gallery');
        $oldBasePathAPP     = public_path('old/app'); 
        
        $newCoverPath     = storage_path('app/public/contents/covers');
        $newSecurePdfPath = storage_path('app/secure_docs');

        File::ensureDirectoryExists($newCoverPath, 0755, true);
        File::ensureDirectoryExists($newSecurePdfPath, 0755, true);
 
        // 3. ดึงข้อมูลจากฐานข้อมูลเก่า DB 1 (ตาราง books)
        $oldRecords = DB::connection('mysql_old')->table('books')->get();
        
        if ($oldRecords->isEmpty()) {
            $this->warn('⚠️ No records found in books table.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($oldRecords));

        foreach ($oldRecords as $old) {
            // 4. จัดการเนื้อหาหลักจากไฟล์ .text แปลงเป็น LongText
            $bodyContent = '';
            if (!empty($old->file_text)) { 
                $textPath = $oldBasePathAPP . '/' . $old->file_text;
                if (File::exists($textPath)) {
                    $bodyContent = File::get($textPath);
                }
            }

            // 5. คัดลอกรูปภาพหน้าปก Cover
            $coverImageValue = null;
            if (!empty($old->image_desktop)) {
                $sourceCover = $oldBasePath . '/' . $old->image_desktop;
                if (File::exists($sourceCover)) {
                    File::copy($sourceCover, $newCoverPath . '/' . $old->image_desktop);
                    $coverImageValue = 'contents/covers/' . $old->image_desktop;
                }
            }

            // 6. คัดลอกไฟล์เอกสาร PDF ลง Secure Storage
            $pdfPathValue = null;
            if (!empty($old->file_pdf)) {
                $sourcePdf = $oldBasePathPDF . '/' . $old->file_pdf;
                if (File::exists($sourcePdf)) {
                    File::copy($sourcePdf, $newSecurePdfPath . '/' . $old->file_pdf);
                    $pdfPathValue = 'secure_docs/' . $old->file_pdf;
                }
            }

            // 7. จัดการ Slug ให้รองรับภาษาไทย และไม่ซ้ำกัน (ป้องกัน Error 1062)
            $slug = !empty($old->slug) ? $old->slug : null;
            if (!$slug) {
                $cleanTitle = preg_replace('/[^A-Za-z0-9ก-๙\s]/u', '', strip_tags($old->title));
                $thaiSlug = preg_replace('/\s+/u', '-', trim($cleanTitle));
                // ตัดความยาวและต่อด้วย ID + Random String ป้องกันการซ้ำซ้อน 100%
                $slug = mb_substr($thaiSlug ?: 'publication', 0, 100) . '-book-' . $old->id . '-' . Str::random(4);
            }

            // 8. บันทึกข้อมูลลงตาราง Contents 
            $content = Content::create([
                'category_id'      => 5, // 5 = หนังสือและวารสารสำนักฯ
                'title'            => strip_tags($old->title), // ป้องกัน XSS
                'slug'             => $slug,
                'type'             => 'PUBLICATION', // กำหนด Type เป็นสิ่งพิมพ์
                'body'             => $bodyContent,
                'cover_image'      => $coverImageValue,
                'secure_pdf_path'  => $pdfPathValue,
                'view_count'       => $old->count_view ?? 0,
                'download_count'   => 0,
                'meta_title'       => $old->meta_title ?? strip_tags($old->title),
                'meta_description' => $old->meta_description ?? strip_tags($old->intro ?? ''),
                'user_id'          => 1, // กำหนดให้ Super Admin เป็นผู้สร้าง
                'is_active'        => (isset($old->status) && $old->status == 1) ? 1 : 0,
                'published_at'     => isset($old->date) ? Carbon::parse($old->date) : now(),
                'created_at'       => $old->created_at ?? now(),
                'updated_at'       => $old->updated_at ?? now(),
            ]);

            // 9. ดึงฟิลด์ย่อย (Non-main fields) มาสร้างเป็น Tags อัตโนมัติ ป้องกัน Truncate Error
            $tagIdsToSync = [];
            $tagFields = [
                'ผู้แต่ง'   => $old->author,
                'ประเภท'    => $old->type,
                'คีย์เวิร์ด'  => $old->keyword,
                'ปี'       => $old->year,
                'ตีพิมพ์'    => $old->published,
                'แท็ก'     => $old->meta_keyword,
            ];

            foreach ($tagFields as $prefix => $value) {
                if (!empty($value)) {
                    // หากมีการคั่นด้วยลูกน้ำ (Comma) ให้แยกเป็นหลายๆ Tag
                    $items = explode(',', $value);
                    foreach ($items as $item) {
                        $item = trim($item);
                        if (!empty($item)) {
                            $tagName = "{$prefix} : {$item}";
                            
                            // 9.1 สร้าง Slug ภาษาไทยสำหรับ Tag
                            $cleanTag = preg_replace('/[^A-Za-z0-9ก-๙\s\-]/u', '', $tagName);
                            $tagSlug = preg_replace('/\s+/u', '-', trim($cleanTag));
                            
                            // 9.2 ป้องกันความยาวเกินจนถูก MySQL ตัด (Truncate)
                            $tagSlug = mb_substr($tagSlug, 0, 100); 

                            // 9.3 ค้นหาจาก "ชื่อแท็ก" (Name) แทน Slug เพื่อความแม่นยำที่สุด
                            $tag = Tag::where('name', $tagName)->first();
                            
                            if (!$tag) {
                                // ถ้าไม่มี Tag นี้อยู่ เช็คกันเหนียวว่า Slug ไปซ้ำกับใครหรือไม่
                                if (Tag::where('slug', $tagSlug)->exists()) {
                                    $tagSlug = $tagSlug . '-' . Str::random(4);
                                }
                                
                                // สร้าง Tag ใหม่
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

            // Sync Tags เข้ากับ Content
            if (count($tagIdsToSync) > 0) {
                $content->tags()->syncWithoutDetaching($tagIdsToSync);
            }

            // 10. จัดการรูปภาพ Gallery (ถ้าหนังสือมี Gallery แนบมาด้วย)
            if (Schema::connection('mysql_old')->hasTable('book_gallerys')) {
                $newGalleryPath = storage_path('app/public/contents/galleries/' . $content->id);
                
                $oldGalleries = DB::connection('mysql_old')
                    ->table('book_gallerys')
                    ->where('book_id', $old->id) 
                    ->get();

                if ($oldGalleries->isNotEmpty()) {
                    File::ensureDirectoryExists($newGalleryPath, 0755, true);

                    foreach ($oldGalleries as $index => $gallery) {
                        if (!empty($gallery->image_desktop)) {
                            $sourceGallery = $oldBasePathGallery . '/' . $gallery->image_desktop;
                            if (File::exists($sourceGallery)) {
                                File::copy($sourceGallery, $newGalleryPath . '/' . $gallery->image_desktop);
                                
                                ContentGallery::create([
                                    'content_id' => $content->id,
                                    'file_path'  => 'contents/galleries/' . $content->id . '/' . $gallery->image_desktop,
                                    'sort_order' => $index + 1,
                                    'created_at' => $gallery->created_at ?? now(),
                                    'updated_at' => $gallery->updated_at ?? now(),
                                ]);
                            }
                        }
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Books Data, Tags, and Files Migration Completed Successfully!');
        return 0;
    }
}