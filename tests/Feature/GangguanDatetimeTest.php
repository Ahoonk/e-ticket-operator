<?php

namespace Tests\Feature;

use App\Models\Gangguan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangguanDatetimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_store_gangguan_with_datetime_fields(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($superadmin)->postJson('/api/gangguan', [
            'tanggal_gangguan' => '2026-04-16T09:15',
            'lokasi_opd' => 'OPD A',
            'jenis_gangguan' => 'Internet Down',
            'mulai_pengerjaan' => '2026-04-16T09:30',
            'selesai_pengerjaan' => '2026-04-16T10:45',
            'status' => 'PROSES',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('tanggal_gangguan', '2026-04-16T09:15');
        $response->assertJsonPath('mulai_pengerjaan', '2026-04-16T09:30');
        $response->assertJsonPath('selesai_pengerjaan', '2026-04-16T10:45');

        $this->assertDatabaseHas('gangguans', [
            'lokasi_opd' => 'OPD A',
            'jenis_gangguan' => 'Internet Down',
            'tanggal_gangguan' => '2026-04-16T09:15',
            'mulai_pengerjaan' => '2026-04-16T09:30',
            'selesai_pengerjaan' => '2026-04-16T10:45',
        ]);
    }

    public function test_user_complete_flow_stores_finish_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-16 14:25:00'));

        try {
            $user = User::factory()->create([
                'role' => 'user',
            ]);

            $gangguan = Gangguan::create([
                'tanggal_gangguan' => '2026-04-16T09:15',
                'lokasi_opd' => 'OPD A',
                'jenis_gangguan' => 'Internet Down',
                'status' => 'PROSES',
                'tim_bertugas' => $user->name.'::'.$user->id,
            ]);

            $response = $this->actingAs($user)->postJson("/api/gangguan/{$gangguan->id}/complete", [
                'kendala' => 'Tidak ada kendala',
                'tindak_lanjut' => 'Perbaikan selesai',
                'keterangan' => 'Selesai hari ini',
            ]);

            $response->assertOk();
            $response->assertJsonPath('status', 'SELESAI');
            $response->assertJsonPath('selesai_pengerjaan', '2026-04-16 14:25:00');

            $this->assertDatabaseHas('gangguans', [
                'id' => $gangguan->id,
                'status' => 'SELESAI',
                'selesai_pengerjaan' => '2026-04-16 14:25:00',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }
}
