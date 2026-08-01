<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- 1. แผนผังหน้าแรกหลักองค์กร -->
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- 2. ผังลูปดึงข้อมูลหน้าข่าวสารและกิจกรรมทางวัฒนธรรมทั้งหมด (Dynamic Articles Node) -->
    @foreach ($contents as $content)
    <url>
        <loc>{{ route('contents.show', $content->slug) }}</loc>
        <lastmod>{{ $content->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    <!-- 3. ผังลูปดึงหมวดหมู่เนื้อหาหลักหลัก (Dynamic Categories Node) -->
    @foreach ($categories as $category)
    <url>
        <loc>{{ route('categories.view', $category->slug) }}</loc>
        <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    @endforeach
</urlset>