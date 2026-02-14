<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\Edge;
use App\Models\User;
use App\Services\GraphService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphServiceTest extends TestCase
{
    use RefreshDatabase;

    private GraphService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GraphService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function subgraph_depth_1_returns_direct_neighbors()
    {
        [$a, $b, $c] = $this->createChain();

        $result = $this->service->getSubgraph($a->id, 1);

        // depth 1: A + B (direct neighbor)
        $this->assertEquals(2, $result['meta']['node_count']);
        $this->assertEquals(1, $result['meta']['edge_count']);
    }

    /** @test */
    public function subgraph_depth_2_returns_two_hops()
    {
        [$a, $b, $c] = $this->createChain();

        $result = $this->service->getSubgraph($a->id, 2);

        // depth 2: A + B + C
        $this->assertEquals(3, $result['meta']['node_count']);
        $this->assertEquals(2, $result['meta']['edge_count']);
    }

    /** @test */
    public function subgraph_filter_confidence_works()
    {
        $high = Node::create(['type' => 'malware', 'name' => 'HighConf', 'confidence' => 90]);
        $low = Node::create(['type' => 'tool', 'name' => 'LowConf', 'confidence' => 20]);
        Edge::create(['type' => 'uses', 'from_node_id' => $high->id, 'to_node_id' => $low->id]);

        $result = $this->service->getSubgraph($high->id, 1, ['confidence_min' => 50]);

        // only high-confidence node passes the filter (low gets filtered out)
        $this->assertEquals(1, $result['meta']['node_count']);
    }

    /** @test */
    public function subgraph_max_nodes_truncates_and_flags()
    {
        $center = Node::create(['type' => 'threat-actor', 'name' => 'Center']);
        for ($i = 0; $i < 10; $i++) {
            $n = Node::create(['type' => 'malware', 'name' => "Mal-{$i}"]);
            Edge::create(['type' => 'uses', 'from_node_id' => $center->id, 'to_node_id' => $n->id]);
        }

        $result = $this->service->getSubgraph($center->id, 1, ['max_nodes' => 5]);

        $this->assertLessThanOrEqual(5, $result['meta']['node_count']);
        $this->assertTrue($result['meta']['truncated']);
    }

    /** @test */
    public function subgraph_cytoscape_format_is_correct()
    {
        $a = Node::create(['type' => 'threat-actor', 'name' => 'APT-Test', 'confidence' => 80, 'severity' => 'high']);
        $b = Node::create(['type' => 'malware', 'name' => 'TestRAT', 'confidence' => 70]);
        $edge = Edge::create(['type' => 'uses', 'from_node_id' => $a->id, 'to_node_id' => $b->id, 'confidence' => 85]);

        $result = $this->service->getSubgraph($a->id, 1);

        // Check Cytoscape node format
        $nodeData = $result['elements']['nodes'][0]['data'];
        $this->assertArrayHasKey('id', $nodeData);
        $this->assertArrayHasKey('label', $nodeData);
        $this->assertArrayHasKey('type', $nodeData);
        $this->assertArrayHasKey('color', $nodeData);
        $this->assertArrayHasKey('icon', $nodeData);

        // Check edge format
        $edgeData = $result['elements']['edges'][0]['data'];
        $this->assertArrayHasKey('id', $edgeData);
        $this->assertArrayHasKey('source', $edgeData);
        $this->assertArrayHasKey('target', $edgeData);
        $this->assertArrayHasKey('type', $edgeData);

        // Check meta
        $this->assertArrayHasKey('meta', $result);
        $this->assertArrayHasKey('truncated', $result['meta']);
    }

    /** @test */
    public function api_subgraph_returns_200()
    {
        $this->actingAs($this->user);
        $node = Node::create(['type' => 'malware', 'name' => 'TestMal']);

        $response = $this->getJson(route('api.subgraph', ['node_id' => $node->id, 'depth' => 1]));
        $response->assertOk()
            ->assertJsonStructure(['elements' => ['nodes', 'edges'], 'meta']);
    }

    /** @test */
    public function api_subgraph_validates_invalid_depth()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.subgraph', ['depth' => 10]));
        $response->assertStatus(422);
    }

    /** @test */
    public function api_search_nodes_returns_results()
    {
        $this->actingAs($this->user);
        Node::create(['type' => 'threat-actor', 'name' => 'APT-Searchable']);

        $response = $this->getJson(route('api.graph.search-nodes', ['q' => 'APT']));
        $response->assertOk()
            ->assertJsonCount(1, 'results');
    }

    /** @test */
    public function api_suggest_relations_returns_suggestions()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.graph.suggest', [
            'from_type' => 'threat-actor',
            'to_type' => 'malware',
        ]));
        $response->assertOk()
            ->assertJsonStructure(['suggestions']);

        $data = $response->json();
        $this->assertContains('uses', $data['suggestions']);
    }

    /** @test */
    public function get_neighbors_with_direction()
    {
        $a = Node::create(['type' => 'threat-actor', 'name' => 'A']);
        $b = Node::create(['type' => 'malware', 'name' => 'B']);
        $c = Node::create(['type' => 'tool', 'name' => 'C']);
        Edge::create(['type' => 'uses', 'from_node_id' => $a->id, 'to_node_id' => $b->id]);
        Edge::create(['type' => 'targets', 'from_node_id' => $c->id, 'to_node_id' => $a->id]);

        $out = $this->service->getNeighbors($a->id, 'out');
        $in = $this->service->getNeighbors($a->id, 'in');
        $both = $this->service->getNeighbors($a->id, 'both');

        $this->assertCount(1, $out);
        $this->assertCount(1, $in);
        $this->assertCount(2, $both);
    }

    /** @test */
    public function search_nodes_filters_by_type()
    {
        Node::create(['type' => 'threat-actor', 'name' => 'SearchTarget']);
        Node::create(['type' => 'malware', 'name' => 'SearchTarget Mal']);

        $results = $this->service->searchNodes('SearchTarget', ['malware']);
        $this->assertCount(1, $results);
        $this->assertEquals('malware', $results->first()->type);
    }

    private function createChain(): array
    {
        $a = Node::create(['type' => 'threat-actor', 'name' => 'A']);
        $b = Node::create(['type' => 'malware', 'name' => 'B']);
        $c = Node::create(['type' => 'tool', 'name' => 'C']);
        Edge::create(['type' => 'uses', 'from_node_id' => $a->id, 'to_node_id' => $b->id]);
        Edge::create(['type' => 'uses', 'from_node_id' => $b->id, 'to_node_id' => $c->id]);
        return [$a, $b, $c];
    }
}
