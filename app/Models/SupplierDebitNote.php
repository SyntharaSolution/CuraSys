<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierDebitNote extends Model
{
    protected $table = 'supplier_debit_notes';

    protected $guarded = [];

    public function grn()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
