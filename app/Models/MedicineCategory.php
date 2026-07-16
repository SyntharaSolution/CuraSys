<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineCategory extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $guarded = [];

    public function medicines()
    {
        return $this->hasMany(Medicine::class, 'category_id');
    }
}
