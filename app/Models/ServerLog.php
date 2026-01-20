<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model ServerLog
 * 
 * Model ini merepresentasikan data log server yang masuk ke sistem
 * beserta hasil analisis deteksi anomali dari ML Service.
 * 
 * @property int $id
 * @property string $ip_address
 * @property string $method
 * @property string $url
 * @property int $status_code
 * @property string|null $user_agent
 * @property float $response_time
 * @property string $prediction_result
 * @property float $severity_score
 * @property float $confidence_score
 * @property string|null $request_id
 * @property array|null $additional_data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ServerLog extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'server_logs';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<string>
     */
    protected $fillable = [
        'ip_address',
        'method',
        'url',
        'status_code',
        'user_agent',
        'response_time',
        'prediction_result',
        'severity_score',
        'confidence_score',
        'request_id',
        'additional_data',
    ];

    /**
     * Casting untuk tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status_code' => 'integer',
        'response_time' => 'float',
        'severity_score' => 'float',
        'confidence_score' => 'float',
        'additional_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope untuk mengambil log yang terdeteksi sebagai anomali.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAnomalies($query)
    {
        return $query->where('prediction_result', 'anomaly');
    }

    /**
     * Scope untuk mengambil log normal.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNormal($query)
    {
        return $query->where('prediction_result', 'normal');
    }

    /**
     * Scope untuk mengambil log berdasarkan rentang waktu.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $start
     * @param string $end
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope untuk mengambil log dengan severity tinggi.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $minSeverity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighSeverity($query, $minSeverity = 70)
    {
        return $query->where('severity_score', '>=', $minSeverity);
    }

    /**
     * Mengecek apakah log ini adalah anomali.
     *
     * @return bool
     */
    public function isAnomaly(): bool
    {
        return $this->prediction_result === 'anomaly';
    }

    /**
     * Mendapatkan label badge untuk tampilan UI.
     *
     * @return string
     */
    public function getBadgeClassAttribute(): string
    {
        return $this->isAnomaly() ? 'bg-danger' : 'bg-success';
    }

    /**
     * Mendapatkan ikon status untuk tampilan UI.
     *
     * @return string
     */
    public function getStatusIconAttribute(): string
    {
        return $this->isAnomaly() ? 'ri-alert-fill' : 'ri-check-fill';
    }
}
