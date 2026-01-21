<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ServerLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;

/**
 * Test Suite untuk Log Sentinel Dashboard
 * 
 * Test ini memastikan semua halaman dan endpoint berfungsi dengan benar
 * sebelum deployment atau presentasi demo.
 * 
 * Tim Pengembang:
 * - JEREMY CHRISTO EMMANUELLE PANJAITAN (237006516084)
 * - MUHAMMAD AKBAR HADI PRATAMA (237006516058)
 * - FARREL ALFARIDZI (237006516028)
 * - CHOSMAS LAURENS RUMNGEWUR (217006516074)
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * User untuk autentikasi testing
     */
    protected User $user;

    /**
     * Setup untuk setiap test - membuat user dan login.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test: Halaman dashboard dapat diakses dan mengembalikan status 200.
     *
     * @return void
     */
    public function test_dashboard_page_returns_status_200(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('sentinel.dashboard');
    }

    /**
     * Test: Halaman dashboard menampilkan komponen yang diperlukan.
     *
     * @return void
     */
    public function test_dashboard_contains_required_components(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Log Sentinel');
        $response->assertSee('Total Request');
        $response->assertSee('Total Ancaman');
        $response->assertSee('Live Monitoring');
        $response->assertSee('Simulasi Serangan');
    }

    /**
     * Test: Halaman daftar log dapat diakses.
     *
     * @return void
     */
    public function test_logs_page_returns_status_200(): void
    {
        $response = $this->actingAs($this->user)->get('/logs');

        $response->assertStatus(200);
        $response->assertViewIs('sentinel.logs');
    }

    /**
     * Test: Halaman about dapat diakses.
     *
     * @return void
     */
    public function test_about_page_returns_status_200(): void
    {
        $response = $this->actingAs($this->user)->get('/about');

        $response->assertStatus(200);
        $response->assertViewIs('sentinel.about');
    }

    /**
     * Test: Halaman about menampilkan informasi tim pengembang.
     *
     * @return void
     */
    public function test_about_page_displays_team_members(): void
    {
        $response = $this->actingAs($this->user)->get('/about');

        $response->assertStatus(200);
        $response->assertSee('Jeremy Christo Emmanuelle Panjaitan');
        $response->assertSee('Muhammad Akbar Hadi Pratama');
        $response->assertSee('Farrel Alfaridzi');
        $response->assertSee('Chosmas Laurens Rumngewur');
        $response->assertSee('237006516084');
        $response->assertSee('237006516058');
        $response->assertSee('237006516028');
        $response->assertSee('217006516074');
    }

    /**
     * Test: Root URL redirect ke dashboard.
     *
     * @return void
     */
    public function test_root_url_redirects_to_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/');

        $response->assertRedirect('/dashboard');
    }

    /**
     * Test: Filter log normal berfungsi.
     *
     * @return void
     */
    public function test_logs_filter_normal_works(): void
    {
        // Buat beberapa log untuk testing
        ServerLog::factory()->count(5)->create(['prediction_result' => 'normal']);
        ServerLog::factory()->count(3)->create(['prediction_result' => 'anomaly']);

        $response = $this->actingAs($this->user)->get('/logs?filter=normal');

        $response->assertStatus(200);
    }

    /**
     * Test: Filter log anomaly berfungsi.
     *
     * @return void
     */
    public function test_logs_filter_anomaly_works(): void
    {
        // Buat beberapa log untuk testing
        ServerLog::factory()->count(5)->create(['prediction_result' => 'normal']);
        ServerLog::factory()->count(3)->create(['prediction_result' => 'anomaly']);

        $response = $this->actingAs($this->user)->get('/logs?filter=anomaly');

        $response->assertStatus(200);
    }

    /**
     * Test: Dashboard menampilkan statistik dengan benar.
     *
     * @return void
     */
    public function test_dashboard_shows_correct_statistics(): void
    {
        // Buat data log
        ServerLog::factory()->count(7)->create(['prediction_result' => 'normal']);
        ServerLog::factory()->count(3)->create(['prediction_result' => 'anomaly']);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        // Total logs harus 10
        $response->assertViewHas('totalLogs', 10);
        $response->assertViewHas('totalAnomalies', 3);
        $response->assertViewHas('totalNormal', 7);
    }

    /**
     * Test: Dashboard menampilkan System Status Badge (Cyber Command Center).
     *
     * @return void
     */
    public function test_dashboard_displays_system_status_badge(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('SYSTEM MONITORING: ACTIVE');
    }

    /**
     * Test: Dashboard menampilkan Live Cyber Threat Map container.
     *
     * @return void
     */
    public function test_dashboard_displays_cyber_threat_map(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('cyber-map');
        $response->assertSee('Live Cyber Threat Map');
        $response->assertSee('SIMULATED ATTACKS');
    }

    /**
     * Test: Dashboard memuat Leaflet.js library untuk threat map.
     *
     * @return void
     */
    public function test_dashboard_loads_leaflet_library(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('leaflet');
    }
}
