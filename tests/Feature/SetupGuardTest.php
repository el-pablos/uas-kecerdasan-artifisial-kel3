<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Http\Controllers\SetupController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class SetupGuardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ─── /setup page ───────────────────────────────────

    /** @test */
    public function test_setup_page_always_returns_200(): void
    {
        // No auth required
        $response = $this->get('/setup');
        $response->assertStatus(200);
        $response->assertSee('Setup Required');
    }

    /** @test */
    public function test_setup_page_shows_db_connection_info(): void
    {
        $response = $this->get('/setup');
        $response->assertStatus(200);
        $response->assertSee('Koneksi Database');
    }

    /** @test */
    public function test_setup_page_shows_table_checklist(): void
    {
        $response = $this->get('/setup');
        $response->assertStatus(200);
        $response->assertSee('nodes');
        $response->assertSee('edges');
        $response->assertSee('cases');
    }

    /** @test */
    public function test_setup_page_shows_all_ok_when_migrated(): void
    {
        $response = $this->get('/setup');
        $response->assertStatus(200);
        // Tables exist in test env via RefreshDatabase
        $response->assertSee('All OK');
        $response->assertSee('Masuk CTI Dashboard');
    }

    // ─── Middleware guard: tables present ───────────────

    /** @test */
    public function test_cti_dashboard_returns_200_when_tables_exist(): void
    {
        $response = $this->actingAs($this->user)->get('/cti');
        $response->assertStatus(200);
        $response->assertSee('Threat Intelligence Dashboard');
    }

    /** @test */
    public function test_threats_page_returns_200_when_tables_exist(): void
    {
        $response = $this->actingAs($this->user)->get('/threats/actors');
        $response->assertStatus(200);
    }

    // ─── Middleware guard: tables missing ───────────────

    /** @test */
    public function test_cti_redirects_to_setup_when_nodes_table_missing(): void
    {
        // Drop the nodes table to simulate missing migration
        Schema::dropIfExists('nodes');

        $response = $this->actingAs($this->user)->get('/cti');
        $response->assertRedirect('/setup');
    }

    /** @test */
    public function test_threats_redirects_to_setup_when_table_missing(): void
    {
        Schema::dropIfExists('nodes');

        $response = $this->actingAs($this->user)->get('/threats/actors');
        $response->assertRedirect('/setup');
    }

    /** @test */
    public function test_knowledge_redirects_to_setup_when_table_missing(): void
    {
        Schema::dropIfExists('edges');

        $response = $this->actingAs($this->user)->get('/knowledge/entities');
        $response->assertRedirect('/setup');
    }

    /** @test */
    public function test_cases_redirects_to_setup_when_table_missing(): void
    {
        Schema::dropIfExists('cases');

        $response = $this->actingAs($this->user)->get('/cases/incidents');
        $response->assertRedirect('/setup');
    }

    /** @test */
    public function test_setup_page_shows_missing_tables_with_fix_instructions(): void
    {
        Schema::dropIfExists('nodes');
        Schema::dropIfExists('edges');

        $response = $this->get('/setup');
        $response->assertStatus(200);
        $response->assertSee('Missing');
        $response->assertSee('php artisan migrate --seed');
        $response->assertSee('php artisan sentinel:doctor --fix');
    }

    // ─── Sentinel not affected ─────────────────────────

    /** @test */
    public function test_sentinel_dashboard_not_affected_by_cti_guard(): void
    {
        // Even with CTI tables missing, sentinel should work
        Schema::dropIfExists('nodes');

        $response = $this->actingAs($this->user)->get('/sentinel/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Log Sentinel');
    }

    // ─── SetupController helper ────────────────────────

    /** @test */
    public function test_cti_tables_ready_returns_true_when_all_present(): void
    {
        $this->assertTrue(SetupController::ctiTablesReady());
    }

    /** @test */
    public function test_cti_tables_ready_returns_false_when_table_missing(): void
    {
        Schema::dropIfExists('nodes');
        $this->assertFalse(SetupController::ctiTablesReady());
    }
}
