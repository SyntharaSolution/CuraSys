<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLoyalty extends Model
{
    protected $table = 'customer_loyalty';

    protected $guarded = [];

    protected $casts = [
        'tier_effective_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tier()
    {
        return $this->belongsTo(LoyaltyTier::class, 'tier_id');
    }
}
