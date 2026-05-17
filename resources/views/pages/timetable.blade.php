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

@section('content')
<section class="relative pt-32 pb-12 bg-gradient-to-br from-teal-brand to-cyan-700 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold">{{ __('timetable.title') }}</h1>
    <p class="mt-3 text-white/90">{{ __('timetable.subtitle') }}</p>
  </div>
</section>

@php
  $timetables = app(\App\Services\TimetableRepository::class)->all();
  $hasUploaded = collect($timetables)->contains(fn ($r) => !empty($r['images']));
@endphp

@if($hasUploaded)
<section class="py-10 sm:py-14 bg-gray-50">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-2xl sm:text-3xl font-bold text-navy-brand mb-6">{{ __('payment.col.route') }}</h2>
    <div class="space-y-8">
      @foreach($timetables as $r)
        @if(!empty($r['images']))
          <article class="bg-white rounded-2xl shadow ring-1 ring-gray-100 overflow-hidden">
            <header class="px-5 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
              <h3 class="font-bold text-navy-brand">{{ $r['label'] }}</h3>
              <span class="text-xs px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 font-semibold">{{ $r['flat_fare'] }}</span>
            </header>

            <div class="grid {{ count($r['images']) === 1 ? 'grid-cols-1' : 'sm:grid-cols-2' }} divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
              @foreach($r['images'] as $img)
                @php $caption = \App\Services\TimetableRepository::localizedCaption($img['caption'] ?? null); @endphp
                <figure class="flex flex-col">
                  <a href="{{ asset($img['path']) }}" target="_blank" class="block bg-gray-50">
                    <img src="{{ asset($img['path']) }}?v={{ md5($img['uploaded_at'] ?? '') }}"
                         alt="{{ $caption ?: $r['label'] }}"
                         class="w-full h-auto block"
                         loading="lazy">
                  </a>
                  @if($caption !== '')
                    <figcaption class="px-5 py-3 text-xs text-gray-600 border-t border-gray-100 bg-gray-50">{{ $caption }}</figcaption>
                  @endif
                </figure>
              @endforeach
            </div>
          </article>
        @endif
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="py-10 sm:py-14">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid sm:grid-cols-2 gap-6">
      <article class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-6">
        <h2 class="text-lg font-bold text-navy-brand mb-4">{{ __('timetable.airport_to_rawai') }}</h2>
        <ul class="grid grid-cols-3 gap-2 text-sm">
          @foreach (['06:30','07:30','08:30','09:30','10:30','11:30','12:30','13:30','14:30','15:30','16:30','17:30'] as $t)
            <li class="text-center py-2 rounded-md bg-gray-50 font-medium"><time>{{ $t }}</time></li>
          @endforeach
        </ul>
      </article>
      <article class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-6">
        <h2 class="text-lg font-bold text-navy-brand mb-4">{{ __('timetable.rawai_to_airport') }}</h2>
        <ul class="grid grid-cols-3 gap-2 text-sm">
          @foreach (['06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'] as $t)
            <li class="text-center py-2 rounded-md bg-gray-50 font-medium"><time>{{ $t }}</time></li>
          @endforeach
        </ul>
      </article>
    </div>

    <aside class="mt-10 bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-sm text-gray-700">
      <strong>{{ __('timetable.note') }}</strong> {{ __('timetable.note_text') }} <a href="{{ lurl('tracking') }}" class="text-teal-700 underline hover:text-teal-900">{{ __('home.cta.tracking') }}</a>.
    </aside>
  </div>
</section>
@endsection
