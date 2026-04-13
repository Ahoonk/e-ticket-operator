<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AnggotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Anggota::query()
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'user');
            })
            ->orderByDesc('id')
            ->get()
            ->map(function (Anggota $anggota) {
                return [
                    'id' => $anggota->id,
                    'user_id' => $anggota->user_id,
                    'nama' => $anggota->user?->name ?? $anggota->nama,
                    'telepon' => $anggota->telepon,
                    'email' => $anggota->user?->email ?? $anggota->email,
                ];
            });

        return response()->json($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $temporaryPassword = Str::random(12);

        $item = DB::transaction(function () use ($data, $temporaryPassword) {
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'] ?? (Str::slug($data['nama']).'.'.Str::lower(Str::random(6)).'@example.com'),
                'password' => Hash::make($temporaryPassword),
                'role' => 'user',
            ]);

            $anggota = Anggota::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'telepon' => $data['telepon'] ?? null,
                'email' => $user->email,
            ]);

            return [
                'anggota' => [
                    'id' => $anggota->id,
                    'user_id' => $anggota->user_id,
                    'nama' => $anggota->nama,
                    'telepon' => $anggota->telepon,
                    'email' => $anggota->email,
                ],
            ];
        });

        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Anggota $anggota)
    {
        $anggota->load('user');

        return response()->json([
            'id' => $anggota->id,
            'user_id' => $anggota->user_id,
            'nama' => $anggota->user?->name ?? $anggota->nama,
            'telepon' => $anggota->telepon,
            'email' => $anggota->user?->email ?? $anggota->email,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Anggota $anggota)
    {
        $data = $request->validate([
            'telepon' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($anggota, $data) {
            $anggota->update([
                'telepon' => $data['telepon'] ?? null,
            ]);
        });

        $anggota->load('user');

        return response()->json([
            'id' => $anggota->id,
            'user_id' => $anggota->user_id,
            'nama' => $anggota->user?->name ?? $anggota->nama,
            'telepon' => $anggota->telepon,
            'email' => $anggota->user?->email ?? $anggota->email,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Anggota $anggota)
    {
        DB::transaction(function () use ($anggota) {
            $anggota->user?->delete();
            $anggota->delete();
        });

        return response()->json(['message' => 'deleted']);
    }
}
