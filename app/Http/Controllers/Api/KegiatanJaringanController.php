<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gangguan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KegiatanJaringanController extends Controller
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

    private function normalizeDateTimeFields(array $data, ?Gangguan $existing = null): array
    {
        foreach (['tanggal_gangguan', 'mulai_pengerjaan', 'selesai_pengerjaan'] as $field) {
            if (array_key_exists($field, $data)) {
                if (filled($data[$field])) {
                    $data[$field] = Carbon::parse($data[$field])->format('Y-m-d H:i:s');
                } else {
                    $data[$field] = null;
                }

                continue;
            }

            if ($existing) {
                $data[$field] = $existing->{$field};
                continue;
            }

            $data[$field] = null;
        }

        return $data;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $items = Gangguan::query()
            ->orderByDesc('tanggal_gangguan')
            ->orderByDesc('id')
            ->get();

        if ($user && $user->role === 'user') {
            $items = $items
                ->filter(fn (Gangguan $gangguan) => $this->isAssignedToUser($gangguan, $user))
                ->values();
        }

        return response()->json($items);
    }

    /**
     * Display a paginated listing for public view.
     */
    public function publicIndex(Request $request)
    {
        $items = Gangguan::orderByDesc('tanggal_gangguan')
            ->orderByDesc('id')
            ->get();

        return response()->json($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->user()?->role === 'user') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'tanggal_gangguan' => ['nullable', 'date'],
            'lokasi_opd' => ['required', 'string', 'max:255'],
            'jenis_gangguan' => ['required', 'string', 'max:255'],
            'mulai_pengerjaan' => ['nullable', 'date'],
            'selesai_pengerjaan' => ['nullable', 'date'],
            'kendala' => ['nullable', 'string'],
            'tindak_lanjut' => ['nullable', 'string'],
            'tim_bertugas' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $data = $this->normalizeDateTimeFields($data);

        $item = Gangguan::create($data)->fresh();

        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Gangguan $gangguan)
    {
        $user = request()->user();

        if ($user && $user->role === 'user' && !$this->isAssignedToUser($gangguan, $user)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($user && $user->role === 'user') {
            return response()->json($gangguan);
        }

        return response()->json($gangguan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gangguan $gangguan)
    {
        if ($request->user()?->role === 'user') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'tanggal_gangguan' => ['nullable', 'date'],
            'lokasi_opd' => ['required', 'string', 'max:255'],
            'jenis_gangguan' => ['required', 'string', 'max:255'],
            'mulai_pengerjaan' => ['nullable', 'date'],
            'selesai_pengerjaan' => ['nullable', 'date'],
            'kendala' => ['nullable', 'string'],
            'tindak_lanjut' => ['nullable', 'string'],
            'tim_bertugas' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $data = $this->normalizeDateTimeFields($data, $gangguan);

        $gangguan->update($data);

        return response()->json($gangguan->fresh());
    }

    /**
     * Mark a user's assigned gangguan as completed.
     */
    public function complete(Request $request, Gangguan $gangguan)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'user') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$this->isAssignedToUser($gangguan, $user)) {
            return response()->json(['message' => 'Kegiatan ini bukan untuk Anda.'], 403);
        }

        if ($gangguan->status === 'SELESAI') {
            return response()->json(['message' => 'Kegiatan sudah selesai.'], 422);
        }

        if ($gangguan->status !== 'PROSES') {
            return response()->json(['message' => 'Kegiatan harus berstatus PROSES sebelum diselesaikan.'], 422);
        }

        $data = $request->validate([
            'kendala' => ['required', 'string'],
            'tindak_lanjut' => ['required', 'string'],
            'keterangan' => ['required', 'string'],
        ]);

        $completedAt = now()->format('Y-m-d H:i:s');

        $gangguan->update([
            'kendala' => $data['kendala'],
            'tindak_lanjut' => $data['tindak_lanjut'],
            'keterangan' => $data['keterangan'],
            'status' => 'SELESAI',
            'selesai_pengerjaan' => $completedAt,
        ]);

        return response()->json($gangguan->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gangguan $gangguan)
    {
        if (request()->user()?->role === 'user') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $gangguan->delete();

        return response()->json(['message' => 'deleted']);
    }
}
