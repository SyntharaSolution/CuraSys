<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNote extends Model
{
    protected $table = 'goods_received_notes';

    protected $guarded = [];

    protected $casts = [
        'approved_at' => 'datetime',
        'variance_flag' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(Purchase::class, 'purchase_order_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }

    public function attachments()
    {
        return $this->hasMany(GrnAttachment::class, 'grn_id');
    }

    public function debitNotes()
    {
        return $this->hasMany(SupplierDebitNote::class, 'grn_id');
    }
}
