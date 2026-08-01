<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slideshow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SlideshowController extends Controller
{
    public function index()
    {
        // โหลดข้อมูลทั้งหมดเรียงตามลำดับเพื่อเข้าหน้าจัดการหลังบ้าน
        $slideshows = Slideshow::orderBy('sort_order', 'asc')->get();
        return view('admin.slideshows.index', compact('slideshows'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_path' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096', // ล็อกความจุ 4MB
            'title'      => 'nullable|string|max:255',
            'link_url'   => 'nullable|url|max:255',
        ]);

        $data = $request->except('image_path');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('slideshows', 'public');
        }

        // หากไม่ระบุลำดับ ให้คำนวณไปต่อท้ายสุดอัตโนมัติ
        if (empty($data['sort_order'])) {
            $data['sort_order'] = Slideshow::max('sort_order') + 1;
        }

        Slideshow::create($data);
        return redirect()->route('admin.slideshows.index')->with('success', 'บันทึกสไลด์โชว์สำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'title'      => 'nullable|string|max:255',
            'link_url'   => 'nullable|url|max:255',
        ]);

        $slideshow = Slideshow::findOrFail($id);
        $data = $request->except('image_path');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image_path')) {
            // 🗑️ ทำลายไฟล์ขยะเก่าออกจาก Hard Drive ก่อนเซฟไฟล์ใหม่
            if (Storage::disk('public')->exists($slideshow->image_path)) {
                Storage::disk('public')->delete($slideshow->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('slideshows', 'public');
        }

        $slideshow->update($data);
        return redirect()->route('admin.slideshows.index')->with('success', 'อัปเดตสไลด์โชว์สำเร็จ');
    }

    public function destroy($id)
    {
        $slideshow = Slideshow::findOrFail($id);
        
        if (Storage::disk('public')->exists($slideshow->image_path)) {
            Storage::disk('public')->delete($slideshow->image_path);
        }
        
        $slideshow->delete();
        return redirect()->route('admin.slideshows.index')->with('success', 'ลบสไลด์โชว์ถาวรสำเร็จ');
    }
}