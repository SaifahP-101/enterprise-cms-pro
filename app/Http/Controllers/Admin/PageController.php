<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('created_at', 'desc')->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
            'secure_pdf' => 'nullable|file|mimes:pdf|max:30720', // ดักจับไม่เกิน 30MB
        ]);

        $data = $request->except('secure_pdf');
        $data['is_active'] = $request->has('is_active');
        $data['slug'] = $request->slug ? Str::slug($request->slug, '-', false) : Str::slug($request->title, '-', false);

        // 🔒 Secure Storage Streaming Shield
        if ($request->hasFile('secure_pdf')) {
            $data['secure_pdf_path'] = $request->file('secure_pdf')->store('secure_documents');
        }

        Page::create($data);
        return redirect()->route('admin.pages.index')->with('success', 'จัดสร้างหน้าเพจอิสระสำเร็จ');
    }

    public function edit($id)
    {
        $page = Page::findOrFail($id);
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
            'secure_pdf' => 'nullable|file|mimes:pdf|max:30720',
        ]);

        $page = Page::findOrFail($id);
        $data = $request->except('secure_pdf');
        $data['is_active'] = $request->has('is_active');
        $data['slug'] = $request->slug ? Str::slug($request->slug, '-', false) : Str::slug($request->title, '-', false);

        if ($request->hasFile('secure_pdf')) {
            if ($page->secure_pdf_path) { Storage::delete($page->secure_pdf_path); }
            $data['secure_pdf_path'] = $request->file('secure_pdf')->store('secure_documents');
        }

        $page->update($data);
        return redirect()->route('admin.pages.index')->with('success', 'ปรับปรุงหน้าเพจอิสระสำเร็จ');
    }

    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->delete(); // ทำการลบแบบ Soft Deletes ข้อมูลไม่หายจากฐานข้อมูล
        return redirect()->route('admin.pages.index')->with('success', 'ย้ายหน้าเพจลงสู่ถังขยะเรียบร้อย');
    }
}