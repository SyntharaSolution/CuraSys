<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountApproval extends Model
{
    protected $guarded = [];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_uuid', 'uuid');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
