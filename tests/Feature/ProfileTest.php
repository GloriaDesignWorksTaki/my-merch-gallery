<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Edit')
            ->where('email', $user->email)
            ->where('auth.user.id', $user->id)
            ->missing('auth.user.email'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $originalUsername = $user->username;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'username' => 'changed_username_should_be_ignored',
                'email' => 'test@example.com',
                'bio' => 'Updated bio',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame($originalUsername, $user->username);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('Updated bio', $user->bio);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                'bio' => $user->bio,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_avatar_can_be_uploaded_and_adjusted(): void
    {
        Storage::fake('uploads');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/profile', [
                '_method' => 'patch',
                'name' => $user->name,
                'email' => $user->email,
                'bio' => 'Avatar updated',
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 800, 800),
                'avatar_focus_x' => 25,
                'avatar_focus_y' => 70,
                'avatar_zoom' => 1.45,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        $this->assertSame(25, $user->avatar_focus_x);
        $this->assertSame(70, $user->avatar_focus_y);
        $this->assertSame(1.45, $user->avatar_zoom);
        $this->assertTrue(Storage::disk('uploads')->exists($user->avatar_path));
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
