<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRolesAndPermissions
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * ความสัมพันธ์ Many-to-Many กับหมวดหมู่ที่ได้รับอนุญาตให้ดูแล
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Category::class, 'category_user', 'user_id', 'category_id');
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains('slug', $roleSlug);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->is_admin || $this->hasRole('super_admin')) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ⚡ ดึงอาร์เรย์ Category IDs ทั้งหมดที่ User คนนี้มีสิทธิ์เข้าถึง
     */
    public function getAccessibleCategoryIds(): array
    {
        // หากเป็น Super Admin หรือมีสิทธิ์ manage_all_categories ให้เข้าถึงได้ทุกหมวดหมู่
        if ($this->is_admin || $this->hasRole('super_admin') || $this->hasPermission('manage_all_categories')) {
            return Category::where('is_active', true)->pluck('id')->toArray();
        }

        // คืนค่าเฉพาะ Category IDs ที่ถูกผูกไว้ในตาราง category_user
        return $this->categories()->where('is_active', true)->pluck('categories.id')->toArray();
    }

    /**
     * ⚡ ตรวจสอบว่า User มีสิทธิ์จัดการหมวดหมู่เฉพาะ (category_id) นี้หรือไม่
     */
    public function canManageCategory($categoryId): bool
    {
        if (empty($categoryId)) {
            return false;
        }

        if ($this->is_admin || $this->hasRole('super_admin') || $this->hasPermission('manage_all_categories')) {
            return true;
        }

        return in_array((int) $categoryId, $this->getAccessibleCategoryIds());
    }
}