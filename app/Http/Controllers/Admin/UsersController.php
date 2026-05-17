<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Services\UserRepository;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(UserRepository $repo)
    {
        $users = $repo->all();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => null]);
    }

    public function store(StoreUserRequest $request, UserRepository $repo)
    {
        $data = $request->validated();
        if ($repo->findByUsername($data['username'])) {
            return back()
                ->withErrors(['username' => 'That username is already taken.'])
                ->withInput();
        }
        $repo->create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'] ?? '',
            'password' => $data['password'],
            'role' => $data['role'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(string $id, UserRepository $repo)
    {
        $user = $repo->find($id);
        abort_if(!$user, 404);
        return view('admin.users.form', compact('user'));
    }

    public function update(UpdateUserRequest $request, string $id, UserRepository $repo)
    {
        $user = $repo->find($id);
        abort_if(!$user, 404);
        $data = $request->validated();

        // Anti-lockout: an owner can't demote or deactivate themselves.
        $self = current_admin();
        if ($self && $self['id'] === $id) {
            $data['role'] = 'owner';
            $data['is_active'] = true;
        }

        $update = [
            'name' => $data['name'],
            'email' => $data['email'] ?? '',
            'role' => $data['role'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
        if (!empty($data['password'])) $update['password'] = $data['password'];

        $repo->update($id, $update);
        return redirect()->route('admin.users.edit', $id)->with('status', 'User saved.');
    }

    public function destroy(string $id, UserRepository $repo)
    {
        $self = current_admin();
        if ($self && $self['id'] === $id) {
            return back()->withErrors(['username' => "You can't delete your own account."]);
        }
        // Don't let the last owner be deleted — would lock everyone out.
        $owners = array_filter(
            $repo->all(),
            fn ($u) => ($u['role'] ?? '') === 'owner' && !empty($u['is_active'])
        );
        $target = $repo->find($id);
        if ($target && ($target['role'] ?? '') === 'owner' && count($owners) <= 1) {
            return back()->withErrors(['username' => "Can't delete the last active owner."]);
        }
        $repo->delete($id);
        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }
}
