<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class SentinelDoctor extends Command
{
    protected $signature = 'sentinel:doctor';
    protected $description = 'Clear caches, check DB tables, and print important URLs';

    public function handle(): int
    {
        $this->info('');
        $this->info('🩺 Log Sentinel Doctor — Quick Health Check');
        $this->info(str_repeat('─', 50));

        // 1) Clear caches
        $this->newLine();
        $this->comment('Clearing caches...');
        Artisan::call('optimize:clear');
        $this->info('  ✓ Config, route, view, event caches cleared.');

        // 2) Check DB tables
        $this->newLine();
        $this->comment('Checking database tables...');
        $tables = ['nodes', 'edges', 'cases', 'case_tasks', 'case_items', 'tags', 'taggables', 'activity_logs', 'integrations', 'server_logs', 'users'];
        $allOk = true;
        foreach ($tables as $table) {
            $exists = Schema::hasTable($table);
            $count = $exists ? \DB::table($table)->count() : 0;
            if ($exists) {
                $this->line("  ✓ <info>{$table}</info> — {$count} rows");
            } else {
                $this->error("  ✗ {$table} — TABLE MISSING! Run: php artisan migrate");
                $allOk = false;
            }
        }

        if ($allOk) {
            $this->info('  All required tables exist.');
        }

        // 3) Print important URLs
        $this->newLine();
        $this->comment('Important URLs:');
        $base = config('app.url', 'http://localhost:8000');
        $urls = [
            'CTI Dashboard (default)'  => '/cti',
            'Sentinel Dashboard'       => '/sentinel/dashboard',
            'Knowledge Entities'       => '/knowledge/entities',
            'Threat Actors'            => '/threats/actors',
            'Graph Explorer'           => '/knowledge/graph',
            'Observations'             => '/observations',
            'Cases'                    => '/cases/incidents',
            'Connectors'               => '/ingestion/connectors',
            'Diagnostics'              => '/settings/diagnostics',
        ];
        foreach ($urls as $label => $path) {
            $this->line("  {$label}: <info>{$base}{$path}</info>");
        }

        // 4) Quick summary
        $this->newLine();
        $this->info('HOME constant: ' . \App\Providers\RouteServiceProvider::HOME);
        $this->info('Root / redirects to: /cti (CTI Dashboard)');
        $this->newLine();
        $this->info('✅ Doctor check complete.');
        $this->newLine();

        return self::SUCCESS;
    }
}
