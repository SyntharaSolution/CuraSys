<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasUuid, SoftDeletes;
    
    protected $guarded = [];

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_uuid', 'uuid');
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class, 'sale_uuid', 'uuid');
    }

    public function discountApprovals()
    {
        return $this->hasMany(DiscountApproval::class, 'sale_uuid', 'uuid');
    }

    public function reprints()
    {
        return $this->hasMany(InvoiceReprint::class, 'sale_uuid', 'uuid');
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class, 'original_sale_uuid', 'uuid');
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
