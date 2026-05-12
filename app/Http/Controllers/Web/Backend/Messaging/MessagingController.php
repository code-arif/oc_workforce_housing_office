<?php

namespace App\Http\Controllers\Web\Backend\Messaging;

use App\Models\Bed;
use App\Models\Room;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\Property;
use App\Models\EmailDraft;
use App\Models\EmailLabel;
use App\Models\MailTemplate;
use App\Models\EmailAccount;
use App\Models\EmailMessage;
use Illuminate\Http\Request;
use App\Services\EmailService;
use App\Models\EmailAttachment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MessagingController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Get or create the default email account for a user
     */
    private function getDefaultAccount($user)
    {
        // 1. Try to find an account already owned by this user
        $account = EmailAccount::where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        if ($account) {
            return $account;
        }

        // 2. If not found, check if the system default email is already in use by someone else
        $defaultEmail = config('mail.from.address', 'info@ocworkforcehousing.com');
        $account = EmailAccount::where('email', $defaultEmail)->first();

        if ($account) {
            // Found it! We'll share this account record.
            // Note: If we want isolation, we'd need to change the DB schema.
            // But for a support inbox, sharing is usually desired.
            return $account;
        }

        // 3. If still not found, create a new one for this user
        return EmailAccount::create([
            'user_id' => $user->id,
            'is_default' => true,
            'email' => $defaultEmail,
            'name' => $user->first_name . ' ' . $user->last_name,
            'provider' => 'gmail',
            'is_active' => true,
        ]);
    }

    /**
     * Show messaging inbox page
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get or create default email account
        $account = $this->getDefaultAccount($user);

        $folder = $request->get('folder', 'inbox');
        $search = $request->get('search');

        // Get messages
        $query = EmailMessage::where('email_account_id', $account->id)
            ->where('folder', $folder)
            ->with(['labels', 'attachmentFiles']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('from_email', 'like', "%{$search}%")
                    ->orWhere('body_text', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderBy('email_date', 'desc')
            ->paginate(50);

        // Get folder counts
        $counts = $this->emailService->getFolderCounts($account);

        // Get labels
        $labels = EmailLabel::where('user_id', $user->id)->get();

        return view('backend.layouts.messaging.index', compact(
            'messages',
            'counts',
            'labels',
            'folder',
            'account'
        ));
    }

    /**
     * Sync emails from server
     */
    public function sync(Request $request)
    {
        $user = Auth::user();
        $account = $this->getDefaultAccount($user);

        $folder = $request->get('folder', 'INBOX');
        $limit = $request->get('limit', 50);

        $result = $this->emailService->syncEmails($account, $folder, $limit);
        // dd($result);
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Emails synced successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to sync emails',
        ], 500);
    }

    /**
     * Get messages (AJAX)
     */
    public function getMessages(Request $request)
    {
        $user = Auth::user();
        $account = $this->getDefaultAccount($user);

        $folder = $request->get('folder', 'inbox');
        $page = $request->get('page', 1);

        $messages = EmailMessage::where('email_account_id', $account->id)
            ->where('folder', $folder)
            ->with(['labels', 'attachmentFiles'])
            ->orderBy('email_date', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Show single message
     */
    public function read($id)
    {
        $message = EmailMessage::with(['labels', 'attachmentFiles', 'account'])
            ->findOrFail($id);

        // Mark as read
        if (!$message->is_read) {
            $this->emailService->markAsRead($message);
        }

        $user = Auth::user();
        $account = $message->account;
        $counts = $this->emailService->getFolderCounts($account);
        $labels = EmailLabel::where('user_id', $user->id)->get();

        return view('backend.layouts.messaging.read', compact(
            'message',
            'counts',
            'labels',
            'account'
        ));
    }

    /**
     * Send email
     */
    public function send(Request $request)
    {
        // Accept both string and array formats
        $toField = $request->input('to');
        $ccField = $request->input('cc');
        $bccField = $request->input('bcc');

        // Convert string to array if needed
        if (is_string($toField)) {
            $toField = json_decode($toField, true) ?? [];
        }
        if (is_string($ccField)) {
            $ccField = json_decode($ccField, true) ?? [];
        }
        if (is_string($bccField)) {
            $bccField = json_decode($bccField, true) ?? [];
        }

        // Ensure all items have 'email' key
        $toField = collect($toField)->map(function ($item) {
            return is_string($item) ? ['email' => $item] : $item;
        })->toArray();

        $ccField = collect($ccField)->map(function ($item) {
            return is_string($item) ? ['email' => $item] : $item;
        })->toArray();

        $bccField = collect($bccField)->map(function ($item) {
            return is_string($item) ? ['email' => $item] : $item;
        })->toArray();

        $validator = Validator::make([
            'to' => $toField,
            'cc' => $ccField,
            'bcc' => $bccField,
            'subject' => $request->subject,
            'body' => $request->body,
            'attachments' => $request->file('attachments'),
        ], [
            'to' => 'required|array|min:1',
            'to.*.email' => 'required|email',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'cc' => 'nullable|array',
            'cc.*.email' => 'nullable|email',
            'bcc' => 'nullable|array',
            'bcc.*.email' => 'nullable|email',
            'attachments.*' => 'nullable|file|max:25600', // 25MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $account = $this->getDefaultAccount($user);

        // Handle attachments
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('temp_attachments');
                $attachmentPaths[] = storage_path('app/private/' . $path);
            }
        }

        $data = [
            'to' => $toField,
            'cc' => $ccField,
            'bcc' => $bccField,
            'subject' => $request->subject,
            'body' => $request->body,
            'attachments' => $attachmentPaths,
        ];

        $result = $this->emailService->sendEmail($account, $data);

        // Clean up temp files
        foreach ($attachmentPaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        return response()->json($result);
    }

    /**
     * Save draft
     */
    public function saveDraft(Request $request)
    {
        $user = Auth::user();
        $account = $this->getDefaultAccount($user);

        $data = [
            'to' => $request->to,
            'cc' => $request->cc,
            'bcc' => $request->bcc,
            'subject' => $request->subject,
            'body' => $request->body,
        ];

        $draft = $this->emailService->saveDraft(
            $account,
            $data,
            $request->draft_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Draft saved',
            'draft' => $draft,
        ]);
    }

    /**
     * Mark as read/unread
     */
    public function toggleRead(Request $request, $id)
    {
        $message = EmailMessage::findOrFail($id);
        $isRead = $request->get('is_read', true);

        $this->emailService->markAsRead($message, $isRead);

        return response()->json([
            'success' => true,
            'message' => $isRead ? 'Marked as read' : 'Marked as unread',
        ]);
    }

    /**
     * Toggle star
     */
    public function toggleStar($id)
    {
        $message = EmailMessage::findOrFail($id);
        $this->emailService->toggleStar($message);

        return response()->json([
            'success' => true,
            'message' => $message->is_starred ? 'Starred' : 'Unstarred',
        ]);
    }

    /**
     * Move to folder
     */
    public function moveToFolder(Request $request, $id)
    {
        $message = EmailMessage::findOrFail($id);
        $folder = $request->get('folder');

        $this->emailService->moveToFolder($message, $folder);

        return response()->json([
            'success' => true,
            'message' => 'Email moved successfully',
        ]);
    }

    /**
     * Delete email
     */
    public function delete($id)
    {
        $message = EmailMessage::findOrFail($id);
        $this->emailService->deleteEmail($message);

        return response()->json([
            'success' => true,
            'message' => 'Email deleted',
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'action' => 'required|in:read,unread,star,unstar,delete,move',
            'folder' => 'required_if:action,move',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $messages = EmailMessage::whereIn('id', $request->ids)->get();

        foreach ($messages as $message) {
            switch ($request->action) {
                case 'read':
                    $this->emailService->markAsRead($message, true);
                    break;
                case 'unread':
                    $this->emailService->markAsRead($message, false);
                    break;
                case 'star':
                    $message->update(['is_starred' => true]);
                    break;
                case 'unstar':
                    $message->update(['is_starred' => false]);
                    break;
                case 'delete':
                    $this->emailService->deleteEmail($message);
                    break;
                case 'move':
                    $this->emailService->moveToFolder($message, $request->folder);
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Action completed successfully',
        ]);
    }

    /**
     * Download attachment
     */
    public function downloadAttachment($id)
    {
        $attachment = EmailAttachment::findOrFail($id);

        if (!Storage::exists($attachment->path)) {
            abort(404);
        }

        return Storage::download($attachment->path, $attachment->filename);
    }

    /**
     * Show compose email page
     */
    public function compose()
    {
        $user = Auth::user();
        
        // Get email account
        $account = $this->getDefaultAccount($user);

        // Get folder counts for sidebar
        $counts = $this->emailService->getFolderCounts($account);
        $labels = EmailLabel::where('user_id', $user->id)->get();
        
        // Get properties for selection
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        
        // Get mail templates
        $mailTemplates = MailTemplate::active()->orderBy('name')->get();

        return view('backend.layouts.messaging.compose', compact(
            'counts',
            'labels',
            'account',
            'properties',
            'mailTemplates'
        ));
    }

    /**
     * Get units by property
     */
    public function getUnits(Request $request)
    {
        $propertyId = $request->get('property_id');
        
        $units = Unit::where('property_id', $propertyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'units' => $units,
        ]);
    }

    /**
     * Get rooms by unit
     */
    public function getRooms(Request $request)
    {
        $unitId = $request->get('unit_id');
        
        $rooms = Room::where('unit_id', $unitId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Get tenants with active leases by property/unit/room
     */
    public function getTenantsByLocation(Request $request)
    {
        $propertyId = $request->get('property_id');
        $unitId = $request->get('unit_id');
        $roomId = $request->get('room_id');

        // Build query for active leases
        $query = Lease::where('status', 'ACTIVE')
            ->whereNotNull('tenant_id')
            ->with(['tenant.profile', 'property', 'assignments.bed.room.unit']);

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        $leases = $query->get();

        // Filter by unit or room if specified
        $tenants = collect();
        
        foreach ($leases as $lease) {
            // If unit or room specified, filter by assignment bed location
            if ($unitId || $roomId) {
                $hasMatchingAssignment = $lease->assignments->filter(function ($assignment) use ($unitId, $roomId) {
                    if (!$assignment->bed || !$assignment->bed->room) {
                        return false;
                    }
                    
                    $room = $assignment->bed->room;
                    
                    if ($roomId && $room->id != $roomId) {
                        return false;
                    }
                    
                    if ($unitId && $room->unit_id != $unitId) {
                        return false;
                    }
                    
                    return true;
                })->isNotEmpty();

                if (!$hasMatchingAssignment) {
                    continue;
                }
            }

            if ($lease->tenant && $lease->tenant->email) {
                $name = '';
                if ($lease->tenant->profile) {
                    $name = trim(($lease->tenant->profile->first_name ?? '') . ' ' . ($lease->tenant->profile->last_name ?? ''));
                }
                
                $tenants->push([
                    'id' => $lease->tenant->id,
                    'email' => $lease->tenant->email,
                    'name' => $name ?: $lease->tenant->email,
                    'text' => $name ? "{$name} <{$lease->tenant->email}>" : $lease->tenant->email,
                    'property' => $lease->property->name ?? '',
                ]);
            }
        }

        // Remove duplicates by email
        $tenants = $tenants->unique('email')->values();

        return response()->json([
            'success' => true,
            'tenants' => $tenants,
            'count' => $tenants->count(),
        ]);
    }

    /**
     * Get mail template content
     */
    public function getMailTemplate(Request $request)
    {
        $templateId = $request->get('template_id');
        
        $template = MailTemplate::find($templateId);
        
        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'subject' => $template->subject,
                'body' => $template->body,
                'variables' => $template->variables ?? [],
            ],
        ]);
    }

    /**
     * Search tenants for email compose
     * Supports searching by email, name, or bed name (e.g., "201-A-1")
     */
    public function searchTenants(Request $request)
    {
        $search = $request->get('q', '');
        
        $query = \App\Models\Tenant::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->with(['profile', 'leases.assignments.bed']);
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    // Search by bed name (e.g., "201-A-1")
                    ->orWhereHas('leases', function ($q) use ($search) {
                        $q->where('status', 'ACTIVE')
                            ->whereHas('assignments', function ($q) use ($search) {
                                $q->whereHas('bed', function ($q) use ($search) {
                                    $q->where('bed_label', 'like', "%{$search}%");
                                });
                            });
                    });
            });
        }
        
        $tenants = $query->limit(20)->get();
        
        $results = $tenants->map(function ($tenant) {
            $name = '';
            if ($tenant->profile) {
                $name = trim(($tenant->profile->first_name ?? '') . ' ' . ($tenant->profile->last_name ?? ''));
            }
            
            // Get bed name from active lease if available
            $bedName = '';
            $activeLease = $tenant->leases->where('status', 'ACTIVE')->first();
            if ($activeLease && $activeLease->assignments->isNotEmpty()) {
                $assignment = $activeLease->assignments->first();
                if ($assignment->bed) {
                    $bedName = $assignment->bed->bed_label;
                }
            }
            
            $displayText = $name ?: $tenant->email;
            if ($bedName) {
                $displayText .= " [{$bedName}]";
            }
            
            return [
                'id' => $tenant->id,
                'email' => $tenant->email,
                'name' => $name ?: $tenant->email,
                'bed' => $bedName,
                'text' => $displayText . " <{$tenant->email}>",
            ];
        });
        
        return response()->json([
            'results' => $results,
        ]);
    }
}
