<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Content;
use App\Models\ContentGallery;
use Carbon\Carbon;

class TransferActiconservationData extends Command
{
    /**
     * The name and signature of the console command.
     * เพิ่ม Option --fresh สำหรับเคลียร์ข้อมูลเก่าเฉพาะหมวดหมู่นี้
     *
     * @var string
     */
    protected $signature = 'cms:transfer-acticonservations {--fresh : ล้างข้อมูลกิจกรรมหน่วยอนุรักษ์ฯ เดิมในระบบใหม่ทิ้งก่อนดึงข้อมูล}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer acticonservations data and copy physical files from old CMS to new Enterprise CMS (Category 3)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $contentIds = Content::where('category_id', 3)->pluck('id');

        $this->info('🚀 Starting Data & Physical Files Transfer for Acticonservations...');

        // 2. กำหนด Path ต้นทางและปลายทาง 
        // (กรุณาปรับโฟลเดอร์ต้นทาง oldBasePath ให้ตรงกับโครงสร้างจริงของระบบเก่า)
        $oldBasePath        = public_path('old/images/acticonservation');
        $oldBasePathPDF     = public_path('old/images/acticonservation/pdf');
        $oldBasePathGallery = public_path('old/images/acticonservation/gallery');
        $oldBasePathAPP     = public_path('old/app'); // Path ที่เก็บไฟล์ .text[cite: 1]
        
        $newCoverPath     = storage_path('app/public/contents/covers');
        $newSecurePdfPath = storage_path('app/secure_docs');

        // สร้าง Directory ปลายทางหากยังไม่มี
        File::ensureDirectoryExists($newCoverPath, 0755, true);
        File::ensureDirectoryExists($newSecurePdfPath, 0755, true);
 
        // 3. ดึงข้อมูลจากฐานข้อมูลเก่า DB 1 (ตาราง acticonservations)[cite: 1]
        $oldRecords = DB::connection('mysql_old')->table('acticonservations')->get();
        
        if ($oldRecords->isEmpty()) {
            $this->warn('⚠️ No records found in acticonservations table.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($oldRecords));

        foreach ($oldRecords as $old) {
            // 4. จัดการเนื้อหาจากไฟล์ .text ให้กลายเป็น LongText[cite: 1]
            $bodyContent = '';
            if (!empty($old->file_text)) {
                $textPath = $oldBasePathAPP . '/' . $old->file_text;
                if (File::exists($textPath)) {
                    $bodyContent = File::get($textPath);
                }
            }

            // 5. คัดลอกไฟล์รูปภาพ Cover[cite: 1, 2]
            $coverImageValue = null;
            if (!empty($old->image_desktop)) {
                $sourceCover = $oldBasePath . '/' . $old->image_desktop;
                if (File::exists($sourceCover)) {
                    File::copy($sourceCover, $newCoverPath . '/' . $old->image_desktop);
                    $coverImageValue = 'contents/covers/' . $old->image_desktop;
                }
            }

            // 6. คัดลอกไฟล์เอกสาร PDF ลง Secure Storage (Streaming)[cite: 1, 2]
            $pdfPathValue = null;
            if (!empty($old->file_pdf)) {
                $sourcePdf = $oldBasePathPDF . '/' . $old->file_pdf;
                if (File::exists($sourcePdf)) {
                    File::copy($sourcePdf, $newSecurePdfPath . '/' . $old->file_pdf);
                    $pdfPathValue = 'secure_docs/' . $old->file_pdf;
                }
            }

            // 7. ทำการ Mapping ข้อมูลจาก DB 1 ไป DB 2[cite: 1, 2]
            $content = Content::create([
                'category_id'      => 3, // บังคับลง หมวดหมู่ กิจกรรมหน่วยอนุรักษ์ฯ
                'title'            => strip_tags($old->title), // XSS Prevention
                'slug'             => $old->slug ?? Str::slug($old->title . '-' . $old->id),
                'type'             => 'ACTIVITY',
                'body'             => $bodyContent,
                'cover_image'      => $coverImageValue,
                'secure_pdf_path'  => $pdfPathValue,
                'view_count'       => $old->count_view ?? 0,
                'download_count'   => 0,
                'meta_title'       => $old->meta_title,
                'meta_description' => $old->meta_description ?? strip_tags($old->intro),
                'user_id'          => 1, // ผูกกับ Super Admin ปัจจุบัน
                'is_active'        => ($old->status == 1) ? 1 : 0, // สลับสถานะ 1=public
                'published_at'     => $old->date ? Carbon::parse($old->date) : now(),
                'created_at'       => $old->created_at,
                'updated_at'       => $old->updated_at,
            ]);

            // 8. จัดการรูปภาพ Gallery จากตาราง acticonservation_gallerys (One-to-Many)[cite: 1, 2]
            $newGalleryPath = storage_path('app/public/contents/galleries/' . $content->id);
            
            $oldGalleries = DB::connection('mysql_old')
                ->table('acticonservation_gallerys')
                ->where('acticonservation_id', $old->id)
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
                                'created_at' => $gallery->created_at,
                                'updated_at' => $gallery->updated_at,
                            ]);
                        }
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Acticonservations Data and Files Migration Completed Successfully!');
        return 0;
    }
}