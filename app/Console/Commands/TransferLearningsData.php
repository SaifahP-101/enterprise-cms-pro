<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Content;
use App\Models\ContentGallery;
use App\Models\Tag;
use Carbon\Carbon;
use DOMDocument;

class TransferLearningsData extends Command
{
    /**
     * The name and signature of the console command.
     * (ยกเลิกระบบเคลียร์ข้อมูลเก่าแล้ว ตามข้อกำหนด)
     *
     * @var string
     */
    protected $signature = 'cms:transfer-learnings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer learnings data, transform non-main fields to tags, extract base64, and copy files to Enterprise CMS (Category 6)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Starting Data, Tags, Base64 & Physical Files Transfer for Learnings...');

        // 1. กำหนด Path ต้นทางและปลายทาง
        // ⚠️ หมายเหตุ: ปรับโฟลเดอร์ 'old/images/learning' ให้ตรงกับระบบเก่า
        $oldBasePath        = public_path('old/images/learning');
        $oldBasePathPDF     = public_path('old/images/learning/pdf');
        $oldBasePathGallery = public_path('old/images/learning/gallery');
        $oldBasePathAPP     = public_path('old/app'); 
        
        $newCoverPath     = storage_path('app/public/contents/covers');
        $newSecurePdfPath = storage_path('app/secure_docs');
        $inlineImagePath  = storage_path('app/public/contents/inline_images');

        File::ensureDirectoryExists($newCoverPath, 0755, true);
        File::ensureDirectoryExists($newSecurePdfPath, 0755, true);
        File::ensureDirectoryExists($inlineImagePath, 0755, true);
 
        // 2. ดึงข้อมูลจากฐานข้อมูลเก่า DB 1 (ตาราง learnings)
        $oldRecords = DB::connection('mysql_old')->table('learnings')->get();
        
        if ($oldRecords->isEmpty()) {
            $this->warn('⚠️ No records found in learnings table.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($oldRecords));

        foreach ($oldRecords as $old) {
            // 3. จัดการเนื้อหาหลักจากไฟล์ .text แปลงเป็น LongText และสกัด Base64
            $bodyContent = '';
            if (!empty($old->file_text)) { 
                $textPath = $oldBasePathAPP . '/' . $old->file_text;
                if (File::exists($textPath)) {
                    $bodyContent = File::get($textPath);
                    
                    // สกัดรูปภาพ Base64 เป็นไฟล์ Physical
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
                                $imagePath = 'contents/inline_images/' . $imageName;

                                Storage::disk('public')->put($imagePath, base64_decode($base64Data));

                                $img->removeAttribute('src');
                                $img->setAttribute('src', asset('storage/' . $imagePath));
                            }
                        }
                        $bodyContent = $dom->saveHTML();
                    }
                }
            }

            // 4. คัดลอกรูปภาพหน้าปก Cover
            $coverImageValue = null;
            if (!empty($old->image_desktop)) {
                $sourceCover = $oldBasePath . '/' . $old->image_desktop;
                if (File::exists($sourceCover)) {
                    File::copy($sourceCover, $newCoverPath . '/' . $old->image_desktop);
                    $coverImageValue = 'contents/covers/' . $old->image_desktop;
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
            $slug = !empty($old->slug) ? $old->slug : null;
            if (!$slug) {
                $cleanTitle = preg_replace('/[^A-Za-z0-9ก-๙\s]/u', '', strip_tags($old->title));
                $thaiSlug = preg_replace('/\s+/u', '-', trim($cleanTitle));
                $slug = mb_substr($thaiSlug ?: 'learning', 0, 100) . '-learning-' . $old->id . '-' . Str::random(4);
            }

            // 7. บันทึกข้อมูลลงตาราง Contents 
            $content = Content::create([
                'category_id'      => 6, // 6 = แหล่งเรียนรู้ 3 บุรี[cite: 2]
                'title'            => strip_tags($old->title), 
                'slug'             => $slug,
                'type'             => 'THREE_BURI_LEARNING_RESOURCES', // อ้างอิงจากโครงสร้างระบบใหม่[cite: 2]
                'body'             => $bodyContent,
                'cover_image'      => $coverImageValue,
                'secure_pdf_path'  => $pdfPathValue,
                'view_count'       => $old->count_view ?? 0,
                'download_count'   => 0,
                'meta_title'       => $old->meta_title ?? strip_tags($old->title),
                'meta_description' => $old->meta_description ?? strip_tags($old->intro ?? ''),
                'user_id'          => 1, 
                'is_active'        => (isset($old->status) && $old->status == 1) ? 1 : 0,
                'published_at'     => isset($old->date) ? Carbon::parse($old->date) : now(),
                'created_at'       => $old->created_at ?? now(),
                'updated_at'       => $old->updated_at ?? now(),
            ]);

            // 8. ดึงฟิลด์ย่อย (Non-main fields) มาสร้างเป็น Tags อัตโนมัติ 
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
                    $items = explode(',', $value);
                    foreach ($items as $item) {
                        $item = trim($item);
                        if (!empty($item)) {
                            $tagName = "{$prefix} : {$item}";
                            $cleanTag = preg_replace('/[^A-Za-z0-9ก-๙\s\-]/u', '', $tagName);
                            $tagSlug = preg_replace('/\s+/u', '-', trim($cleanTag));
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

            if (count($tagIdsToSync) > 0) {
                $content->tags()->syncWithoutDetaching($tagIdsToSync);
            }

            // 9. จัดการรูปภาพ Gallery (ถ้ามี)
            // เช็คว่ามีตาราง learning_gallerys ในระบบเก่าหรือไม่
            if (Schema::connection('mysql_old')->hasTable('learning_gallerys')) {
                $newGalleryPath = storage_path('app/public/contents/galleries/' . $content->id);
                
                $oldGalleries = DB::connection('mysql_old')
                    ->table('learning_gallerys')
                    ->where('learning_id', $old->id) 
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
        $this->info('✅ Learnings Data, Tags, Base64 Extraction, and Files Migration Completed!');
        return 0;
    }
}