<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gangguan;
use App\Models\GangguanDokumen;
use App\Services\KegiatanJaringanDocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class KegiatanJaringanDokumenController extends Controller
{
    private function teamMembers(?string $timBertugas): array
    {
        return collect(explode(',', (string) $timBertugas))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();
    }

    private function teamMemberMatchesUser(string $teamMember, $user): bool
    {
        [$label, $userId] = array_pad(explode('::', $teamMember, 2), 2, null);

        if ($userId !== null && $userId !== '' && (string) $user->id === trim($userId)) {
            return true;
        }

        $needle = str_replace(' ', '', trim($user->name));

        return str_replace(' ', '', trim($label)) === $needle;
    }

    private function isAssignedToUser(Gangguan $gangguan, $user): bool
    {
        if (!$user) {
            return false;
        }

        foreach ($this->teamMembers($gangguan->tim_bertugas) as $teamMember) {
            if ($this->teamMemberMatchesUser($teamMember, $user)) {
                return true;
            }
        }

        return false;
    }

    private function documentPayload(GangguanDokumen $document): array
    {
        $document->loadMissing(['gangguan', 'user']);

        return [
            'id' => $document->id,
            'gangguan_id' => $document->gangguan_id,
            'original_name' => $document->original_name,
            'stored_name' => $document->stored_name,
            'drive_file_id' => $document->drive_file_id,
            'drive_url' => $document->drive_url,
            'drive_content_url' => $document->drive_content_url,
            'mime_type' => $document->mime_type,
            'file_size' => $document->file_size,
            'caption' => $document->caption,
            'uploader' => [
                'id' => $document->user?->id,
                'name' => $document->user?->name,
                'email' => $document->user?->email,
            ],
            'gangguan' => [
                'id' => $document->gangguan?->id,
                'tanggal_gangguan' => $document->gangguan?->tanggal_gangguan,
                'lokasi_opd' => $document->gangguan?->lokasi_opd,
                'jenis_gangguan' => $document->gangguan?->jenis_gangguan,
                'status' => $document->gangguan?->status,
                'tim_bertugas' => $document->gangguan?->tim_bertugas,
            ],
            'created_at' => $document->created_at?->toDateTimeString(),
        ];
    }

    /**
     * Display a listing of all uploaded documents.
     */
    public function index(Request $request)
    {
        $actor = $request->user();
        if (!$actor || !in_array($actor->role, ['superadmin', 'admin'], true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!Schema::hasTable('gangguan_documents')) {
            return response()->json(['message' => 'Fitur dokumen belum aktif di server. Jalankan migrasi database.'], 503);
        }

        $documents = GangguanDokumen::query()
            ->with(['gangguan', 'user'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (GangguanDokumen $document) => $this->documentPayload($document));

        return response()->json($documents);
    }

    /**
     * Display uploaded documents for a single gangguan.
     */
    public function show(Request $request, Gangguan $gangguan)
    {
        $actor = $request->user();

        if (!$actor) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!Schema::hasTable('gangguan_documents')) {
            return response()->json(['message' => 'Fitur dokumen belum aktif di server. Jalankan migrasi database.'], 503);
        }

        if ($actor->role === 'user' && !$this->isAssignedToUser($gangguan, $actor)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $documents = $gangguan->documents()
            ->with('user')
            ->orderByDesc('id')
            ->get()
            ->map(fn (GangguanDokumen $document) => $this->documentPayload($document));

        return response()->json($documents);
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(Request $request, Gangguan $gangguan, KegiatanJaringanDocumentStorageService $storageService)
    {
        $actor = $request->user();

        if (!$actor || $actor->role !== 'user') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!Schema::hasTable('gangguan_documents')) {
            return response()->json(['message' => 'Fitur dokumen belum aktif di server. Jalankan migrasi database.'], 503);
        }

        if (!$this->isAssignedToUser($gangguan, $actor)) {
            return response()->json(['message' => 'Dokumen hanya bisa diunggah untuk kegiatan yang ditugaskan kepada Anda.'], 403);
        }

        $data = $request->validate([
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $upload = $storageService->upload($data['file'], 'gangguan-documents/'.$gangguan->id);

        $document = GangguanDokumen::create([
            'gangguan_id' => $gangguan->id,
            'user_id' => $actor->id,
            'original_name' => $upload['original_name'],
            'stored_name' => $upload['stored_name'] ?? null,
            'drive_file_id' => $upload['drive_file_id'],
            'drive_url' => $upload['drive_url'],
            'drive_content_url' => $upload['drive_content_url'] ?? null,
            'mime_type' => $upload['mime_type'] ?? null,
            'file_size' => $upload['file_size'] ?? null,
            'caption' => $data['caption'] ?? null,
        ]);

        return response()->json($this->documentPayload($document), 201);
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Request $request, GangguanDokumen $dokumen)
    {
        $actor = $request->user();

        if (!$actor || !in_array($actor->role, ['superadmin', 'admin'], true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!Schema::hasTable('gangguan_documents')) {
            return response()->json(['message' => 'Fitur dokumen belum aktif di server. Jalankan migrasi database.'], 503);
        }

        if ($dokumen->drive_file_id && Storage::disk('public')->exists($dokumen->drive_file_id)) {
            Storage::disk('public')->delete($dokumen->drive_file_id);
        }

        $dokumen->delete();

        return response()->json(['message' => 'deleted']);
    }
}
