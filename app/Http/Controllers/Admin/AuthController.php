<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (request()->session()->get('admin_user_id')) {
            return redirect()->route('admin.posts.index');
        }
        return view('admin.login');
    }

    public function login(Request $request, UserRepository $users)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = $users->verifyLogin(
            $request->input('username'),
            $request->input('password'),
        );

        if (!$user) {
            Log::channel('security')->warning('Failed admin login', [
                'username' => $request->input('username'),
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);
            return back()
                ->withErrors(['username' => 'Invalid username or password.'])
                ->onlyInput('username');
        }

        Log::channel('security')->info('Admin login OK', [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'ip' => $request->ip(),
        ]);

        $request->session()->regenerate();
        $request->session()->put('admin_user_id', $user['id']);
        $users->recordLogin($user['id']);

        return redirect()->intended(route('admin.posts.index'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_user_id');
        $request->session()->forget('admin_user'); // legacy key, in case
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
