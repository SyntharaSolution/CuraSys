<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceReprint extends Model
{
    protected $guarded = [];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_uuid', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'reprinted_by');
    }
}
