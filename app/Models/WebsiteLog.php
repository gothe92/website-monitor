<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WebsiteLog extends Model
{
    protected $fillable = [
        'website_id',
        'response_time',
        'status',
        'error_message'
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
