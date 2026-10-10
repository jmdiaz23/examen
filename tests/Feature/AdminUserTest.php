<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Profe Juan',
                'email' => 'Juan@Example.com',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertRedirect(route('users.index'));

        $created = User::where('email', 'juan@example.com')->firstOrFail();

        $this->assertSame('Profe Juan', $created->name);
        $this->assertNotNull($created->email_verified_at);
        $this->assertTrue(password_verify('secret123', $created->password));
    }

    public function test_email_must_be_unique(): void
    {
        $admin = User::factory()->create();
        User::factory()->create(['email' => 'duplicado@example.com']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Duplicado',
                'email' => 'duplicado@example.com',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame(2, User::count());
    }

    public function test_password_must_be_confirmed(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Sin confirmar',
                'email' => 'sin@example.com',
                'password' => 'secret123',
                'password_confirmation' => 'otra',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $admin = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $other))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }
}