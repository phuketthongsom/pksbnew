<?php

namespace App\Http\Middleware;

use App\Services\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pream's note: hydrates the admin user on every request from session('admin_user_id'),
 * so route handlers can call current_admin() / current_admin_can() without
 * doing the lookup themselves.
 */
class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('admin_user_id');
        if (!$userId) {
            return redirect()->route('admin.login');
        }
        $user = app(UserRepository::class)->find($userId);
        if (!$user || empty($user['is_active'])) {
            $request->session()->forget('admin_user_id');
            return redirect()->route('admin.login')->withErrors(['username' => 'Your account is no longer active.']);
        }
        // Stash on the request so view composers + helpers can read it.
        $request->attributes->set('admin_user', $user);
        view()->share('adminUser', $user);
        return $next($request);
    }
}
