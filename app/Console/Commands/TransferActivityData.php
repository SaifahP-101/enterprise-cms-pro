<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Content;
use App\Models\Tag;
use App\Models\ContentGallery;
use Carbon\Carbon;

class TransferActivityData extends Command
{
    protected $signature = 'cms:transfer-activities';
    protected $description = 'Transfer activity data and copy physical files from old CMS to new Enterprise CMS';

    public function handle()
    {
        $this->info('🚀 Starting Data & Physical Files Transfer...');

        // กำหนด Path ต้นทางและปลายทาง (อ้างอิงตาม Requirements)
        $oldBasePath = public_path('old/images/activity');
        $oldBasePathPDF = public_path('old/images/activity/pdf');
        $oldBasePathGallery = public_path('old/images/activity/gallery');
        $oldBasePathAPP = public_path('old/app');
        
        $newCoverPath = storage_path('app/public/contents/covers');
        $newSecurePdfPath = storage_path('app/secure_docs');

        // สร้าง Directory ปลายทางหากยังไม่มี
        File::ensureDirectoryExists($newCoverPath, 0755, true);
        File::ensureDirectoryExists($newSecurePdfPath, 0755, true);
 
        // 2. ดึงข้อมูลจากฐานข้อมูลเก่า DB 1[cite: 1]
        $oldActivities = DB::connection('mysql_old')->table('activitys')->get();
        $bar = $this->output->createProgressBar(count($oldActivities));

        foreach ($oldActivities as $old) {
            // 3. จัดการเนื้อหาจากไฟล์ .text ให้กลายเป็น LongText[cite: 1]
            $bodyContent = '';
            if (!empty($old->activity_file_text)) {
                $textPath = $oldBasePathAPP . '/' . $old->activity_file_text;
                if (File::exists($textPath)) {
                    $bodyContent = File::get($textPath);
                }
            }

            // 4. คัดลอกไฟล์รูปภาพ Cover[cite: 1, 2]
            $coverImageValue = null;
            if (!empty($old->activity_image_desktop)) {
                $sourceCover = $oldBasePath . '/' . $old->activity_image_desktop;
                if (File::exists($sourceCover)) {
                    File::copy($sourceCover, $newCoverPath . '/' . $old->activity_image_desktop);
                    $coverImageValue = 'contents/covers/' . $old->activity_image_desktop;
                }
            }

            // 5. คัดลอกไฟล์เอกสาร PDF ลง Secure Storage[cite: 1, 2]
            $pdfPathValue = null;
            if (!empty($old->file_pdf)) {
                $sourcePdf = $oldBasePathPDF . '/' . $old->file_pdf;
                if (File::exists($sourcePdf)) {
                    File::copy($sourcePdf, $newSecurePdfPath . '/' . $old->file_pdf);
                    $pdfPathValue = 'secure_docs/' . $old->file_pdf;
                }
            }

            // 6. ทำการ Mapping ข้อมูลจาก DB 1 ไป DB 2[cite: 1, 2]
            $content = Content::create([
                'category_id'      => 2,
                'title'            => strip_tags($old->activity_title), // XSS Prevention
                'slug'             => $old->activity_slug ?? Str::slug($old->activity_title . '-' . $old->id),
                'type'             => 'ACTIVITY',
                'body'             => $bodyContent,
                'cover_image'      => $coverImageValue,
                'secure_pdf_path'  => $pdfPathValue,
                'view_count'       => $old->count_view ?? 0,
                'download_count'   => 0,
                'meta_title'       => $old->activity_meta_title,
                'meta_description' => $old->activity_meta_description ?? strip_tags($old->activity_intro),
                'user_id'          => 1, // ผูกกับ Super Admin ปัจจุบัน
                'is_active'        => ($old->activity_status == 1) ? 1 : 0,
                'published_at'     => $old->activity_date ? Carbon::parse($old->activity_date) : now(),
                'created_at'       => $old->created_at,
                'updated_at'       => $old->updated_at,
            ]);

            // 7. จัดการข้อมูล Tags จากฟิลด์ activity_year[cite: 1, 2]
            if (!empty($old->activity_year)) {
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug('ปี-' . $old->activity_year)],
                    ['name' => 'ปี ' . $old->activity_year]
                );
                $content->tags()->syncWithoutDetaching([$tag->id]);
            }

            // 8. จัดการรูปภาพ Gallery (One-to-Many)[cite: 1, 2]
            $newGalleryPath = storage_path('app/public/contents/galleries/' . $content->id);
            File::ensureDirectoryExists($newGalleryPath, 0755, true);

            $oldGalleries = DB::connection('mysql_old')
                ->table('activity_gallerys')
                ->where('activity_id', $old->id)
                ->get();

            foreach ($oldGalleries as $index => $gallery) {
                if (!empty($gallery->image_desktop)) {
                    $sourceGallery = $oldBasePathGallery . '/' . $gallery->image_desktop;
                    if (File::exists($sourceGallery)) {
                        File::copy($sourceGallery, $newGalleryPath . '/' . $gallery->image_desktop);
                        
                        ContentGallery::create([
                            'content_id' => $content->id,
                            'file_path'  => 'contents/galleries/' . $content->id . '/' . $gallery->image_desktop,
                            'sort_order' => $index + 1,
                            'created_at' => $gallery->created_at,
                            'updated_at' => $gallery->updated_at,
                        ]);
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Data and Files Migration Completed Successfully!');
    }
}