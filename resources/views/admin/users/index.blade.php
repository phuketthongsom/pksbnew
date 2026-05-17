@extends('admin.layout')
@section('title', 'Users')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl font-bold text-navy-brand">Users</h1>
    <p class="text-sm text-gray-500">Owners can add team members. Editors manage content; translators only edit per-language fields.</p>
  </div>
  <a href="{{ route('admin.users.create') }}"
     class="inline-flex items-center justify-center px-4 py-2.5 rounded-md bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600 sm:self-start whitespace-nowrap">
    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
    New User
  </a>
</div>

{{-- Mobile: stacked cards --}}
<div class="sm:hidden space-y-3">
  @forelse($users as $u)
    @php
      $role = \App\Services\UserRepository::ROLES[$u['role'] ?? ''] ?? ['label' => '—', 'color' => 'bg-gray-100 text-gray-700'];
      $isSelf = current_admin() && current_admin()['id'] === $u['id'];
    @endphp
    <article class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
      <a href="{{ route('admin.users.edit', $u['id']) }}" class="flex items-center gap-3 p-3 hover:bg-gray-50">
        <div class="w-11 h-11 rounded-full bg-teal-brand text-white flex items-center justify-center font-bold flex-none">
          {{ strtoupper(mb_substr($u['name'] ?? $u['username'], 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1">
          <div class="font-semibold text-navy-brand text-sm truncate">{{ $u['name'] ?? $u['username'] }} @if($isSelf)<span class="text-xs text-gray-500 font-normal">(you)</span>@endif</div>
          <div class="text-[11px] text-gray-500 truncate">@<span>{{ $u['username'] }}</span></div>
          <div class="mt-1 flex items-center gap-2 flex-wrap">
            <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded {{ $role['color'] }}">{{ $role['label'] }}</span>
            @if(!empty($u['is_active']))
              <span class="text-[10px] text-green-700">● Active</span>
            @else
              <span class="text-[10px] text-gray-400">○ Inactive</span>
            @endif
          </div>
        </div>
        <svg class="w-5 h-5 text-gray-400 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </a>
      <div class="flex border-t border-gray-100 text-xs">
        <a href="{{ route('admin.users.edit', $u['id']) }}" class="flex-1 py-2.5 text-center font-semibold text-navy-brand hover:bg-gray-50">Edit</a>
        @if(!$isSelf)
          <form method="POST" action="{{ route('admin.users.destroy', $u['id']) }}"
                onsubmit="return confirm('Delete user @{{ $u['username'] }}?');"
                class="flex-1 border-l border-gray-100">
            @csrf @method('DELETE')
            <button class="w-full py-2.5 text-center font-semibold text-red-700 hover:bg-red-50">Delete</button>
          </form>
        @endif
      </div>
    </article>
  @empty
    <p class="text-sm text-gray-500 text-center py-8 bg-white rounded-xl">No users yet.</p>
  @endforelse
</div>

{{-- Desktop: table --}}
<div class="hidden sm:block bg-white rounded-2xl shadow ring-1 ring-gray-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-gray-600">
      <tr>
        <th class="text-left font-semibold px-4 py-3">User</th>
        <th class="text-left font-semibold px-4 py-3">Role</th>
        <th class="text-left font-semibold px-4 py-3 hidden md:table-cell">Status</th>
        <th class="text-left font-semibold px-4 py-3 hidden md:table-cell">Last login</th>
        <th class="text-right font-semibold px-4 py-3">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @forelse($users as $u)
        @php
          $role = \App\Services\UserRepository::ROLES[$u['role'] ?? ''] ?? ['label' => '—', 'color' => 'bg-gray-100 text-gray-700'];
          $isSelf = current_admin() && current_admin()['id'] === $u['id'];
        @endphp
        <tr class="hover:bg-gray-50/50 transition">
          <td class="px-4 py-3">
            <div class="font-semibold text-navy-brand">{{ $u['name'] ?? $u['username'] }} @if($isSelf)<span class="text-xs text-gray-500 font-normal">(you)</span>@endif</div>
            <div class="text-xs text-gray-500">@<span>{{ $u['username'] }}</span> @if(!empty($u['email'])) · {{ $u['email'] }} @endif</div>
          </td>
          <td class="px-4 py-3">
            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded {{ $role['color'] }}">{{ $role['label'] }}</span>
          </td>
          <td class="px-4 py-3 hidden md:table-cell">
            @if(!empty($u['is_active']))
              <span class="text-xs text-green-700">● Active</span>
            @else
              <span class="text-xs text-gray-400">○ Inactive</span>
            @endif
          </td>
          <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $u['last_login_at'] ?? '—' }}</td>
          <td class="px-4 py-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.users.edit', $u['id']) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">Edit</a>
            @if(!$isSelf)
              <form method="POST" action="{{ route('admin.users.destroy', $u['id']) }}" class="inline ml-1" onsubmit="return confirm('Delete user @{{ $u['username'] }}?');">
                @csrf @method('DELETE')
                <button class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-50 text-red-700 hover:bg-red-100 text-sm font-medium">Delete</button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No users yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<aside class="mt-6 bg-blue-50 ring-1 ring-blue-100 rounded-xl p-4 text-xs text-blue-900">
  <strong>Roles &amp; permissions</strong>
  <ul class="mt-2 space-y-1 list-disc list-inside">
    <li><strong>Owner</strong> — full access, including managing users.</li>
    <li><strong>Editor</strong> — manages destinations, timetables, day passes. Cannot manage users.</li>
    <li><strong>Translator</strong> — edits per-language fields on destinations only. Cannot create / delete posts, change images, or touch timetables / passes / users.</li>
  </ul>
</aside>
@endsection
