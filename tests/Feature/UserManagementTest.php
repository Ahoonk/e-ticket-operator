<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_update_user_without_changing_password(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $user = User::create([
            'name' => 'User Lama',
            'email' => 'user-lama@example.com',
            'password' => 'secret123',
            'role' => 'user',
        ]);

        $originalPassword = $user->password;

        $response = $this->actingAs($superadmin)->putJson("/api/users/{$user->id}", [
            'name' => 'User Baru',
            'email' => 'user-baru@example.com',
            'role' => 'admin',
            'telepon' => '08123456789',
        ]);

        $response->assertOk();
        $response->assertJsonPath('name', 'User Baru');
        $response->assertJsonPath('email', 'user-baru@example.com');
        $response->assertJsonPath('role', 'admin');
        $this->assertSame($originalPassword, $user->fresh()->password);
        $this->assertTrue(Hash::check('secret123', $user->fresh()->password));
    }
}
