@extends('layouts.app')
@section('title', 'Phuket Smart Bus Timetable — Airport ⇄ Patong ⇄ Rawai | PKSB')
@section('description', 'Phuket Smart Bus daily timetable. Hourly departures Airport → Rawai (06:30–17:30) and Rawai → Airport (06:00–17:00). 100฿ flat fare on this route.')

@push('jsonld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "WebPage",
      "@@id": "{{ url('/timetable') }}",
      "url": "{{ url('/timetable') }}",
      "name": "Phuket Smart Bus Timetable",
      "isPartOf": {"@@id":"{{ url('/') }}"},
      "breadcrumb": {"@@id":"{{ url('/timetable') }}#breadcrumb"}
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ url('/timetable') }}#breadcrumb",
      "itemListElement": [
        {"@@type":"ListItem","position":1,"name":"Home","item":"{{ url('/') }}"},
        {"@@type":"ListItem","position":2,"name":"Timetable"}
      ]
    },
    {
      "@@type": "ItemList",
      "name": "Phuket Smart Bus Routes",
      "itemListElement": [
        {
          "@@type": "ListItem",
          "position": 1,
          "item": {
            "@@type": "BusTrip",
            "name": "Route 1 — Airport to Rawai",
            "description": "Phuket International Airport → Patong Beach → Karon → Kata → Rawai. Hourly 06:30–17:30. 100฿ flat fare.",
            "provider": {"@@type":"Organization","name":"Phuket Smart Bus"},
            "departureBusStop": {"@@type":"BusStop","name":"Phuket International Airport"},
            "arrivalBusStop":   {"@@type":"BusStop","name":"Rawai"},
            "offers": {"@@type":"Offer","price":"100","priceCurrency":"THB"}
          }
        },
        {
          "@@type": "ListItem",
          "position": 2,
          "item": {
            "@@type": "BusTrip",
            "name": "Route 2 — Bus Terminal to Patong",
            "description": "Phuket Bus Terminal 2 → City Centre → Patong Beach. 50฿ flat fare.",
            "provider": {"@@type":"Organization","name":"Phuket Smart Bus"},
            "departureBusStop": {"@@type":"BusStop","name":"Phuket Bus Terminal 2"},
            "arrivalBusStop":   {"@@type":"BusStop","name":"Patong Beach"},
            "offers": {"@@type":"Offer","price":"50","priceCurrency":"THB"}
          }
        },
        {
          "@@type": "ListItem",
          "position": 3,
          "item": {
            "@@type": "BusTrip",
            "name": "Dragon Line — Free City Shuttle",
            "description": "Free city-centre circular route. Central Festival → Old Town → OTOP market and back.",
            "provider": {"@@type":"Organization","name":"Phuket Smart Bus"},
            "offers": {"@@type":"Offer","price":"0","priceCurrency":"THB"}
          }
        }
      ]
    }
  ]
}
</script>
@endpush

@php
  $timetables = app(\App\Services\TimetableRepository::class)->all();
@endphp

@section('content')

{{-- ── Hero ── --}}
<section class="relative pt-32 pb-12 bg-gradient-to-br from-teal-brand to-cyan-700 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold">{{ __('timetable.title') }}</h1>
    <p class="mt-3 text-white/90">{{ __('timetable.subtitle') }}</p>
  </div>
</section>

{{-- ── Route Tabs ── --}}
<div class="bg-white border-b border-gray-200 sticky top-16 z-30 shadow-sm">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <nav class="flex gap-1 overflow-x-auto scrollbar-none -mb-px" role="tablist" aria-label="{{ __('timetable.select_route') }}">

      {{-- Tab: Airport ⇄ Rawai --}}
      <button type="button" role="tab"
              id="tab-rawai" aria-controls="panel-rawai" aria-selected="true"
              onclick="switchTab('rawai')"
              class="route-tab active-tab flex-shrink-0 flex items-center gap-2 px-4 py-4 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
        {{ __('tracking.route.rawai') }}
        <span class="ml-1 text-[11px] font-normal opacity-70">100฿</span>
      </button>

      {{-- Tab: Bus Terminal ⇄ Patong --}}
      <button type="button" role="tab"
              id="tab-patong" aria-controls="panel-patong" aria-selected="false"
              onclick="switchTab('patong')"
              class="route-tab flex-shrink-0 flex items-center gap-2 px-4 py-4 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
        {{ __('tracking.route.patong') }}
        <span class="ml-1 text-[11px] font-normal opacity-70">50฿</span>
      </button>

      {{-- Tab: Dragon Line --}}
      <button type="button" role="tab"
              id="tab-dragon" aria-controls="panel-dragon" aria-selected="false"
              onclick="switchTab('dragon')"
              class="route-tab flex-shrink-0 flex items-center gap-2 px-4 py-4 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
        {{ __('tracking.route.dragon') }}
        <span class="ml-1 text-[11px] font-normal opacity-70">Free</span>
      </button>

    </nav>
  </div>
</div>

{{-- ════════════════════════════════════════════
     PANEL 1 — Airport ⇄ Rawai
════════════════════════════════════════════ --}}
<div id="panel-rawai" role="tabpanel" aria-labelledby="tab-rawai" class="route-panel">

  {{-- Route stops — always visible --}}
  <div class="bg-white border-b border-gray-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-2">
      {{-- Direction 1: Airport → Rawai --}}
      <div class="flex flex-wrap items-center gap-1.5 text-sm">
        <span class="font-semibold text-[#003087] text-xs uppercase tracking-wide w-20 flex-shrink-0">→ South</span>
        @foreach(['Airport','Patong','Karon','Kata','Rawai'] as $stop)
          <span class="px-2.5 py-1 rounded-full bg-blue-50 text-[#003087] text-xs font-medium ring-1 ring-blue-200">{{ $stop }}</span>
          @if(!$loop->last)<span class="text-gray-300 text-xs">→</span>@endif
        @endforeach
      </div>
      {{-- Direction 2: Rawai → Airport --}}
      <div class="flex flex-wrap items-center gap-1.5 text-sm">
        <span class="font-semibold text-[#003087] text-xs uppercase tracking-wide w-20 flex-shrink-0">→ North</span>
        @foreach(['Rawai','Kata','Karon','Patong','Airport'] as $stop)
          <span class="px-2.5 py-1 rounded-full bg-blue-50 text-[#003087] text-xs font-medium ring-1 ring-blue-200">{{ $stop }}</span>
          @if(!$loop->last)<span class="text-gray-300 text-xs">→</span>@endif
        @endforeach
      </div>
    </div>
  </div>

  {{-- Uploaded timetable images --}}
  @if(!empty($timetables['rawai']['images']))
  <section class="py-10 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-navy-brand">{{ __('timetable.uploaded_images') }}</h2>
        <span class="text-xs text-gray-400">{{ count($timetables['rawai']['images']) }} {{ count($timetables['rawai']['images']) === 1 ? 'image' : 'images' }}</span>
      </div>
      @include('partials.timetable-gallery', ['images' => $timetables['rawai']['images'], 'routeLabel' => __('tracking.route.rawai')])
    </div>
  </section>
  @endif

  {{-- Hardcoded time grid — only shown when no image has been uploaded --}}
  @if(empty($timetables['rawai']['images']))
  <section class="py-10 sm:py-14">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

      {{-- Route badge --}}
      <div class="flex flex-wrap items-center gap-3 mb-8">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-[#003087] text-sm font-semibold ring-1 ring-blue-200">
          <span class="w-5 h-5 rounded-full bg-[#003087] text-white text-[10px] font-bold flex items-center justify-center">1</span>
          Airport → Patong → Karon → Kata → Rawai
        </span>
        <span class="text-sm text-gray-500 font-medium">100 ฿ flat fare</span>
      </div>

      <div class="grid sm:grid-cols-2 gap-6">
        <article class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-6">
          <h2 class="text-lg font-bold text-navy-brand mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-teal-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            {{ __('timetable.airport_to_rawai') }}
          </h2>
          <ul class="grid grid-cols-3 gap-2 text-sm">
            @foreach (['06:30','07:30','08:30','09:30','10:30','11:30','12:30','13:30','14:30','15:30','16:30','17:30'] as $t)
              <li class="text-center py-2 rounded-md bg-gray-50 font-medium"><time>{{ $t }}</time></li>
            @endforeach
          </ul>
        </article>

        <article class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-6">
          <h2 class="text-lg font-bold text-navy-brand mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-teal-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            {{ __('timetable.rawai_to_airport') }}
          </h2>
          <ul class="grid grid-cols-3 gap-2 text-sm">
            @foreach (['06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'] as $t)
              <li class="text-center py-2 rounded-md bg-gray-50 font-medium"><time>{{ $t }}</time></li>
            @endforeach
          </ul>
        </article>
      </div>

      <aside class="mt-8 bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-sm text-gray-700">
        <strong>{{ __('timetable.note') }}</strong> {{ __('timetable.note_text') }}
        <a href="{{ lurl('tracking') }}" class="text-teal-700 underline hover:text-teal-900 ml-1">{{ __('home.cta.tracking') }}</a>.
      </aside>
    </div>
  </section>
  @endif
</div>

{{-- ════════════════════════════════════════════
     PANEL 2 — Bus Terminal ⇄ Patong
════════════════════════════════════════════ --}}
<div id="panel-patong" role="tabpanel" aria-labelledby="tab-patong" class="route-panel hidden">

  {{-- Route stops — always visible --}}
  <div class="bg-white border-b border-gray-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-2">
      {{-- Direction 1: Terminal → Patong --}}
      <div class="flex flex-wrap items-center gap-1.5 text-sm">
        <span class="font-semibold text-teal-700 text-xs uppercase tracking-wide w-20 flex-shrink-0">→ Patong</span>
        @foreach(['Bus Terminal 2','Phuket Town','Patong Beach'] as $stop)
          <span class="px-2.5 py-1 rounded-full bg-teal-50 text-teal-700 text-xs font-medium ring-1 ring-teal-200">{{ $stop }}</span>
          @if(!$loop->last)<span class="text-gray-300 text-xs">→</span>@endif
        @endforeach
      </div>
      {{-- Direction 2: Patong → Terminal --}}
      <div class="flex flex-wrap items-center gap-1.5 text-sm">
        <span class="font-semibold text-teal-700 text-xs uppercase tracking-wide w-20 flex-shrink-0">→ Terminal</span>
        @foreach(['Patong Beach','Phuket Town','Bus Terminal 2'] as $stop)
          <span class="px-2.5 py-1 rounded-full bg-teal-50 text-teal-700 text-xs font-medium ring-1 ring-teal-200">{{ $stop }}</span>
          @if(!$loop->last)<span class="text-gray-300 text-xs">→</span>@endif
        @endforeach
      </div>
    </div>
  </div>

  {{-- Uploaded timetable images --}}
  @if(!empty($timetables['patong']['images']))
  <section class="py-10 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-navy-brand">{{ __('timetable.uploaded_images') }}</h2>
        <span class="text-xs text-gray-400">{{ count($timetables['patong']['images']) }} {{ count($timetables['patong']['images']) === 1 ? 'image' : 'images' }}</span>
      </div>
      @include('partials.timetable-gallery', ['images' => $timetables['patong']['images'], 'routeLabel' => __('tracking.route.patong')])
    </div>
  </section>
  @endif

  <section class="py-10 sm:py-14">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

      {{-- Route badge --}}
      <div class="flex flex-wrap items-center gap-3 mb-8">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-50 text-teal-700 text-sm font-semibold ring-1 ring-teal-200">
          <span class="w-5 h-5 rounded-full bg-teal-brand text-white text-[10px] font-bold flex items-center justify-center">2</span>
          Bus Terminal 2 → Patong Beach
        </span>
        <span class="text-sm text-gray-500 font-medium">50 ฿ flat fare</span>
      </div>

      @if(empty($timetables['patong']['images']))
      {{-- No uploaded images — show placeholder notice --}}
      <div class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-10 text-center">
        <div class="w-14 h-14 rounded-full bg-teal-50 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-teal-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-gray-600 text-sm max-w-sm mx-auto">{{ __('timetable.no_schedule') }}</p>
        <a href="{{ lurl('tracking') }}"
           class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600 transition">
          {{ __('home.cta.tracking') }} →
        </a>
      </div>
      @endif

      <aside class="mt-8 bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-sm text-gray-700">
        <strong>{{ __('timetable.note') }}</strong> {{ __('timetable.note_text') }}
        <a href="{{ lurl('tracking') }}" class="text-teal-700 underline hover:text-teal-900 ml-1">{{ __('home.cta.tracking') }}</a>.
      </aside>
    </div>
  </section>
</div>

{{-- ════════════════════════════════════════════
     PANEL 3 — Dragon Line
════════════════════════════════════════════ --}}
<div id="panel-dragon" role="tabpanel" aria-labelledby="tab-dragon" class="route-panel hidden">

  {{-- Uploaded timetable images --}}
  @if(!empty($timetables['dragon']['images']))
  <section class="py-10 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-navy-brand">{{ __('timetable.uploaded_images') }}</h2>
        <span class="text-xs text-gray-400">{{ count($timetables['dragon']['images']) }} {{ count($timetables['dragon']['images']) === 1 ? 'image' : 'images' }}</span>
      </div>
      @include('partials.timetable-gallery', ['images' => $timetables['dragon']['images'], 'routeLabel' => __('tracking.route.dragon')])
    </div>
  </section>
  @endif

  <section class="py-10 sm:py-14">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

      {{-- Route badge --}}
      <div class="flex flex-wrap items-center gap-3 mb-8">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-50 text-green-700 text-sm font-semibold ring-1 ring-green-200">
          Dragon Line — City Shuttle
        </span>
        <span class="text-sm text-green-600 font-semibold">FREE</span>
      </div>

      <div class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-8">
        <p class="text-gray-600 text-sm leading-relaxed mb-6">{{ __('timetable.dragon.desc') }}</p>

        @if(empty($timetables['dragon']['images']))
        <div class="text-center py-6 border-t border-gray-100">
          <p class="text-gray-500 text-sm mb-4">{{ __('timetable.no_schedule') }}</p>
          <a href="{{ lurl('tracking') }}"
             class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
            {{ __('home.cta.tracking') }} →
          </a>
        </div>
        @endif
      </div>

      <aside class="mt-8 bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-sm text-gray-700">
        <strong>{{ __('timetable.note') }}</strong> {{ __('timetable.note_text') }}
        <a href="{{ lurl('tracking') }}" class="text-teal-700 underline hover:text-teal-900 ml-1">{{ __('home.cta.tracking') }}</a>.
      </aside>
    </div>
  </section>
</div>

{{-- ── Tab JS & Styles ── --}}
<style>
  .route-tab {
    color: #6b7280;
    border-color: transparent;
  }
  .route-tab:hover {
    color: #01aaa8;
    border-color: #01aaa8;
  }
  .route-tab.active-tab {
    color: #01aaa8;
    border-color: #01aaa8;
  }
</style>

<script>
  const BASE = '{{ url("/timetable") }}';
  const VALID = ['rawai','patong','dragon'];

  function switchTab(key, pushState) {
    // Hide all panels
    document.querySelectorAll('.route-panel').forEach(p => p.classList.add('hidden'));
    // Deactivate all tabs
    document.querySelectorAll('.route-tab').forEach(t => {
      t.classList.remove('active-tab');
      t.setAttribute('aria-selected', 'false');
    });
    // Show selected panel
    document.getElementById('panel-' + key).classList.remove('hidden');
    // Activate selected tab
    const tab = document.getElementById('tab-' + key);
    tab.classList.add('active-tab');
    tab.setAttribute('aria-selected', 'true');
    // Update URL without reloading
    if (pushState !== false) {
      const url = key === 'rawai' ? BASE : BASE + '/' + key;
      history.pushState({route: key}, '', url);
    }
  }

  // Read active route from URL path: /timetable, /timetable/patong, /timetable/dragon
  (function () {
    const parts = window.location.pathname.split('/').filter(Boolean);
    const last  = parts[parts.length - 1];
    const key   = VALID.includes(last) ? last : 'rawai';
    if (key !== 'rawai') switchTab(key, false);
  })();

  // Handle browser back/forward
  window.addEventListener('popstate', function (e) {
    const key = (e.state && e.state.route) || 'rawai';
    switchTab(key, false);
  });
</script>

@endsection
