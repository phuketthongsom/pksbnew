<?php

namespace Tests\Feature;

use App\Services\UserRepository;
use Tests\TestCase;

/**
 * Smoke tests for the new controller layer (A1 + A2).
 * Confirms route → controller → FormRequest authorization flow.
 */
class AdminControllersTest extends TestCase
{
    protected function authenticate(): void
    {
        $user = app(UserRepository::class)->findByUsername('admin');
        $this->session(['admin_user_id' => $user['id']]);
    }

    public function test_unauth_admin_routes_redirect_to_login(): void
    {
        $this->get('/admin/posts')->assertRedirect('/admin/login');
        $this->get('/admin/passes')->assertRedirect('/admin/login');
        $this->get('/admin/timetables')->assertRedirect('/admin/login');
        $this->get('/admin/users')->assertRedirect('/admin/login');
    }

    public function test_owner_can_load_each_admin_index(): void
    {
        $this->authenticate();
        $this->get('/admin/posts')->assertStatus(200)->assertSee('Destinations', false);
        $this->get('/admin/passes')->assertStatus(200)->assertSee('Day Passes', false);
        $this->get('/admin/timetables')->assertStatus(200)->assertSee('Route Timetables', false);
        $this->get('/admin/users')->assertStatus(200)->assertSee('Users', false);
    }

    public function test_form_request_blocks_translator_from_creating_post(): void
    {
        // Build a translator session by injecting the user id of a translator
        // we create here.
        $repo = app(UserRepository::class);
        $tx = $repo->create([
            'username' => 'tx_'.bin2hex(random_bytes(3)),
            'name' => 'Test Translator',
            'password' => 'temp-password-1234',
            'role' => 'translator',
            'is_active' => true,
        ]);
        $this->session(['admin_user_id' => $tx['id']]);

        // Translator hits the create page → 403 (StorePostRequest::authorize false)
        $this->get('/admin/posts/create')->assertStatus(403);

        // cleanup
        $repo->delete($tx['id']);
    }

    public function test_owner_load_post_create_form(): void
    {
        $this->authenticate();
        $this->get('/admin/posts/create')->assertStatus(200);
    }
}
