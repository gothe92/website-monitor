<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    protected $fillable = ['url', 'name'];

    public function logs(): HasMany
    {
        return $this->hasMany(WebsiteLog::class);
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
