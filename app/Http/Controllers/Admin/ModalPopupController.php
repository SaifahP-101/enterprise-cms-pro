<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModalPopup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModalPopupController extends Controller
{
    public function index()
    {
        $popups = ModalPopup::orderBy('created_at', 'desc')->get();
        return view('admin.modal_popups.index', compact('popups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'link_url'   => 'nullable|url|max:255',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $data = $request->except('image_path');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('popups', 'public');
        }

        // ควบคุมตรรกะระบบ: ระบบนี้จะอนุญาตให้มีป๊อปอัป "Active" ได้เพียง 1 ตัวในเวลาเดียวกัน (เพื่อไม่ให้รบกวน UX ของผู้เยี่ยมชม)
        if ($data['is_active']) {
            ModalPopup::where('is_active', true)->update(['is_active' => false]);
        }

        ModalPopup::create($data);
        return redirect()->route('admin.modal-popups.index')->with('success', 'บันทึกป๊อปอัปแจ้งเตือนสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'link_url'   => 'nullable|url|max:255',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $popup = ModalPopup::findOrFail($id);
        $data = $request->except('image_path');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image_path')) {
            if (Storage::disk('public')->exists($popup->image_path)) {
                Storage::disk('public')->delete($popup->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('popups', 'public');
        }

        if ($data['is_active']) {
            // ปิดการใช้งานป๊อปอัปตัวอื่นๆ หากตัวนี้ถูกตั้งให้ Active (Single Point of Truth)
            ModalPopup::where('id', '!=', $id)->update(['is_active' => false]);
        }

        $popup->update($data);
        return redirect()->route('admin.modal-popups.index')->with('success', 'อัปเดตป๊อปอัปแจ้งเตือนสำเร็จ');
    }

    public function destroy($id)
    {
        $popup = ModalPopup::findOrFail($id);
        if (Storage::disk('public')->exists($popup->image_path)) {
            Storage::disk('public')->delete($popup->image_path);
        }
        $popup->delete();
        return redirect()->route('admin.modal-popups.index')->with('success', 'ลบป๊อปอัปถาวรสำเร็จ');
    }
}