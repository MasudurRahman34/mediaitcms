<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_login_screen_is_reachable(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Sign in');
    }

    public function test_admin_can_log_in_and_reach_the_dashboard(): void
    {
        $user = $this->admin();

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'Password!234',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk();
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = $this->admin();

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_users_cannot_log_in(): void
    {
        $user = $this->admin();
        $user->update(['is_active' => false]);

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'Password!234',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_user_session_is_invalidated(): void
    {
        $user = $this->admin();

        $this->actingAs($user);
        $user->update(['is_active' => false]);

        $this->get('/admin')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_guests_are_redirected_away_from_admin(): void
    {
        $this->get('/admin/pages')->assertRedirect('/admin/login');
        $this->get('/admin/api/pages')->assertRedirect('/admin/login');
    }

    public function test_admin_can_log_out(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/logout')
            ->assertRedirect('/admin/login');

        $this->assertGuest();
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        $user = $this->admin();

        $this->post('/admin/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = $this->admin();

        foreach (range(1, 6) as $attempt) {
            $response = $this->post('/admin/login', [
                'email' => $user->email,
                'password' => 'nope',
            ]);
        }

        $response->assertStatus(429);
    }
}
