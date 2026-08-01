<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::query()->delete();

        // 👑 1. บัญชี Super Admin (สิทธิ์สูงสุด)
        User::create([
            'name'      => 'ผู้ดูแลระบบ สำนักศิลปะฯ',
            'email'     => 'admin@tru.ac.th',
            'password'  => Hash::make('password'), // รหัสผ่านคือ password
            'is_admin'  => true,
        ]);

        // 👤 2. บัญชีผู้ใช้งานทั่วไป (ทดสอบการโดนดีดออกจากหลังบ้าน)
        User::create([
            'name'      => 'บุคลากรทั่วไป',
            'email'     => 'user@tru.ac.th',
            'password'  => Hash::make('password'),
            'is_admin'  => false,
        ]);
    }
}