<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ServerLog;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Seeder ini membuat:
     * 1. User admin default untuk demo
     * 2. Dummy data server_logs untuk tampilan dashboard
     *
     * @return void
     */
    public function run()
    {
        // =============================================
        // 1. BUAT USER ADMIN DEFAULT UNTUK DEMO
        // =============================================
        User::create([
            'name' => 'Admin Sentinel',
            'email' => 'admin@logsentinel.com',
            'password' => Hash::make('password'),
            'avatar' => 'avatar-1.jpg',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ User admin berhasil dibuat: admin@logsentinel.com / password');

        // =============================================
        // 2. BUAT DUMMY DATA SERVER LOGS UNTUK DEMO
        // =============================================
        
        // Data log normal (traffic biasa)
        $normalLogs = [
            ['ip' => '192.168.1.10', 'method' => 'GET', 'url' => '/dashboard', 'status' => 200, 'response_time' => 120.5],
            ['ip' => '192.168.1.15', 'method' => 'GET', 'url' => '/api/users', 'status' => 200, 'response_time' => 85.3],
            ['ip' => '10.0.0.25', 'method' => 'POST', 'url' => '/api/login', 'status' => 200, 'response_time' => 250.0],
            ['ip' => '172.16.0.50', 'method' => 'GET', 'url' => '/home', 'status' => 200, 'response_time' => 95.2],
            ['ip' => '192.168.1.100', 'method' => 'GET', 'url' => '/products', 'status' => 200, 'response_time' => 180.4],
            ['ip' => '10.0.0.30', 'method' => 'PUT', 'url' => '/api/profile', 'status' => 200, 'response_time' => 320.1],
            ['ip' => '192.168.1.55', 'method' => 'GET', 'url' => '/about', 'status' => 200, 'response_time' => 75.8],
            ['ip' => '172.16.0.80', 'method' => 'GET', 'url' => '/contact', 'status' => 200, 'response_time' => 110.6],
        ];

        foreach ($normalLogs as $log) {
            ServerLog::create([
                'ip_address' => $log['ip'],
                'method' => $log['method'],
                'url' => $log['url'],
                'status_code' => $log['status'],
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'response_time' => $log['response_time'],
                'prediction_result' => 'normal',
                'severity_score' => rand(5, 25) / 10,
                'additional_data' => json_encode(['source' => 'seeder', 'type' => 'normal_traffic']),
                'created_at' => now()->subMinutes(rand(5, 120)),
            ]);
        }

        $this->command->info('✅ 8 log normal berhasil dibuat');

        // Data log anomaly (serangan terdeteksi)
        $anomalyLogs = [
            // DDoS Attack Pattern
            ['ip' => '45.33.32.156', 'method' => 'GET', 'url' => '/api/flood', 'status' => 503, 'response_time' => 5000.0, 'type' => 'DDoS Attack'],
            ['ip' => '45.33.32.156', 'method' => 'GET', 'url' => '/api/stress', 'status' => 503, 'response_time' => 4500.0, 'type' => 'DDoS Attack'],
            ['ip' => '45.33.32.156', 'method' => 'GET', 'url' => '/api/overload', 'status' => 503, 'response_time' => 4800.0, 'type' => 'DDoS Attack'],
            
            // Brute Force Attack Pattern
            ['ip' => '103.21.244.15', 'method' => 'POST', 'url' => '/login', 'status' => 401, 'response_time' => 150.0, 'type' => 'Brute Force'],
            ['ip' => '103.21.244.15', 'method' => 'POST', 'url' => '/admin/login', 'status' => 401, 'response_time' => 145.0, 'type' => 'Brute Force'],
            ['ip' => '103.21.244.15', 'method' => 'POST', 'url' => '/api/auth', 'status' => 401, 'response_time' => 160.0, 'type' => 'Brute Force'],
            
            // SQL Injection Attempt
            ['ip' => '185.220.101.33', 'method' => 'GET', 'url' => "/users?id=1'OR'1'='1", 'status' => 400, 'response_time' => 50.0, 'type' => 'SQL Injection'],
            ['ip' => '185.220.101.33', 'method' => 'POST', 'url' => '/search', 'status' => 400, 'response_time' => 45.0, 'type' => 'SQL Injection'],
            
            // Port Scanning
            ['ip' => '91.240.118.172', 'method' => 'GET', 'url' => '/admin', 'status' => 404, 'response_time' => 30.0, 'type' => 'Port Scan'],
            ['ip' => '91.240.118.172', 'method' => 'GET', 'url' => '/phpmyadmin', 'status' => 404, 'response_time' => 25.0, 'type' => 'Port Scan'],
            ['ip' => '91.240.118.172', 'method' => 'GET', 'url' => '/wp-admin', 'status' => 404, 'response_time' => 28.0, 'type' => 'Port Scan'],
            ['ip' => '91.240.118.172', 'method' => 'GET', 'url' => '/.env', 'status' => 403, 'response_time' => 15.0, 'type' => 'Port Scan'],
        ];

        foreach ($anomalyLogs as $log) {
            ServerLog::create([
                'ip_address' => $log['ip'],
                'method' => $log['method'],
                'url' => $log['url'],
                'status_code' => $log['status'],
                'user_agent' => 'curl/7.68.0',
                'response_time' => $log['response_time'],
                'prediction_result' => 'anomaly',
                'severity_score' => rand(70, 95) / 10,
                'additional_data' => json_encode([
                    'source' => 'seeder',
                    'attack_type' => $log['type'],
                    'threat_level' => 'high'
                ]),
                'created_at' => now()->subMinutes(rand(1, 60)),
            ]);
        }

        $this->command->info('✅ 12 log anomaly (serangan) berhasil dibuat');
        $this->command->info('');
        $this->command->info('🎯 Total: 20 server logs siap untuk demo');
        $this->command->info('📊 Statistik: 8 Normal | 12 Anomaly (60% threat rate)');
    }
}
