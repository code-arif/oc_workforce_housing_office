<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\EmailDraft;
use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Models\EmailAttachment;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailService
{
    /**
     * Sync emails from Gmail IMAP
     */
    public function syncEmails(EmailAccount $account, $folder = 'INBOX', $limit = 10)
    {
        try {
            $client = Client::account('default');
            $client->connect();

            $imapFolder = $client->getFolder($folder);
            
            if (!$imapFolder) {
                Log::error('Email sync failed: Folder not found - ' . $folder);
                return false;
            }

            // Use query()->all() for proper IMAP search command
            $messages = $imapFolder->query()->all()->limit($limit)->get();

            foreach ($messages as $message) {
                $this->storeMessage($account, $message, $this->mapFolderName($imapFolder->name));
            }

            $client->disconnect();
            return true;
        } catch (Exception $e) {
            Log::error('Email sync failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'folder' => $folder,
                'account' => $account->email
            ]);
            return false;
        }
    }

    /**
     * Store individual message
     */
    protected function storeMessage(EmailAccount $account, $message, $folderName)
    {
        try {
            // Handle messageId - can be Attribute object
            $messageIdAttr = $message->getMessageId();
            $messageId = $this->attributeToString($messageIdAttr) ?? uniqid('msg_');

            // Check if message already exists
            $existing = EmailMessage::where('message_id', $messageId)->first();
            if ($existing) {
                return $existing;
            }

            // Safely get from address - getFrom() returns Attribute
            $fromAttr = $message->getFrom();
            $fromEmail = '';
            $fromName = '';
            if ($fromAttr) {
                $fromFirst = $fromAttr->first();
                if ($fromFirst) {
                    $fromEmail = $fromFirst->mail ?? '';
                    $fromName = $fromFirst->personal ?? '';
                }
            }

            // Safely get in-reply-to for thread
            $inReplyToAttr = $message->getInReplyTo();
            $threadId = $this->attributeToString($inReplyToAttr) ?? $messageId;

            // Safely get subject
            $subjectAttr = $message->getSubject();
            $subject = $this->attributeToString($subjectAttr) ?? '(No Subject)';

            // Safely get flags
            $flags = $message->getFlags();
            $isRead = false;
            $isStarred = false;
            if ($flags) {
                $flagsArray = $flags->toArray();
                $isRead = in_array('Seen', $flagsArray) || in_array('seen', $flagsArray) || in_array('\\Seen', $flagsArray);
                $isStarred = in_array('Flagged', $flagsArray) || in_array('flagged', $flagsArray) || in_array('\\Flagged', $flagsArray);
            }

            $emailMessage = EmailMessage::create([
                'email_account_id' => $account->id,
                'message_id' => $messageId,
                'thread_id' => (string) $threadId,
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'to' => $this->extractRecipients($message->getTo()),
                'cc' => $this->extractRecipients($message->getCc()),
                'bcc' => $this->extractRecipients($message->getBcc()),
                'subject' => $subject,
                'body_text' => $message->getTextBody() ?? '',
                'body_html' => $message->getHTMLBody() ?? '',
                'has_attachments' => $message->hasAttachments(),
                'folder' => $folderName,
                'is_read' => $isRead,
                'is_starred' => $isStarred,
                'email_date' => $message->getDate() ?? now(),
            ]);

            // Store attachments
            if ($message->hasAttachments()) {
                $this->storeAttachments($emailMessage, $message->getAttachments());
            }

            return $emailMessage;
        } catch (Exception $e) {
            Log::error('Failed to store message: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Convert Attribute object to string
     */
    protected function attributeToString($attr)
    {
        if ($attr === null) {
            return null;
        }
        if (is_string($attr)) {
            return $attr;
        }
        if (is_object($attr)) {
            if (method_exists($attr, 'toString')) {
                return $attr->toString();
            }
            if (method_exists($attr, 'first')) {
                $first = $attr->first();
                return is_string($first) ? $first : (string) $first;
            }
            if (method_exists($attr, '__toString')) {
                return (string) $attr;
            }
        }
        return (string) $attr;
    }

    /**
     * Extract recipients
     */
    protected function extractRecipients($recipients)
    {
        if (!$recipients) return [];

        $result = [];
        
        // Handle Attribute object
        if (is_object($recipients) && method_exists($recipients, 'toArray')) {
            $recipients = $recipients->toArray();
        }
        
        if (!is_array($recipients)) {
            return [];
        }
        
        foreach ($recipients as $recipient) {
            if (is_object($recipient)) {
                $result[] = [
                    'email' => $recipient->mail ?? '',
                    'name' => $recipient->personal ?? '',
                ];
            }
        }
        return $result;
    }

    /**
     * Store attachments
     */
    protected function storeAttachments(EmailMessage $emailMessage, $attachments)
    {
        foreach ($attachments as $attachment) {
            $filename = $attachment->getName();
            $path = 'email_attachments/' . $emailMessage->id . '/' . $filename;

            Storage::put($path, $attachment->getContent());

            EmailAttachment::create([
                'email_message_id' => $emailMessage->id,
                'filename' => $filename,
                'mime_type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
                'path' => $path,
                'content_id' => $attachment->getContentId(),
            ]);
        }
    }

    /**
     * Send email
     */
    public function sendEmail(EmailAccount $account, array $data)
    {
        try {
            Mail::send([], [], function ($message) use ($account, $data) {
                $message->from($account->email, $account->name);

                // To recipients
                if (!empty($data['to'])) {
                    foreach ($data['to'] as $recipient) {
                        $message->to($recipient['email'], $recipient['name'] ?? '');
                    }
                }

                // CC recipients
                if (!empty($data['cc'])) {
                    foreach ($data['cc'] as $recipient) {
                        $message->cc($recipient['email'], $recipient['name'] ?? '');
                    }
                }

                // BCC recipients
                if (!empty($data['bcc'])) {
                    foreach ($data['bcc'] as $recipient) {
                        $message->bcc($recipient['email'], $recipient['name'] ?? '');
                    }
                }

                $message->subject($data['subject'] ?? '');
                $message->html($data['body'] ?? '');

                // Attachments
                if (!empty($data['attachments'])) {
                    foreach ($data['attachments'] as $attachment) {
                        $message->attach($attachment);
                    }
                }
            });

            // Store sent message
            $this->storeSentMessage($account, $data);

            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (\Exception $e) {
            Log::error('Email send failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Store sent message
     */
    protected function storeSentMessage(EmailAccount $account, array $data)
    {
        EmailMessage::create([
            'email_account_id' => $account->id,
            'message_id' => uniqid('sent_'),
            'from_email' => $account->email,
            'from_name' => $account->name,
            'to' => $data['to'] ?? [],
            'cc' => $data['cc'] ?? [],
            'bcc' => $data['bcc'] ?? [],
            'subject' => $data['subject'] ?? '',
            'body_html' => $data['body'] ?? '',
            'folder' => 'sent',
            'is_read' => true,
            'email_date' => now(),
        ]);
    }

    /**
     * Save draft
     */
    public function saveDraft(EmailAccount $account, array $data, $draftId = null)
    {
        if ($draftId) {
            $draft = EmailDraft::findOrFail($draftId);
            $draft->update($data);
        } else {
            $draft = EmailDraft::create([
                'email_account_id' => $account->id,
                ...$data
            ]);
        }

        return $draft;
    }

    /**
     * Mark as read/unread
     */
    public function markAsRead(EmailMessage $message, bool $isRead = true)
    {
        $message->update(['is_read' => $isRead]);
        return $message;
    }

    /**
     * Toggle star
     */
    public function toggleStar(EmailMessage $message)
    {
        $message->update(['is_starred' => !$message->is_starred]);
        return $message;
    }

    /**
     * Move to folder
     */
    public function moveToFolder(EmailMessage $message, string $folder)
    {
        $message->update(['folder' => $folder]);
        return $message;
    }

    /**
     * Delete email (move to trash)
     */
    public function deleteEmail(EmailMessage $message)
    {
        if ($message->folder === 'trash') {
            // Permanent delete
            $message->forceDelete();
        } else {
            // Move to trash
            $message->update(['folder' => 'trash']);
        }
        return true;
    }

    /**
     * Map folder names
     */
    protected function mapFolderName($folderName)
    {
        $map = [
            'INBOX' => 'inbox',
            'Sent' => 'sent',
            '[Gmail]/Sent Mail' => 'sent',
            'Drafts' => 'drafts',
            '[Gmail]/Drafts' => 'drafts',
            'Trash' => 'trash',
            '[Gmail]/Trash' => 'trash',
            'Spam' => 'spam',
            '[Gmail]/Spam' => 'spam',
        ];

        return $map[$folderName] ?? 'inbox';
    }

    /**
     * Get folder counts
     */
    public function getFolderCounts(EmailAccount $account)
    {
        return [
            'inbox' => EmailMessage::where('email_account_id', $account->id)->inbox()->count(),
            'unread' => EmailMessage::where('email_account_id', $account->id)->unread()->count(),
            'sent' => EmailMessage::where('email_account_id', $account->id)->sent()->count(),
            'drafts' => EmailDraft::where('email_account_id', $account->id)->count(),
            'starred' => EmailMessage::where('email_account_id', $account->id)->starred()->count(),
            'trash' => EmailMessage::where('email_account_id', $account->id)->trash()->count(),
        ];
    }
}
