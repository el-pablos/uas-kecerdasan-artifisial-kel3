<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ServerLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * Security Test Suite untuk Log Sentinel
 * 
 * Test ini memastikan keamanan aplikasi dengan mengetes:
 * 1. Guest tidak bisa akses halaman protected (redirect ke login)
 * 2. User tanpa avatar tidak menyebabkan crash
 * 3. View variables selalu aman dari null
 * 
 * Tim Pengembang:
 * - JEREMY CHRISTO EMMANUELLE PANJAITAN (237006516084)
 * - MUHAMMAD AKBAR HADI PRATAMA (237006516058)
 * - FARREL ALFARIDZI (237006516028)
 * - CHOSMAS LAURENS RUMNGEWUR (217006516074)
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // ========================================
    // TEST 1: GUEST ACCESS PROTECTION
    // ========================================

    /**
     * Test: Guest mengakses /dashboard harus redirect ke /login, BUKAN error 500.
     *
     * @return void
     */
    public function test_guest_accessing_dashboard_redirects_to_login(): void
    {
        $response = $this->get('/dashboard');

        // WAJIB redirect (302), bukan error 500
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test: Guest mengakses /logs harus redirect ke /login.
     *
     * @return void
     */
    public function test_guest_accessing_logs_redirects_to_login(): void
    {
        $response = $this->get('/logs');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test: Guest mengakses /about harus redirect ke /login.
     *
     * @return void
     */
    public function test_guest_accessing_about_redirects_to_login(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test: Root URL (/) harus redirect ke dashboard, lalu ke login jika guest.
     *
     * @return void
     */
    public function test_guest_accessing_root_url_eventually_redirects_to_login(): void
    {
        $response = $this->get('/');

        // Root akan redirect ke dashboard
        $response->assertStatus(302);
        
        // Follow redirect - akan ke login karena dashboard protected
        $response = $this->followingRedirects()->get('/');
        $response->assertStatus(200);
        // Harus di halaman login
        $response->assertSee('Sign In');
    }

    // ========================================
    // TEST 2: NULL AVATAR SAFETY
    // ========================================

    /**
     * Test: User dengan avatar NULL tidak menyebabkan crash.
     *
     * @return void
     */
    public function test_user_with_null_avatar_can_access_dashboard(): void
    {
        // Buat user dengan avatar null
        $user = User::factory()->create([
            'avatar' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        // Harus status 200, bukan 500
        $response->assertStatus(200);
        $response->assertViewIs('sentinel.dashboard');
    }

    /**
     * Test: User dengan avatar empty string tidak menyebabkan crash.
     *
     * @return void
     */
    public function test_user_with_empty_avatar_can_access_dashboard(): void
    {
        // Buat user dengan avatar empty string
        $user = User::factory()->create([
            'avatar' => '',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        // Harus status 200, bukan 500
        $response->assertStatus(200);
    }

    /**
     * Test: User dengan avatar valid dapat mengakses dashboard.
     *
     * @return void
     */
    public function test_user_with_valid_avatar_can_access_dashboard(): void
    {
        // Buat user dengan avatar valid
        $user = User::factory()->create([
            'avatar' => 'avatar-1.jpg',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('sentinel.dashboard');
    }

    // ========================================
    // TEST 3: VIEW VARIABLES SAFETY
    // ========================================

    /**
     * Test: Dashboard view tidak crash meskipun database kosong.
     *
     * @return void
     */
    public function test_dashboard_works_with_empty_database(): void
    {
        $user = User::factory()->create();

        // Tidak ada ServerLog sama sekali
        $this->assertEquals(0, ServerLog::count());

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        // View harus meng-handle empty state dengan baik
        $response->assertViewHas('totalLogs', 0);
        $response->assertViewHas('totalAnomalies', 0);
    }

    /**
     * Test: Logs page tidak crash meskipun database kosong.
     *
     * @return void
     */
    public function test_logs_page_works_with_empty_database(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/logs');

        $response->assertStatus(200);
        $response->assertViewIs('sentinel.logs');
    }

    /**
     * Test: About page selalu dapat diakses oleh authenticated user.
     *
     * @return void
     */
    public function test_about_page_works_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/about');

        $response->assertStatus(200);
        $response->assertViewIs('sentinel.about');
        // Pastikan nama tim muncul
        $response->assertSee('Jeremy Christo Emmanuelle Panjaitan');
    }

    // ========================================
    // TEST 4: AUTHENTICATION STATE CONSISTENCY
    // ========================================

    /**
     * Test: User yang sudah login dapat logout dengan benar.
     *
     * @return void
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        // Login dulu
        $this->actingAs($user);

        // Logout
        $response = $this->post('/logout');

        $response->assertStatus(302);
        // Setelah logout, akses dashboard harus redirect ke login
        $this->assertGuest();
    }

    /**
     * Test: Setelah logout, akses ke protected route harus redirect.
     *
     * @return void
     */
    public function test_after_logout_protected_routes_redirect_to_login(): void
    {
        $user = User::factory()->create();

        // Login
        $this->actingAs($user);
        
        // Akses dashboard - harus sukses
        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Logout
        $this->post('/logout');

        // Akses dashboard lagi sebagai guest - harus redirect
        $response = $this->get('/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ========================================
    // TEST 5: MULTIPLE USER SCENARIOS
    // ========================================

    /**
     * Test: Multiple users dengan berbagai kondisi avatar.
     *
     * @return void
     */
    public function test_multiple_users_with_different_avatar_states(): void
    {
        // User 1: Avatar normal
        $user1 = User::factory()->create(['avatar' => 'avatar-1.jpg']);
        
        // User 2: Avatar null
        $user2 = User::factory()->create(['avatar' => null]);
        
        // User 3: Avatar empty
        $user3 = User::factory()->create(['avatar' => '']);

        // Semua harus bisa akses dashboard tanpa crash
        $this->actingAs($user1)->get('/dashboard')->assertStatus(200);
        $this->actingAs($user2)->get('/dashboard')->assertStatus(200);
        $this->actingAs($user3)->get('/dashboard')->assertStatus(200);
    }
}
