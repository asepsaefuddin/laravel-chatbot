<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [

        'message_id',

        'type',

        'path',

        'original_name',

        'mime',

        'size'

    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
