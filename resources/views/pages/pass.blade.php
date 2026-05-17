@extends('layouts.app')
@section('title', 'Day Pass and Multi-Day Pass — Unlimited Phuket Smart Bus Rides | PKSB')
@section('description', 'Unlimited Phuket Smart Bus rides with our multi-day passes. The smartest way to explore Phuket — airport to Patong, Karon, Kata and Rawai.')

@push('jsonld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "WebPage",
      "@@id": "{{ url('/pass') }}",
      "url": "{{ url('/pass') }}",
      "name": "Day Pass and Multi-Day Pass — Phuket Smart Bus",
      "description": "Unlimited Phuket Smart Bus rides with our multi-day passes. Airport to Patong, Karon, Kata and Rawai.",
      "isPartOf": {"@@id": "{{ url('/') }}"},
      "breadcrumb": {"@@id": "{{ url('/pass') }}#breadcrumb"}
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ url('/pass') }}#breadcrumb",
      "itemListElement": [
        {"@@type":"ListItem","position":1,"name":"Home","item":"{{ url('/') }}"},
        {"@@type":"ListItem","position":2,"name":"Day Passes"}
      ]
    }
  ]
}
</script>
@endpush

@php
  $passRepo = app(\App\Services\PassesRepository::class);
  $passes = collect($passRepo->all())->map(fn ($p) => $passRepo->localized($p));
@endphp

@push('jsonld')
<script type="application/ld+json">
{
  "@@context":"https://schema.org",
  "@@type":"ItemList",
  "name":"Phuket Smart Bus Day Passes",
  "itemListElement":[
@foreach($passes as $i => $p)
    {"@@type":"Product","name":"{{ addslashes($p['name']) }}","offers":{"@@type":"Offer","price":"{{ $p['price'] }}","priceCurrency":"{{ $p['currency'] ?? 'THB' }}"}}@if($i < $passes->count() - 1),@endif
@endforeach
  ]
}
</script>
@endpush

@section('content')
<section class="relative pt-32 pb-12 bg-gradient-to-br from-teal-brand to-cyan-700 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold">{{ __('pass.title') }}</h1>
    <p class="mt-3 text-white/90">{{ __('pass.subtitle') }}</p>
  </div>
</section>

<section class="py-12 sm:py-16" aria-label="Pass options">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
    @foreach($passes as $p)
      @php
        $hasCover = !empty($p['cover']);
        // If cover is uploaded (storage/...), use it raw. If it's a default
        // bundled image (images/pass-30days.png), we know there's a webp twin.
        $coverPath = $p['cover'] ?? 'images/bus-airport.jpg';
        $coverIsCustomDesign = $hasCover; // any custom image = no overlay
        $coverWebp = pathinfo($coverPath, PATHINFO_DIRNAME).'/'.pathinfo($coverPath, PATHINFO_FILENAME).'.webp';
        $hasWebp = file_exists(public_path($coverWebp));
      @endphp
      <article class="rounded-2xl overflow-hidden shadow-lg ring-1 ring-gray-100 bg-white">
        <div class="relative h-40 bg-cover bg-center bg-gray-200">
          <picture>
            @if($hasWebp)<source srcset="{{ asset($coverWebp) }}" type="image/webp">@endif
            <img src="{{ asset($coverPath) }}" alt="{{ $p['name'] }} — Phuket Smart Bus Day Pass"
                 class="absolute inset-0 w-full h-full object-cover" loading="lazy" width="540" height="200">
          </picture>
          @unless($coverIsCustomDesign)
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/20"></div>
            <div class="absolute inset-0 p-5 flex flex-col justify-between text-white drop-shadow">
              <div class="text-xs font-semibold uppercase tracking-wider">PKSB</div>
              <div>
                <div class="text-2xl font-extrabold leading-none">{{ $p['name'] }}</div>
                <div class="text-xs mt-1 opacity-90">By Phuket Smart Bus</div>
              </div>
            </div>
          @endunless
        </div>
        <div class="p-5">
          <h2 class="sr-only">{{ $p['name'] }}</h2>
          <div class="flex items-baseline gap-1 mb-2">
            <span class="text-3xl font-extrabold text-teal-brand">{{ number_format($p['price']) }}</span>
            <span class="text-sm font-medium text-gray-500">{{ $p['currency'] ?? 'THB' === 'THB' ? '฿' : $p['currency'] }}</span>
          </div>
          @if(!empty($p['description']))
            <p class="text-sm text-gray-600 mb-4">{{ $p['description'] }}</p>
          @endif
          <a href="{{ lurl('contact') }}"
             aria-label="{{ __('pass.buy', ['name' => $p['name']]) }} ({{ number_format($p['price']) }} ฿)"
             class="block w-full text-center px-4 py-2 rounded-md bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600 transition">
            {{ __('pass.buy', ['name' => $p['name']]) }}
          </a>
        </div>
      </article>
    @endforeach
  </div>
</section>
@endsection
