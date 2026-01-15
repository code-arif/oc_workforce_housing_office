<?php

namespace App\Http\Controllers\Web\Backend\Messaging;

use App\Models\EmailDraft;
use App\Models\EmailLabel;
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
     * Show messaging inbox page
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get or create default email account
        $account = EmailAccount::firstOrCreate(
            ['user_id' => $user->id, 'is_default' => true],
            [
                'email' => 'rufuzxyz@gmail.com', // Your email
                'name' => $user->first_name . ' ' . $user->last_name,
                'provider' => 'gmail',
                'is_active' => true,
            ]
        );

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
        $account = EmailAccount::where('user_id', $user->id)
            ->where('is_default', true)
            ->firstOrFail();

        $folder = $request->get('folder', 'INBOX');
        $limit = $request->get('limit', 50);

        $result = $this->emailService->syncEmails($account, $folder, $limit);

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
        $account = EmailAccount::where('user_id', $user->id)
            ->where('is_default', true)
            ->firstOrFail();

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
        $account = EmailAccount::where('user_id', $user->id)
            ->where('is_default', true)
            ->firstOrFail();

        // Handle attachments
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('temp_attachments');
                $attachmentPaths[] = storage_path('app/' . $path);
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
        $account = EmailAccount::where('user_id', $user->id)
            ->where('is_default', true)
            ->firstOrFail();

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
}
