<?php

namespace App\Jobs;

use App\Models\Integration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Base class untuk semua ingestion connector jobs.
 *
 * Subclass harus mengimplementasi method `run()`.
 * Job ini otomatis update status Integration sebelum/sesudah execution.
 */
abstract class ConnectorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    protected Integration $integration;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }

    /**
     * Execute the connector logic.
     * Return array with keys: nodes_created, edges_created, message.
     */
    abstract protected function run(): array;

    public function handle(): void
    {
        $this->integration->update([
            'status' => 'running',
            'last_run_at' => now(),
        ]);

        try {
            $result = $this->run();

            $this->integration->update([
                'status' => 'success',
                'last_message' => $result['message'] ?? 'Completed successfully.',
            ]);

            activity_log(
                'connector_run',
                'integration',
                $this->integration->id,
                "Connector '{$this->integration->name}' selesai: " . ($result['message'] ?? '')
            );
        } catch (\Exception $e) {
            $this->integration->update([
                'status' => 'error',
                'last_message' => $e->getMessage(),
            ]);

            activity_log(
                'connector_error',
                'integration',
                $this->integration->id,
                "Connector '{$this->integration->name}' error: {$e->getMessage()}"
            );
        }
    }
}
