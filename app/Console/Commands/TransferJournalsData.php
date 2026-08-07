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

class TransferJournalsData extends Command
{
    protected $signature = 'cms:transfer-journals';
    protected $description = 'Transfer journals data, transform base64 images, tags, and files to Enterprise CMS (Category 12)';

    public function handle()
    {
        $this->info('🚀 Starting Data, Tags & Physical Files Transfer for Journals...');

        $oldBasePath        = public_path('old/images/journals');
        $oldBasePathPDF     = public_path('old/images/journals/pdf');
        $oldBasePathGallery = public_path('old/images/journals/gallery');
        $oldBasePathAPP     = public_path('old/app'); 
        
        $newCoverPath     = storage_path('app/public/contents/covers');
        $newSecurePdfPath = storage_path('app/secure_docs');
        $inlineImagePath  = storage_path('app/public/contents/inline_images'); // โฟลเดอร์สำหรับรูปภาพที่สกัดจาก Base64

        File::ensureDirectoryExists($newCoverPath, 0755, true);
        File::ensureDirectoryExists($newSecurePdfPath, 0755, true);
        File::ensureDirectoryExists($inlineImagePath, 0755, true);
 
        $oldRecords = DB::connection('mysql_old')->table('journals')->get();
        
        if ($oldRecords->isEmpty()) {
            $this->warn('⚠️ No records found in journals table.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($oldRecords));

        foreach ($oldRecords as $old) {
            $bodyContent = '';
            if (!empty($old->file_text)) { 
                $textPath = $oldBasePathAPP . '/' . $old->file_text;
                if (File::exists($textPath)) {
                    $bodyContent = File::get($textPath);
                    
                    // 🌟 Enterprise Feature: Extract Base64 Images to Physical Files
                    if (!empty($bodyContent) && strpos($bodyContent, 'data:image') !== false) {
                        $dom = new DOMDocument();
                        // ปิด Error การอ่าน HTML ที่ไม่สมบูรณ์
                        libxml_use_internal_errors(true);
                        $dom->loadHTML(mb_convert_encoding($bodyContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                        libxml_clear_errors();

                        $images = $dom->getElementsByTagName('img');

                        foreach ($images as $img) {
                            $src = $img->getAttribute('src');

                            if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
                                $base64Data = substr($src, strpos($src, ',') + 1);
                                $type = strtolower($type[1]); // png, jpg, gif
                                
                                // ป้องกันนามสกุลไฟล์ผิดปกติ
                                if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                    $type = 'jpg';
                                }

                                $imageName = 'inline_' . Str::random(10) . '_' . time() . '.' . $type;
                                $imagePath = 'contents/inline_images/' . $imageName;

                                // บันทึกไฟล์ลง Disk
                                Storage::disk('public')->put($imagePath, base64_decode($base64Data));

                                // แก้ไข src ใน HTML ให้ชี้ไปที่ URL จริง
                                $img->removeAttribute('src');
                                $img->setAttribute('src', asset('storage/' . $imagePath));
                            }
                        }
                        $bodyContent = $dom->saveHTML();
                    }
                }
            }

            // คัดลอก Cover Image
            $coverImageValue = null;
            if (!empty($old->image_desktop)) {
                $sourceCover = $oldBasePath . '/' . $old->image_desktop;
                if (File::exists($sourceCover)) {
                    File::copy($sourceCover, $newCoverPath . '/' . $old->image_desktop);
                    $coverImageValue = 'contents/covers/' . $old->image_desktop;
                }
            }

            // คัดลอก PDF (Secure Streaming)
            $pdfPathValue = null;
            if (!empty($old->file_pdf)) {
                $sourcePdf = $oldBasePathPDF . '/' . $old->file_pdf;
                if (File::exists($sourcePdf)) {
                    File::copy($sourcePdf, $newSecurePdfPath . '/' . $old->file_pdf);
                    $pdfPathValue = 'secure_docs/' . $old->file_pdf;
                }
            }

            // จัดการ Slug ป้องกัน Error 1062
            $slug = !empty($old->slug) ? $old->slug : null;
            if (!$slug) {
                $cleanTitle = preg_replace('/[^A-Za-z0-9ก-๙\s]/u', '', strip_tags($old->title));
                $thaiSlug = preg_replace('/\s+/u', '-', trim($cleanTitle));
                $slug = mb_substr($thaiSlug ?: 'journal', 0, 100) . '-journal-' . $old->id . '-' . Str::random(4);
            }

            // บันทึกเนื้อหาลง Database
            $content = Content::create([
                'category_id'      => 12,
                'title'            => strip_tags($old->title),
                'slug'             => $slug,
                'type'             => 'PUBLICATION',
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

            // ระบบ Auto-Tags
            $tagIdsToSync = [];
            $tagFields = [
                'ประจำเดือน' => $old->month,
                'คีย์เวิร์ด'  => $old->meta_keyword,
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

            // ระบบ Gallery
            if (Schema::connection('mysql_old')->hasTable('journal_gallerys')) {
                $newGalleryPath = storage_path('app/public/contents/galleries/' . $content->id);
                
                $oldGalleries = DB::connection('mysql_old')
                    ->table('journal_gallerys')
                    ->where('journal_id', $old->id) 
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
        $this->info('✅ Journals Data, Base64 Extraction, Tags, and Files Migration Completed Successfully!');
        return 0;
    }
}