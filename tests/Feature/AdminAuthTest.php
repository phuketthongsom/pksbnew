<?php

namespace Tests\Feature;

use App\Services\UserRepository;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    public function test_admin_routes_redirect_to_login_when_anonymous(): void
    {
        $this->get('/admin/posts')->assertRedirect('/admin/login');
        $this->get('/admin/users')->assertRedirect('/admin/login');
        $this->get('/admin/timetables')->assertRedirect('/admin/login');
    }

    public function test_login_with_seeded_admin_succeeds(): void
    {
        // The seeded admin uses ADMIN_PASSWORD from env (admin123 in tests).
        $response = $this->post('/admin/login', [
            'username' => env('ADMIN_USERNAME', 'admin'),
            'password' => env('ADMIN_PASSWORD', 'admin123'),
        ]);
        $response->assertRedirect('/admin/posts');
        $this->assertNotNull(session('admin_user_id'));
    }

    public function test_login_with_bad_password_fails(): void
    {
        $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');
        $this->assertNull(session('admin_user_id'));
    }

    public function test_user_repository_seeds_owner_on_first_run(): void
    {
        $repo = app(UserRepository::class);
        $owner = $repo->findByUsername(env('ADMIN_USERNAME', 'admin'));
        $this->assertNotNull($owner);
        $this->assertSame('owner', $owner['role']);
    }

    public function test_translator_lacks_posts_manage_permission(): void
    {
        $this->assertFalse(UserRepository::userCan(['role' => 'translator'], 'posts.manage'));
        $this->assertTrue(UserRepository::userCan(['role' => 'translator'], 'translations.edit'));
        $this->assertTrue(UserRepository::userCan(['role' => 'owner'], 'users.manage'));
        $this->assertFalse(UserRepository::userCan(['role' => 'editor'], 'users.manage'));
    }
}
