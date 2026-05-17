@extends('layouts.app')
@section('title', $post['title'].' | Phuket Smart Bus')
@section('description', $post['excerpt'])
@section('og_type', 'article')
@section('og_image', asset($post['cover']))

@php
  $coverBase    = pathinfo($post['cover'], PATHINFO_DIRNAME).'/'.pathinfo($post['cover'], PATHINFO_FILENAME);
  $coverWebp    = $coverBase.'.webp';
  $hasWebpCover = file_exists(public_path($coverWebp));
  $publishedAt  = $post['published_at'] ?? date('Y-m-d');
  $updatedAt    = $post['updated_at']   ?? $publishedAt;
@endphp

@section('og_extra')
<meta property="article:published_time" content="{{ $publishedAt }}">
<meta property="article:modified_time"  content="{{ $updatedAt }}">
<meta property="article:section"        content="Travel Guides">
<meta property="article:tag"            content="Phuket">
<meta property="article:tag"            content="{{ $post['area'] ?? 'Phuket' }}">
@endsection

@push('preload')
  <link rel="preload" as="image"
        href="{{ asset($hasWebpCover ? $coverWebp : $post['cover']) }}"
        @if($hasWebpCover) type="image/webp" @endif
        fetchpriority="high">
@endpush

@push('jsonld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "TravelAction",
      "@@id": "{{ url()->current() }}#travel",
      "name": "How to get to {{ addslashes($post['title']) }} by bus",
      "toLocation": {"@@type":"TouristAttraction","name":"{{ addslashes($post['title']) }}","containedInPlace":{"@@type":"City","name":"Phuket","containedInPlace":{"@@type":"Country","name":"Thailand"}}},
      "instrument": {"@@id": "{{ url('/') }}"}
    },
    {
      "@@type": "Article",
      "@@id": "{{ url()->current() }}",
      "url": "{{ url()->current() }}",
      "headline": "{{ addslashes($post['title']) }}",
      "description": "{{ addslashes($post['excerpt']) }}",
      "image": {
        "@@type": "ImageObject",
        "url": "{{ asset($post['cover']) }}",
        "width": 1080,
        "height": 720
      },
      "datePublished": "{{ $publishedAt }}",
      "dateModified":  "{{ $updatedAt }}",
      "inLanguage": "{{ app()->getLocale() }}",
      "author": {"@@type":"Organization","name":"Phuket Smart Bus","url":"{{ url('/') }}"},
      "publisher": {
        "@@type": "Organization",
        "name": "Phuket Smart Bus",
        "logo": {"@@type":"ImageObject","url":"{{ asset('images/logo.png') }}","width":240,"height":120}
      },
      "mainEntityOfPage": {"@@type":"WebPage","@@id":"{{ url()->current() }}"},
      "isPartOf": {"@@id": "{{ url('/blog') }}"},
      "breadcrumb": {"@@id": "{{ url()->current() }}#breadcrumb"},
      "about": {"@@type":"TouristAttraction","name":"{{ addslashes($post['title']) }}","containedInPlace":{"@@type":"City","name":"Phuket"}}
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ url()->current() }}#breadcrumb",
      "itemListElement": [
        {"@@type":"ListItem","position":1,"name":"Home","item":"{{ url('/') }}"},
        {"@@type":"ListItem","position":2,"name":"Destinations","item":"{{ url('/blog') }}"},
        {"@@type":"ListItem","position":3,"name":"{{ addslashes($post['title']) }}"}
      ]
    }
  ]
}
</script>
@endpush

@section('content')
<article>
  <header class="relative pt-32 pb-16 sm:pb-24 text-white overflow-hidden">
    <picture>
      @if($hasWebpCover)
        <source srcset="{{ asset($coverWebp) }}" type="image/webp">
      @endif
      <img src="{{ asset($post['cover']) }}" alt="{{ $post['title'] }} — Phuket Smart Bus destination"
           class="absolute inset-0 w-full h-full object-cover" width="1080" height="720" fetchpriority="high">
    </picture>
    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/70" aria-hidden="true"></div>

    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <nav class="text-xs text-white/80 mb-4" aria-label="Breadcrumb">
        <a href="{{ lurl('home') }}" class="hover:text-white">{{ __('nav.home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ lurl('blog') }}" class="hover:text-white">{{ __('nav.destinations') }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-white/60">{{ $post['area'] }}</span>
      </nav>
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold leading-tight drop-shadow-lg">{{ $post['title'] }}</h1>
      <p class="mt-4 text-base sm:text-lg text-white/90">{{ $post['excerpt'] }}</p>
      <div class="mt-5 flex flex-wrap items-center gap-3 text-xs">
        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/15 backdrop-blur ring-1 ring-white/30">{{ $post['area'] }}</span>
        <span class="text-white/80"><time datetime="{{ $post['published_at'] }}">{{ \Carbon\Carbon::parse($post['published_at'])->format('F j, Y') }}</time></span>
        <span class="text-white/60" aria-hidden="true">·</span>
        <span class="text-white/80">{{ __('blog.min_read', ['n' => $post['reading_minutes']]) }}</span>
      </div>
    </div>
  </header>

  <div class="py-10 sm:py-14">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <aside class="mb-8 p-5 rounded-2xl bg-teal-50 ring-1 ring-teal-100 text-sm text-teal-900 flex items-start gap-3">
        <svg class="w-5 h-5 mt-0.5 flex-none text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-7.5-7-12a7 7 0 1114 0c0 4.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
        <div>
          <div class="font-semibold mb-0.5">{{ __('blog.nearest_stop') }}</div>
          <div>{{ $post['nearest_stop'] }} —
            <a href="{{ lurl('tracking', ['route' => $post['route_recommendation']]) }}" class="underline hover:text-teal-700">{{ __('blog.track_route') }}</a>.
          </div>
        </div>
      </aside>

      <div lang="{{ app()->getLocale() }}"
           class="prose prose-lg max-w-none
                  prose-headings:text-navy-brand prose-headings:font-bold
                  prose-h2:text-2xl prose-h2:mt-8 prose-h2:mb-3
                  prose-p:text-gray-700 prose-p:leading-relaxed
                  prose-li:text-gray-700
                  prose-a:text-teal-brand prose-a:no-underline hover:prose-a:underline
                  prose-strong:text-navy-brand">
        {!! $post['body'] !!}
      </div>

      @if(!empty($post['gallery']) && count($post['gallery']) > 1)
        <section class="mt-10" aria-labelledby="gallery-title">
          <h2 id="gallery-title" class="text-2xl font-bold text-navy-brand mb-4">{{ __('blog.gallery') }}</h2>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($post['gallery'] as $img)
              <a href="{{ asset($img) }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden ring-1 ring-gray-200 bg-gray-100 group">
                <img src="{{ asset($img) }}" alt="{{ $post['title'] }} — photo {{ $loop->iteration }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
              </a>
            @endforeach
          </div>
        </section>
      @endif

      <div class="mt-12 p-6 rounded-2xl bg-gradient-to-br from-teal-brand to-cyan-700 text-white">
        <h2 class="text-xl font-bold mb-2">{{ __('blog.plan_your_ride') }}</h2>
        <p class="text-white/90 mb-4 text-sm">{{ __('blog.plan_your_ride.desc') }}</p>
        <div class="flex flex-wrap gap-2">
          <a href="{{ lurl('timetable') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-white text-teal-700 text-sm font-semibold hover:bg-gray-100">{{ __('home.cta.timetable') }}</a>
          <a href="{{ lurl('tracking', ['route' => $post['route_recommendation']]) }}" class="inline-flex items-center px-4 py-2 rounded-md bg-white/15 backdrop-blur text-white text-sm font-semibold border border-white/30 hover:bg-white/25">{{ __('home.cta.tracking') }}</a>
        </div>
      </div>
    </div>
  </div>
</article>

@if($related->isNotEmpty())
<section class="bg-gray-50 py-12 sm:py-16" aria-labelledby="related-title">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 id="related-title" class="text-2xl font-bold text-navy-brand mb-6">{{ __('blog.more_destinations') }}</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($related as $r)
        @php
          $rBase = pathinfo($r['cover'], PATHINFO_DIRNAME).'/'.pathinfo($r['cover'], PATHINFO_FILENAME);
          $rHasWebp = file_exists(public_path($rBase.'.webp'));
        @endphp
        <article class="bg-white rounded-2xl overflow-hidden shadow ring-1 ring-gray-100 hover:shadow-lg transition">
          <a href="{{ lurl('blog.show', $r['slug']) }}" class="block aspect-[16/10] overflow-hidden bg-gray-100">
            <picture>
              @if($rHasWebp)<source srcset="{{ asset($rBase.'.webp') }}" type="image/webp">@endif
              <img src="{{ asset($r['cover']) }}" alt="{{ $r['title'] }}" class="w-full h-full object-cover" loading="lazy" width="640" height="400">
            </picture>
          </a>
          <div class="p-4">
            <div class="text-xs text-gray-500 mb-1">{{ $r['area'] }}</div>
            <h3 class="font-semibold text-navy-brand leading-snug">
              <a href="{{ lurl('blog.show', $r['slug']) }}" class="hover:text-teal-brand">{{ $r['title'] }}</a>
            </h3>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection
