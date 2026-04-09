<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
  use RefreshDatabase;

  public function test_login_route_redirects_to_home_with_auth_query(): void
  {
    $response = $this->get('/login');

    $response->assertRedirect(route('home', ['auth' => 'login']));
  }

  public function test_users_can_authenticate_using_the_login_screen(): void
  {
    $user = User::factory()->create();

    $response = $this->post('/login', [
      'email' => $user->email,
      'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
  }

  public function test_users_can_not_authenticate_with_invalid_password(): void
  {
    $user = User::factory()->create();

    $this->post('/login', [
      'email' => $user->email,
      'password' => 'wrong-password',
    ]);

    $this->assertGuest();
  }

  public function test_users_can_logout(): void
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
  }

  public function test_get_logout_redirects_guests_to_home(): void
  {
    $response = $this->get('/logout');

    $response->assertRedirect(route('home'));
  }

  public function test_get_logout_offers_post_form_for_authenticated_users(): void
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/logout');

    $response->assertOk();
    $response->assertSee('logout-form', false);
    $this->assertAuthenticated();
  }
}
