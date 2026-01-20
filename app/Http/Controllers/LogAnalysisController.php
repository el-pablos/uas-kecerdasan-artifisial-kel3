<?php

namespace App\Http\Controllers;

use App\Models\ServerLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * LogAnalysisController
 * 
 * Controller ini menangani semua operasi terkait analisis log server,
 * termasuk komunikasi dengan ML Service Python untuk deteksi anomali.
 * 
 * Tim Pengembang:
 * - JEREMY CHRISTO EMMANUELLE PANJAITAN (237006516084)
 * - MUHAMMAD AKBAR HADI PRATAMA (237006516058)
 * - FARREL ALFARIDZI (237006516028)
 * - CHOSMAS LAURENS RUMNGEWUR (217006516074)
 */
class LogAnalysisController extends Controller
{
    /**
     * URL endpoint ML Service Python.
     *
     * @var string
     */
    protected $mlServiceUrl;

    /**
     * Timeout untuk request ke ML Service (dalam detik).
     *
     * @var int
     */
    protected $timeout = 10;

    /**
     * Konstruktor controller.
     */
    public function __construct()
    {
        $this->mlServiceUrl = env('ML_SERVICE_URL', 'http://127.0.0.1:5000');
    }

    /**
     * Menampilkan halaman dashboard utama.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Mengambil statistik untuk dashboard
        $totalLogs = ServerLog::count();
        $totalAnomalies = ServerLog::anomalies()->count();
        $totalNormal = ServerLog::normal()->count();
        
        // Menghitung persentase ancaman
        $threatPercentage = $totalLogs > 0 
            ? round(($totalAnomalies / $totalLogs) * 100, 2) 
            : 0;

        // Mengambil log terbaru untuk live monitoring
        $recentLogs = ServerLog::orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        // Data untuk chart - log per jam dalam 24 jam terakhir
        $chartData = $this->getHourlyChartData();

        // Statistik severity
        $highSeverityCount = ServerLog::anomalies()
            ->highSeverity(70)
            ->count();

        return view('sentinel.dashboard', compact(
            'totalLogs',
            'totalAnomalies',
            'totalNormal',
            'threatPercentage',
            'recentLogs',
            'chartData',
            'highSeverityCount'
        ));
    }

    /**
     * Menyimpan dan menganalisis log baru.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyze(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'ip_address' => 'required|string|max:45',
            'method' => 'required|string|max:10',
            'url' => 'required|string',
            'status_code' => 'required|integer|min:100|max:599',
            'user_agent' => 'nullable|string',
            'response_time' => 'nullable|numeric|min:0',
        ]);

        // Generate request ID untuk tracking
        $requestId = Str::uuid()->toString();

        try {
            // Kirim data ke ML Service untuk prediksi
            $predictionResult = $this->sendToMlService($validated);

            // Simpan ke database dengan hasil prediksi
            $serverLog = ServerLog::create([
                'ip_address' => $validated['ip_address'],
                'method' => strtoupper($validated['method']),
                'url' => $validated['url'],
                'status_code' => $validated['status_code'],
                'user_agent' => $validated['user_agent'] ?? 'Unknown',
                'response_time' => $validated['response_time'] ?? 0,
                'prediction_result' => $predictionResult['prediction'] ?? 'normal',
                'severity_score' => $predictionResult['severity_score'] ?? 0,
                'confidence_score' => $predictionResult['confidence'] ?? 0,
                'request_id' => $requestId,
                'additional_data' => [
                    'ml_response' => $predictionResult,
                    'analyzed_at' => now()->toISOString(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Log berhasil dianalisis',
                'data' => [
                    'log_id' => $serverLog->id,
                    'request_id' => $requestId,
                    'prediction' => $serverLog->prediction_result,
                    'severity_score' => $serverLog->severity_score,
                    'is_anomaly' => $serverLog->isAnomaly(),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Gagal menganalisis log: ' . $e->getMessage());

            // Jika ML Service gagal, simpan dengan prediksi default
            $serverLog = ServerLog::create([
                'ip_address' => $validated['ip_address'],
                'method' => strtoupper($validated['method']),
                'url' => $validated['url'],
                'status_code' => $validated['status_code'],
                'user_agent' => $validated['user_agent'] ?? 'Unknown',
                'response_time' => $validated['response_time'] ?? 0,
                'prediction_result' => 'normal', // Default jika ML service tidak tersedia
                'severity_score' => 0,
                'confidence_score' => 0,
                'request_id' => $requestId,
                'additional_data' => [
                    'error' => $e->getMessage(),
                    'ml_service_status' => 'unavailable',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Log tersimpan, namun ML Service tidak tersedia',
                'warning' => 'Prediksi menggunakan nilai default',
                'data' => [
                    'log_id' => $serverLog->id,
                    'request_id' => $requestId,
                    'prediction' => 'normal',
                ],
            ], 201);
        }
    }

    /**
     * Simulasi serangan untuk demonstrasi.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function simulateAttack(Request $request)
    {
        $attackType = $request->input('attack_type', 'random');
        $count = min($request->input('count', 5), 20); // Maksimal 20 log per simulasi

        $attackPatterns = $this->getAttackPatterns($attackType);
        $results = [];

        foreach ($attackPatterns as $index => $pattern) {
            if ($index >= $count) break;

            try {
                $predictionResult = $this->sendToMlService($pattern);

                $serverLog = ServerLog::create([
                    'ip_address' => $pattern['ip_address'],
                    'method' => $pattern['method'],
                    'url' => $pattern['url'],
                    'status_code' => $pattern['status_code'],
                    'user_agent' => $pattern['user_agent'],
                    'response_time' => $pattern['response_time'],
                    'prediction_result' => $predictionResult['prediction'] ?? 'anomaly',
                    'severity_score' => $predictionResult['severity_score'] ?? 75,
                    'confidence_score' => $predictionResult['confidence'] ?? 0.8,
                    'request_id' => Str::uuid()->toString(),
                    'additional_data' => [
                        'simulation' => true,
                        'attack_type' => $attackType,
                    ],
                ]);

                $results[] = [
                    'id' => $serverLog->id,
                    'prediction' => $serverLog->prediction_result,
                    'severity' => $serverLog->severity_score,
                ];

            } catch (\Exception $e) {
                // Jika ML service tidak tersedia, tetap simpan sebagai anomaly
                $serverLog = ServerLog::create([
                    'ip_address' => $pattern['ip_address'],
                    'method' => $pattern['method'],
                    'url' => $pattern['url'],
                    'status_code' => $pattern['status_code'],
                    'user_agent' => $pattern['user_agent'],
                    'response_time' => $pattern['response_time'],
                    'prediction_result' => 'anomaly',
                    'severity_score' => 75,
                    'confidence_score' => 0.8,
                    'request_id' => Str::uuid()->toString(),
                    'additional_data' => [
                        'simulation' => true,
                        'attack_type' => $attackType,
                        'ml_unavailable' => true,
                    ],
                ]);

                $results[] = [
                    'id' => $serverLog->id,
                    'prediction' => 'anomaly',
                    'severity' => 75,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Simulasi {$attackType} berhasil dijalankan",
            'total_generated' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * Mengambil data log terbaru untuk live monitoring.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentLogs(Request $request)
    {
        $limit = min($request->input('limit', 20), 100);
        $lastId = $request->input('last_id', 0);

        $query = ServerLog::orderBy('created_at', 'desc');

        if ($lastId > 0) {
            $query->where('id', '>', $lastId);
        }

        $logs = $query->take($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'ip_address' => $log->ip_address,
                    'method' => $log->method,
                    'url' => Str::limit($log->url, 50),
                    'status_code' => $log->status_code,
                    'prediction' => $log->prediction_result,
                    'severity_score' => $log->severity_score,
                    'is_anomaly' => $log->isAnomaly(),
                    'badge_class' => $log->badge_class,
                    'created_at' => $log->created_at->format('H:i:s'),
                    'created_at_full' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $logs->count(),
        ]);
    }

    /**
     * Mengambil data statistik untuk chart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData()
    {
        $chartData = $this->getHourlyChartData();

        return response()->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }

    /**
     * Mengambil statistik dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $totalLogs = ServerLog::count();
        $totalAnomalies = ServerLog::anomalies()->count();
        $totalNormal = ServerLog::normal()->count();
        $threatPercentage = $totalLogs > 0 
            ? round(($totalAnomalies / $totalLogs) * 100, 2) 
            : 0;

        // Statistik hari ini
        $todayLogs = ServerLog::whereDate('created_at', today())->count();
        $todayAnomalies = ServerLog::anomalies()
            ->whereDate('created_at', today())
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_logs' => $totalLogs,
                'total_anomalies' => $totalAnomalies,
                'total_normal' => $totalNormal,
                'threat_percentage' => $threatPercentage,
                'today_logs' => $todayLogs,
                'today_anomalies' => $todayAnomalies,
            ],
        ]);
    }

    /**
     * Halaman daftar semua log.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function logList(Request $request)
    {
        $query = ServerLog::orderBy('created_at', 'desc');

        // Filter berdasarkan prediction
        if ($request->has('filter') && $request->filter !== 'all') {
            $query->where('prediction_result', $request->filter);
        }

        // Filter berdasarkan tanggal
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25);

        return view('sentinel.logs', compact('logs'));
    }

    /**
     * Halaman tentang sistem.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        $teamMembers = [
            [
                'name' => 'Jeremy Christo Emmanuelle Panjaitan',
                'nim' => '237006516084',
                'role' => 'Lead Developer',
            ],
            [
                'name' => 'Muhammad Akbar Hadi Pratama',
                'nim' => '237006516058',
                'role' => 'Backend Developer',
            ],
            [
                'name' => 'Farrel Alfaridzi',
                'nim' => '237006516028',
                'role' => 'Frontend Developer',
            ],
            [
                'name' => 'Chosmas Laurens Rumngewur',
                'nim' => '217006516074',
                'role' => 'ML Engineer',
            ],
        ];

        // Check ML Service status
        $mlServiceStatus = $this->checkMlServiceStatus();

        return view('sentinel.about', compact('teamMembers', 'mlServiceStatus'));
    }

    /**
     * Mengirim data ke ML Service untuk prediksi.
     *
     * @param array $logData
     * @return array
     * @throws \Exception
     */
    protected function sendToMlService(array $logData): array
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->mlServiceUrl}/predict", [
                'ip_address' => $logData['ip_address'],
                'method' => $logData['method'],
                'url' => $logData['url'],
                'status_code' => $logData['status_code'],
                'user_agent' => $logData['user_agent'] ?? 'Unknown',
                'response_time' => $logData['response_time'] ?? 100,
            ]);

        if (!$response->successful()) {
            throw new \Exception('ML Service mengembalikan error: ' . $response->status());
        }

        $result = $response->json();

        if ($result['status'] !== 'success') {
            throw new \Exception($result['error'] ?? 'Unknown ML Service error');
        }

        return $result['data'];
    }

    /**
     * Memeriksa status ML Service.
     *
     * @return array
     */
    protected function checkMlServiceStatus(): array
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->mlServiceUrl}/health");

            if ($response->successful()) {
                return [
                    'status' => 'online',
                    'message' => 'ML Service berjalan normal',
                ];
            }

            return [
                'status' => 'error',
                'message' => 'ML Service mengembalikan error',
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'offline',
                'message' => 'ML Service tidak dapat dihubungi',
            ];
        }
    }

    /**
     * Menghasilkan data chart per jam untuk 24 jam terakhir.
     *
     * @return array
     */
    protected function getHourlyChartData(): array
    {
        $labels = [];
        $normalData = [];
        $anomalyData = [];

        // Loop 24 jam ke belakang
        for ($i = 23; $i >= 0; $i--) {
            $hour = Carbon::now()->subHours($i);
            $labels[] = $hour->format('H:i');

            $startHour = $hour->copy()->startOfHour();
            $endHour = $hour->copy()->endOfHour();

            $normalCount = ServerLog::normal()
                ->whereBetween('created_at', [$startHour, $endHour])
                ->count();

            $anomalyCount = ServerLog::anomalies()
                ->whereBetween('created_at', [$startHour, $endHour])
                ->count();

            $normalData[] = $normalCount;
            $anomalyData[] = $anomalyCount;
        }

        return [
            'labels' => $labels,
            'normal' => $normalData,
            'anomaly' => $anomalyData,
        ];
    }

    /**
     * Menghasilkan pola serangan untuk simulasi.
     *
     * @param string $type
     * @return array
     */
    protected function getAttackPatterns(string $type): array
    {
        $patterns = [];

        switch ($type) {
            case 'ddos':
                // Simulasi DDoS - banyak request dari IP berbeda dalam waktu singkat
                for ($i = 0; $i < 20; $i++) {
                    $patterns[] = [
                        'ip_address' => '10.0.' . rand(0, 255) . '.' . rand(1, 255),
                        'method' => 'GET',
                        'url' => '/',
                        'status_code' => 503,
                        'user_agent' => 'DDoS-Bot/' . rand(1, 100),
                        'response_time' => rand(5000, 30000), // Response time sangat tinggi
                    ];
                }
                break;

            case 'bruteforce':
                // Simulasi Brute Force Login
                $targetIp = '192.168.1.' . rand(100, 200);
                for ($i = 0; $i < 20; $i++) {
                    $patterns[] = [
                        'ip_address' => $targetIp,
                        'method' => 'POST',
                        'url' => '/login',
                        'status_code' => 401,
                        'user_agent' => 'BruteForce-Tool/1.0',
                        'response_time' => rand(100, 500),
                    ];
                }
                break;

            case 'sql_injection':
                // Simulasi SQL Injection
                $maliciousUrls = [
                    "/users?id=1' OR '1'='1",
                    "/search?q='; DROP TABLE users; --",
                    "/api/data?filter=1 UNION SELECT * FROM passwords",
                    "/products?category=1' AND 1=1--",
                    "/login?user=admin'--",
                ];
                foreach ($maliciousUrls as $url) {
                    $patterns[] = [
                        'ip_address' => '45.33.' . rand(0, 255) . '.' . rand(1, 255),
                        'method' => 'GET',
                        'url' => $url,
                        'status_code' => rand(0, 1) ? 500 : 200,
                        'user_agent' => 'sqlmap/1.5',
                        'response_time' => rand(50, 200),
                    ];
                }
                break;

            case 'path_traversal':
                // Simulasi Path Traversal
                $maliciousPaths = [
                    '/../../etc/passwd',
                    '/..%2f..%2f..%2fetc/shadow',
                    '/files/../../../windows/system32/config/sam',
                    '/download?file=../../../etc/hosts',
                    '/image?path=....//....//....//etc/passwd',
                ];
                foreach ($maliciousPaths as $path) {
                    $patterns[] = [
                        'ip_address' => '103.21.' . rand(0, 255) . '.' . rand(1, 255),
                        'method' => 'GET',
                        'url' => $path,
                        'status_code' => 403,
                        'user_agent' => 'Mozilla/5.0 (compatible; Nikto)',
                        'response_time' => rand(20, 100),
                    ];
                }
                break;

            default:
                // Random mixed attacks
                $types = ['ddos', 'bruteforce', 'sql_injection', 'path_traversal'];
                foreach ($types as $t) {
                    $subPatterns = $this->getAttackPatterns($t);
                    $patterns = array_merge($patterns, array_slice($subPatterns, 0, 5));
                }
        }

        return $patterns;
    }
}
