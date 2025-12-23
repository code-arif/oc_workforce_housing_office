<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Team;
use App\Models\Work;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkCalendarController extends Controller
{
    /**
     * Display the calendar view with filters
     */
    public function index(Request $request)
    {
        $teams = Team::all();
        $categories = Category::all();

        // Get filter parameters
        $filters = [
            'team_id' => $request->get('team_id'),
            'category_id' => $request->get('category_id'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        return view('works.calendar', compact('teams', 'categories', 'filters'));
    }

    /**
     * Show create work form
     */
    public function create()
    {
        $teams = Team::all();
        $categories = Category::all();

        return view('works.create', compact('teams', 'categories'));
    }

    /**
     * Store a new work
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'time' => 'required',
            'work_date' => 'required|date',
            'note' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1,2',
            'team_id' => 'nullable|exists:teams,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $work = Work::create($validated);

        // Sync to Google Calendar if requested
        if ($request->has('sync_to_google') && session('google_calendar_token')) {
            app(GoogleCalendarController::class)->syncWorkToCalendar($work->id);
        }

        return redirect()->route('works.index')
            ->with('success', 'Work created successfully');
    }

    /**
     * Show edit work form
     */
    public function edit($id)
    {
        $work = Work::with(['team', 'category'])->findOrFail($id);
        $teams = Team::all();
        $categories = Category::all();

        return view('works.edit', compact('work', 'teams', 'categories'));
    }

    /**
     * Update work
     */
    public function update(Request $request, $id)
    {
        $work = Work::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'time' => 'required',
            'work_date' => 'required|date',
            'note' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1,2',
            'team_id' => 'nullable|exists:teams,id',
            'category_id' => 'nullable|exists:categories,id',
            'is_completed' => 'nullable|boolean',
            'is_rescheduled' => 'nullable|boolean',
        ]);

        $work->update($validated);

        // Update Google Calendar if synced
        if ($work->google_event_id && session('google_calendar_token')) {
            app(GoogleCalendarController::class)->updateCalendarEvent($work->id);
        }

        return redirect()->route('works.index')
            ->with('success', 'Work updated successfully');
    }

    /**
     * Delete work
     */
    public function destroy($id)
    {
        $work = Work::findOrFail($id);

        // Delete from Google Calendar if synced
        if ($work->google_event_id && session('google_calendar_token')) {
            app(GoogleCalendarController::class)->deleteCalendarEvent($work->id);
        }

        $work->delete();

        return redirect()->route('works.index')
            ->with('success', 'Work deleted successfully');
    }

    /**
     * Mark work as completed
     */
    public function markCompleted($id)
    {
        $work = Work::findOrFail($id);
        $work->update([
            'is_completed' => true,
            'status' => 2
        ]);

        // Update Google Calendar
        if ($work->google_event_id && session('google_calendar_token')) {
            app(GoogleCalendarController::class)->updateCalendarEvent($work->id);
        }

        return back()->with('success', 'Work marked as completed');
    }

    /**
     * Reschedule work
     */
    public function reschedule(Request $request, $id)
    {
        $work = Work::findOrFail($id);

        $validated = $request->validate([
            'work_date' => 'required|date',
            'time' => 'required',
        ]);

        $work->update([
            'work_date' => $validated['work_date'],
            'time' => $validated['time'],
            'is_rescheduled' => true,
        ]);

        // Update Google Calendar
        if ($work->google_event_id && session('google_calendar_token')) {
            app(GoogleCalendarController::class)->updateCalendarEvent($work->id);
        }

        return back()->with('success', 'Work rescheduled successfully');
    }

    /**
     * Export works to CSV
     */
    public function export(Request $request)
    {
        $query = Work::with(['team', 'category']);

        // Apply filters
        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('work_date', [$request->date_from, $request->date_to]);
        }

        $works = $query->get();

        $filename = 'works_export_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($works) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID',
                'Title',
                'Description',
                'Location',
                'Date',
                'Time',
                'Status',
                'Team',
                'Category',
                'Completed',
                'Rescheduled',
                'Created At'
            ]);

            // Data
            foreach ($works as $work) {
                fputcsv($file, [
                    $work->id,
                    $work->title,
                    $work->description,
                    $work->location,
                    $work->work_date,
                    $work->time,
                    ['Pending', 'In Progress', 'Completed'][$work->status],
                    $work->team ? $work->team->name : '',
                    $work->category ? $work->category->name : '',
                    $work->is_completed ? 'Yes' : 'No',
                    $work->is_rescheduled ? 'Yes' : 'No',
                    $work->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print works
     */
    public function print(Request $request)
    {
        $query = Work::with(['team', 'category']);

        // Apply filters
        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('work_date', [$request->date_from, $request->date_to]);
        }

        $works = $query->orderBy('work_date')->orderBy('time')->get();

        return view('works.print', compact('works'));
    }

    /**
     * Duplicate work
     */
    public function duplicate($id)
    {
        $work = Work::findOrFail($id);

        $newWork = $work->replicate();
        $newWork->title = $work->title . ' (Copy)';
        $newWork->is_completed = false;
        $newWork->is_rescheduled = false;
        $newWork->status = 0;
        $newWork->google_event_id = null;
        $newWork->save();

        return back()->with('success', 'Work duplicated successfully');
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'work_ids' => 'required|array',
            'work_ids.*' => 'exists:works,id',
            'action' => 'required|in:complete,delete,assign_team,assign_category,change_status',
            'value' => 'nullable',
        ]);

        $workIds = $validated['work_ids'];
        $action = $validated['action'];

        switch ($action) {
            case 'complete':
                Work::whereIn('id', $workIds)->update([
                    'is_completed' => true,
                    'status' => 2
                ]);
                $message = 'Works marked as completed';
                break;

            case 'delete':
                $works = Work::whereIn('id', $workIds)->get();
                foreach ($works as $work) {
                    if ($work->google_event_id && session('google_calendar_token')) {
                        app(GoogleCalendarController::class)->deleteCalendarEvent($work->id);
                    }
                }
                Work::whereIn('id', $workIds)->delete();
                $message = 'Works deleted successfully';
                break;

            case 'assign_team':
                Work::whereIn('id', $workIds)->update(['team_id' => $validated['value']]);
                $message = 'Team assigned successfully';
                break;

            case 'assign_category':
                Work::whereIn('id', $workIds)->update(['category_id' => $validated['value']]);
                $message = 'Category assigned successfully';
                break;

            case 'change_status':
                Work::whereIn('id', $workIds)->update(['status' => $validated['value']]);
                $message = 'Status updated successfully';
                break;

            default:
                $message = 'Action completed';
        }

        return back()->with('success', $message);
    }
}
