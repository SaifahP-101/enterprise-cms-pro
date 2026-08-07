<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * รันกระบวนการ Seed ข้อมูลบทบาท สิทธิ์การใช้งาน และผู้ดูแลระบบหลัก
     *
     * @return void
     */
    public function run()
    {
        // ---------------------------------------------------------------------
        // 1. รายการสิทธิ์การใช้งานทั้งหมด (Permissions Array grouped by Module)
        // ---------------------------------------------------------------------
        $permissions = [
            // 👥 โมดูลจัดการสมาชิกและผู้ดูแลระบบ
            [
                'name'        => 'จัดการผู้ใช้งานในระบบ',
                'slug'        => 'manage_users',
                'module'      => 'users',
                'description' => 'สามารถเพิ่ม แก้ไข และลบข้อมูลผู้ใช้งานหลังบ้านได้'
            ],
            // 🔐 โมดูลจัดการบทบาทและสิทธิ์ (RBAC)
            [
                'name'        => 'จัดการบทบาทและสิทธิ์การใช้งาน',
                'slug'        => 'manage_roles',
                'module'      => 'roles',
                'description' => 'สามารถสร้างบทบาท กำหนดสิทธิ์ และผูก Permission ได้'
            ],
            // 🛡️ โมดูลตรวจสอบประวัติการทำงาน (Audit Logs)
            [
                'name'        => 'ดูประวัติบันทึกการทำงาน (Audit Logs)',
                'slug'        => 'view_audit_logs',
                'module'      => 'audit_logs',
                'description' => 'สามารถเข้าดูประวัติการเปลี่ยนแปลงข้อมูลของแอดมินและยกเลิกสิทธิ์ได้'
            ],
            // 🌿 โมดูลจัดการเมนูหลังบ้าน/หน้าบ้าน
            [
                'name'        => 'จัดการโครงสร้างเมนู',
                'slug'        => 'manage_menus',
                'module'      => 'menus',
                'description' => 'สามารถจัดเรียง เพิ่ม แก้ไข และลบเมนูแบบแตกกิ่งได้'
            ],
            // 🗂️ โมดูลจัดการหมวดหมู่สารสนเทศ
            [
                'name'        => 'จัดการหมวดหมู่ข่าวสารและบทความ',
                'slug'        => 'manage_categories',
                'module'      => 'categories',
                'description' => 'สามารถเพิ่ม แก้ไข และกำหนดสเปกรูปภาพประจำหมวดหมู่ได้'
            ],
            // 📝 โมดูลจัดการคลังบทความหลัก
            [
                'name'        => 'จัดการบทความและข่าวสาร',
                'slug'        => 'manage_contents',
                'module'      => 'contents',
                'description' => 'สามารถสร้าง แก้ไข เผยแพร่ และจัดการไฟล์ PDF ลับได้'
            ],
            [
                'name'        => 'ลบและทำลายบทความถาวร',
                'slug'        => 'delete_contents',
                'module'      => 'contents',
                'description' => 'สามารถย้ายบทความลงถังขยะและลบไฟล์ออกจากดิสก์ถาวรได้'
            ],
            // 📸 โมดูลจัดการมีเดียแกลเลอรี
            [
                'name'        => 'จัดการคลังภาพแกลเลอรี (Dropzone)',
                'slug'        => 'manage_media',
                'module'      => 'media',
                'description' => 'สามารถอัปโหลดภาพแกลเลอรีแบบกลุ่มและลบรูปภาพประกอบได้'
            ],
            // 🖼️ โมดูลจัดการองค์ประกอบหน้าแรก (Slideshow, Popup, Video, Event)
            [
                'name'        => 'จัดการแบนเนอร์และองค์ประกอบหน้าแรก',
                'slug'        => 'manage_components',
                'module'      => 'components',
                'description' => 'จัดการสไลด์โชว์ ป๊อปอัปแจ้งเตือน วิดีโอเด่น และปฏิทินกิจกรรม'
            ],
            // 🔖 โมดูลจัดการแท็กคีย์เวิร์ด
            [
                'name'        => 'จัดการแท็กคีย์เวิร์ด (Tags)',
                'slug'        => 'manage_tags',
                'module'      => 'tags',
                'description' => 'สามารถเพิ่ม แก้ไข และลบแท็กคีย์เวิร์ดสืบค้นได้'
            ],
            // 💻 โมดูลจัดการหน้าเพจอิสระ
            [
                'name'        => 'จัดการหน้าเพจอิสระ (Static Pages)',
                'slug'        => 'manage_pages',
                'module'      => 'pages',
                'description' => 'สามารถสร้างและแก้ไขเนื้อหาหน้าเพจอิสระได้'
            ],
            // 📩 โมดูลจัดการข้อร้องเรียนและข้อเสนอแนะ
            [
                'name'        => 'ดูและจัดการข้อร้องเรียน/ข้อเสนอแนะ',
                'slug'        => 'view_feedbacks',
                'module'      => 'feedbacks',
                'description' => 'สามารถเข้าอ่าน เปลี่ยนสถานะ และลบข้อร้องเรียนจากหน้าบ้านได้'
            ],
            // จัดการระบบ Offsite Backup (Google Drive OAuth 2.0)
            [
                'name'        => 'จัดการระบบ Offsite Backup',
                'slug'        => 'manage_backup',
                'module'      => 'backup',
                'description' => 'สามารถจัดการการสำรองข้อมูลไปยัง Google Drive ได้'
            ],
            // จัดการระบบลงทะเบียนยืมอุปกรณ์และครุภัณฑ์
            [
                'name'        => 'จัดการระบบลงทะเบียนยืมอุปกรณ์และครุภัณฑ์',
                'slug'        => 'manage_borrows',
                'module'      => 'borrows',
                'description' => 'สามารถจัดการการยืมได้',
            ],
        ];

        // บันทึกรายการ Permissions ลงตาราง
        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['slug']] = Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                [
                    'name'        => $perm['name'],
                    'module'      => $perm['module'],
                    'description' => $perm['description'],
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 2. สร้างบทบาทการทำงานหลัก (Roles) และผูกสิทธิ์ (Sync Permissions)
        // ---------------------------------------------------------------------

        // 👑 Role 1: Super Admin (ผู้ดูแลระบบสูงสุด - ได้สิทธิ์ทั้งหมดในระบบ)
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super_admin'],
            [
                'name'        => 'ผู้ดูแลระบบสูงสุด (Super Admin)',
                'description' => 'เข้าถึงและควบคุมการทำงานได้ทุกโมดูลในระบบ Enterprise CMS',
            ]
        );
        $allPermissionIds = collect($permissionModels)->pluck('id')->toArray();
        $superAdminRole->permissions()->sync($allPermissionIds);

        // 📝 Role 2: Content Editor (ผู้บรรณาธิการเนื้อหา)
        $contentEditorRole = Role::firstOrCreate(
            ['slug' => 'content_editor'],
            [
                'name'        => 'ผู้บรรณาธิการเนื้อหา (Content Editor)',
                'description' => 'จัดการคลังบทความ หมวดหมู่ มีเดีย แท็ก หน้าเพจ และองค์ประกอบหน้าแรก',
            ]
        );
        $editorPermSlugs = [
            'manage_categories', 'manage_contents', 'delete_contents',
            'manage_media', 'manage_components', 'manage_tags', 'manage_pages'
        ];
        $editorPermIds = collect($permissionModels)->only($editorPermSlugs)->pluck('id')->toArray();
        $contentEditorRole->permissions()->sync($editorPermIds);

        // 📢 Role 3: PR Officer (เจ้าหน้าที่ประชาสัมพันธ์)
        $prOfficerRole = Role::firstOrCreate(
            ['slug' => 'pr_officer'],
            [
                'name'        => 'เจ้าหน้าที่ประชาสัมพันธ์ (PR Officer)',
                'description' => 'เน้นการลงข่าวสาร กิจกรรม อัปโหลดภาพแกลเลอรี และสไลด์โชว์หน้าแรก',
            ]
        );
        $prPermSlugs = ['manage_contents', 'manage_media', 'manage_components', 'manage_tags', 'view_feedbacks'];
        $prPermIds = collect($permissionModels)->only($prPermSlugs)->pluck('id')->toArray();
        $prOfficerRole->permissions()->sync($prPermIds);

        // 🛡️ Role 4: Auditor (ผู้ตรวจสอบระบบ)
        $auditorRole = Role::firstOrCreate(
            ['slug' => 'auditor'],
            [
                'name'        => 'ผู้ตรวจสอบระบบ (Auditor)',
                'description' => 'เข้าถึงประวัติบันทึกการทำงาน (Audit Logs) และตรวจรับข้อเสนอแนะองค์กร',
            ]
        );
        $auditorPermSlugs = ['view_audit_logs', 'view_feedbacks'];
        $auditorPermIds = collect($permissionModels)->only($auditorPermSlugs)->pluck('id')->toArray();
        $auditorRole->permissions()->sync($auditorPermIds);

        // ---------------------------------------------------------------------
        // 3. สร้างผู้ดูแลระบบตั้งต้น (Default Super Admin User) และมอบ Role
        // ---------------------------------------------------------------------
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@tru.ac.th'],
            [
                'name'              => 'ผู้ดูแลระบบ สำนักศิลปะฯ',
                'password'          => Hash::make('Admin@TRU2026!'), // 🔐 รหัสผ่านเริ่มต้น
                'is_admin'          => true,
                'email_verified_at' => now(),
            ]
        );

        // ผูก Super Admin Role ให้กับผู้ใช้หลัก
        $adminUser->roles()->sync([$superAdminRole->id]);
    }
}