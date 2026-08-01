<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache; 
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
 
/**
 * ⚡ CRITICAL FIX: ต้องเพิ่มบรรทัดนำเข้า (Import) Namespace ของ MenuComposer ให้ถูกต้อง 
 * เพื่อป้องกันไม่ให้ PHP Engine สับสนคลาสข้ามโฟลเดอร์กับตู้ Providers หลัก
 */
use App\Http\View\Composers\MenuComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // แชร์ตัวแปร $navigationTree ไปยังทุก View ที่ใช้งานระบบ
        View::composer('*', function ($view) {
            $navigationTree = Cache::remember('frontend_navigation_tree', now()->addDays(7), function () {
                return Menu::whereNull('parent_id')
                    ->where('is_active', true)
                    ->with(['children' => function ($query) {
                        $query->where('is_active', true)->orderBy('sort_order', 'asc');
                    }])
                    ->orderBy('sort_order', 'asc')
                    ->get();
            });

            $view->with('navigationTree', $navigationTree);
        });

        /**
         * 🌿 สั่งฉีดพ่นตัวแปรผังเมนูแตกกิ่งจากความทรงจำ RAM (Cache Layer) 
         * เข้าสู่โครงสร้าง Layout หน้าร้านสาธารณะผ่านระบบอินทิเกรต View Composer
         */
        View::composer('layouts.frontend', MenuComposer::class); 
    }
}