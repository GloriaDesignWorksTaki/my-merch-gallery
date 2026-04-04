<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
  use RefreshDatabase;

  public function test_register_route_redirects_to_home_with_auth_query(): void
  {
    $response = $this->get('/register');

    $response->assertRedirect(route('home', ['auth' => 'register']));
  }

  public function test_new_users_can_register(): void
  {
    $response = $this->post('/register', [
      'name' => 'Test User',
      'username' => 'test_user',
      'email' => 'test@example.com',
      'password' => 'password',
      'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
  }
}
