@extends('layouts.app')
@section('title', 'Page Not Found — Phuket Smart Bus | PKSB')
@section('description', 'The page you were looking for could not be found. Explore Phuket Smart Bus routes, timetables, day passes and destinations.')

@push('jsonld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    {"@@type":"ListItem","position":1,"name":"Home","item":"{{ url('/') }}"},
    {"@@type":"ListItem","position":2,"name":"Page Not Found"}
  ]
}
</script>
@endpush

@section('content')
<section class="relative pt-32 pb-12 bg-gradient-to-br from-teal-brand to-cyan-700 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="text-6xl font-extrabold opacity-30 leading-none">404</p>
    <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold">Page Not Found</h1>
    <p class="mt-3 text-white/90">Sorry, we couldn't find what you were looking for.</p>
  </div>
</section>

<section class="py-16">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <p class="text-gray-500 mb-10">The page may have moved or the link may be incorrect. Here are some helpful starting points:</p>

    <div class="grid sm:grid-cols-2 gap-4 text-left mb-12">
      <a href="{{ lurl('home') }}"
         class="flex items-start gap-4 p-5 rounded-2xl ring-1 ring-gray-100 bg-white shadow-sm hover:shadow-md transition group">
        <span class="mt-0.5 flex-shrink-0 w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-brand group-hover:bg-teal-brand group-hover:text-white transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>
        </span>
        <div>
          <div class="font-semibold text-navy-brand">Home</div>
          <div class="text-sm text-gray-500">Back to the main page</div>
        </div>
      </a>

      <a href="{{ lurl('timetable') }}"
         class="flex items-start gap-4 p-5 rounded-2xl ring-1 ring-gray-100 bg-white shadow-sm hover:shadow-md transition group">
        <span class="mt-0.5 flex-shrink-0 w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-brand group-hover:bg-teal-brand group-hover:text-white transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
        <div>
          <div class="font-semibold text-navy-brand">Timetable</div>
          <div class="text-sm text-gray-500">Bus schedules and routes</div>
        </div>
      </a>

      <a href="{{ lurl('tracking') }}"
         class="flex items-start gap-4 p-5 rounded-2xl ring-1 ring-gray-100 bg-white shadow-sm hover:shadow-md transition group">
        <span class="mt-0.5 flex-shrink-0 w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-brand group-hover:bg-teal-brand group-hover:text-white transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </span>
        <div>
          <div class="font-semibold text-navy-brand">Live GPS Tracking</div>
          <div class="text-sm text-gray-500">See buses in real time</div>
        </div>
      </a>

      <a href="{{ lurl('pass') }}"
         class="flex items-start gap-4 p-5 rounded-2xl ring-1 ring-gray-100 bg-white shadow-sm hover:shadow-md transition group">
        <span class="mt-0.5 flex-shrink-0 w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-brand group-hover:bg-teal-brand group-hover:text-white transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </span>
        <div>
          <div class="font-semibold text-navy-brand">Day Passes</div>
          <div class="text-sm text-gray-500">Unlimited rides from 300฿</div>
        </div>
      </a>

      <a href="{{ lurl('blog') }}"
         class="flex items-start gap-4 p-5 rounded-2xl ring-1 ring-gray-100 bg-white shadow-sm hover:shadow-md transition group">
        <span class="mt-0.5 flex-shrink-0 w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-brand group-hover:bg-teal-brand group-hover:text-white transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
        </span>
        <div>
          <div class="font-semibold text-navy-brand">Destinations</div>
          <div class="text-sm text-gray-500">Travel guides for Phuket</div>
        </div>
      </a>

      <a href="{{ lurl('contact') }}"
         class="flex items-start gap-4 p-5 rounded-2xl ring-1 ring-gray-100 bg-white shadow-sm hover:shadow-md transition group">
        <span class="mt-0.5 flex-shrink-0 w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-brand group-hover:bg-teal-brand group-hover:text-white transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </span>
        <div>
          <div class="font-semibold text-navy-brand">Contact Us</div>
          <div class="text-sm text-gray-500">Get help from our team</div>
        </div>
      </a>
    </div>

    <a href="{{ lurl('home') }}"
       class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-teal-brand text-white font-semibold hover:bg-teal-600 transition">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Back to Home
    </a>
  </div>
</section>
@endsection
