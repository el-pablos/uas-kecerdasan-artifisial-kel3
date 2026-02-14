<?php

namespace Tests\Feature;

use App\Models\Edge;
use App\Models\Node;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IngestMitreAttackTest extends TestCase
{
    use RefreshDatabase;

    private function fakeMitreBundle(int $entityCount = 3, int $relCount = 2): array
    {
        $entities = [];
        $relationships = [];

        for ($i = 1; $i <= $entityCount; $i++) {
            $entities[] = [
                'id' => "attack-pattern--fake-{$i}",
                'type' => 'attack-pattern',
                'name' => "Fake Technique {$i}",
                'description' => "Description for technique {$i}",
                'external_references' => [
                    ['source_name' => 'mitre-attack', 'external_id' => "T100{$i}", 'url' => "https://attack.mitre.org/techniques/T100{$i}"],
                ],
                'kill_chain_phases' => [
                    ['kill_chain_name' => 'mitre-attack', 'phase_name' => 'execution'],
                ],
            ];
        }

        for ($i = 1; $i <= min($relCount, $entityCount - 1); $i++) {
            $relationships[] = [
                'id' => "relationship--fake-rel-{$i}",
                'type' => 'relationship',
                'source_ref' => "attack-pattern--fake-{$i}",
                'target_ref' => "attack-pattern--fake-" . ($i + 1),
                'relationship_type' => 'uses',
            ];
        }

        return [
            'type' => 'bundle',
            'id' => 'bundle--fake',
            'objects' => array_merge($entities, $relationships),
        ];
    }

    /** @test */
    public function dry_run_does_not_create_records()
    {
        $bundle = $this->fakeMitreBundle();
        $bundleJson = json_encode($bundle);

        // Write temp file
        $path = storage_path('app/test-mitre.json');
        file_put_contents($path, $bundleJson);

        $this->artisan('ingest:mitre-attack', [
            '--dry-run' => true,
            '--source' => 'local',
            '--file' => $path,
        ])->assertSuccessful();

        $this->assertEquals(0, Node::count());
        $this->assertEquals(0, Edge::count());

        @unlink($path);
    }

    /** @test */
    public function imports_entities_and_relationships_from_local_file()
    {
        $bundle = $this->fakeMitreBundle(5, 3);
        $path = storage_path('app/test-mitre2.json');
        file_put_contents($path, json_encode($bundle));

        $this->artisan('ingest:mitre-attack', [
            '--source' => 'local',
            '--file' => $path,
        ])->assertSuccessful();

        $this->assertEquals(5, Node::count());
        $this->assertEquals(3, Edge::count());

        // Verify STIX IDs stored
        $node = Node::where('stix_id', 'attack-pattern--fake-1')->first();
        $this->assertNotNull($node);
        $this->assertEquals('Fake Technique 1', $node->name);
        $this->assertEquals('attack-pattern', $node->type);
        $this->assertEquals('critical', $node->severity); // execution phase = critical
        $this->assertEquals('T1001', $node->raw['mitre_attack_id']);

        @unlink($path);
    }

    /** @test */
    public function limit_option_restricts_entity_count()
    {
        $bundle = $this->fakeMitreBundle(10, 5);
        $path = storage_path('app/test-mitre3.json');
        file_put_contents($path, json_encode($bundle));

        $this->artisan('ingest:mitre-attack', [
            '--source' => 'local',
            '--file' => $path,
            '--limit' => 3,
        ])->assertSuccessful();

        $this->assertEquals(3, Node::count());

        @unlink($path);
    }

    /** @test */
    public function duplicate_import_skips_existing_nodes()
    {
        $bundle = $this->fakeMitreBundle(3, 2);
        $path = storage_path('app/test-mitre4.json');
        file_put_contents($path, json_encode($bundle));

        // First import
        $this->artisan('ingest:mitre-attack', [
            '--source' => 'local',
            '--file' => $path,
        ])->assertSuccessful();

        $this->assertEquals(3, Node::count());
        $this->assertEquals(2, Edge::count());

        // Second import — should skip duplicates
        $this->artisan('ingest:mitre-attack', [
            '--source' => 'local',
            '--file' => $path,
        ])->assertSuccessful();

        // Counts should remain the same
        $this->assertEquals(3, Node::count());
        $this->assertEquals(2, Edge::count());

        @unlink($path);
    }

    /** @test */
    public function handles_missing_file_gracefully()
    {
        $this->artisan('ingest:mitre-attack', [
            '--source' => 'local',
            '--file' => '/nonexistent/path.json',
        ])->assertFailed();
    }
}
