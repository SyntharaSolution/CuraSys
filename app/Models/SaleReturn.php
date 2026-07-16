<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $table = 'returns';

    protected $guarded = [];

    public function originalSale()
    {
        return $this->belongsTo(Sale::class, 'original_sale_uuid', 'uuid');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
