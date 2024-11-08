<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HandleUserUpdate implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $updatedUsers
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $api = "";
        $payload = [
            'batches' => [
                'subscribers' => $this->updatedUsers
            ]
        ];

        try {
            $response = Http::post($api, $payload);

            if ($response->failed()) {
                throw new \Exception('Batch Post request failed');
            }
        } catch (\Exception $e) {
            Log::error("Failed to process batch request:  #{$e->getMessage()}");
        }
    }
}
