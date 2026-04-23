<?php

namespace Tests\Feature;

use App\Models\Gangguan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangguanKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_gangguan_response_includes_kpi_metrics(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $gangguan = Gangguan::create([
            'tanggal_gangguan' => '2026-04-16 09:00:00',
            'lokasi_opd' => 'OPD KPI',
            'jenis_gangguan' => 'Internet Down',
            'mulai_pengerjaan' => '2026-04-16 09:45:00',
            'selesai_pengerjaan' => '2026-04-16 13:15:00',
            'status' => 'SELESAI',
            'tim_bertugas' => 'Tim KPI',
        ]);

        $response = $this->actingAs($superadmin)->getJson("/api/gangguan/{$gangguan->id}");

        $response->assertOk();
        $response->assertJsonPath('kpi.response_minutes', 45);
        $response->assertJsonPath('kpi.handling_minutes', 210);
        $response->assertJsonPath('kpi.total_minutes', 255);
        $response->assertJsonPath('kpi.response_score', 67);
        $response->assertJsonPath('kpi.handling_score', 86);
        $response->assertJsonPath('kpi.total_score', 94);
        $response->assertJsonPath('kpi.score', 82);
    }
}
