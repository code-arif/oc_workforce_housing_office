<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use Exception;
use App\Helper\Helper;
use App\Models\SocialLink;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class SocialLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SocialLink::orderBy('created_at', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $platforms = SocialLink::getPlatforms();
                    return $platforms[$row->name] ?? ucfirst($row->name);
                })
                ->addColumn('icon', function ($row) {
                    if ($row->icon) {
                        return '<img src="' . asset($row->icon) . '" alt="' . $row->name . '" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">';
                    }
                    return '<span class="badge bg-secondary">No Icon</span>';
                })
                ->addColumn('url', function ($row) {
                    return '<a href="' . $row->url . '" target="_blank" class="text-primary">' . \Illuminate\Support\Str::limit($row->url, 30) . '</a>';
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status === 'active' ? 'checked' : '';
                    return '
                        <label class="custom-toggle">
                            <input type="checkbox" class="status-toggle" data-id="' . $row->id . '" ' . $checked . '>
                            <span class="toggle-slider"></span>
                        </label>
                    ';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-info edit-social"
                            data-id="' . $row->id . '"
                            data-name="' . $row->name . '"
                            data-url="' . htmlspecialchars($row->url) . '"
                            data-icon="' . ($row->icon ?? '') . '"
                            data-status="' . $row->status . '">
                            <i class="fe fe-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-social" data-id="' . $row->id . '">
                            <i class="fe fe-trash-2"></i>
                        </button>
                    ';
                })
                ->rawColumns(['icon', 'url', 'status', 'action'])
                ->make(true);
        }

        return view("backend.layouts.settings.social.index");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|in:facebook,twitter,instagram,linkedin,youtube,tiktok,pinterest,whatsapp|unique:social_links,name',
                'url' => 'required|url|max:255',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'status' => 'nullable|in:active,inactive'
            ]);

            if ($request->hasFile('icon')) {
                $validated['icon'] = Helper::uploadImage($request->file('icon'), 'social');
            }

            $validated['status'] = $validated['status'] ?? 'active';

            SocialLink::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Social link created successfully!'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            \Log::error('Social Link Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create social link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $social = SocialLink::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|in:facebook,twitter,instagram,linkedin,youtube,tiktok,pinterest,whatsapp|unique:social_links,name,' . $id,
                'url' => 'required|url|max:255',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'status' => 'nullable|in:active,inactive'
            ]);

            if ($request->hasFile('icon')) {
                // Delete old icon
                if ($social->icon) {
                    Helper::deleteImage($social->icon);
                }
                $validated['icon'] = Helper::uploadImage($request->file('icon'), 'social');
            }

            $social->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Social link updated successfully!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            \Log::error('Social Link Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update social link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $social = SocialLink::findOrFail($id);

            if ($social->icon) {
                Helper::deleteImage($social->icon);
            }

            $social->delete();

            return response()->json([
                'success' => true,
                'message' => 'Social link deleted successfully!'
            ]);
        } catch (Exception $e) {
            \Log::error('Social Link Delete Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete social link'
            ], 500);
        }
    }

    /**
     * Update status
     */
    public function status($id): JsonResponse
    {
        try {
            $social = SocialLink::findOrFail($id);
            $social->status = $social->status === 'active' ? 'inactive' : 'active';
            $social->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'status' => $social->status
            ]);
        } catch (Exception $e) {
            \Log::error('Social Link Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }
}
