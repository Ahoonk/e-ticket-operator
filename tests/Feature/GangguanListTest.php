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
}
