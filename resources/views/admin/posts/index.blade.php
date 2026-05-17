@extends('admin.layout')
@section('title', 'Destinations')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl font-bold text-navy-brand">Destinations</h1>
    <p class="text-sm text-gray-500">Blog posts shown on the public /blog page.</p>
  </div>
  @if(current_admin_can('posts.manage'))
    <a href="{{ route('admin.posts.create') }}"
       class="inline-flex items-center justify-center px-4 py-2.5 rounded-md bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600 sm:self-start whitespace-nowrap">
      <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
      New Destination
    </a>
  @endif
</div>

@if(empty($posts))
  <div class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-10 text-center">
    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-teal-50 text-teal-brand flex items-center justify-center">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-7.5-7-12a7 7 0 1114 0c0 4.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
    </div>
    <h2 class="font-bold text-navy-brand mb-1">No destinations yet</h2>
    <p class="text-sm text-gray-600 mb-4">Your first destination guide will appear here.</p>
    @if(current_admin_can('posts.manage'))
      <a href="{{ route('admin.posts.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600">+ Create your first one</a>
    @endif
  </div>
@else

  {{-- Mobile: stacked cards --}}
  <div class="sm:hidden space-y-3">
    @foreach($posts as $p)
      @php $isFuture = ($p['published_at'] ?? '') > date('Y-m-d'); @endphp
      <article class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
        <a href="{{ route('admin.posts.edit', $p['slug']) }}" class="flex items-center gap-3 p-3 hover:bg-gray-50 transition">
          <img src="{{ asset($p['cover'] ?? 'images/bus-mastercard.jpg') }}" alt=""
               class="w-16 h-16 rounded-md object-cover bg-gray-100 flex-none">
          <div class="min-w-0 flex-1">
            <div class="font-semibold text-navy-brand text-sm truncate">{{ $p['title'] }}</div>
            <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-2 flex-wrap">
              @if(!empty($p['area']))<span class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 font-semibold">{{ $p['area'] }}</span>@endif
              <span>{{ count($p['gallery'] ?? []) }} photo{{ count($p['gallery'] ?? []) === 1 ? '' : 's' }}</span>
              <span>·</span>
              <span>{{ $p['published_at'] ?? '—' }}</span>
              @if($isFuture)
                <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-yellow-brand text-navy-brand">Scheduled</span>
              @endif
            </div>
          </div>
          <svg class="w-5 h-5 text-gray-400 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <div class="flex border-t border-gray-100 text-xs">
          <a href="{{ route('admin.posts.edit', $p['slug']) }}"
             class="flex-1 py-2.5 text-center font-semibold text-navy-brand hover:bg-gray-50">
            {{ current_admin_can('posts.manage') ? 'Edit' : 'Translate' }}
          </a>
          @if(current_admin_can('posts.manage'))
            <form method="POST" action="{{ route('admin.posts.destroy', $p['slug']) }}"
                  onsubmit="return confirm('Delete this destination and all its photos?');"
                  class="flex-1 border-l border-gray-100">
              @csrf @method('DELETE')
              <button type="submit" class="w-full py-2.5 text-center font-semibold text-red-700 hover:bg-red-50">Delete</button>
            </form>
          @endif
        </div>
      </article>
    @endforeach
  </div>

  {{-- Desktop: table --}}
  <div class="hidden sm:block bg-white rounded-2xl shadow ring-1 ring-gray-100 overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="text-left font-semibold px-4 py-3">Title</th>
          <th class="text-left font-semibold px-4 py-3">Area</th>
          <th class="text-left font-semibold px-4 py-3 hidden md:table-cell">Photos</th>
          <th class="text-left font-semibold px-4 py-3 hidden md:table-cell">Published</th>
          <th class="text-right font-semibold px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($posts as $p)
          @php $isFuture = ($p['published_at'] ?? '') > date('Y-m-d'); @endphp
          <tr class="hover:bg-gray-50/50 transition">
            <td class="px-4 py-4">
              <div class="flex items-center gap-3">
                <img src="{{ asset($p['cover'] ?? 'images/bus-mastercard.jpg') }}" alt="" class="w-12 h-12 rounded object-cover bg-gray-100 flex-none">
                <div class="min-w-0">
                  <div class="font-semibold text-navy-brand truncate">{{ $p['title'] }}</div>
                  <a href="{{ route('blog.show', $p['slug']) }}" target="_blank" class="text-xs text-gray-500 hover:text-teal-brand">/blog/{{ $p['slug'] }} ↗</a>
                </div>
              </div>
            </td>
            <td class="px-4 py-4">{{ $p['area'] ?? '—' }}</td>
            <td class="px-4 py-4 hidden md:table-cell">
              @php $n = count($p['gallery'] ?? []); @endphp
              {{ $n }} {{ $n === 1 ? 'photo' : 'photos' }}
            </td>
            <td class="px-4 py-4 hidden md:table-cell text-gray-600">
              {{ $p['published_at'] ?? '—' }}
              @if($isFuture)
                <span class="ml-1 inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-yellow-brand text-navy-brand">Scheduled</span>
              @endif
            </td>
            <td class="px-4 py-4 text-right whitespace-nowrap">
              <a href="{{ route('admin.posts.edit', $p['slug']) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-sm font-medium">{{ current_admin_can('posts.manage') ? 'Edit' : 'Translate' }}</a>
              @if(current_admin_can('posts.manage'))
                <form method="POST" action="{{ route('admin.posts.destroy', $p['slug']) }}" class="inline ml-1" onsubmit="return confirm('Delete this destination and all its photos?');">
                  @csrf @method('DELETE')
                  <button class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-50 text-red-700 hover:bg-red-100 text-sm font-medium">Delete</button>
                </form>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
@endsection
