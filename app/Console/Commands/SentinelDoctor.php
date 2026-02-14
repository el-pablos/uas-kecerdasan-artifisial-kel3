<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class SentinelDoctor extends Command
{
    protected $signature = 'sentinel:doctor
        {--fix : Run safe auto-fix (optimize:clear + migrate --seed)}
        {--fresh : DESTRUCTIVE — migrate:fresh --seed (local only, asks confirmation)}';

    protected $description = 'Check DB health, clear caches, optionally auto-fix missing tables';

    private const REQUIRED_TABLES = [
        'nodes', 'edges', 'cases', 'case_tasks', 'case_items',
        'tags', 'taggables', 'activity_logs', 'integrations',
        'server_logs', 'users',
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('🩺 Log Sentinel Doctor — Quick Health Check');
        $this->info(str_repeat('─', 50));

        // --fresh flag: destructive reset
        if ($this->option('fresh')) {
            return $this->handleFresh();
        }

        // --fix flag: safe auto-fix
        if ($this->option('fix')) {
            return $this->handleFix();
        }

        // Default: diagnose only
        return $this->diagnose();
    }

    private function diagnose(): int
    {
        // 1) Clear caches
        $this->newLine();
        $this->comment('Clearing caches...');
        Artisan::call('optimize:clear');
        $this->info('  ✓ Config, route, view, event caches cleared.');

        // 2) Check DB tables
        $this->newLine();
        $this->comment('Checking database tables...');
        $allOk = true;
        foreach (self::REQUIRED_TABLES as $table) {
            $exists = Schema::hasTable($table);
            $count = $exists ? \DB::table($table)->count() : 0;
            if ($exists) {
                $this->line("  ✓ <info>{$table}</info> — OK ({$count} rows)");
            } else {
                $this->error("  ✗ {$table} — MISSING");
                $allOk = false;
            }
        }

        if ($allOk) {
            $this->newLine();
            $this->info('  ✅ All required tables exist.');
        } else {
            $this->newLine();
            $this->warn('  ⚠️  Some tables are missing! Run: php artisan sentinel:doctor --fix');
        }

        // 3) Print important URLs
        $this->printUrls();

        // 4) Summary
        $this->newLine();
        $this->info('HOME constant: ' . \App\Providers\RouteServiceProvider::HOME);
        $this->info('Root / redirects to: /cti (CTI Dashboard)');
        $this->newLine();
        $this->info('✅ Doctor check complete.');
        $this->newLine();

        return self::SUCCESS;
    }

    private function handleFix(): int
    {
        $this->newLine();
        $this->comment('🔧 Running safe auto-fix...');
        $this->newLine();

        // Step 1: Clear cache
        $this->info('  [1/2] Clearing caches...');
        Artisan::call('optimize:clear');
        $this->info('  ✓ Caches cleared.');

        // Step 2: Migrate + seed
        $this->info('  [2/2] Running migrate --seed...');
        Artisan::call('migrate', ['--seed' => true, '--force' => true]);
        $this->info('  ✓ Migrations and seeders executed.');

        // Verify
        $this->newLine();
        $this->comment('Verifying tables...');
        $allOk = true;
        foreach (self::REQUIRED_TABLES as $table) {
            $exists = Schema::hasTable($table);
            if ($exists) {
                $this->line("  ✓ <info>{$table}</info> — OK");
            } else {
                $this->error("  ✗ {$table} — STILL MISSING");
                $allOk = false;
            }
        }

        $this->printUrls();

        $this->newLine();
        if ($allOk) {
            $this->info('✅ Auto-fix complete. All tables ready!');
        } else {
            $this->error('❌ Some tables still missing. Check migration files.');
        }
        $this->newLine();

        return $allOk ? self::SUCCESS : self::FAILURE;
    }

    private function handleFresh(): int
    {
        // Safety check: only allow in local environment
        if (app()->environment('production')) {
            $this->error('❌ --fresh is BLOCKED in production. This would drop all tables!');
            return self::FAILURE;
        }

        if (!app()->environment('local', 'testing')) {
            $this->warn("⚠️  APP_ENV is '" . app()->environment() . "' (not 'local').");
            if (!$this->confirm('Are you SURE you want to drop ALL tables? This is destructive!', false)) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        } else {
            if (!$this->confirm('⚠️  This will DROP ALL TABLES and re-create them. Continue?', false)) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        $this->newLine();
        $this->comment('🗑️  Running migrate:fresh --seed ...');
        Artisan::call('optimize:clear');
        Artisan::call('migrate:fresh', ['--seed' => true]);
        $this->info('  ✓ Database wiped and re-seeded.');

        $this->printUrls();

        $this->newLine();
        $this->info('✅ Fresh reset complete.');
        $this->newLine();

        return self::SUCCESS;
    }

    private function printUrls(): void
    {
        $this->newLine();
        $this->comment('Important URLs:');
        $base = config('app.url', 'http://localhost:8000');
        $urls = [
            'CTI Dashboard (default)'  => '/cti',
            'Sentinel Dashboard'       => '/sentinel/dashboard',
            'Setup / Health Check'     => '/setup',
            'Knowledge Entities'       => '/knowledge/entities',
            'Threat Actors'            => '/threats/actors',
            'Cases'                    => '/cases/incidents',
            'Diagnostics'              => '/settings/diagnostics',
        ];
        foreach ($urls as $label => $path) {
            $this->line("  {$label}: <info>{$base}{$path}</info>");
        }
    }
}
