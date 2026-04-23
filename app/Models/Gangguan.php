<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Gangguan extends Model
{
    private const KPI_TARGET_RESPONSE_MINUTES = 30;
    private const KPI_TARGET_HANDLING_MINUTES = 180;
    private const KPI_TARGET_TOTAL_MINUTES = 240;

    protected $fillable = [
        'tanggal_gangguan',
        'lokasi_opd',
        'jenis_gangguan',
        'mulai_pengerjaan',
        'selesai_pengerjaan',
        'kendala',
        'tindak_lanjut',
        'tim_bertugas',
        'status',
        'keterangan',
    ];

    protected $appends = [
        'kpi',
    ];

    public function documents()
    {
        return $this->hasMany(GangguanDokumen::class);
    }

    private function kpiScore(int $actualMinutes, int $targetMinutes): int
    {
        if ($actualMinutes <= 0) {
            return 100;
        }

        return max(0, min(100, (int) round(($targetMinutes / $actualMinutes) * 100)));
    }

    public function getKpiAttribute(): ?array
    {
        if (strtoupper((string) $this->status) !== 'SELESAI') {
            return null;
        }

        if (!$this->tanggal_gangguan || !$this->mulai_pengerjaan || !$this->selesai_pengerjaan) {
            return null;
        }

        $inputAt = Carbon::parse($this->tanggal_gangguan);
        $startAt = Carbon::parse($this->mulai_pengerjaan);
        $finishAt = Carbon::parse($this->selesai_pengerjaan);

        if ($startAt->lt($inputAt) || $finishAt->lt($startAt)) {
            return null;
        }

        $responseMinutes = $inputAt->diffInMinutes($startAt);
        $handlingMinutes = $startAt->diffInMinutes($finishAt);
        $totalMinutes = $inputAt->diffInMinutes($finishAt);

        $responseScore = $this->kpiScore($responseMinutes, self::KPI_TARGET_RESPONSE_MINUTES);
        $handlingScore = $this->kpiScore($handlingMinutes, self::KPI_TARGET_HANDLING_MINUTES);
        $totalScore = $this->kpiScore($totalMinutes, self::KPI_TARGET_TOTAL_MINUTES);

        return [
            'response_minutes' => $responseMinutes,
            'handling_minutes' => $handlingMinutes,
            'total_minutes' => $totalMinutes,
            'response_score' => $responseScore,
            'handling_score' => $handlingScore,
            'total_score' => $totalScore,
            'score' => (int) round(($responseScore + $handlingScore + $totalScore) / 3),
        ];
    }
}
