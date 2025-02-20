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
    protected $description = 'Asynchronously monitor website response times, even with certificate errors';

    private $client;
    private $promises = [];
    private $startTimes = [];

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => 10,
            'connect_timeout' => 10,
            'verify' => false, // Don't verify SSL certificates
        ]);
    }

    public function handle()
    {
        try {
            $this->cleanupOldLogs();

            $websites = Website::all();

            // Start all requests in parallel
            foreach ($websites as $website) {
                $this->startTimes[$website->id] = microtime(true);

                $this->promises[$website->id] = $this->client
                    ->getAsync($website->url)
                    ->then(
                        // On successful response
                        function ($response) use ($website) {
                            $responseTime = (microtime(true) - $this->startTimes[$website->id]) * 1000;

                            // Create log
                            WebsiteLog::create([
                                'website_id' => $website->id,
                                'response_time' => $responseTime,
                                'status' => 'success'
                            ]);

                            // Check response time
                            if ($responseTime > 20000) {
                                $this->sendDiscordNotification(
                                    "⚠️ Slow Response Warning",
                                    "Website: {$website->url}\n" .
                                    "Response Time: " . round($responseTime/1000, 2) . " seconds\n" .
                                    "Average Response Time: " . round($website->average_response_time/1000, 2) . " seconds"
                                );
                            }
                        },
                        // On error
                        function ($exception) use ($website) {
                            // Check if it's an SSL error
                            $errorMessage = $exception->getMessage();
                            $isSSLError = strpos($errorMessage, 'SSL') !== false ||
                                         strpos($errorMessage, 'certificate') !== false ||
                                         strpos($errorMessage, 'cert') !== false;

                            if ($isSSLError) {
                                // For SSL errors, still try to measure the response time
                                $this->info("SSL error for {$website->url}, still measuring response time");

                                // Try again without verification and with different options
                                $retryClient = new Client([
                                    'timeout' => 15,
                                    'connect_timeout' => 15,
                                    'verify' => false,
                                    'curl' => [
                                        CURLOPT_SSL_VERIFYHOST => false,
                                        CURLOPT_SSL_VERIFYPEER => false,
                                    ]
                                ]);

                                try {
                                    $retryStartTime = microtime(true);
                                    $response = $retryClient->get($website->url);
                                    $responseTime = (microtime(true) - $retryStartTime) * 1000;

                                    WebsiteLog::create([
                                        'website_id' => $website->id,
                                        'response_time' => $responseTime,
                                        'status' => 'success_with_ssl_warning',
                                        'error_message' => 'Original error: ' . $errorMessage
                                    ]);

                                    $this->info("Successfully loaded {$website->url} despite SSL error. Response time: {$responseTime}ms");

                                    if ($responseTime > 20000) {
                                        $this->sendDiscordNotification(
                                            "⚠️ Slow Response Warning (SSL Issue)",
                                            "Website: {$website->url}\n" .
                                            "Response Time: " . round($responseTime/1000, 2) . " seconds\n" .
                                            "Note: Site has SSL certificate issues but is loading\n" .
                                            "Average Response Time: " . round($website->average_response_time/1000, 2) . " seconds"
                                        );
                                    }
                                } catch (\Exception $retryException) {
                                    // If retry also fails, log the original error
                                    WebsiteLog::create([
                                        'website_id' => $website->id,
                                        'status' => 'error',
                                        'error_message' => 'Original SSL error: ' . $errorMessage .
                                                          ' | Retry failed: ' . $retryException->getMessage()
                                    ]);

                                    $this->sendDiscordNotification(
                                        "🔴 Website Down Alert (SSL Issue)",
                                        "Website: {$website->url}\n" .
                                        "Error: SSL/Certificate error and retry failed\n" .
                                        "Details: {$retryException->getMessage()}\n" .
                                        "Last Successful Check: " .
                                        ($website->logs()->where('status', 'success')->latest()->first()?->created_at?->diffForHumans() ?? 'Never')
                                    );
                                }
                            } else {
                                // For non-SSL errors, handle normally
                                WebsiteLog::create([
                                    'website_id' => $website->id,
                                    'status' => 'error',
                                    'error_message' => $errorMessage
                                ]);

                                $this->sendDiscordNotification(
                                    "🔴 Website Down Alert",
                                    "Website: {$website->url}\n" .
                                    "Error: {$errorMessage}\n" .
                                    "Last Successful Check: " .
                                    ($website->logs()->where('status', 'success')->latest()->first()?->created_at?->diffForHumans() ?? 'Never')
                                );
                            }
                        }
                    );
            }

            // Wait for all promises to complete
            Promise\Utils::settle($this->promises)->wait();

        } catch (\Exception $e) {
            Log::error('Async website monitoring failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete log entries older than 1 day
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
