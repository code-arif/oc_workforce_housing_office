<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailDraft extends Model
{
    protected $fillable = [
        'email_account_id',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'attachments',
        'reply_to_message_id',
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'attachments' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function replyToMessage(): BelongsTo
    {
        return $this->belongsTo(EmailMessage::class, 'reply_to_message_id');
    }
}
