<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupController extends Controller
{
    /**
     * Required tables for CTI platform.
     */
    public const REQUIRED_TABLES = [
        'nodes',
        'edges',
        'cases',
        'case_tasks',
        'case_items',
        'tags',
        'taggables',
        'activity_logs',
        'integrations',
    ];

    /**
     * Check if all required CTI tables exist.
     */
    public static function ctiTablesReady(): bool
    {
        foreach (self::REQUIRED_TABLES as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Show setup/status page.
     */
    public function index()
    {
        $dbDriver   = config('database.default');
        $dbName     = config("database.connections.{$dbDriver}.database");

        $canConnect = false;
        try {
            DB::connection()->getPdo();
            $canConnect = true;
        } catch (\Throwable $e) {
            // DB not reachable
        }

        $tableStatus = [];
        foreach (self::REQUIRED_TABLES as $table) {
            $exists = $canConnect && Schema::hasTable($table);
            $tableStatus[$table] = [
                'exists' => $exists,
                'rows'   => $exists ? DB::table($table)->count() : 0,
            ];
        }

        $allReady   = $canConnect && self::ctiTablesReady();
        $missingCount = collect($tableStatus)->where('exists', false)->count();

        return view('setup.index', compact(
            'dbDriver',
            'dbName',
            'canConnect',
            'tableStatus',
            'allReady',
            'missingCount'
        ));
    }
}
