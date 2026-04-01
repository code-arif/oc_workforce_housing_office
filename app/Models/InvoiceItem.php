<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'item_id',
        'item_name',
        'description',
        'quantity',
        'rate',
        'amount'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
