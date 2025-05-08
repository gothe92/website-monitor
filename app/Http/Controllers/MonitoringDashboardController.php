<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MonitoringDashboardController extends Controller
{
    public function index()
    {
        $websites = Auth::user()->websites;
        $dashboardData = $this->prepareDashboardData($websites);

        return view('monitoring-dashboard', [
            'websites' => $dashboardData
        ]);
    }

    private function prepareDashboardData(Collection $websites)
    {
        return $websites->map(function ($website) {
            $hourAgo = now()->subHour(1);

            // Lekérjük az elmúlt óra logjait
            $logs = $website->logs()
                ->where('created_at', '>=', $hourAgo)
                ->orderBy('created_at', 'asc')
                ->get();

            // Utolsó log alapján státusz
            $lastLog = $website->logs()->latest()->first();

            // Válaszidő statisztikák
            $successLogs = $logs->where('status', 'success');
            $stats = [
                'average_response' => $successLogs->avg('response_time'),
                'min_response' => $successLogs->min('response_time'),
                'max_response' => $successLogs->max('response_time'),
                'availability' => $this->calculateAvailability($logs),
                'status' => [
                    'is_online' => $lastLog && $lastLog->status === 'success',
                    'last_check' => $lastLog ? $lastLog->created_at : null,
                    'last_error' => $this->getLastError($website)
                ]
            ];

            // Idősorok előkészítése a grafikonhoz
            $timeSeriesData = $this->prepareTimeSeriesData($logs, $hourAgo);

            return [
                'id' => $website->id,
                'name' => $website->name,
                'url' => $website->url,
                'stats' => $stats,
                'logs' => $timeSeriesData,
                'performance_indicators' => $this->calculatePerformanceIndicators($logs)
            ];
        });
    }

    private function calculateAvailability($logs)
    {
        if ($logs->isEmpty()) {
            return 0;
        }

        $successCount = $logs->where('status', 'success')->count();
        return round(($successCount / $logs->count()) * 100, 1);
    }

    private function getLastError($website)
    {
        return $website->logs()
            ->where('status', 'error')
            ->latest()
            ->first();
    }

    private function prepareTimeSeriesData($logs, $hourAgo)
    {
        // Percenkénti bontás létrehozása
        $timeSlots = collect();
        for ($time = $hourAgo; $time <= now(); $time->addMinute()) {
            $timeSlots->push($time->copy());
        }

        return $timeSlots->map(function ($timeSlot) use ($logs) {
            $log = $logs->first(function ($log) use ($timeSlot) {
                return $log->created_at->format('Y-m-d H:i') === $timeSlot->format('Y-m-d H:i');
            });

            return [
                'timestamp' => $timeSlot->timestamp,
                'formatted_time' => $timeSlot->format('H:i'),
                'response_time' => $log ? $log->response_time : null,
                'status' => $log ? $log->status : null,
                'error_message' => $log && $log->status === 'error' ? $log->error_message : null
            ];
        });
    }

    private function calculatePerformanceIndicators($logs)
    {
        $successLogs = $logs->where('status', 'success');

        return [
            'slow_responses' => $successLogs->where('response_time', '>', 5000)->count(),
            'errors' => $logs->where('status', 'error')->count(),
            'fast_responses' => $successLogs->where('response_time', '<', 1000)->count(),
            'average_response_trend' => $this->calculateResponseTrend($logs)
        ];
    }

    private function calculateResponseTrend($logs)
    {
        if ($logs->count() < 2) {
            return 'stable';
        }

        $firstHalf = $logs->take($logs->count() / 2)->avg('response_time');
        $secondHalf = $logs->skip($logs->count() / 2)->avg('response_time');

        $difference = $secondHalf - $firstHalf;

        if (abs($difference) < 100) { // 100ms különbség alatt stabilnak tekintjük
            return 'stable';
        }

        return $difference > 0 ? 'increasing' : 'decreasing';
    }
}
