<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Models\WebsiteLog;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsyncMonitorWebsite extends Command
{
    protected $signature = 'monitor:website-async';
    protected $description = 'Asynchronously monitor website response times';


    private $client;
    private $promises = [];
    private $startTimes = [];

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => 10,
            'connect_timeout' => 10
        ]);
    }

    public function handle()
    {
        try {
            $this->cleanupOldLogs();

            $websites = Website::all();

            // Indítjuk az összes lekérdezést párhuzamosan
            foreach ($websites as $website) {
                $this->startTimes[$website->id] = microtime(true);

                $this->promises[$website->id] = $this->client
                    ->getAsync($website->url)
                    ->then(
                        // Sikeres válasz esetén
                        function ($response) use ($website) {
                            $responseTime = (microtime(true) - $this->startTimes[$website->id]) * 1000;

                            // Log létrehozása
                            WebsiteLog::create([
                                'website_id' => $website->id,
                                'response_time' => $responseTime,
                                'status' => 'success'
                            ]);

                            // Ellenőrzés a válaszidőre
                            if ($responseTime > 20000) {
                                $this->sendDiscordNotification(
                                    "⚠️ Slow Response Warning",
                                    "Website: {$website->url}\n" .
                                    "Response Time: " . round($responseTime/1000, 2) . " seconds\n" .
                                    "Average Response Time: " . round($website->average_response_time/1000, 2) . " seconds"
                                );
                            }
                        },
                        // Hiba esetén
                        function ($exception) use ($website) {
                            WebsiteLog::create([
                                'website_id' => $website->id,
                                'status' => 'error',
                                'error_message' => $exception->getMessage()
                            ]);

                            $this->sendDiscordNotification(
                                "🔴 Website Down Alert",
                                "Website: {$website->url}\n" .
                                "Error: {$exception->getMessage()}\n" .
                                "Last Successful Check: " .
                                ($website->logs()->where('status', 'success')->latest()->first()?->created_at?->diffForHumans() ?? 'Never')
                            );
                        }
                    );
            }

            // Várunk az összes promise befejeződésére
            Promise\Utils::settle($this->promises)->wait();

        } catch (\Exception $e) {
            Log::error('Async website monitoring failed: ' . $e->getMessage());
        }
    }

        /**
     * Törli az 1 napnál régebbi log bejegyzéseket
     */
    private function cleanupOldLogs()
    {
        try {
            $deletedCount = WebsiteLog::where('created_at', '<', Carbon::now()->subDay())
                ->delete();

            $this->info("Cleaned up {$deletedCount} old log entries.");
        } catch (\Exception $e) {
            Log::error('Log cleanup failed: ' . $e->getMessage());
            $this->error('Log cleanup failed: ' . $e->getMessage());
        }
    }


    private function sendDiscordNotification($title, $description)
    {
        Http::post(config("services.discord.webhook"), [
            'embeds' => [
                [
                    'title' => $title,
                    'description' => $description,
                    'color' => 16711680,
                    'timestamp' => Carbon::now()->toIso8601String()
                ]
            ]
        ]);
    }
}
