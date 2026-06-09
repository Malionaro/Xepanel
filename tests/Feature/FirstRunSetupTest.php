<?php

namespace Tests\Feature;

use App\Models\FileUser;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FirstRunSetupTest extends TestCase
{
    public function test_login_redirects_to_setup_when_no_user_exists(): void
    {
        Storage::fake('local');

        $this->get('/')->assertRedirect(route('setup.index'));
    }

    public function test_setup_creates_first_admin_user(): void
    {
        Storage::fake('local');

        $this->post(route('setup.store'), [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ])->assertRedirect(route('dashboard'));

        $user = FileUser::findByEmail('admin@example.com');

        $this->assertNotNull($user);
        $this->assertTrue($user->isAdmin());
        $this->assertSame('admin', $user->role);
    }
}
