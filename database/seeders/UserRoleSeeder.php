<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => 'password',
                'role' => 'superadmin',
            ],
            [
                'name' => 'Admin Sistem',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'admin',
            ],
            [
                'name' => 'Irvan Amir',
                'email' => 'irvan.amir@example.com',
                'password' => 'password',
                'role' => 'user',
                'telepon' => '089617451858',
            ],
            [
                'name' => 'Muhammad Maulizar',
                'email' => 'maulizar@example.com',
                'password' => 'password',
                'role' => 'user',
                'telepon' => '085373143771',
            ],
            [
                'name' => 'Edwin Julian',
                'email' => 'edwin.julian@example.com',
                'password' => 'password',
                'role' => 'user',
                'telepon' => '089615784906',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                ]
            );

            if ($user->role === 'user') {
                Anggota::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nama' => $user->name,
                        'email' => $user->email,
                        'telepon' => $account['telepon'] ?? null,
                    ]
                );
            }
        }

        User::query()
            ->where('role', 'user')
            ->with('anggota')
            ->get()
            ->each(function (User $user) {
                Anggota::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nama' => $user->name,
                        'email' => $user->email,
                        'telepon' => $user->anggota?->telepon,
                    ]
                );
            });
    }
}
