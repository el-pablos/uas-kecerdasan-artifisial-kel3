<?php

/**
 * =============================================================================
 * FeedbackController - Human-in-the-Loop ML Feedback System
 * =============================================================================
 * 
 * Controller untuk mengelola feedback dari admin/analis keamanan
 * ke ML Service (Flask) untuk Active Learning.
 * 
 * Lead Developer: Muhammad Akbar Hadi Pratama (@el-pablos)
 * 
 * Endpoints yang ditangani:
 * - POST /api/feedback     : Kirim feedback ke ML Service
 * - POST /api/whitelist    : Tambah IP ke whitelist
 * - GET  /api/feedback/stats : Statistik feedback
 * 
 * =============================================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ServerLog;

class FeedbackController extends Controller
{
    /**
     * URL ML Service dari environment
     */
    protected $mlServiceUrl;

    public function __construct()
    {
        $this->mlServiceUrl = env('ML_SERVICE_URL', 'http://127.0.0.1:5000');
    }

    /**
     * Kirim feedback ke ML Service untuk Active Learning
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitFeedback(Request $request)
    {
        $request->validate([
            'log_id' => 'required|integer',
            'correct_label' => 'required|in:normal,anomaly',
            'analyst_notes' => 'nullable|string|max:500',
        ]);

        try {
            // Ambil data log dari database
            $log = ServerLog::findOrFail($request->log_id);
            
            // Siapkan data untuk ML Service
            $feedbackData = [
                'log_id' => $log->id,
                'features' => [
                    $log->response_time / 1000,
                    $log->status_code >= 400 ? 1 : 0,
                    $log->response_time,
                    $log->status_code,
                    strlen($log->url),
                    $this->countRequestsFromIp($log->ip_address),
                ],
                'original_prediction' => $log->prediction_result,
                'correct_label' => $request->correct_label,
                'analyst_notes' => $request->analyst_notes ?? '',
                'timestamp' => now()->toIso8601String(),
            ];

            // Kirim ke Flask ML Service
            $response = Http::timeout(10)
                ->post("{$this->mlServiceUrl}/feedback", $feedbackData);

            if ($response->successful()) {
                // Update status feedback di database
                $log->update([
                    'feedback_submitted' => true,
                    'feedback_label' => $request->correct_label,
                    'feedback_at' => now(),
                ]);

                Log::info("Feedback submitted for log #{$log->id}", $feedbackData);

                return response()->json([
                    'success' => true,
                    'message' => 'Feedback berhasil dikirim ke ML Service',
                    'ml_response' => $response->json(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'ML Service menolak feedback',
                'error' => $response->body(),
            ], 422);

        } catch (\Exception $e) {
            Log::error("Feedback submission failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim feedback',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tambah IP ke whitelist di ML Service
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToWhitelist(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $response = Http::timeout(10)
                ->post("{$this->mlServiceUrl}/whitelist", [
                    'action' => 'add',
                    'ip' => $request->ip_address,
                    'reason' => $request->reason ?? 'Added by admin',
                ]);

            if ($response->successful()) {
                Log::info("IP {$request->ip_address} added to whitelist");
                
                return response()->json([
                    'success' => true,
                    'message' => "IP {$request->ip_address} berhasil ditambahkan ke whitelist",
                    'ml_response' => $response->json(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'ML Service menolak request whitelist',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan ke whitelist',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus IP dari whitelist
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromWhitelist(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        try {
            $response = Http::timeout(10)
                ->post("{$this->mlServiceUrl}/whitelist", [
                    'action' => 'remove',
                    'ip' => $request->ip_address,
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => "IP {$request->ip_address} dihapus dari whitelist",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dari whitelist',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get feedback statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $stats = [
            'total_logs' => ServerLog::count(),
            'total_with_feedback' => ServerLog::where('feedback_submitted', true)->count(),
            'feedback_normal' => ServerLog::where('feedback_label', 'normal')->count(),
            'feedback_anomaly' => ServerLog::where('feedback_label', 'anomaly')->count(),
            'pending_review' => ServerLog::where('prediction_result', 'anomaly')
                ->where('feedback_submitted', false)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Helper: Hitung jumlah request dari IP tertentu dalam 1 jam terakhir
     */
    private function countRequestsFromIp(string $ipAddress): int
    {
        return ServerLog::where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subHour())
            ->count();
    }
}
