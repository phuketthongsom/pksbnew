@extends('layouts.app')
@section('title', 'About Phuket Smart Bus — Modern, Affordable Public Transport | PKSB')
@section('description', 'Phuket Smart Bus operates a modern fleet of EV and low-emission buses across three routes — Airport ⇄ Rawai (100฿), Bus Terminal ⇄ Patong (50฿) and the Dragon Line (free) — with contactless payment and live GPS tracking.')

@push('jsonld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "AboutPage",
      "@@id": "{{ url('/about') }}",
      "url": "{{ url('/about') }}",
      "name": "About Phuket Smart Bus",
      "description": "Phuket Smart Bus operates a modern fleet across three routes — Airport ⇄ Rawai, Bus Terminal ⇄ Patong, and the free Dragon Line — with contactless payment and live GPS tracking.",
      "isPartOf": {"@@id": "{{ url('/') }}"},
      "breadcrumb": {"@@id": "{{ url('/about') }}#breadcrumb"},
      "about": {
        "@@type": "Organization",
        "name": "Phuket City Development Co., Ltd.",
        "alternateName": "Phuket Smart Bus",
        "foundingLocation": {"@@type":"Place","name":"Phuket, Thailand"},
        "description": "Operator of the Phuket Smart Bus network — Phuket's modern, low-cost public bus service connecting the airport, beach towns and city centre."
      }
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ url('/about') }}#breadcrumb",
      "itemListElement": [
        {"@@type":"ListItem","position":1,"name":"Home","item":"{{ url('/') }}"},
        {"@@type":"ListItem","position":2,"name":"About Us"}
      ]
    }
  ]
}
</script>
@endpush

@section('content')
<section class="relative pt-32 pb-16 bg-gradient-to-br from-teal-brand to-cyan-700 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold">{{ __('about.title') }}</h1>
    <p class="mt-4 max-w-2xl text-white/90 text-base sm:text-lg">{{ __('about.subtitle') }}</p>
  </div>
</section>

<section class="py-12 sm:py-16">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-10 items-center">
    <div>
      <h2 class="text-2xl font-bold text-navy-brand mb-4">{{ __('about.mission') }}</h2>
      <p class="text-gray-700 leading-relaxed mb-4">Phuket Smart Bus operates a modern fleet of EV and low-emission buses connecting the airport, Patong, Karon, Kata, and Rawai — making island travel simple for residents and visitors alike.</p>
      <p class="text-gray-700 leading-relaxed">With Tap, Go &amp; Ride contactless payment, real-time GPS tracking, and friendly fares from <strong>FREE on the Dragon Line</strong> up to <strong>100฿ on the Airport route</strong>, we are reshaping public transport in Phuket.</p>
    </div>
    <picture>
      <source srcset="{{ asset('images/bus-mastercard.webp') }}" type="image/webp">
      <img src="{{ asset('images/bus-mastercard.jpg') }}" alt="Phuket Smart Bus EV at the charging station"
           class="aspect-video rounded-2xl shadow-lg object-cover w-full" loading="lazy" width="1080" height="1080">
    </picture>
  </div>
</section>

<section class="bg-gray-50 py-12 sm:py-16" aria-label="Key facts">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-3 gap-6">
    @foreach([
      ['3 routes', 'From Free to 100฿'],
      ['10+', 'Stops island-wide'],
      ['EV', 'Eco-friendly fleet']
    ] as [$big, $small])
      <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
        <div class="text-4xl font-extrabold text-teal-brand">{{ $big }}</div>
        <div class="mt-2 text-gray-600">{{ $small }}</div>
      </div>
    @endforeach
  </div>
</section>
@endsection
