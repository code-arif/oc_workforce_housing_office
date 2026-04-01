<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use Exception;
use Illuminate\Support\Str;
use App\Models\MailTemplate;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;

class MailTemplateController extends Controller
{
    /**
     * Display a listing of mail templates.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $templates = MailTemplate::latest();
            
            return DataTables::of($templates)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $checked = $row->status ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" 
                            data-id="' . $row->id . '" ' . $checked . '>
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group">
                        <a href="' . route('setting.mail-templates.edit', $row->slug) . '" 
                            class="btn btn-sm btn-primary" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                            data-id="' . $row->id . '" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        
        return view('backend.layouts.settings.mail-templates.index');
    }

    /**
     * Show the form for creating a new mail template.
     */
    public function create(): View
    {
        return view('backend.layouts.settings.mail-templates.create');
    }

    /**
     * Store a newly created mail template.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:mail_templates,name',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'description' => 'nullable|string',
            'variables' => 'nullable|string',
        ]);

        try {
            $variables = [];
            if (!empty($validated['variables'])) {
                $vars = explode(',', $validated['variables']);
                $variables = array_map('trim', $vars);
            }

            MailTemplate::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'description' => $validated['description'] ?? null,
                'variables' => $variables,
                'status' => true,
            ]);

            return redirect()
                ->route('setting.mail-templates.index')
                ->with('success', 'Mail template created successfully.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create mail template: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified mail template.
     */
    public function edit(MailTemplate $mailTemplate): View
    {
        return view('backend.layouts.settings.mail-templates.edit', compact('mailTemplate'));
    }

    /**
     * Update the specified mail template.
     */
    public function update(Request $request, MailTemplate $mailTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:mail_templates,name,' . $mailTemplate->id,
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'description' => 'nullable|string',
            'variables' => 'nullable|string',
        ]);

        try {
            $variables = [];
            if (!empty($validated['variables'])) {
                $vars = explode(',', $validated['variables']);
                $variables = array_map('trim', $vars);
            }

            $mailTemplate->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'description' => $validated['description'] ?? null,
                'variables' => $variables,
            ]);

            return redirect()
                ->route('setting.mail-templates.index')
                ->with('success', 'Mail template updated successfully.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update mail template: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of the specified mail template.
     */
    public function toggleStatus(MailTemplate $mailTemplate)
    {
        try {
            $mailTemplate->update([
                'status' => !$mailTemplate->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'status' => $mailTemplate->status,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }

    /**
     * Remove the specified mail template.
     */
    public function destroy(MailTemplate $mailTemplate)
    {
        try {
            $mailTemplate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mail template deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete mail template.',
            ], 500);
        }
    }
}
