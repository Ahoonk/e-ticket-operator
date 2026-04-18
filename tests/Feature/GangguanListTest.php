<?php

namespace Tests\Feature;

use App\Models\Gangguan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangguanListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_assigned_gangguan_in_list(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $assigned = Gangguan::create([
            'tanggal_gangguan' => now()->toDateString(),
            'lokasi_opd' => 'OPD A',
            'jenis_gangguan' => 'Internet Down',
            'status' => 'PROSES',
            'tim_bertugas' => $user->name.'::'.$user->id,
        ]);

        Gangguan::create([
            'tanggal_gangguan' => now()->toDateString(),
            'lokasi_opd' => 'OPD B',
            'jenis_gangguan' => 'Printer',
            'status' => 'PROSES',
            'tim_bertugas' => 'User Lain::999',
        ]);

        $response = $this->actingAs($user)->getJson('/api/gangguan');

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $assigned->id,
            'jenis_gangguan' => 'Internet Down',
        ]);
        $response->assertJsonMissing([
            'lokasi_opd' => 'OPD B',
        ]);
    }

    public function test_completed_gangguan_edit_by_admin_is_visible_to_user(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $gangguan = Gangguan::create([
            'tanggal_gangguan' => now()->toDateString(),
            'lokasi_opd' => 'OPD Awal',
            'jenis_gangguan' => 'Internet Down',
            'status' => 'PROSES',
            'tim_bertugas' => $user->name.'::'.$user->id,
        ]);

        $this->actingAs($user)->postJson("/api/gangguan/{$gangguan->id}/complete", [
            'kendala' => 'Kendala awal',
            'tindak_lanjut' => 'Solusi awal',
            'keterangan' => 'Catatan awal',
        ])->assertOk();

        $this->actingAs($admin)->putJson("/api/gangguan/{$gangguan->id}", [
            'tanggal_gangguan' => now()->format('Y-m-d\TH:i'),
            'lokasi_opd' => 'OPD Edit Admin',
            'jenis_gangguan' => 'Internet Down - Revisi',
            'mulai_pengerjaan' => now()->format('Y-m-d\TH:i'),
            'selesai_pengerjaan' => now()->format('Y-m-d\TH:i'),
            'kendala' => 'Kendala admin',
            'tindak_lanjut' => 'Solusi admin',
            'tim_bertugas' => $user->name.'::'.$user->id,
            'status' => 'SELESAI',
            'keterangan' => 'Catatan admin',
        ])->assertOk();

        $response = $this->actingAs($user)->getJson('/api/gangguan');

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $gangguan->id,
            'lokasi_opd' => 'OPD Edit Admin',
            'jenis_gangguan' => 'Internet Down - Revisi',
            'kendala' => 'Kendala admin',
            'keterangan' => 'Catatan admin',
        ]);
    }

    public function test_superadmin_sees_latest_gangguan_detail_after_admin_edit(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $gangguan = Gangguan::create([
            'tanggal_gangguan' => now()->format('Y-m-d\TH:i'),
            'lokasi_opd' => 'OPD Awal',
            'jenis_gangguan' => 'Internet Down',
            'mulai_pengerjaan' => now()->format('Y-m-d\TH:i'),
            'selesai_pengerjaan' => now()->format('Y-m-d\TH:i'),
            'kendala' => 'Kendala awal',
            'tindak_lanjut' => 'Solusi awal',
            'tim_bertugas' => 'Tim A',
            'status' => 'SELESAI',
            'keterangan' => 'Catatan awal',
        ]);

        $this->actingAs($admin)->putJson("/api/gangguan/{$gangguan->id}", [
            'tanggal_gangguan' => now()->addDay()->format('Y-m-d\TH:i'),
            'lokasi_opd' => 'OPD Edit Admin',
            'jenis_gangguan' => 'Internet Down - Revisi',
            'mulai_pengerjaan' => now()->addDay()->format('Y-m-d\TH:i'),
            'selesai_pengerjaan' => now()->addDay()->format('Y-m-d\TH:i'),
            'kendala' => 'Kendala admin',
            'tindak_lanjut' => 'Solusi admin',
            'tim_bertugas' => 'Tim B',
            'status' => 'SELESAI',
            'keterangan' => 'Catatan admin',
        ])->assertOk();

        $response = $this->actingAs($superadmin)->getJson("/api/gangguan/{$gangguan->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $gangguan->id,
            'lokasi_opd' => 'OPD Edit Admin',
            'jenis_gangguan' => 'Internet Down - Revisi',
            'kendala' => 'Kendala admin',
            'keterangan' => 'Catatan admin',
        ]);
    }
}
