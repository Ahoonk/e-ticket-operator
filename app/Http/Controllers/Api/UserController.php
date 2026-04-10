<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = User::query()
            ->with('anggota')
            ->orderByDesc('id')
            ->get();
        
        return response()->json(
            $items->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'telepon' => $user->anggota?->telepon,
                ];
            })
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'user'])],
            'telepon' => ['nullable', 'string', 'max:50'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            if ($user->role === 'user') {
                Anggota::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nama' => $user->name,
                        'email' => $user->email,
                        'telepon' => $data['telepon'] ?? null,
                    ]
                );
            }

            return $user;
        });

        $user->load('anggota');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'telepon' => $user->anggota?->telepon,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'user'])],
            'telepon' => ['nullable', 'string', 'max:50'],
        ]);

        $updatedUser = DB::transaction(function () use ($user, $data) {
            $previousRole = $user->role;

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            if ($user->role === 'user') {
                Anggota::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nama' => $user->name,
                        'email' => $user->email,
                        'telepon' => $data['telepon'] ?? $user->anggota?->telepon,
                    ]
                );
            } elseif ($previousRole === 'user') {
                $user->anggota?->delete();
            }

            return $user;
        });

        $updatedUser->load('anggota');

        return response()->json([
            'id' => $updatedUser->id,
            'name' => $updatedUser->name,
            'email' => $updatedUser->email,
            'role' => $updatedUser->role,
            'telepon' => $updatedUser->anggota?->telepon,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        DB::transaction(function () use ($user) {
            $user->anggota?->delete();
            $user->delete();
        });

        return response()->json(['message' => 'deleted']);
    }
}
