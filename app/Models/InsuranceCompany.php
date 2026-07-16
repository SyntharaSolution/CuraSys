<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceCompany extends Model
{
    protected $guarded = [];

    public function policies()
    {
        return $this->hasMany(CustomerInsurancePolicy::class);
    }

    public function claims()
    {
        return $this->hasMany(InsuranceClaim::class);
    }
}
