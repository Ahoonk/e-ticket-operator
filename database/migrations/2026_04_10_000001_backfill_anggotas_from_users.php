<?php

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('anggotas')) {
            return;
        }

        User::query()
            ->where('role', 'user')
            ->get()
            ->each(function (User $user) {
                $anggota = Anggota::where('user_id', $user->id)
                    ->orWhere('email', $user->email)
                    ->orWhere('nama', $user->name)
                    ->first();

                if ($anggota) {
                    $anggota->update([
                        'user_id' => $user->id,
                        'nama' => $user->name,
                        'email' => $user->email,
                    ]);

                    return;
                }

                Anggota::create([
                    'user_id' => $user->id,
                    'nama' => $user->name,
                    'email' => $user->email,
                    'telepon' => null,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
