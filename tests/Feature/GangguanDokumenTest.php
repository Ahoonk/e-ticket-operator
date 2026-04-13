<?php

namespace Tests\Feature;

use App\Models\Gangguan;
use App\Models\GangguanDokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GangguanDokumenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_document_for_assigned_gangguan(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $gangguan = Gangguan::create([
            'tanggal_gangguan' => now()->toDateString(),
            'lokasi_opd' => 'OPD A',
            'jenis_gangguan' => 'Internet Down',
            'status' => 'PROSES',
            'tim_bertugas' => $user->name.'::'.$user->id,
        ]);

        $response = $this->actingAs($user)->postJson("/api/gangguan/{$gangguan->id}/dokumen", [
            'file' => UploadedFile::fake()->image('dokumentasi.jpg'),
            'caption' => 'Foto perbaikan',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('original_name', 'dokumentasi.jpg');
        $path = $response->json('drive_file_id');
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseHas('gangguan_documents', [
            'gangguan_id' => $gangguan->id,
            'user_id' => $user->id,
            'drive_file_id' => $path,
        ]);
    }

    public function test_user_cannot_upload_document_for_unassigned_gangguan(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $gangguan = Gangguan::create([
            'tanggal_gangguan' => now()->toDateString(),
            'lokasi_opd' => 'OPD A',
            'jenis_gangguan' => 'Internet Down',
            'status' => 'PROSES',
            'tim_bertugas' => 'User Lain::999',
        ]);

        $response = $this->actingAs($user)->postJson("/api/gangguan/{$gangguan->id}/dokumen", [
            'file' => UploadedFile::fake()->image('dokumentasi.jpg'),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('gangguan_documents', 0);
    }

    public function test_admin_can_list_all_documents(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $gangguan = Gangguan::create([
            'tanggal_gangguan' => now()->toDateString(),
            'lokasi_opd' => 'OPD A',
            'jenis_gangguan' => 'Internet Down',
            'status' => 'PROSES',
            'tim_bertugas' => $user->name.'::'.$user->id,
        ]);

        GangguanDokumen::create([
            'gangguan_id' => $gangguan->id,
            'user_id' => $user->id,
            'original_name' => 'dokumentasi.jpg',
            'stored_name' => 'stored-dokumentasi.jpg',
            'drive_file_id' => 'gangguan-documents/'.$gangguan->id.'/stored-dokumentasi.jpg',
            'drive_url' => 'http://localhost/storage/gangguan-documents/'.$gangguan->id.'/stored-dokumentasi.jpg',
            'drive_content_url' => 'http://localhost/storage/gangguan-documents/'.$gangguan->id.'/stored-dokumentasi.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 12345,
            'caption' => 'Foto 1',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/dokumen');

        $response->assertOk();
        $response->assertJsonFragment([
            'original_name' => 'dokumentasi.jpg',
        ]);
    }

    public function test_admin_can_delete_document(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $gangguan = Gangguan::create([
            'tanggal_gangguan' => now()->toDateString(),
            'lokasi_opd' => 'OPD A',
            'jenis_gangguan' => 'Internet Down',
            'status' => 'PROSES',
            'tim_bertugas' => $user->name.'::'.$user->id,
        ]);

        Storage::disk('public')->put('gangguan-documents/'.$gangguan->id.'/stored-dokumentasi.jpg', 'fake-image');

        $dokumen = GangguanDokumen::create([
            'gangguan_id' => $gangguan->id,
            'user_id' => $user->id,
            'original_name' => 'dokumentasi.jpg',
            'stored_name' => 'stored-dokumentasi.jpg',
            'drive_file_id' => 'gangguan-documents/'.$gangguan->id.'/stored-dokumentasi.jpg',
            'drive_url' => 'http://localhost/storage/gangguan-documents/'.$gangguan->id.'/stored-dokumentasi.jpg',
            'drive_content_url' => 'http://localhost/storage/gangguan-documents/'.$gangguan->id.'/stored-dokumentasi.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 12345,
            'caption' => 'Foto 1',
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/dokumen/{$dokumen->id}");

        $response->assertOk();
        $response->assertJsonPath('message', 'deleted');
        $this->assertDatabaseMissing('gangguan_documents', [
            'id' => $dokumen->id,
        ]);
        Storage::disk('public')->assertMissing('gangguan-documents/'.$gangguan->id.'/stored-dokumentasi.jpg');
    }
}
