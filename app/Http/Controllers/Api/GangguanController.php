<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gangguan;
use Illuminate\Http\Request;

class GangguanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Gangguan::query();

        $user = $request->user();
        if ($user && $user->role === 'user') {
            $needle = str_replace(' ', '', trim($user->name));
            $query->whereRaw(
                "CONCAT(',', REPLACE(COALESCE(tim_bertugas, ''), ' ', ''), ',') LIKE ?",
                ['%,'.$needle.',%']
            );
        }

        $items = $query->orderByDesc('tanggal_gangguan')
            ->orderByDesc('id')
            ->get();

        return response()->json($items);
    }

    /**
     * Display a paginated listing for public view.
     */
    public function publicIndex(Request $request)
    {
        $limit = (int) $request->query('limit', 6);
        if ($limit <= 0) {
            $limit = 6;
        }
        if ($limit > 24) {
            $limit = 24;
        }

        $items = Gangguan::orderByDesc('tanggal_gangguan')
            ->orderByDesc('id')
            ->paginate($limit);

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

        $item = Gangguan::create($data);

        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Gangguan $gangguan)
    {
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

        $gangguan->update($data);

        return response()->json($gangguan);
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
