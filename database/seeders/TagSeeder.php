<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run()
    {
        Tag::query()->delete();

        $tags = ['ภูมิปัญญาลพบุรี', 'นิทรรศการ', 'บริการวิชาการ', 'ศิลปวัฒนธรรม', 'ราชภัฏเทพสตรี'];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag,
                'slug' => Str::slug($tag)
            ]);
        }
    }
}