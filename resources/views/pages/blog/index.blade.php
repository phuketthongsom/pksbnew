@extends('layouts.app')
@section('title', 'Phuket Destinations & Travel Guides | Phuket Smart Bus Blog')
@section('description', 'Where to go in Phuket and how to get there by bus. Curated guides to Old Town, Patong, Promthep Cape, Big Buddha and more — all reachable on the Phuket Smart Bus network.')

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
      "description": "Where to go in Phuket and how to get there by bus. Curated guides to Old Town, Patong, Promthep Cape, Big Buddha and more — all reachable on the Phuket Smart Bus network.",
      "inLanguage": "{{ app()->getLocale() }}",
      "isPartOf": {"@@id": "{{ url('/') }}"},
      "breadcrumb": {"@@id": "{{ url('/blog') }}#breadcrumb"},
      "publisher": {
        "@@type": "Organization",
        "name": "Phuket Smart Bus",
        "logo": {"@@type":"ImageObject","url":"{{ asset('images/logo.png') }}"}
      }
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ url('/blog') }}#breadcrumb",
      "itemListElement": [
        {"@@type":"ListItem","position":1,"name":"Home","item":"{{ url('/') }}"},
        {"@@type":"ListItem","position":2,"name":"Destinations"}
      ]
    }
  ]
}
</script>
@endpush

@section('content')
<section class="relative pt-32 pb-12 bg-gradient-to-br from-teal-brand to-cyan-700 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="text-white/80 text-sm font-semibold uppercase tracking-wider mb-2">{{ __('blog.hero.kicker') }}</p>
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold">{{ __('blog.hero.title') }}</h1>
    <p class="mt-3 max-w-2xl text-white/90">{{ __('blog.hero.subtitle') }}</p>
  </div>
</section>

<section class="py-10 sm:py-14">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    @if($posts->isEmpty())
      <p class="text-gray-600">{{ __('blog.empty') }}</p>
    @else
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($posts as $p)
          @php
            $coverBase = pathinfo($p['cover'], PATHINFO_DIRNAME).'/'.pathinfo($p['cover'], PATHINFO_FILENAME);
            $hasWebp = file_exists(public_path($coverBase.'.webp'));
          @endphp
          <article class="group bg-white rounded-2xl overflow-hidden shadow ring-1 ring-gray-100 hover:shadow-lg transition flex flex-col">
            <a href="{{ lurl('blog.show', $p['slug']) }}" class="block aspect-[16/10] overflow-hidden bg-gray-100">
              <picture>
                @if($hasWebp)<source srcset="{{ asset($coverBase.'.webp') }}" type="image/webp">@endif
                <img src="{{ asset($p['cover']) }}" alt="{{ $p['title'] }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                     loading="lazy" width="640" height="400">
              </picture>
            </a>
            <div class="p-5 flex-1 flex flex-col">
              <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                <span class="px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 font-semibold">{{ $p['area'] }}</span>
                <time datetime="{{ $p['published_at'] }}">{{ \Carbon\Carbon::parse($p['published_at'])->format('M j, Y') }}</time>
                <span aria-hidden="true">·</span>
                <span>{{ __('blog.min_read', ['n' => $p['reading_minutes']]) }}</span>
              </div>
              <h2 class="text-lg font-bold text-navy-brand mb-2 leading-snug">
                <a href="{{ lurl('blog.show', $p['slug']) }}" class="hover:text-teal-brand transition">{{ $p['title'] }}</a>
              </h2>
              <p class="text-sm text-gray-600 leading-relaxed flex-1">{{ $p['excerpt'] }}</p>
              <a href="{{ lurl('blog.show', $p['slug']) }}"
                 class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-teal-brand hover:text-teal-700"
                 aria-label="{{ $p['title'] }}">
                {{ __('blog.read_more') }}
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 5l7 7-7 7"/></svg>
              </a>
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </div>
</section>
@endsection
