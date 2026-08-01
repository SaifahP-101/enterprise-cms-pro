<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use App\Models\ContentGallery;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run()
    {
        // 🔒 1. ปิด Foreign Key ป้องกัน Truncate Error
        Schema::disableForeignKeyConstraints();
        DB::table('content_tag')->truncate();
        ContentGallery::truncate();
        Content::truncate();
        Schema::enableForeignKeyConstraints();

        // 👤 2. หาไอดีแอดมินผู้สร้าง
        $user = User::where('is_admin', true)->first() ?? User::factory()->create(['is_admin' => true, 'name' => 'System Admin']);
        $tags = Tag::all();

        // 🗂️ 3. ดึง Category ทั้ง 14 รายการมาวนลูปสร้าง Content
        $categories = Category::all();

        foreach ($categories as $index => $category) {
            
            // 💡 กำหนดจำนวนบทความต่อหมวดหมู่ (จำลองหมวดละ 2-3 บทความ)
            $contentCount = rand(2, 3);

            for ($i = 1; $i <= $contentCount; $i++) {
                
                // 🔄 4. วิเคราะห์ Type จาก Slug ของ Category (รองรับ PHP 7.3/8.0 ไม่ใช้ match)
                $type = 'NEWS';
                $slug = $category->slug;
                
                if (strpos($slug, 'activities') !== false || strpos($slug, 'hall') !== false) {
                    $type = 'ACTIVITY';
                } elseif (strpos($slug, 'research') !== false || strpos($slug, 'studies') !== false) {
                    $type = 'RESEARCH';
                } elseif (strpos($slug, 'books') !== false || strpos($slug, 'newsletters') !== false) {
                    $type = 'PUBLICATION';
                } elseif (strpos($slug, 'learning') !== false) {
                    $type = 'LEARNING_RESOURCE';
                } elseif (strpos($slug, 'quality') !== false || strpos($slug, 'letters') !== false || strpos($slug, 'public') !== false) {
                    $type = 'ANNOUNCEMENT';
                }

                // 📐 5. ตรวจสอบสัดส่วนรูปภาพ เพื่อดึง Image Placeholder ให้ตรงไซส์ (เช่น 800x1200 หรือ 1200x630)
                $imageWidth = 1200;
                $imageHeight = 630;
                
                // ถ้าระบุใน Seeder หรือเช็คจาก Type ว่าเป็นวารสาร/งานวิจัย
                if (in_array($type, ['PUBLICATION', 'RESEARCH']) || strpos($slug, 'studies') !== false) {
                    $imageWidth = 800;
                    $imageHeight = 1200;
                }

                // ดึงรูปภาพทดสอบความละเอียดสูง จาก Lorem Picsum อิงตามขนาด
                $imageId = rand(10, 300);
                $coverImageUrl = "https://picsum.photos/id/{$imageId}/{$imageWidth}/{$imageHeight}";
                
                // 📝 6. สร้างชุดข้อความจำลองสมจริง (Realistic Dummy Data)
                $title = "จำลองข้อมูล{$category->name} รายการที่ {$i} ประจำปีการศึกษา " . (date('Y') + 543);
                $bodyHtml = "<p>นี่คือเนื้อหาจำลองสำหรับหมวดหมู่ <strong>{$category->name}</strong> จัดทำขึ้นเพื่อทดสอบระบบ Enterprise CMS การสตรีมมิ่งไฟล์ PDF และการแสดงผลแกลเลอรีรูปภาพบนหน้าเว็บไซต์หลักของ สำนักศิลปะและวัฒนธรรม มหาวิทยาลัยราชภัฏเทพสตรี</p><ul><li>ทดสอบการแสดงผล CKEditor 5</li><li>การจัดวางโครงสร้าง Bootstrap 5</li></ul>";

                $content = Content::create([
                    'category_id'      => $category->id,
                    'user_id'          => $user->id,
                    'title'            => $title,
                    // slug จะเจนอัตโนมัติจาก Model Boot Event
                    'type'             => $type, 
                    'body'             => $bodyHtml,
                    'cover_image'      => $coverImageUrl,
                    'secure_pdf_path'  => ($type == 'PUBLICATION' || $type == 'RESEARCH') ? 'secure_vault/dummy_document.pdf' : null,
                    'youtube_url'      => ($type == 'ACTIVITY') ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
                    'view_count'       => rand(150, 4500), // สุ่มตัวเลขยอดฮิต
                    'share_count'      => rand(10, 350),
                    'meta_title'       => $title . ' | มรภ.เทพสตรี',
                    'meta_description' => strip_tags($bodyHtml),
                    'is_active'        => true,
                    // สุ่มเวลา Published ย้อนหลังภายใน 30 วัน เพื่อทดสอบ Order By
                    'published_at'     => Carbon::now()->subDays(rand(0, 30))->subHours(rand(1, 24)), 
                ]);

                // 🔗 7. ผูกความสัมพันธ์ Many-to-Many เข้ากับตาราง Tags (สุ่ม 2-3 แท็ก)
                if ($tags->count() > 0) {
                    $randomTags = $tags->random(rand(1, 3))->pluck('id')->toArray();
                    $content->tags()->attach($randomTags);
                }

                // 📸 8. หากเป็นภาพกิจกรรม ให้สร้าง Gallery ทดสอบพ่วงเข้าไป (One-to-Many)
                if ($type === 'ACTIVITY' || $type === 'LEARNING_RESOURCE') {
                    for ($g = 1; $g <= rand(3, 6); $g++) {
                        $galleryId = rand(300, 500);
                        ContentGallery::create([
                            'content_id' => $content->id,
                            'file_path'  => "https://picsum.photos/id/{$galleryId}/1200/800", // ภาพแกลเลอรีสัดส่วน 3:2
                            'sort_order' => $g,
                        ]);
                    }
                }
            }
        }
    }
}