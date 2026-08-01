<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index()
    {
        $events = CalendarEvent::orderBy('event_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(15);

        return view('admin.calendar_events.index', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'event_date' => 'required|date',
        ]);

        $data = $request->only(['title', 'description', 'event_date', 'start_time', 'end_time', 'location']);
        $data['is_active'] = $request->has('is_active');

        CalendarEvent::create($data);

        return redirect()->route('admin.calendar-events.index')->with('success', 'เพิ่มกิจกรรมลงปฏิทินเรียบร้อย');
    }

    public function update(Request $request, $id)
    {
        $event = CalendarEvent::findOrFail($id);

        $request->validate([
            'title'      => 'required|string|max:255',
            'event_date' => 'required|date',
        ]);

        $data = $request->only(['title', 'description', 'event_date', 'start_time', 'end_time', 'location']);
        $data['is_active'] = $request->has('is_active');

        $event->update($data);

        return redirect()->route('admin.calendar-events.index')->with('success', 'อัปเดตกิจกรรมเรียบร้อย');
    }

    public function destroy($id)
    {
        CalendarEvent::findOrFail($id)->delete();
        return redirect()->route('admin.calendar-events.index')->with('success', 'ลบกิจกรรมเรียบร้อย');
    }
}