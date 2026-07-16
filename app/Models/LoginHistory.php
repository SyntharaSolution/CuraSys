<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'ip_address', 'user_agent', 'status'])]
class LoginHistory extends Model
{
    //
}
