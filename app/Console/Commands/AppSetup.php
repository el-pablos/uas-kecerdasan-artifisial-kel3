<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AppSetup extends Command
{
    protected $signature = 'app:setup';
    protected $description = 'One-shot setup: clear cache, migrate, seed, storage link';

    public function handle(): int
    {
        $this->info('');
        $this->info('🚀 Log Sentinel CTI — App Setup');
        $this->info(str_repeat('─', 50));
        $this->newLine();

        // 1) Clear caches
        $this->info('[1/3] Clearing caches...');
        Artisan::call('optimize:clear');
        $this->info('  ✓ Done.');

        // 2) Migrate + seed
        $this->info('[2/3] Running migrate --seed...');
        Artisan::call('migrate', ['--seed' => true, '--force' => true]);
        $this->info('  ✓ Database migrated and seeded.');

        // 3) Storage link
        $this->info('[3/3] Creating storage link...');
        try {
            Artisan::call('storage:link');
            $this->info('  ✓ Storage linked.');
        } catch (\Throwable $e) {
            $this->warn('  ⚠ Storage link already exists (skipped).');
        }

        // Verify
        $this->newLine();
        $this->comment('Verifying required tables...');
        $tables = ['nodes', 'edges', 'cases', 'case_tasks', 'case_items', 'tags', 'taggables', 'activity_logs', 'integrations'];
        $allOk = true;
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->line("  ✓ <info>{$table}</info>");
            } else {
                $this->error("  ✗ {$table} — MISSING");
                $allOk = false;
            }
        }

        $this->newLine();
        $base = config('app.url', 'http://localhost:8000');
        if ($allOk) {
            $this->info('✅ Setup complete! Open your browser:');
            $this->line("  CTI Dashboard:      <info>{$base}/cti</info>");
            $this->line("  Sentinel Dashboard: <info>{$base}/sentinel/dashboard</info>");
            $this->line("  Setup Status:       <info>{$base}/setup</info>");
        } else {
            $this->error('❌ Some tables are still missing. Check your migration files.');
        }
        $this->newLine();

        return $allOk ? self::SUCCESS : self::FAILURE;
    }
}
