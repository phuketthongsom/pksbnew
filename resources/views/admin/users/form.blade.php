@extends('admin.layout')
@section('title', $user ? 'Edit · '.($user['username'] ?? '') : 'New User')

@php
  $isNew = $user === null;
  $action = $isNew ? route('admin.users.store') : route('admin.users.update', $user['id']);
  $isSelf = !$isNew && current_admin() && current_admin()['id'] === $user['id'];
@endphp

@section('content')
<div class="mb-6">
  <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-teal-brand">← All users</a>
  <h1 class="text-2xl font-bold text-navy-brand mt-1">{{ $isNew ? 'New User' : 'Edit User' }}</h1>
</div>

<form method="POST" action="{{ $action }}" class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-6 grid sm:grid-cols-2 gap-5 max-w-2xl">
  @csrf
  @if(!$isNew) @method('PUT') @endif

  <div class="sm:col-span-2">
    <label for="f-username" class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
    <input id="f-username" name="username" required minlength="3" maxlength="60"
           pattern="[a-zA-Z0-9._-]+" placeholder="e.g. somsak"
           value="{{ old('username', $user['username'] ?? '') }}"
           {{ $isNew ? '' : 'readonly' }}
           class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none {{ $isNew ? '' : 'bg-gray-50 text-gray-600' }}">
    <p class="text-xs text-gray-500 mt-1">{{ $isNew ? 'Letters, numbers, dot, underscore, dash only.' : 'Username is locked after creation. Delete and recreate to change.' }}</p>
  </div>

  <div>
    <label for="f-name" class="block text-sm font-semibold text-gray-700 mb-1">Display name</label>
    <input id="f-name" name="name" required maxlength="120"
           value="{{ old('name', $user['name'] ?? '') }}"
           class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
  </div>

  <div>
    <label for="f-email" class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="font-normal text-gray-500">(optional)</span></label>
    <input id="f-email" type="email" name="email" maxlength="120"
           value="{{ old('email', $user['email'] ?? '') }}"
           class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
  </div>

  <div>
    <label for="f-role" class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
    <select id="f-role" name="role" required {{ $isSelf ? 'disabled' : '' }}
            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none {{ $isSelf ? 'bg-gray-50 text-gray-500' : '' }}">
      @php $current = old('role', $user['role'] ?? 'editor'); @endphp
      @foreach(\App\Services\UserRepository::ROLES as $code => $info)
        <option value="{{ $code }}" @selected($current === $code)>{{ $info['label'] }}</option>
      @endforeach
    </select>
    @if($isSelf)
      <p class="text-xs text-gray-500 mt-1">You can't change your own role (lock-out protection).</p>
      <input type="hidden" name="role" value="owner">
    @endif
  </div>

  <div>
    <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
    <label class="inline-flex items-center gap-2 mt-2">
      <input type="checkbox" name="is_active" value="1"
             {{ ($isNew || !empty($user['is_active'])) ? 'checked' : '' }}
             {{ $isSelf ? 'disabled' : '' }}
             class="rounded border-gray-300 text-teal-brand focus:ring-teal-brand">
      <span class="text-sm text-gray-700">Account active</span>
    </label>
    @if($isSelf)<input type="hidden" name="is_active" value="1">@endif
  </div>

  <div class="sm:col-span-2">
    <label for="f-pw" class="block text-sm font-semibold text-gray-700 mb-1">
      {{ $isNew ? 'Password' : 'New password' }}
      <span class="font-normal text-gray-500">{{ $isNew ? '(min 10 chars)' : '(leave blank to keep current)' }}</span>
    </label>
    <input id="f-pw" type="password" name="password" minlength="10" maxlength="120"
           {{ $isNew ? 'required' : '' }} autocomplete="new-password"
           class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
  </div>

  <div class="sm:col-span-2">
    <button type="submit" class="px-5 py-2.5 rounded-md bg-teal-brand text-white font-semibold hover:bg-teal-600 transition">
      {{ $isNew ? 'Create user' : 'Save changes' }}
    </button>
  </div>
</form>
@endsection
