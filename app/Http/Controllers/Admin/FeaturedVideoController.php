<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeaturedVideoController extends Controller
{
    /**
     * แสดงรายการวิดีโอแนะนำหลังบ้าน
     */
    public function index()
    {
        $videos = FeaturedVideo::orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.featured_videos.index', compact('videos'));
    }

    /**
     * บันทึกข้อมูลวิดีโอใหม่
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'youtube_url'      => 'required|url',
            'description'      => 'nullable|string',
            'custom_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order'       => 'nullable|integer',
        ], [
            'title.required'       => 'กรุณากรอกหัวข้อวิดีโอแนะนำ',
            'youtube_url.required' => 'กรุณากรอก URL วิดีโอ YouTube',
            'youtube_url.url'      => 'รูปแบบ URL ไม่ถูกต้อง',
            'custom_thumbnail.max' => 'ขนาดรูปภาพปกต้องไม่เกิน 2MB',
        ]);

        $data = $request->only(['title', 'youtube_url', 'description', 'sort_order']);
        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $request->input('sort_order', 0);

        // จัดการอัปโหลดรูปภาพปกกำหนดเอง (Custom Thumbnail)
        if ($request->hasFile('custom_thumbnail')) {
            $path = $request->file('custom_thumbnail')->store('featured_videos', 'public');
            $data['custom_thumbnail'] = $path;
        }

        FeaturedVideo::create($data);

        return redirect()->route('admin.featured-videos.index')
            ->with('success', 'บันทึกข้อมูลวิดีโอแนะนำเรียบร้อยแล้ว');
    }

    /**
     * อัปเดตข้อมูลวิดีโอ
     */
    public function update(Request $request, $id)
    {
        $video = FeaturedVideo::findOrFail($id);

        $request->validate([
            'title'            => 'required|string|max:255',
            'youtube_url'      => 'required|url',
            'description'      => 'nullable|string',
            'custom_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order'       => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'youtube_url', 'description', 'sort_order']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('custom_thumbnail')) {
            // ลบรูปภาพเดิมออกเพื่อประหยัด Disk Space
            if ($video->custom_thumbnail && Storage::disk('public')->exists($video->custom_thumbnail)) {
                Storage::disk('public')->delete($video->custom_thumbnail);
            }
            $path = $request->file('custom_thumbnail')->store('featured_videos', 'public');
            $data['custom_thumbnail'] = $path;
        }

        $video->update($data);

        return redirect()->route('admin.featured-videos.index')
            ->with('success', 'อัปเดตข้อมูลวิดีโอแนะนำเรียบร้อยแล้ว');
    }

    /**
     * ลบวิดีโอ (Soft Delete)
     */
    public function destroy($id)
    {
        $video = FeaturedVideo::findOrFail($id);
        $video->delete();

        return redirect()->route('admin.featured-videos.index')
            ->with('success', 'ลบรายการวิดีโอแนะนำเรียบร้อยแล้ว');
    }
}