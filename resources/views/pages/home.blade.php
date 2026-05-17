@extends('layouts.app')
@section('title', 'PKSB - Phuket Smart Bus | Airport · Patong · Karon · Rawai')
@section('description', 'Ride the Phuket Smart Bus from the airport to Patong, Karon, Kata and Rawai. Three routes from FREE to 100฿. Real-time GPS tracking, daily timetable and contactless Tap, Go and Ride payment.')

@push('preload')
  <link rel="preload" as="image" href="{{ asset('images/bus-mastercard.webp') }}" type="image/webp" fetchpriority="high">
@endpush

@push('jsonld')
<script type="application/ld+json">
{
  "@@context":"https://schema.org",
  "@@type":"WebSite",
  "name":"Phuket Smart Bus",
  "url":"{{ url('/') }}",
  "potentialAction":{
    "@@type":"SearchAction",
    "target":"{{ url('/timetable') }}",
    "query-input":"required name=search_term_string"
  }
}
</script>
@endpush

@section('content')
<!-- Hero -->
<section class="relative min-h-[480px] sm:min-h-[600px] md:min-h-[680px] flex items-center overflow-hidden" aria-labelledby="hero-title">
  <picture>
    <source srcset="{{ asset('images/bus-mastercard.webp') }}" type="image/webp">
    <img src="{{ asset('images/bus-mastercard.jpg') }}" alt=""
         class="absolute inset-0 w-full h-full object-cover" width="1080" height="1080" fetchpriority="high">
  </picture>
  <div class="absolute inset-0 hero-overlay" aria-hidden="true"></div>

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-24 sm:pt-32 pb-16 sm:pb-20">
    <div class="max-w-2xl text-white">
      <h1 id="hero-title" class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight drop-shadow-lg">
        {{ __('home.hero.title') }}
      </h1>
      <p class="mt-4 sm:mt-5 text-sm sm:text-lg text-white/90 max-w-xl">
        {{ __('home.hero.subtitle') }}
      </p>
      <div class="mt-6 sm:mt-8 flex flex-wrap items-center gap-3">
        <a href="{{ lurl('timetable') }}" class="inline-flex items-center px-5 py-3 rounded-md bg-teal-brand text-white text-sm font-semibold shadow-lg hover:bg-teal-600 transition">
          {{ __('home.cta.timetable') }}
        </a>
        <a href="{{ lurl('tracking') }}" class="inline-flex items-center px-5 py-3 rounded-md bg-white/15 backdrop-blur text-white text-sm font-semibold border border-white/30 hover:bg-white/25 transition">
          {{ __('home.cta.tracking') }}
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Feature cards -->
<section class="bg-white py-12 sm:py-16" aria-labelledby="features-title">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 id="features-title" class="sr-only">Services</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
      @php
        $features = [
          ['title' => __('nav.timetable'),                 'desc' => __('home.features.timetable.desc'), 'href' => lurl('timetable'), 'icon' => 'clock'],
          ['title' => __('home.features.tracking.title'),  'desc' => __('home.features.tracking.desc'),  'href' => lurl('tracking'),  'icon' => 'map'],
          ['title' => __('nav.payment'),                   'desc' => __('home.features.payment.desc'),   'href' => lurl('payment'),   'icon' => 'card'],
          ['title' => __('home.features.pass.title'),      'desc' => __('home.features.pass.desc'),      'href' => lurl('pass'),      'icon' => 'ticket'],
        ];
      @endphp

      @foreach ($features as $f)
        <div class="bg-gray-50 rounded-2xl p-5 sm:p-6 flex flex-col items-center text-center shadow-sm hover:shadow-md transition">
          <div class="w-14 h-14 rounded-xl bg-white shadow flex items-center justify-center mb-4" aria-hidden="true">
            @if ($f['icon'] === 'clock')
              <svg class="w-7 h-7 text-teal-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
            @elseif ($f['icon'] === 'map')
              <svg class="w-7 h-7 text-teal-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-7.5-7-12a7 7 0 1114 0c0 4.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
            @elseif ($f['icon'] === 'card')
              <svg class="w-7 h-7 text-teal-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="12" rx="2"/><path stroke-linecap="round" d="M3 10h18M7 15h3"/></svg>
            @else
              <svg class="w-7 h-7 text-teal-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14a2 2 0 012 2v2a2 2 0 100 4v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 100-4V9a2 2 0 012-2z"/></svg>
            @endif
          </div>
          <h3 class="text-base sm:text-lg font-semibold text-teal-brand mb-2">{{ $f['title'] }}</h3>
          <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-5 flex-1">{{ $f['desc'] }}</p>
          <a href="{{ $f['href'] }}" class="inline-block px-5 py-1.5 rounded-md bg-teal-brand text-white text-xs sm:text-sm font-medium hover:bg-teal-600 transition" aria-label="{{ $f['title'] }}">{{ __('home.more') }}</a>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- Destinations teaser -->
<section class="bg-gray-50 py-12 sm:py-16" aria-labelledby="destinations-title">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between gap-4 mb-6">
      <div>
        <p class="text-teal-brand text-xs font-semibold uppercase tracking-wider mb-1">{{ __('home.destinations.kicker') }}</p>
        <h2 id="destinations-title" class="text-2xl sm:text-3xl font-bold text-navy-brand">{{ __('home.destinations.title') }}</h2>
      </div>
      <a href="{{ lurl('blog') }}" class="hidden sm:inline-flex text-sm font-semibold text-teal-brand hover:text-teal-700">{{ __('home.destinations.view_all') }} →</a>
    </div>

    @php
      $repo = app(\App\Services\PostsRepository::class);
      $latest = collect($repo->all())->shuffle()->take(3)->map(fn ($p) => $repo->localized($p));
    @endphp

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($latest as $p)
        @php
          $coverBase = pathinfo($p['cover'], PATHINFO_DIRNAME).'/'.pathinfo($p['cover'], PATHINFO_FILENAME);
          $hasWebp = file_exists(public_path($coverBase.'.webp'));
        @endphp
        <article class="group bg-white rounded-2xl overflow-hidden shadow-sm ring-1 ring-gray-100 hover:shadow-md transition flex flex-col">
          <a href="{{ lurl('blog.show', $p['slug']) }}" class="block aspect-[16/10] overflow-hidden bg-gray-100">
            <picture>
              @if($hasWebp)<source srcset="{{ asset($coverBase.'.webp') }}" type="image/webp">@endif
              <img src="{{ asset($p['cover']) }}" alt="{{ $p['title'] }}"
                   class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                   loading="lazy" width="640" height="400">
            </picture>
          </a>
          <div class="p-4 flex-1 flex flex-col">
            <div class="text-xs text-gray-500 mb-1">{{ $p['area'] }}</div>
            <h3 class="text-base font-bold text-navy-brand leading-snug mb-2">
              <a href="{{ lurl('blog.show', $p['slug']) }}" class="hover:text-teal-brand">{{ $p['title'] }}</a>
            </h3>
            <p class="text-sm text-gray-600 leading-relaxed flex-1">{{ $p['excerpt'] }}</p>
          </div>
        </article>
      @endforeach
    </div>

    <div class="mt-6 sm:hidden text-center">
      <a href="{{ lurl('blog') }}" class="inline-flex text-sm font-semibold text-teal-brand">{{ __('home.destinations.view_all') }} →</a>
    </div>
  </div>
</section>
@endsection
