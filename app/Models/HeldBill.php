<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeldBill extends Model
{
    protected $guarded = [];

    protected $casts = [
        'cart_json' => 'array',
        'held_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public $timestamps = false;

    public function register()
    {
        return $this->belongsTo(Register::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
