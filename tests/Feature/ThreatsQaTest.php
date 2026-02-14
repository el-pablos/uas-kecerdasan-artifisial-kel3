<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Node;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class ThreatsQaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ═══════════════════════════════════════════════
    //  A) DB table missing → redirect to /setup
    // ═══════════════════════════════════════════════

    /** @test */
    public function test_threats_actors_redirects_to_setup_when_nodes_table_missing(): void
    {
        Schema::dropIfExists('nodes');

        $response = $this->actingAs($this->user)->get('/threats/actors');
        $response->assertRedirect('/setup');
    }

    // ═══════════════════════════════════════════════
    //  B) count() on null — list pages must be 200
    // ═══════════════════════════════════════════════

    /** @test */
    public function test_threats_actors_index_returns_200(): void
    {
        Node::create([
            'type' => 'threat-actor',
            'name' => 'Test APT',
            'description' => 'Test actor',
            'confidence' => 80,
            'severity' => 'high',
        ]);

        $response = $this->actingAs($this->user)->get('/threats/actors');
        $response->assertStatus(200);
        $response->assertSee('Test APT');
    }

    /** @test */
    public function test_threats_malware_index_returns_200(): void
    {
        Node::create([
            'type' => 'malware',
            'name' => 'TestRAT',
            'description' => 'A test malware',
            'confidence' => 90,
            'severity' => 'critical',
        ]);

        $response = $this->actingAs($this->user)->get('/threats/malware');
        $response->assertStatus(200);
        $response->assertSee('TestRAT');
    }

    /** @test */
    public function test_threats_campaigns_index_returns_200(): void
    {
        Node::create([
            'type' => 'campaign',
            'name' => 'Operation Test',
            'description' => 'A test campaign',
            'confidence' => 75,
            'severity' => 'medium',
        ]);

        $response = $this->actingAs($this->user)->get('/threats/campaigns');
        $response->assertStatus(200);
        $response->assertSee('Operation Test');
    }

    /** @test */
    public function test_threats_intrusion_sets_index_returns_200(): void
    {
        Node::create([
            'type' => 'intrusion-set',
            'name' => 'Test Cluster',
            'description' => 'A test intrusion set',
            'confidence' => 70,
            'severity' => 'high',
        ]);

        $response = $this->actingAs($this->user)->get('/threats/intrusion-sets');
        $response->assertStatus(200);
        $response->assertSee('Test Cluster');
    }

    /** @test */
    public function test_threats_vulnerabilities_index_returns_200(): void
    {
        Node::create([
            'type' => 'vulnerability',
            'name' => 'CVE-2099-0001',
            'description' => 'A test vuln',
            'confidence' => 95,
            'severity' => 'critical',
        ]);

        $response = $this->actingAs($this->user)->get('/threats/vulnerabilities');
        $response->assertStatus(200);
        $response->assertSee('CVE-2099-0001');
    }

    /** @test */
    public function test_threats_actors_index_empty_returns_200(): void
    {
        // No seed — should show empty state, not crash
        $response = $this->actingAs($this->user)->get('/threats/actors');
        $response->assertStatus(200);
        $response->assertSee('Create one');
    }

    // ═══════════════════════════════════════════════
    //  C) Route not defined on create pages
    // ═══════════════════════════════════════════════

    /** @test */
    public function test_threats_actors_create_returns_200(): void
    {
        $response = $this->actingAs($this->user)->get('/threats/actors/create');
        $response->assertStatus(200);
        $response->assertSee('Create');
    }

    /** @test */
    public function test_threats_malware_create_returns_200(): void
    {
        $response = $this->actingAs($this->user)->get('/threats/malware/create');
        $response->assertStatus(200);
        $response->assertSee('Create');
    }

    /** @test */
    public function test_threats_campaigns_create_returns_200(): void
    {
        $response = $this->actingAs($this->user)->get('/threats/campaigns/create');
        $response->assertStatus(200);
        $response->assertSee('Create');
    }

    /** @test */
    public function test_threats_intrusion_sets_create_returns_200(): void
    {
        $response = $this->actingAs($this->user)->get('/threats/intrusion-sets/create');
        $response->assertStatus(200);
        $response->assertSee('Create');
    }

    /** @test */
    public function test_threats_vulnerabilities_create_returns_200(): void
    {
        $response = $this->actingAs($this->user)->get('/threats/vulnerabilities/create');
        $response->assertStatus(200);
        $response->assertSee('Create');
    }

    /** @test */
    public function test_threats_actors_create_form_action_is_valid_route(): void
    {
        $response = $this->actingAs($this->user)->get('/threats/actors/create');
        $response->assertStatus(200);
        // Form action must point to /threats/actors (the store route)
        $response->assertSee('action="' . route('threats.actors.store') . '"', false);
    }

    /** @test */
    public function test_threats_malware_create_form_action_is_valid_route(): void
    {
        $response = $this->actingAs($this->user)->get('/threats/malware/create');
        $response->assertStatus(200);
        $response->assertSee('action="' . route('threats.malware.store') . '"', false);
    }

    // ═══════════════════════════════════════════════
    //  D) Store (POST) actually works
    // ═══════════════════════════════════════════════

    /** @test */
    public function test_threats_actors_store_creates_node_and_redirects(): void
    {
        $response = $this->actingAs($this->user)->post('/threats/actors', [
            'name' => 'New APT Group',
            'description' => 'A new threat actor',
            'confidence' => 80,
            'severity' => 'high',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('nodes', [
            'name' => 'New APT Group',
            'type' => 'threat-actor',
        ]);
    }

    /** @test */
    public function test_threats_malware_store_creates_node_and_redirects(): void
    {
        $response = $this->actingAs($this->user)->post('/threats/malware', [
            'name' => 'New Malware',
            'description' => 'A new malware sample',
            'confidence' => 90,
            'severity' => 'critical',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('nodes', [
            'name' => 'New Malware',
            'type' => 'malware',
        ]);
    }
}
