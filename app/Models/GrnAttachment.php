<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnAttachment extends Model
{
    protected $table = 'grn_attachments';

    protected $guarded = [];

    public function grn()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
