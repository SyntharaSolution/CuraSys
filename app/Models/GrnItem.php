<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $table = 'grn_items';

    protected $guarded = [];

    public $timestamps = false;

    public function grn()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_order_item_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
