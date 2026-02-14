<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CtiEntrypointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_root_redirects_to_cti_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/');
        $response->assertRedirect('/cti');
    }

    /** @test */
    public function test_old_dashboard_redirects_to_cti(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertRedirect('/cti');
    }

    /** @test */
    public function test_cti_dashboard_uses_cti_layout(): void
    {
        $response = $this->actingAs($this->user)->get('/cti');
        $response->assertStatus(200);
        $response->assertSee('Threat Intelligence Dashboard');
        $response->assertSee('THREAT INTELLIGENCE');
        $response->assertSee('ANALYSIS');
    }

    /** @test */
    public function test_sentinel_dashboard_still_accessible(): void
    {
        $response = $this->actingAs($this->user)->get('/sentinel/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('sentinel.dashboard');
        $response->assertSee('Log Sentinel');
    }

    /** @test */
    public function test_cti_routes_accessible_when_authenticated(): void
    {
        // Knowledge entities
        $response = $this->actingAs($this->user)->get('/knowledge/entities');
        $response->assertStatus(200);

        // Threats actors
        $response = $this->actingAs($this->user)->get('/threats/actors');
        $response->assertStatus(200);

        // Observations
        $response = $this->actingAs($this->user)->get('/observations');
        $response->assertStatus(200);

        // Cases
        $response = $this->actingAs($this->user)->get('/cases/incidents');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_guest_redirected_to_login_for_cti(): void
    {
        $response = $this->get('/cti');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_mode_switcher_visible_on_cti_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/cti');
        $response->assertStatus(200);
        // Check mode switcher partial is included
        $response->assertSee('SWITCH MODE');
        $response->assertSee('Threat Intelligence (CTI)');
        $response->assertSee('Log Sentinel (Anomaly)');
    }

    /** @test */
    public function test_mode_switcher_visible_on_sentinel_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/sentinel/dashboard');
        $response->assertStatus(200);
        $response->assertSee('SWITCH MODE');
    }

    /** @test */
    public function test_diagnostics_page_accessible(): void
    {
        $response = $this->actingAs($this->user)->get('/settings/diagnostics');
        $response->assertStatus(200);
        $response->assertSee('System Diagnostics');
        $response->assertSee('nodes');
    }

    /** @test */
    public function test_cti_dashboard_not_caught_by_fallback(): void
    {
        // /cti should hit CtiDashboardController, not the fallback HomeController
        $response = $this->actingAs($this->user)->get('/cti');
        $response->assertStatus(200);
        $response->assertSee('Threat Intelligence Dashboard');
    }
}
