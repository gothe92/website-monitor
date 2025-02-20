<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Website extends Model
{
    protected $fillable = ['url', 'name', 'user_id', 'notification'];

    public function logs(): HasMany
    {
        return $this->hasMany(WebsiteLog::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLatestLogAttribute()
    {
        return $this->logs()->latest()->first();
    }

    public function getAverageResponseTimeAttribute()
    {
        return $this->logs()
            ->where('response_time', '!=', null)
            ->avg('response_time');
    }
}
