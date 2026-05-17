<?php

namespace App\Http\Middleware;

use App\Services\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pream's note: route-level permission check. Use as 'admin.can:posts.manage'
 * inside the existing 'admin' middleware group so the AdminAuth pre-hydration
 * has already happened.
 */
class AdminCan
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->attributes->get('admin_user');
        if (!UserRepository::userCan($user, $permission)) {
            abort(403, "You don't have permission to do that.");
        }
        return $next($request);
    }
}
