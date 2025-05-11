<?php

namespace App\Http\Controllers;

use App\Models\WebsiteLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CriticalEventsController extends Controller
{
    public function index()
    {
        $criticalEvents = WebsiteLog::with('website')
            ->where(function ($query) {
                $query->where('status', 'error')
                    ->orWhere('status', 'success_with_ssl_warning')
                    ->orWhere('response_time', '>', 5000);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('critical-events.index', compact('criticalEvents'));
    }
} 