@extends('layouts.app')
@section('title', 'Live GPS Tracking — Phuket Smart Bus | PKSB')
@section('description', 'See exactly where every Phuket Smart Bus is in real time. Live GPS tracking for Airport → Rawai, Bus Terminal → Patong, and Dragon Line routes.')

@push('jsonld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "WebPage",
      "@@id": "{{ url('/tracking') }}",
      "url": "{{ url('/tracking') }}",
      "name": "Live GPS Tracking — Phuket Smart Bus",
      "isPartOf": {"@@id":"{{ url('/') }}"},
      "breadcrumb": {"@@id":"{{ url('/tracking') }}#breadcrumb"}
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ url('/tracking') }}#breadcrumb",
      "itemListElement": [
        {"@@type":"ListItem","position":1,"name":"Home","item":"{{ url('/') }}"},
        {"@@type":"ListItem","position":2,"name":"GPS Tracking"}
      ]
    }
  ]
}
</script>
@endpush

@section('content')
<section class="relative pt-32 pb-10 bg-gradient-to-br from-teal-brand to-cyan-700 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold">{{ __('tracking.title') }}</h1>
    <p class="mt-3 text-white/90">{{ __('tracking.subtitle') }}</p>
  </div>
</section>

<section class="py-8 sm:py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @php
      $routes = [
        'all'    => ['label' => __('tracking.route.all'),    'url' => 'https://smartbus.phuket.cloud/'],
        'rawai'  => ['label' => __('tracking.route.rawai'),  'url' => 'https://smartbus.phuket.cloud/rawai-airport'],
        'patong' => ['label' => __('tracking.route.patong'), 'url' => 'https://smartbus.phuket.cloud/patong-old-bus-station'],
        'dragon' => ['label' => __('tracking.route.dragon'), 'url' => 'https://smartbus.phuket.cloud/dragon-line'],
      ];
      $active = request('route', 'all');
      if (!isset($routes[$active])) $active = 'all';
    @endphp

    <nav class="flex flex-wrap gap-2 mb-4" aria-label="Select route">
      @foreach ($routes as $key => $r)
        <a href="?route={{ $key }}"
           @if($active === $key) aria-current="page" @endif
           class="px-4 py-2 rounded-full text-sm font-semibold transition
                  {{ $active === $key
                       ? 'bg-teal-brand text-white shadow'
                       : 'bg-white text-navy-brand ring-1 ring-gray-200 hover:bg-gray-50' }}">
          {{ $r['label'] }}
        </a>
      @endforeach
    </nav>

    <div class="rounded-2xl overflow-hidden shadow ring-1 ring-gray-200 bg-white">
      <iframe
        src="{{ $routes[$active]['url'] }}"
        title="Live GPS map — {{ $routes[$active]['label'] }}"
        class="w-full h-[70vh] min-h-[420px] sm:h-[600px] border-0"
        loading="lazy"
        sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
      <noscript>
        <p class="p-6 text-sm text-gray-700">
          The live map requires JavaScript. Open
          <a href="{{ $routes[$active]['url'] }}" class="text-teal-700 underline">smartbus.phuket.cloud</a>
          directly to view it.
        </p>
      </noscript>
    </div>

    <p class="mt-3 text-xs text-gray-500">
      {{ __('tracking.credit') }}
      <a href="https://smartbus.phuket.cloud/" target="_blank" rel="noopener" class="text-teal-brand hover:underline">smartbus.phuket.cloud</a>.
    </p>
  </div>
</section>
@endsection
