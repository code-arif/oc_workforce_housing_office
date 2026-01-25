<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    protected $table = 'items';
    protected $guarded = [];

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
