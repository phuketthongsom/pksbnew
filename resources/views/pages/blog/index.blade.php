@extends('layouts.app')
@section('title', __('blog.hero.title') . ' | Phuket Smart Bus')
@section('description', __('blog.hero.subtitle'))

@push('jsonld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": ["Blog","CollectionPage"],
      "@@id": "{{ url('/blog') }}",
      "url": "{{ url('/blog') }}",
      "name": "Phuket Destinations & Travel Guides",
      "description": "Where to go in Phuket and how to get there by bus.",
      "inLanguage": "{{ app()->getLocale() }}",
      "isPartOf": {"@@id": "{{ url('/') }}"},
      "publisher": {
        "@@type": "Organization",
        "name": "Phuket Smart Bus",
        "logo": {"@@type":"ImageObject","url":"{{ asset('images/logo.png') }}"}
      }
    }
  ]
}
</script>
@endpush

@php
  $activeCategory = $activeCategory ?? null;
  $activeCatSlug  = $activeCategory['slug'] ?? 'all';

  $catIcons = [
    'hotel'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12v7a2 2 0 002 2h14a2 2 0 002-2v-7M3 12V7a2 2 0 012-2h4m10 7V7a2 2 0 00-2-2h-4m-4 0V3m0 2v2m0-2h4m-4 0H7"/></svg>',
    'coffee' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zm4-4v4M10 4v4M14 4v4"/></svg>',
    'palm'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 7C9.5 4 12 2 12 2s2.5 2 2.5 5c0 2-1 3.5-2.5 4.5C10.5 10.5 9.5 9 9.5 7zM12 11.5V22M6 9c-2 0-4 1-4 3 2 0 4-1 5-2.5M18 9c2 0 4 1 4 3-2 0-4-1-5-2.5"/></svg>',
    'camera' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>',
    'tag'    => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M3 3h8l9 9a2 2 0 010 2.828l-5.172 5.172a2 2 0 01-2.828 0L3 11V3z"/></svg>',
    'list'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>',
  ];
@endphp

@section('content')

{{-- ─── Hero ─── --}}
<section class="relative pt-32 pb-14 bg-gradient-to-br from-teal-brand to-cyan-700 text-white overflow-hidden">
  <div class="absolute inset-0 opacity-10"
       style="background-image:url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\")"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="text-white/70 text-xs font-bold uppercase tracking-widest mb-3">{{ __('blog.hero.kicker') }}</p>
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold leading-tight">{{ __('blog.hero.title') }}</h1>
    <p class="mt-4 max-w-xl text-white/85 text-base sm:text-lg">{{ __('blog.hero.subtitle') }}</p>
  </div>
</section>

@if(!empty($categories))

  {{-- ─── Sticky category tabs ─── --}}
  <div class="sticky top-0 z-30 bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-2 overflow-x-auto py-3 scrollbar-none -mb-px">

        {{-- All tab --}}
        <a href="{{ lurl('blog') }}"
           class="flex-none inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all
                  {{ $activeCatSlug === 'all'
                       ? 'bg-navy-brand text-white shadow'
                       : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
          {!! $catIcons['list'] !!}
          {{ __('blog.all_categories') ?? 'All' }}
        </a>

        @foreach($categories as $cat)
          <a href="{{ lurl('blog.category', $cat['slug']) }}"
             class="flex-none inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all border-2
                    {{ $activeCatSlug === $cat['slug'] ? 'text-white border-transparent shadow' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400' }}"
             @if($activeCatSlug === $cat['slug']) style="background-color:{{ $cat['accent'] ?? '#01aaa8' }}" @endif>
            {!! $catIcons[$cat['icon'] ?? 'camera'] ?? $catIcons['camera'] !!}
            {{ $cat['name'] }}
          </a>
        @endforeach

      </div>
    </div>
  </div>

  {{-- ─── Content area ─── --}}
  <div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-12">

      @php
        $visibleCats = $activeCatSlug === 'all'
            ? $categories
            : array_values(array_filter($categories, fn($c) => $c['slug'] === $activeCatSlug));
      @endphp

      @foreach($visibleCats as $cat)
        @php
          $catPosts = collect($grouped->get($cat['slug'], []));
          $accent   = $cat['accent'] ?? '#01aaa8';
          $heroImg  = $cat['hero_image'] ?? ($catPosts->first()['cover'] ?? null);
          $icon     = $catIcons[$cat['icon'] ?? 'camera'] ?? $catIcons['camera'];
          $showPosts = $activeCatSlug === 'all' ? $catPosts->take(3) : $catPosts;
        @endphp

        <div>
          {{-- Section header --}}
          <div class="flex items-center justify-between mb-5 gap-4">
            <div class="flex items-center gap-3">
              {{-- Colored icon badge --}}
              <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white flex-none"
                   style="background-color:{{ $accent }}">
                {!! $icon !!}
              </div>
              <div>
                <h2 class="text-xl font-bold text-navy-brand leading-tight">{{ $cat['name'] }}</h2>
                @if(!empty($cat['tagline']))
                  <p class="text-sm text-gray-500">{{ $cat['tagline'] }}</p>
                @endif
              </div>
            </div>
            @if($catPosts->count() > 0)
              <a href="{{ lurl('blog.category', $cat['slug']) }}"
                 class="flex-none text-sm font-semibold inline-flex items-center gap-1 hover:underline whitespace-nowrap"
                 style="color:{{ $accent }}">
                @if($activeCatSlug === 'all')
                  See all {{ $catPosts->count() }} →
                @else
                  ← All categories
                @endif
              </a>
            @endif
          </div>

          @if($catPosts->isEmpty())
            {{-- Empty state --}}
            <div class="bg-white rounded-2xl ring-1 ring-gray-100 p-10 text-center text-gray-400 text-sm">
              <div class="text-3xl mb-2">🗂️</div>
              No posts in this category yet — check back soon.
            </div>
          @else
            {{-- Category hero image banner (when thumbnail is set) --}}
            @if(!empty($cat['hero_image']))
              <div class="relative rounded-2xl overflow-hidden h-40 sm:h-52 mb-5 shadow">
                <img src="{{ asset($cat['hero_image']) }}" alt="{{ $cat['name'] }}"
                     class="w-full h-full object-cover">
                <div class="absolute inset-0" style="background:linear-gradient(to right, {{ $accent }}bb 0%, transparent 60%)"></div>
                <div class="absolute inset-0 flex items-center px-7">
                  <div class="text-white">
                    <p class="text-2xl font-extrabold leading-tight">{{ $cat['name'] }}</p>
                    @if(!empty($cat['tagline']))<p class="text-white/80 text-sm mt-1">{{ $cat['tagline'] }}</p>@endif
                  </div>
                </div>
              </div>
            @endif

            {{-- Post cards grid --}}
            <div class="{{ $activeCatSlug === 'all' ? 'grid sm:grid-cols-2 lg:grid-cols-3 gap-5' : 'grid sm:grid-cols-2 lg:grid-cols-3 gap-6' }}">
              @foreach($showPosts as $p)
                @php
                  $coverBase = pathinfo($p['cover'], PATHINFO_DIRNAME).'/'.pathinfo($p['cover'], PATHINFO_FILENAME);
                  $hasWebp   = file_exists(public_path($coverBase.'.webp'));
                @endphp
                <article class="group bg-white rounded-2xl overflow-hidden shadow-sm ring-1 ring-gray-100 hover:shadow-md transition flex flex-col">
                  <a href="{{ lurl('blog.show', $p['slug']) }}"
                     class="block aspect-[16/10] overflow-hidden bg-gray-100 flex-none">
                    <picture>
                      @if($hasWebp)
                        <source srcset="{{ asset($coverBase.'.webp') }}" type="image/webp">
                      @endif
                      <img src="{{ asset($p['cover']) }}"
                           alt="{{ $p['title'] }}"
                           class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                           loading="lazy">
                    </picture>
                  </a>
                  <div class="p-4 flex-1 flex flex-col">
                    {{-- Category badge + meta --}}
                    <div class="flex items-center gap-2 text-xs mb-2">
                      <span class="px-2 py-0.5 rounded-full font-semibold text-white"
                            style="background-color:{{ $accent }}">
                        {{ $p['area'] ?? '' }}
                      </span>
                      <span class="text-gray-400">{{ $p['reading_minutes'] }} min read</span>
                    </div>
                    {{-- Title --}}
                    <h3 class="text-base font-bold text-navy-brand leading-snug line-clamp-2 flex-1 group-hover:text-teal-brand transition mb-3">
                      <a href="{{ lurl('blog.show', $p['slug']) }}">{{ $p['title'] }}</a>
                    </h3>
                    {{-- Read more --}}
                    <a href="{{ lurl('blog.show', $p['slug']) }}"
                       class="inline-flex items-center gap-1 text-sm font-semibold"
                       style="color:{{ $accent }}">
                      {{ __('blog.read_more') }}
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                  </div>
                </article>
              @endforeach
            </div>

            {{-- "View all" row when in All mode and there are more posts --}}
            @if($activeCatSlug === 'all' && $catPosts->count() > 3)
              <div class="mt-4 text-right">
                <a href="{{ lurl('blog.category', $cat['slug']) }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold hover:underline"
                   style="color:{{ $accent }}">
                  View all {{ $catPosts->count() }} {{ $cat['name'] }} posts →
                </a>
              </div>
            @endif
          @endif
        </div>
      @endforeach

    </div>
  </div>

@else
  {{-- No categories: plain post grid fallback --}}
  <section class="py-10 sm:py-14 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      @if($posts->isEmpty())
        <p class="text-gray-500 text-center py-16">{{ __('blog.empty') }}</p>
      @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($posts as $p)
            @php
              $coverBase = pathinfo($p['cover'], PATHINFO_DIRNAME).'/'.pathinfo($p['cover'], PATHINFO_FILENAME);
              $hasWebp   = file_exists(public_path($coverBase.'.webp'));
            @endphp
            <article class="group bg-white rounded-2xl overflow-hidden shadow ring-1 ring-gray-100 hover:shadow-lg transition flex flex-col">
              <a href="{{ lurl('blog.show', $p['slug']) }}" class="block aspect-[16/10] overflow-hidden bg-gray-100">
                <picture>
                  @if($hasWebp)<source srcset="{{ asset($coverBase.'.webp') }}" type="image/webp">@endif
                  <img src="{{ asset($p['cover']) }}" alt="{{ $p['title'] }}"
                       class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                       loading="lazy">
                </picture>
              </a>
              <div class="p-5 flex-1 flex flex-col">
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                  <span class="px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 font-semibold">{{ $p['area'] }}</span>
                  <span>{{ $p['reading_minutes'] }} min</span>
                </div>
                <h2 class="text-lg font-bold text-navy-brand mb-2 leading-snug flex-1">
                  <a href="{{ lurl('blog.show', $p['slug']) }}" class="hover:text-teal-brand transition">{{ $p['title'] }}</a>
                </h2>
                <a href="{{ lurl('blog.show', $p['slug']) }}"
                   class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-teal-brand hover:text-teal-700">
                  {{ __('blog.read_more') }}
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
              </div>
            </article>
          @endforeach
        </div>
      @endif
    </div>
  </section>
@endif

@endsection
