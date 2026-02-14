<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Models\Node;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function search_page_returns_200()
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('search', ['q' => 'test']))
            ->assertStatus(200)
            ->assertSee('Search Results');
    }

    /** @test */
    public function search_finds_entities_by_name()
    {
        $user = User::factory()->create();
        Node::create(['type' => 'malware', 'name' => 'DarkComet RAT', 'description' => 'Remote access trojan']);
        Node::create(['type' => 'threat-actor', 'name' => 'APT29', 'description' => 'Russian group']);

        $response = $this->actingAs($user)
            ->get(route('search', ['q' => 'DarkComet']));

        $response->assertStatus(200)->assertSee('DarkComet RAT');
    }

    /** @test */
    public function search_finds_cases()
    {
        $user = User::factory()->create();
        CaseModel::create([
            'title' => 'Ransomware Incident Alpha',
            'severity' => 'critical',
            'status' => 'open',
            'owner_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('search', ['q' => 'Ransomware']));

        $response->assertStatus(200)->assertSee('Ransomware Incident Alpha');
    }

    /** @test */
    public function api_search_returns_json()
    {
        $user = User::factory()->create();
        Node::create(['type' => 'malware', 'name' => 'Emotet', 'description' => 'Banking trojan']);

        $response = $this->actingAs($user)
            ->getJson(route('search', ['q' => 'Emotet']));

        $response->assertStatus(200)
            ->assertJsonStructure(['results', 'query', 'count', 'search_url'])
            ->assertJsonFragment(['label' => 'Emotet']);
    }

    /** @test */
    public function short_query_returns_empty_results()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('search', ['q' => 'a']));

        $response->assertStatus(200)->assertJson(['results' => []]);
    }
}
