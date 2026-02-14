<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LogAnalysisController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\ThreatsController;
use App\Http\Controllers\ObservationsController;
use App\Http\Controllers\CasesController;
use App\Http\Controllers\InvestigationsController;
use App\Http\Controllers\IngestionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CtiDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes - Log Sentinel CTI Platform
|--------------------------------------------------------------------------
|
| Definisi route untuk aplikasi Log Sentinel - Threat & Security
| Intelligence Command Center. Mengintegrasikan Laravel dengan
| Python ML Service + STIX-inspired Knowledge Graph.
|
*/

// Authentication Routes
Auth::routes();

// Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// ========================================
// LOG SENTINEL ROUTES (PROTECTED BY AUTH)
// ========================================

// Redirect root ke CTI dashboard (halaman utama)
Route::get('/', function () {
    return redirect()->route('cti.dashboard');
});

// Legacy redirect: /dashboard juga ke CTI
Route::get('/dashboard', function () {
    return redirect()->route('cti.dashboard');
});

// Semua route Log Sentinel WAJIB login
Route::middleware(['auth'])->group(function () {
    // ========================================
    // CTI DASHBOARD (LANDING UTAMA)
    // ========================================
    Route::get('/cti', [CtiDashboardController::class, 'index'])
        ->name('cti.dashboard');

    // ========================================
    // SENTINEL (LOG ANOMALY DETECTION)
    // ========================================
    Route::prefix('sentinel')->group(function () {
        Route::get('/dashboard', [LogAnalysisController::class, 'dashboard'])
            ->name('sentinel.dashboard');
        Route::get('/logs', [LogAnalysisController::class, 'logList'])
            ->name('sentinel.logs');
        Route::get('/about', [LogAnalysisController::class, 'about'])
            ->name('sentinel.about');
    });

    // ========================================
    // CTI PLATFORM ROUTES
    // ========================================

    // --- THREATS ---
    Route::prefix('threats')->group(function () {
        Route::get('/actors',          [ThreatsController::class, 'actorsIndex'])->name('threats.actors.index');
        Route::get('/actors/create',   [ThreatsController::class, 'actorCreate'])->name('threats.actors.create');
        Route::post('/actors',         [ThreatsController::class, 'actorStore'])->name('threats.actors.store');

        Route::get('/malware',         [ThreatsController::class, 'malwareIndex'])->name('threats.malware.index');
        Route::get('/malware/create',  [ThreatsController::class, 'malwareCreate'])->name('threats.malware.create');
        Route::post('/malware',        [ThreatsController::class, 'malwareStore'])->name('threats.malware.store');

        Route::get('/campaigns',        [ThreatsController::class, 'campaignsIndex'])->name('threats.campaigns.index');
        Route::get('/campaigns/create', [ThreatsController::class, 'campaignCreate'])->name('threats.campaigns.create');
        Route::post('/campaigns',       [ThreatsController::class, 'campaignStore'])->name('threats.campaigns.store');

        Route::get('/intrusion-sets',        [ThreatsController::class, 'intrusionSetsIndex'])->name('threats.intrusion-sets.index');
        Route::get('/intrusion-sets/create', [ThreatsController::class, 'intrusionSetCreate'])->name('threats.intrusion-sets.create');
        Route::post('/intrusion-sets',       [ThreatsController::class, 'intrusionSetStore'])->name('threats.intrusion-sets.store');

        Route::get('/vulnerabilities',        [ThreatsController::class, 'vulnerabilitiesIndex'])->name('threats.vulnerabilities.index');
        Route::get('/vulnerabilities/create', [ThreatsController::class, 'vulnerabilityCreate'])->name('threats.vulnerabilities.create');
        Route::post('/vulnerabilities',       [ThreatsController::class, 'vulnerabilityStore'])->name('threats.vulnerabilities.store');

        Route::post('/quick-link',            [ThreatsController::class, 'quickLink'])->name('threats.quick-link');
        Route::post('/nodes/{node}/notes',    [ThreatsController::class, 'addNote'])->name('threats.add-note');
    });

    // --- KNOWLEDGE ---
    Route::prefix('knowledge')->group(function () {
        Route::get('/entities',             [KnowledgeController::class, 'entitiesIndex'])->name('knowledge.entities.index');
        Route::get('/entities/create',      [KnowledgeController::class, 'entityCreate'])->name('knowledge.entities.create');
        Route::post('/entities',            [KnowledgeController::class, 'entityStore'])->name('knowledge.entities.store');
        Route::get('/entities/{node}',      [KnowledgeController::class, 'entityShow'])->name('knowledge.entities.show');
        Route::get('/entities/{node}/edit', [KnowledgeController::class, 'entityEdit'])->name('knowledge.entities.edit');
        Route::put('/entities/{node}',      [KnowledgeController::class, 'entityUpdate'])->name('knowledge.entities.update');
        Route::delete('/entities/{node}',   [KnowledgeController::class, 'entityDestroy'])->name('knowledge.entities.destroy');

        Route::get('/relationships',        [KnowledgeController::class, 'relationshipsIndex'])->name('knowledge.relationships.index');
        Route::post('/relationships',       [KnowledgeController::class, 'relationshipStore'])->name('knowledge.relationships.store');
        Route::delete('/relationships/{edge}', [KnowledgeController::class, 'relationshipDestroy'])->name('knowledge.relationships.destroy');

        Route::get('/graph',                [KnowledgeController::class, 'graphExplorer'])->name('knowledge.graph');
    });

    // --- OBSERVATIONS ---
    Route::prefix('observations')->group(function () {
        Route::get('/',       [ObservationsController::class, 'index'])->name('observations.index');
        Route::get('/alerts', [ObservationsController::class, 'alerts'])->name('observations.alerts');
        Route::get('/correlations', [ObservationsController::class, 'correlations'])->name('observations.correlations');
        Route::post('/promote/{serverLog}', [ObservationsController::class, 'promoteToObservable'])->name('observations.promote');
        Route::post('/bulk-promote',  [ObservationsController::class, 'bulkPromote'])->name('observations.bulk-promote');
        Route::put('/triage/{node}',  [ObservationsController::class, 'triage'])->name('observations.triage');
    });

    // --- CASES ---
    Route::prefix('cases')->group(function () {
        Route::get('/incidents',             [CasesController::class, 'incidentsIndex'])->name('cases.incidents.index');
        Route::get('/incidents/create',      [CasesController::class, 'incidentCreate'])->name('cases.incidents.create');
        Route::post('/incidents',            [CasesController::class, 'incidentStore'])->name('cases.incidents.store');
        Route::get('/incidents/{case}',      [CasesController::class, 'incidentShow'])->name('cases.incidents.show');
        Route::put('/incidents/{case}',      [CasesController::class, 'incidentUpdate'])->name('cases.incidents.update');
        Route::delete('/incidents/{case}',   [CasesController::class, 'incidentDestroy'])->name('cases.incidents.destroy');

        Route::get('/tasks',                 [CasesController::class, 'tasksIndex'])->name('cases.tasks.index');
        Route::post('/tasks',                [CasesController::class, 'taskStore'])->name('cases.tasks.store');
        Route::put('/tasks/{task}',          [CasesController::class, 'taskUpdate'])->name('cases.tasks.update');

        Route::post('/items',                [CasesController::class, 'attachItem'])->name('cases.items.attach');
        Route::delete('/items/{item}',       [CasesController::class, 'detachItem'])->name('cases.items.detach');

        Route::get('/incidents/{case}/report', [CasesController::class, 'exportReport'])->name('cases.incidents.report');
    });

    // --- INVESTIGATIONS ---
    Route::get('/investigations', [InvestigationsController::class, 'index'])->name('investigations.index');

    // --- INGESTION ---
    Route::prefix('ingestion')->group(function () {
        Route::get('/connectors',            [IngestionController::class, 'connectors'])->name('ingestion.connectors');
        Route::get('/import',                [IngestionController::class, 'import'])->name('ingestion.import');
        Route::post('/import/stix',          [IngestionController::class, 'importStixBundle'])->name('ingestion.import.stix');
        Route::post('/connectors/{integration}/run', [IngestionController::class, 'runConnector'])->name('ingestion.connectors.run');
    });

    // --- SETTINGS ---
    Route::prefix('settings')->group(function () {
        Route::get('/users',                 [SettingsController::class, 'users'])->name('settings.users');
        Route::put('/users/{user}/role',     [SettingsController::class, 'assignRole'])->name('settings.users.assign-role');
        Route::get('/tokens',                [SettingsController::class, 'tokens'])->name('settings.tokens');
        Route::post('/tokens',               [SettingsController::class, 'createToken'])->name('settings.tokens.create');
        Route::delete('/tokens/{tokenId}',   [SettingsController::class, 'revokeToken'])->name('settings.tokens.revoke');
        Route::get('/taxonomy',              [SettingsController::class, 'taxonomy'])->name('settings.taxonomy');
        Route::post('/taxonomy',             [SettingsController::class, 'tagStore'])->name('settings.taxonomy.store');
        Route::delete('/taxonomy/{tag}',     [SettingsController::class, 'tagDestroy'])->name('settings.taxonomy.destroy');
        Route::get('/audit',                 [SettingsController::class, 'audit'])->name('settings.audit');
        Route::get('/diagnostics',           [SettingsController::class, 'diagnostics'])->name('settings.diagnostics');
    });

    // --- GLOBAL SEARCH ---
    Route::get('/search', [SearchController::class, 'search'])->name('search');
});

// ========================================
// API ENDPOINTS UNTUK AJAX
// ========================================

Route::prefix('api')->group(function () {
    // Endpoint untuk analisis log baru
    Route::post('/analyze', [LogAnalysisController::class, 'analyze'])
        ->name('api.analyze');

    // Endpoint untuk simulasi serangan
    Route::post('/simulate-attack', [LogAnalysisController::class, 'simulateAttack'])
        ->name('api.simulate');

    // Endpoint untuk mengambil log terbaru (polling)
    Route::get('/recent-logs', [LogAnalysisController::class, 'getRecentLogs'])
        ->name('api.recent-logs');

    // Endpoint untuk data chart
    Route::get('/chart-data', [LogAnalysisController::class, 'getChartData'])
        ->name('api.chart-data');

    // Endpoint untuk statistik dashboard
    Route::get('/stats', [LogAnalysisController::class, 'getStats'])
        ->name('api.stats');

    // Knowledge Graph API
    Route::get('/subgraph', [KnowledgeController::class, 'apiSubgraph'])
        ->name('api.subgraph');
    Route::get('/graph/suggest-relations', [KnowledgeController::class, 'apiSuggestRelations'])
        ->name('api.graph.suggest');
    Route::get('/graph/search-nodes', [KnowledgeController::class, 'apiSearchNodes'])
        ->name('api.graph.search-nodes');

    // Threat stats API
    Route::get('/threat-stats', [ThreatsController::class, 'apiStats'])
        ->name('api.threat-stats');
});

// ========================================
// ROUTE UNTUK PROFILE (dari Velzon)
// ========================================

Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])
    ->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])
    ->name('updatePassword');

// Fallback untuk halaman Velzon lainnya (jika diperlukan)
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])
    ->where('any', '^(?!api|cti|sentinel|dashboard|logs|about|threats|knowledge|observations|cases|investigations|ingestion|settings|search).*$')
    ->name('index');
