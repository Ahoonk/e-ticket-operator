<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Kegiatan::orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return response()->json($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal' => ['nullable', 'date'],
            'waktu' => ['nullable', 'date_format:H:i'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $item = Kegiatan::create($data);

        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Kegiatan $kegiatan)
    {
        return response()->json($kegiatan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal' => ['nullable', 'date'],
            'waktu' => ['nullable', 'date_format:H:i'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $kegiatan->update($data);

        return response()->json($kegiatan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();

        return response()->json(['message' => 'deleted']);
    }
}
