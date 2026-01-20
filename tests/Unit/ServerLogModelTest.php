<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ServerLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Test untuk Model ServerLog
 * 
 * Test ini memastikan model dan atribut-atributnya berfungsi dengan benar.
 */
class ServerLogModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Model dapat dibuat dengan data yang valid.
     *
     * @return void
     */
    public function test_server_log_can_be_created(): void
    {
        $log = ServerLog::create([
            'ip_address' => '192.168.1.1',
            'method' => 'GET',
            'url' => '/api/test',
            'status_code' => 200,
            'user_agent' => 'Test Agent',
            'response_time' => 150.5,
            'prediction_result' => 'normal',
            'severity_score' => 10.0,
            'confidence_score' => 0.95,
        ]);

        $this->assertDatabaseHas('server_logs', [
            'id' => $log->id,
            'ip_address' => '192.168.1.1',
        ]);
    }

    /**
     * Test: Method isAnomaly() mengembalikan nilai yang benar.
     *
     * @return void
     */
    public function test_is_anomaly_returns_correct_value(): void
    {
        $normalLog = ServerLog::factory()->create(['prediction_result' => 'normal']);
        $anomalyLog = ServerLog::factory()->create(['prediction_result' => 'anomaly']);

        $this->assertFalse($normalLog->isAnomaly());
        $this->assertTrue($anomalyLog->isAnomaly());
    }

    /**
     * Test: Scope anomalies() berfungsi dengan benar.
     *
     * @return void
     */
    public function test_scope_anomalies_returns_only_anomalies(): void
    {
        ServerLog::factory()->count(5)->create(['prediction_result' => 'normal']);
        ServerLog::factory()->count(3)->create(['prediction_result' => 'anomaly']);

        $anomalies = ServerLog::anomalies()->get();

        $this->assertCount(3, $anomalies);
        $this->assertTrue($anomalies->every(fn($log) => $log->prediction_result === 'anomaly'));
    }

    /**
     * Test: Scope normal() berfungsi dengan benar.
     *
     * @return void
     */
    public function test_scope_normal_returns_only_normal(): void
    {
        ServerLog::factory()->count(5)->create(['prediction_result' => 'normal']);
        ServerLog::factory()->count(3)->create(['prediction_result' => 'anomaly']);

        $normalLogs = ServerLog::normal()->get();

        $this->assertCount(5, $normalLogs);
        $this->assertTrue($normalLogs->every(fn($log) => $log->prediction_result === 'normal'));
    }

    /**
     * Test: Scope highSeverity() berfungsi dengan benar.
     *
     * @return void
     */
    public function test_scope_high_severity_returns_correct_logs(): void
    {
        ServerLog::factory()->create(['severity_score' => 80]);
        ServerLog::factory()->create(['severity_score' => 90]);
        ServerLog::factory()->create(['severity_score' => 50]);
        ServerLog::factory()->create(['severity_score' => 30]);

        $highSeverity = ServerLog::highSeverity(70)->get();

        $this->assertCount(2, $highSeverity);
    }

    /**
     * Test: Badge class attribute mengembalikan nilai yang benar.
     *
     * @return void
     */
    public function test_badge_class_attribute_returns_correct_class(): void
    {
        $normalLog = ServerLog::factory()->create(['prediction_result' => 'normal']);
        $anomalyLog = ServerLog::factory()->create(['prediction_result' => 'anomaly']);

        $this->assertEquals('bg-success', $normalLog->badge_class);
        $this->assertEquals('bg-danger', $anomalyLog->badge_class);
    }

    /**
     * Test: Status icon attribute mengembalikan nilai yang benar.
     *
     * @return void
     */
    public function test_status_icon_attribute_returns_correct_icon(): void
    {
        $normalLog = ServerLog::factory()->create(['prediction_result' => 'normal']);
        $anomalyLog = ServerLog::factory()->create(['prediction_result' => 'anomaly']);

        $this->assertEquals('ri-check-fill', $normalLog->status_icon);
        $this->assertEquals('ri-alert-fill', $anomalyLog->status_icon);
    }

    /**
     * Test: Additional data di-cast sebagai array.
     *
     * @return void
     */
    public function test_additional_data_is_cast_to_array(): void
    {
        $log = ServerLog::factory()->create([
            'additional_data' => ['key' => 'value', 'test' => true],
        ]);

        $this->assertIsArray($log->additional_data);
        $this->assertEquals('value', $log->additional_data['key']);
    }

    /**
     * Test: Fillable attributes dapat di-assign secara massal.
     *
     * @return void
     */
    public function test_fillable_attributes_can_be_mass_assigned(): void
    {
        $data = [
            'ip_address' => '10.0.0.1',
            'method' => 'POST',
            'url' => '/api/data',
            'status_code' => 201,
            'user_agent' => 'Test',
            'response_time' => 100,
            'prediction_result' => 'normal',
            'severity_score' => 5,
            'confidence_score' => 0.99,
            'request_id' => 'test-123',
        ];

        $log = ServerLog::create($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $log->$key);
        }
    }
}
