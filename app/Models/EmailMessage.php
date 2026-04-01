<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email_account_id',
        'message_id',
        'thread_id',
        'from_email',
        'from_name',
        'to',
        'cc',
        'bcc',
        'subject',
        'body_text',
        'body_html',
        'has_attachments',
        'attachments',
        'folder',
        'is_read',
        'is_starred',
        'is_important',
        'email_date',
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'attachments' => 'array',
        'has_attachments' => 'boolean',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_important' => 'boolean',
        'email_date' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(EmailLabel::class, 'email_message_labels');
    }

    public function attachmentFiles(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }

    // Scopes
    public function scopeInbox($query)
    {
        return $query->where('folder', 'inbox');
    }

    public function scopeSent($query)
    {
        return $query->where('folder', 'sent');
    }

    public function scopeDrafts($query)
    {
        return $query->where('folder', 'drafts');
    }

    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeTrash($query)
    {
        return $query->where('folder', 'trash');
    }
}
