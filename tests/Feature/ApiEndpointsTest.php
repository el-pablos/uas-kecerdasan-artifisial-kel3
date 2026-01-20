<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ServerLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;

/**
 * Test Suite untuk API Endpoints Log Sentinel
 * 
 * Test ini memastikan semua API endpoint berfungsi dengan benar
 * untuk komunikasi dengan frontend dan ML Service.
 */
class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test: API analyze dapat menerima dan memproses log baru.
     *
     * @return void
     */
    public function test_api_analyze_accepts_valid_log_data(): void
    {
        // Mock response dari ML Service
        Http::fake([
            '*/predict' => Http::response([
                'status' => 'success',
                'data' => [
                    'prediction' => 'normal',
                    'severity_score' => 15.5,
                    'confidence' => 0.85,
                ]
            ], 200)
        ]);

        $logData = [
            'ip_address' => '192.168.1.100',
            'method' => 'GET',
            'url' => '/api/users',
            'status_code' => 200,
            'user_agent' => 'Mozilla/5.0',
            'response_time' => 150.5,
        ];

        $response = $this->postJson('/api/analyze', $logData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Log berhasil dianalisis',
        ]);

        // Pastikan data tersimpan di database
        $this->assertDatabaseHas('server_logs', [
            'ip_address' => '192.168.1.100',
            'method' => 'GET',
            'url' => '/api/users',
            'status_code' => 200,
        ]);
    }

    /**
     * Test: API analyze menolak data yang tidak valid.
     *
     * @return void
     */
    public function test_api_analyze_rejects_invalid_data(): void
    {
        $invalidData = [
            'ip_address' => '', // IP kosong
            'method' => 'GET',
            // url tidak ada
            'status_code' => 200,
        ];

        $response = $this->postJson('/api/analyze', $invalidData);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test: API analyze tetap berfungsi meski ML Service tidak tersedia.
     *
     * @return void
     */
    public function test_api_analyze_handles_ml_service_unavailable(): void
    {
        // Mock ML Service tidak tersedia
        Http::fake([
            '*/predict' => Http::response([], 500)
        ]);

        $logData = [
            'ip_address' => '10.0.0.1',
            'method' => 'POST',
            'url' => '/login',
            'status_code' => 401,
            'user_agent' => 'Test-Agent',
            'response_time' => 200,
        ];

        $response = $this->postJson('/api/analyze', $logData);

        // Harus tetap berhasil dengan default prediction
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'warning' => 'Prediksi menggunakan nilai default',
        ]);

        // Data tetap tersimpan
        $this->assertDatabaseHas('server_logs', [
            'ip_address' => '10.0.0.1',
            'prediction_result' => 'normal', // Default value
        ]);
    }

    /**
     * Test: API simulate-attack berfungsi dengan benar.
     *
     * @return void
     */
    public function test_api_simulate_attack_generates_logs(): void
    {
        // Mock ML Service
        Http::fake([
            '*/predict' => Http::response([
                'status' => 'success',
                'data' => [
                    'prediction' => 'anomaly',
                    'severity_score' => 85.0,
                    'confidence' => 0.92,
                ]
            ], 200)
        ]);

        $response = $this->postJson('/api/simulate-attack', [
            'attack_type' => 'ddos',
            'count' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total_generated' => 5,
        ]);

        // Pastikan log dibuat
        $this->assertEquals(5, ServerLog::count());
    }

    /**
     * Test: API recent-logs mengembalikan data dengan format yang benar.
     *
     * @return void
     */
    public function test_api_recent_logs_returns_correct_format(): void
    {
        // Buat beberapa log
        ServerLog::factory()->count(10)->create();

        $response = $this->getJson('/api/recent-logs?limit=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'ip_address',
                    'method',
                    'url',
                    'status_code',
                    'prediction',
                    'is_anomaly',
                ]
            ],
            'total',
        ]);

        // Pastikan limit berfungsi
        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test: API stats mengembalikan statistik dengan benar.
     *
     * @return void
     */
    public function test_api_stats_returns_correct_statistics(): void
    {
        // Buat data dengan hasil berbeda
        ServerLog::factory()->count(8)->create(['prediction_result' => 'normal']);
        ServerLog::factory()->count(2)->create(['prediction_result' => 'anomaly']);

        $response = $this->getJson('/api/stats');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_logs' => 10,
                'total_anomalies' => 2,
                'total_normal' => 8,
                'threat_percentage' => 20.0,
            ]
        ]);
    }

    /**
     * Test: API chart-data mengembalikan data chart dengan format yang benar.
     *
     * @return void
     */
    public function test_api_chart_data_returns_correct_format(): void
    {
        $response = $this->getJson('/api/chart-data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'labels',
                'normal',
                'anomaly',
            ]
        ]);

        // Labels harus 24 item (24 jam)
        $this->assertCount(24, $response->json('data.labels'));
    }
}
