<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'branch_id', 'last_sync_timestamp', 'sync_type', 'status', 'error_message'
])]
class SyncLog extends Model
{
    protected function casts(): array
    {
        return [
            'last_sync_timestamp' => 'datetime',
        ];
    }
}
