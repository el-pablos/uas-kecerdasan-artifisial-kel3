<?php

namespace Database\Factories;

use App\Models\ServerLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory untuk model ServerLog
 * Digunakan untuk menghasilkan data dummy dalam testing
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServerLog>
 */
class ServerLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServerLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        $statusCodes = [200, 201, 301, 302, 400, 401, 403, 404, 500, 502, 503];
        $urls = [
            '/api/users',
            '/api/products',
            '/login',
            '/dashboard',
            '/api/orders',
            '/api/auth/token',
            '/api/search',
            '/admin/settings',
        ];
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'Mozilla/5.0 (Linux; Android 11; SM-G991B)',
            'curl/7.68.0',
            'PostmanRuntime/7.29.2',
            'Python-requests/2.28.1',
        ];

        $predictionResult = $this->faker->randomElement(['normal', 'anomaly']);

        return [
            'ip_address' => $this->faker->ipv4(),
            'method' => $this->faker->randomElement($methods),
            'url' => $this->faker->randomElement($urls),
            'status_code' => $this->faker->randomElement($statusCodes),
            'user_agent' => $this->faker->randomElement($userAgents),
            'response_time' => $this->faker->randomFloat(2, 50, 2000),
            'prediction_result' => $predictionResult,
            'severity_score' => $predictionResult === 'anomaly' 
                ? $this->faker->randomFloat(2, 50, 100) 
                : $this->faker->randomFloat(2, 0, 30),
            'confidence_score' => $this->faker->randomFloat(4, 0.5, 1.0),
            'request_id' => Str::uuid()->toString(),
            'additional_data' => [
                'factory_generated' => true,
            ],
            'created_at' => $this->faker->dateTimeBetween('-24 hours', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * State untuk log yang terdeteksi sebagai anomali.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function anomaly(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'prediction_result' => 'anomaly',
                'severity_score' => $this->faker->randomFloat(2, 60, 100),
                'status_code' => $this->faker->randomElement([401, 403, 404, 500, 502, 503]),
            ];
        });
    }

    /**
     * State untuk log normal.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function normal(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'prediction_result' => 'normal',
                'severity_score' => $this->faker->randomFloat(2, 0, 20),
                'status_code' => $this->faker->randomElement([200, 201, 301, 302]),
            ];
        });
    }

    /**
     * State untuk simulasi serangan DDoS.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function ddosAttack(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'method' => 'GET',
                'url' => '/',
                'status_code' => 503,
                'response_time' => $this->faker->randomFloat(2, 5000, 30000),
                'user_agent' => 'DDoS-Bot/' . $this->faker->randomNumber(3),
                'prediction_result' => 'anomaly',
                'severity_score' => $this->faker->randomFloat(2, 80, 100),
            ];
        });
    }

    /**
     * State untuk simulasi brute force.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function bruteForce(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'method' => 'POST',
                'url' => '/login',
                'status_code' => 401,
                'user_agent' => 'BruteForce-Tool/1.0',
                'prediction_result' => 'anomaly',
                'severity_score' => $this->faker->randomFloat(2, 70, 95),
            ];
        });
    }
}
