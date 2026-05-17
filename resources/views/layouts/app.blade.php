@php
  $currentLocale = app()->getLocale();
  $locales = [
    'en' => ['flag' => '🇬🇧', 'label' => 'English'],
    'th' => ['flag' => '🇹🇭', 'label' => 'ไทย'],
    'zh' => ['flag' => '🇨🇳', 'label' => '中文'],
    'ru' => ['flag' => '🇷🇺', 'label' => 'Русский'],
  ];
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

{{-- Hreflang for SEO across locales — points at the URL-prefixed variant --}}
@php
  // Compute the canonical (locale-less) path so we can rebuild per-locale URLs.
  // request()->path() returns paths WITHOUT a leading slash (e.g. "th/blog").
  $rawPath = request()->path();
  $cleanPath = preg_replace('#^(?:th|zh|ru)(?:/|$)#', '', $rawPath);
  $path = ($cleanPath === '' || $cleanPath === '/') ? '' : '/' . ltrim($cleanPath, '/');
  $base = url('/');
@endphp
@foreach($locales as $code => $info)
  @php $prefix = $code === 'en' ? '' : '/'.$code; @endphp
  <link rel="alternate" hreflang="{{ $code }}" href="{{ $base.$prefix.$path }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $base.$path }}">

@php
  $defaultTitle = 'PKSB - Phuket Smart Bus | Airport · Patong · Rawai · Bus Terminal · Dragon Line';
  $defaultDesc  = 'Phuket Smart Bus connects the airport, Patong, Karon, Kata and Rawai. Three routes from FREE to 100฿. Real-time GPS tracking, daily timetable and contactless Tap, Go & Ride payment.';
  $title       = trim($__env->yieldContent('title')) ?: $defaultTitle;
  $description = trim($__env->yieldContent('description')) ?: $defaultDesc;
  $ogImage     = asset('images/bus-mastercard.jpg');
  $canonical   = url()->current();
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph --}}
@php
  $ogLocaleMap = ['en'=>'en_US','th'=>'th_TH','zh'=>'zh_CN','ru'=>'ru_RU'];
  $ogLocale    = $ogLocaleMap[$currentLocale] ?? 'en_US';
@endphp
<meta property="og:type"        content="{{ trim($__env->yieldContent('og_type')) ?: 'website' }}">
<meta property="og:site_name"   content="Phuket Smart Bus">
<meta property="og:locale"      content="{{ $ogLocale }}">
@foreach($locales as $code => $info)
  @if($code !== $currentLocale)
    <meta property="og:locale:alternate" content="{{ $ogLocaleMap[$code] ?? $code }}">
  @endif
@endforeach
<meta property="og:title"       content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url"         content="{{ $canonical }}">
<meta property="og:image"       content="{{ trim($__env->yieldContent('og_image')) ?: $ogImage }}">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt"    content="{{ $title }}">
@yield('og_extra')
{{-- Twitter Card --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:site"        content="@PhuketSmartBus">
<meta name="twitter:title"       content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image"       content="{{ trim($__env->yieldContent('og_image')) ?: $ogImage }}">
<meta name="theme-color"         content="#01aaa8">

<link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

{{-- Sitemap hint for crawlers --}}
<link rel="sitemap" type="application/xml" href="{{ url('/sitemap.xml') }}">

{{-- Performance: preload hero (Inter is now self-hosted via Vite, no CDN preconnect) --}}
@stack('preload')

@vite(['resources/css/app.css'])

{{-- Page-specific JSON-LD goes into this stack --}}
@stack('jsonld')

{{-- Site-wide: Organization + LocalBusiness --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": ["LocalBusiness","TransitAgency"],
  "name": "Phuket Smart Bus",
  "alternateName": "PKSB",
  "description": "Modern low-cost bus service connecting Phuket International Airport, Patong Beach, Karon, Kata and Rawai. Three routes from free to 100฿ with contactless payment and live GPS tracking.",
  "url": "{{ url('/') }}",
  "logo": {
    "@@type": "ImageObject",
    "url": "{{ asset('images/logo.png') }}",
    "width": 240,
    "height": 120
  },
  "image": "{{ asset('images/bus-mastercard.jpg') }}",
  "telephone": "+66863061257",
  "email": "info@phuketsmartbus.com",
  "priceRange": "฿–฿฿",
  "currenciesAccepted": "THB",
  "paymentAccepted": "Cash, Credit Card, Contactless",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "9/9 Moo 5, Wichit",
    "addressLocality": "Mueang Phuket",
    "addressRegion": "Phuket",
    "postalCode": "83000",
    "addressCountry": "TH"
  },
  "geo": {
    "@@type": "GeoCoordinates",
    "latitude": 7.8804,
    "longitude": 98.3923
  },
  "hasMap": "https://maps.google.com/?q=Phuket+Smart+Bus,+Wichit,+Mueang+Phuket",
  "openingHoursSpecification": [
    {
      "@@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
      "opens": "06:00",
      "closes": "18:00"
    }
  ],
  "areaServed": [
    {"@@type":"City","name":"Phuket","containedInPlace":{"@@type":"Country","name":"Thailand"}},
    {"@@type":"Place","name":"Phuket International Airport"},
    {"@@type":"Place","name":"Patong Beach"},
    {"@@type":"Place","name":"Karon Beach"},
    {"@@type":"Place","name":"Kata Beach"},
    {"@@type":"Place","name":"Rawai"}
  ],
  "knowsAbout": ["Bus transportation","Airport transfers","Tourist transport","Contactless payment"],
  "sameAs": [
    "https://facebook.com/PhuketSmartBus",
    "https://line.me/R/ti/p/@pksb",
    "https://wa.me/66863061257"
  ]
}
</script>
</head>
<body class="bg-white text-gray-800 antialiased font-sans">

<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 z-50 bg-white text-navy-brand px-4 py-2 rounded-md shadow-lg font-semibold">{{ __('skip.to_content') }}</a>

<!-- Header -->
<header class="absolute top-0 left-0 right-0 z-30">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between py-4">
    <a href="{{ lurl('home') }}" class="flex items-center gap-2 text-white" aria-label="Phuket Smart Bus — Home">
      <picture>
        <source srcset="{{ asset('images/logo.webp') }}" type="image/webp">
        <img src="{{ asset('images/logo.png') }}" alt="PKSB - Phuket Smart Bus" width="240" height="120" class="h-16 sm:h-20 w-auto drop-shadow-lg">
      </picture>
    </a>

    <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-white text-sm font-medium" aria-label="Primary">
      <a href="{{ lurl('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">{{ __('nav.home') }}</a>
      <a href="{{ lurl('blog') }}" class="nav-link {{ request()->routeIs('blog*') ? 'active' : '' }}">{{ __('nav.destinations') }}</a>
      <a href="{{ lurl('tracking') }}" class="nav-link {{ request()->routeIs('tracking') ? 'active' : '' }}">{{ __('nav.tracking') }}</a>
      <a href="{{ lurl('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">{{ __('nav.about') }}</a>
      <a href="{{ lurl('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">{{ __('nav.contact') }}</a>
    </nav>

    <div class="flex items-center gap-2 sm:gap-3">
      {{-- Language picker --}}
      <div class="relative" x-data id="langSwitcher">
        <button type="button" id="langBtn" aria-haspopup="true" aria-expanded="false"
                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md bg-white/15 backdrop-blur text-white text-sm font-semibold border border-white/30 hover:bg-white/25">
          <span class="text-base leading-none">{{ $locales[$currentLocale]['flag'] }}</span>
          <span class="hidden sm:inline">{{ strtoupper($currentLocale) }}</span>
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
        </button>
        <ul id="langMenu" role="menu" class="hidden absolute right-0 mt-1 w-44 rounded-md bg-white text-navy-brand shadow-lg ring-1 ring-black/10 py-1 z-50 text-sm">
          @foreach($locales as $code => $info)
            @php $prefix = $code === 'en' ? '' : '/'.$code; @endphp
            <li role="none">
              <a role="menuitem" href="{{ $base.$prefix.$path }}" hreflang="{{ $code }}"
                 class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 {{ $code === $currentLocale ? 'font-semibold bg-gray-50' : '' }}">
                <span class="text-base">{{ $info['flag'] }}</span>
                <span>{{ $info['label'] }}</span>
                @if($code === $currentLocale)<span class="ml-auto text-teal-brand" aria-hidden="true">✓</span>@endif
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      <a href="{{ lurl('pass') }}" class="hidden sm:inline-flex items-center justify-center w-10 h-10 rounded-full bg-teal-brand text-white shadow-md hover:bg-teal-600 transition" aria-label="{{ __('nav.pass') }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      </a>
      <button id="mobileMenuBtn" type="button" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle menu"
        class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-md bg-white/20 backdrop-blur text-white">
        <svg id="iconOpen" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg id="iconClose" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6"/></svg>
      </button>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="mobileMenu" class="hidden md:hidden bg-navy-brand text-white px-4 py-4 space-y-3">
    <a href="{{ lurl('home') }}" class="block py-2 border-b border-white/10">{{ __('nav.home') }}</a>
    <a href="{{ lurl('blog') }}" class="block py-2 border-b border-white/10">{{ __('nav.destinations') }}</a>
    <a href="{{ lurl('about') }}" class="block py-2 border-b border-white/10">{{ __('nav.about') }}</a>
    <a href="{{ lurl('contact') }}" class="block py-2 border-b border-white/10">{{ __('nav.contact') }}</a>
    <a href="{{ lurl('timetable') }}" class="block py-2 border-b border-white/10">{{ __('nav.timetable') }}</a>
    <a href="{{ lurl('tracking') }}" class="block py-2 border-b border-white/10">{{ __('nav.tracking') }}</a>
    <a href="{{ lurl('payment') }}" class="block py-2 border-b border-white/10">{{ __('nav.payment') }}</a>
    <a href="{{ lurl('pass') }}" class="block py-2">{{ __('nav.pass') }}</a>
  </div>
</header>

<main id="main">
  @yield('content')
</main>

<!-- Phuket City Illustration Band -->
<div class="relative w-full overflow-hidden" style="height:220px;" aria-hidden="true">
  <style>
    @keyframes pksb-bus    { from{transform:translateX(-260px)} to{transform:translateX(1320px)} }
    @keyframes pksb-cloud1 { from{transform:translateX(-200px)} to{transform:translateX(1400px)} }
    @keyframes pksb-cloud2 { from{transform:translateX(-500px)} to{transform:translateX(1400px)} }
    @keyframes pksb-cloud3 { from{transform:translateX(800px)}  to{transform:translateX(1400px)} }
    @keyframes pksb-bird   { 0%{transform:translateX(-80px) translateY(0)} 50%{transform:translateX(600px) translateY(-18px)} 100%{transform:translateX(1300px) translateY(5px)} }
    @keyframes pksb-wheel  { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
    .pksb-bus    { animation: pksb-bus    16s linear infinite }
    .pksb-cloud1 { animation: pksb-cloud1 26s linear infinite }
    .pksb-cloud2 { animation: pksb-cloud2 38s linear infinite 6s }
    .pksb-cloud3 { animation: pksb-cloud3 20s linear infinite 14s }
    .pksb-bird   { animation: pksb-bird   18s ease-in-out infinite 3s }
    .pksb-wheel1 { transform-origin:30px 190px; animation: pksb-wheel 1.8s linear infinite }
    .pksb-wheel2 { transform-origin:148px 190px; animation: pksb-wheel 1.8s linear infinite }
  </style>

  <svg viewBox="0 0 1200 220" class="absolute inset-0 w-full h-full" preserveAspectRatio="xMidYMax meet" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="pSky" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%"   stop-color="#5bc8e8"/>
        <stop offset="60%"  stop-color="#a8dff0"/>
        <stop offset="100%" stop-color="#d6eeee"/>
      </linearGradient>
      <linearGradient id="pSea" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%"   stop-color="#2ab8c8"/>
        <stop offset="100%" stop-color="#01aaa8"/>
      </linearGradient>
      <linearGradient id="pRoad" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%"   stop-color="#52606d"/>
        <stop offset="100%" stop-color="#3a4450"/>
      </linearGradient>
      <linearGradient id="pBus" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%"   stop-color="#017b79"/>
        <stop offset="100%" stop-color="#01aaa8"/>
      </linearGradient>
      <linearGradient id="pGrass" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%"   stop-color="#6db56d"/>
        <stop offset="100%" stop-color="#4e9a5b"/>
      </linearGradient>
      <linearGradient id="pGround" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%"   stop-color="#e8d8a0"/>
        <stop offset="100%" stop-color="#d4c07a"/>
      </linearGradient>
      <linearGradient id="pHill1" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%"   stop-color="#6fa87a"/>
        <stop offset="100%" stop-color="#4a7c5a"/>
      </linearGradient>
      <linearGradient id="pHill2" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%"   stop-color="#567a60"/>
        <stop offset="100%" stop-color="#3d5e46"/>
      </linearGradient>
      <filter id="pShadow" x="-5%" y="-5%" width="115%" height="120%">
        <feDropShadow dx="3" dy="3" stdDeviation="3" flood-color="#00000022"/>
      </filter>
    </defs>

    <!-- ── SKY ── -->
    <rect width="1200" height="220" fill="url(#pSky)"/>

    <!-- Sun with glow rings -->
    <circle cx="1090" cy="42" r="38" fill="#fde68a" opacity="0.2"/>
    <circle cx="1090" cy="42" r="28" fill="#fde68a" opacity="0.35"/>
    <circle cx="1090" cy="42" r="20" fill="#fbbf24"/>
    <circle cx="1090" cy="42" r="14" fill="#fef08a"/>

    <!-- ── BIRDS (animated) ── -->
    <g class="pksb-bird" opacity="0.6">
      <path d="M0,30 Q5,26 10,30" stroke="#374151" stroke-width="1.5" fill="none" stroke-linecap="round"/>
      <path d="M12,30 Q17,26 22,30" stroke="#374151" stroke-width="1.5" fill="none" stroke-linecap="round"/>
    </g>
    <g class="pksb-bird" style="animation-delay:7s;animation-duration:22s" opacity="0.5">
      <path d="M50,18 Q55,14 60,18" stroke="#374151" stroke-width="1.2" fill="none"/>
      <path d="M62,18 Q67,14 72,18" stroke="#374151" stroke-width="1.2" fill="none"/>
    </g>

    <!-- ── CLOUDS ── -->
    <g class="pksb-cloud1">
      <ellipse cx="200" cy="30" rx="60" ry="20" fill="white" opacity="0.88"/>
      <ellipse cx="235" cy="22" rx="44" ry="26" fill="white" opacity="0.95"/>
      <ellipse cx="170" cy="35" rx="36" ry="17" fill="white" opacity="0.8"/>
      <ellipse cx="256" cy="32" rx="28" ry="16" fill="white" opacity="0.75"/>
    </g>
    <g class="pksb-cloud2">
      <ellipse cx="600" cy="20" rx="46" ry="16" fill="white" opacity="0.72"/>
      <ellipse cx="630" cy="13" rx="32" ry="20" fill="white" opacity="0.82"/>
      <ellipse cx="576" cy="24" rx="28" ry="14" fill="white" opacity="0.68"/>
    </g>
    <g class="pksb-cloud3">
      <ellipse cx="900" cy="26" rx="38" ry="14" fill="white" opacity="0.65"/>
      <ellipse cx="924" cy="19" rx="26" ry="17" fill="white" opacity="0.75"/>
    </g>

    <!-- ── BACKGROUND HILLS (far, faded) ── -->
    <path d="M680,138 Q740,80 800,88 Q840,76 880,95 Q910,108 940,138 Z" fill="#8ab898" opacity="0.35"/>
    <path d="M860,138 Q920,60 980,68 Q1020,50 1060,72 Q1090,88 1120,138 Z" fill="url(#pHill1)" opacity="0.6" filter="url(#pShadow)"/>
    <!-- Big Buddha hill (main) -->
    <path d="M960,138 Q1010,52 1060,60 Q1100,42 1140,65 Q1165,82 1180,138 Z" fill="url(#pHill2)" opacity="0.75"/>

    <!-- Big Buddha silhouette (more detailed) -->
    <g transform="translate(1052,58)" opacity="0.8">
      <!-- Pedestal base -->
      <rect x="-8" y="0"   width="16" height="12" rx="1" fill="#3a5e44"/>
      <!-- Body/robe -->
      <polygon points="0,-34 -7,-2 7,-2" fill="#3a5e44"/>
      <!-- Lap/base of seated figure -->
      <ellipse cx="0" cy="-4" rx="8" ry="4" fill="#3a5e44"/>
      <!-- Torso -->
      <rect x="-4" y="-28" width="8" height="16" rx="2" fill="#3a5e44"/>
      <!-- Head -->
      <circle cx="0" cy="-32" r="5.5" fill="#3a5e44"/>
      <!-- Ushnisha (topknot) -->
      <ellipse cx="0" cy="-37" rx="2.5" ry="3" fill="#3a5e44"/>
      <!-- Halo -->
      <circle cx="0" cy="-32" r="9" fill="none" stroke="#3a5e44" stroke-width="1.5" opacity="0.4"/>
    </g>

    <!-- Secondary hill right -->
    <path d="M1130,138 Q1158,96 1185,100 Q1200,104 1200,138 Z" fill="#4d7a5e" opacity="0.5"/>

    <!-- ── OCEAN (right side behind hills) ── -->
    <rect x="640" y="120" width="420" height="18" fill="url(#pSea)" opacity="0.5" rx="2"/>
    <path d="M640,124 Q680,120 720,124 Q760,128 800,124 Q840,120 880,124 Q920,128 960,124 Q1000,120 1040,124" stroke="white" stroke-width="1.8" fill="none" opacity="0.4"/>
    <path d="M660,130 Q700,126 740,130 Q780,134 820,130 Q860,126 900,130" stroke="white" stroke-width="1" fill="none" opacity="0.3"/>

    <!-- ── GROUND / LAND ── -->
    <rect y="138" width="1200" height="82" fill="url(#pGround)"/>

    <!-- Grass strip on top of ground -->
    <rect y="138" width="1200" height="8" fill="url(#pGrass)"/>

    <!-- ── BUILDINGS ── (all sitting on y=146 ground line) -->

    <!-- B1: Teal glass tower — far left bg -->
    <rect x="18"  y="56" width="42" height="90" rx="3" fill="#01aaa8" opacity="0.78" filter="url(#pShadow)"/>
    <!-- shadow face -->
    <rect x="52"  y="56" width="8"  height="90" rx="0" fill="#006e6c" opacity="0.3"/>
    <rect x="23"  y="62" width="11" height="9"  rx="1" fill="#b2f0ee" opacity="0.75"/>
    <rect x="38"  y="62" width="11" height="9"  rx="1" fill="#b2f0ee" opacity="0.65"/>
    <rect x="23"  y="75" width="11" height="9"  rx="1" fill="#b2f0ee" opacity="0.55"/>
    <rect x="38"  y="75" width="11" height="9"  rx="1" fill="#b2f0ee" opacity="0.75"/>
    <rect x="23"  y="88" width="11" height="9"  rx="1" fill="#b2f0ee" opacity="0.65"/>
    <rect x="38"  y="88" width="11" height="9"  rx="1" fill="#b2f0ee" opacity="0.45"/>
    <rect x="23"  y="101" width="11" height="9" rx="1" fill="#b2f0ee" opacity="0.55"/>
    <rect x="38"  y="101" width="11" height="9" rx="1" fill="#b2f0ee" opacity="0.35"/>
    <rect x="36"  y="51" width="4"  height="5"  fill="#01aaa8"/><!-- antenna -->

    <!-- B2: Salmon/coral tall building -->
    <rect x="68"  y="40" width="50" height="106" rx="3" fill="#e07b6a" opacity="0.9" filter="url(#pShadow)"/>
    <rect x="110" y="40" width="8"  height="106" fill="#b05848" opacity="0.25"/>
    <rect x="73"  y="47" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.7"/>
    <rect x="92"  y="47" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.65"/>
    <rect x="73"  y="62" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.6"/>
    <rect x="92"  y="62" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.75"/>
    <rect x="73"  y="77" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.55"/>
    <rect x="92"  y="77" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.6"/>
    <rect x="73"  y="92" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.65"/>
    <rect x="92"  y="92" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.5"/>
    <rect x="73"  y="107" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.55"/>
    <rect x="92"  y="107" width="14" height="10" rx="1" fill="#fce8e4" opacity="0.4"/>

    <!-- B3: Thai temple / house -->
    <polygon points="130,84 158,60 186,84" fill="#b5722e"/>
    <!-- second tier -->
    <polygon points="138,84 158,68 178,84" fill="#d4925c"/>
    <rect x="135" y="84"  width="46" height="62" rx="1" fill="#c88040"/>
    <!-- side shadow -->
    <rect x="173" y="84"  width="8"  height="62" fill="#8a5020" opacity="0.25"/>
    <rect x="140" y="91"  width="10" height="9"  rx="1" fill="#ffe4c4" opacity="0.65"/>
    <rect x="164" y="91"  width="10" height="9"  rx="1" fill="#ffe4c4" opacity="0.55"/>
    <rect x="140" y="104" width="10" height="9"  rx="1" fill="#ffe4c4" opacity="0.5"/>
    <rect x="164" y="104" width="10" height="9"  rx="1" fill="#ffe4c4" opacity="0.6"/>
    <rect x="149" y="117" width="16" height="29" rx="2" fill="#6b3a14"/><!-- door -->
    <!-- door panel -->
    <rect x="151" y="119" width="6"  height="13" rx="1" fill="#8b5530" opacity="0.6"/>
    <rect x="159" y="119" width="4"  height="13" rx="1" fill="#8b5530" opacity="0.4"/>

    <!-- B4: Tall navy skyscraper -->
    <rect x="196" y="28" width="36" height="118" rx="3" fill="#1a3a5c" opacity="0.9" filter="url(#pShadow)"/>
    <rect x="224" y="28" width="8"  height="118" fill="#0e2040" opacity="0.3"/>
    <rect x="201" y="35" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.65"/>
    <rect x="214" y="35" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.8"/>
    <rect x="201" y="48" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.55"/>
    <rect x="214" y="48" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.65"/>
    <rect x="201" y="61" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.7"/>
    <rect x="214" y="61" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.45"/>
    <rect x="201" y="74" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.6"/>
    <rect x="214" y="74" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.55"/>
    <rect x="201" y="87" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.5"/>
    <rect x="214" y="87" width="9"  height="9"   rx="1" fill="#93c5fd" opacity="0.65"/>
    <rect x="201" y="100" width="9" height="9"   rx="1" fill="#93c5fd" opacity="0.4"/>
    <rect x="214" y="100" width="9" height="9"   rx="1" fill="#93c5fd" opacity="0.55"/>
    <rect x="212" y="22" width="3"  height="6"   fill="#f5c518"/><!-- warning light -->
    <circle cx="213" cy="21" r="2.5" fill="#fde047" opacity="0.8"/>

    <!-- B5: White/cream mid-rise hotel style -->
    <rect x="242" y="62" width="48" height="84" rx="3" fill="#f0e8d0" opacity="0.92" filter="url(#pShadow)"/>
    <rect x="282" y="62" width="8"  height="84" fill="#c8b888" opacity="0.2"/>
    <!-- balconies -->
    <rect x="246" y="70" width="13" height="10" rx="1" fill="#c8b080" opacity="0.55"/>
    <rect x="263" y="70" width="13" height="10" rx="1" fill="#c8b080" opacity="0.55"/>
    <rect x="246" y="84" width="13" height="10" rx="1" fill="#c8b080" opacity="0.5"/>
    <rect x="263" y="84" width="13" height="10" rx="1" fill="#c8b080" opacity="0.6"/>
    <rect x="246" y="98" width="13" height="10" rx="1" fill="#c8b080" opacity="0.45"/>
    <rect x="263" y="98" width="13" height="10" rx="1" fill="#c8b080" opacity="0.5"/>
    <rect x="246" y="112" width="13" height="10" rx="1" fill="#c8b080" opacity="0.55"/>
    <rect x="263" y="112" width="13" height="10" rx="1" fill="#c8b080" opacity="0.4"/>
    <rect x="254" y="126" width="18" height="20" rx="2" fill="#8a6a30"/>
    <!-- horizontal balcony lines -->
    <rect x="246" y="68" width="36" height="2"  fill="#b8a070" opacity="0.5"/>
    <rect x="246" y="82" width="36" height="2"  fill="#b8a070" opacity="0.5"/>
    <rect x="246" y="96" width="36" height="2"  fill="#b8a070" opacity="0.5"/>
    <rect x="246" y="110" width="36" height="2" fill="#b8a070" opacity="0.5"/>

    <!-- B6: Teal-dark glass tower (tallest center) -->
    <rect x="300" y="20" width="44" height="126" rx="3" fill="#0e7490" opacity="0.88" filter="url(#pShadow)"/>
    <rect x="336" y="20" width="8"  height="126" fill="#064e63" opacity="0.3"/>
    <!-- glass curtain wall panels -->
    <rect x="305" y="27" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.5"/>
    <rect x="320" y="27" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.65"/>
    <rect x="305" y="44" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.55"/>
    <rect x="320" y="44" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.45"/>
    <rect x="305" y="61" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.6"/>
    <rect x="320" y="61" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.5"/>
    <rect x="305" y="78" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.45"/>
    <rect x="320" y="78" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.6"/>
    <rect x="305" y="95" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.5"/>
    <rect x="320" y="95" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.4"/>
    <rect x="305" y="112" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.55"/>
    <rect x="320" y="112" width="11" height="13" rx="1" fill="#a5f3fc" opacity="0.45"/>
    <!-- rooftop structure -->
    <rect x="308" y="14" width="28" height="6"  fill="#0a5e74"/>
    <rect x="316" y="8"  width="12" height="6"  fill="#0a5e74"/>
    <rect x="321" y="4"  width="2"  height="4"  fill="#f5c518"/>

    <!-- B7: Warm shophouse row -->
    <rect x="354" y="76" width="72" height="70" rx="2" fill="#d4864a" opacity="0.88" filter="url(#pShadow)"/>
    <rect x="418" y="76" width="8"  height="70" fill="#a06030" opacity="0.25"/>
    <!-- awning -->
    <rect x="354" y="76" width="72" height="7"  fill="#b05e28"/>
    <!-- awning scallop -->
    <path d="M354,83 Q363,88 372,83 Q381,78 390,83 Q399,88 408,83 Q417,78 426,83" fill="#c87038" opacity="0.6"/>
    <rect x="359" y="85" width="16" height="11" rx="1" fill="#fef3c7" opacity="0.65"/>
    <rect x="380" y="85" width="16" height="11" rx="1" fill="#fef3c7" opacity="0.6"/>
    <rect x="401" y="85" width="16" height="11" rx="1" fill="#fef3c7" opacity="0.65"/>
    <rect x="359" y="100" width="16" height="11" rx="1" fill="#fef3c7" opacity="0.55"/>
    <rect x="380" y="100" width="16" height="11" rx="1" fill="#fef3c7" opacity="0.6"/>
    <rect x="401" y="100" width="16" height="11" rx="1" fill="#fef3c7" opacity="0.5"/>
    <!-- central door -->
    <rect x="375" y="115" width="22" height="31" rx="2" fill="#7c4020"/>
    <circle cx="393" cy="132" r="2" fill="#d4864a"/><!-- door knob -->

    <!-- ── PALM TREES ── -->
    <!-- Palm 1 (big, left of ocean) -->
    <g transform="translate(500,100)">
      <path d="M7,46 Q5,20 8,0" stroke="#7c4a1a" stroke-width="7" fill="none" stroke-linecap="round"/>
      <!-- fronds -->
      <path d="M8,6 Q-18,0 -30,18"   stroke="#1a7a3a" stroke-width="8" fill="none" stroke-linecap="round"/>
      <path d="M8,6 Q32,0 44,18"     stroke="#1a7a3a" stroke-width="8" fill="none" stroke-linecap="round"/>
      <path d="M8,6 Q-6,-18 4,-34"   stroke="#16a34a" stroke-width="8" fill="none" stroke-linecap="round"/>
      <path d="M8,6 Q22,-18 30,-32"  stroke="#16a34a" stroke-width="7" fill="none" stroke-linecap="round"/>
      <path d="M8,6 Q-2,-24 12,-36"  stroke="#1a7a3a" stroke-width="7" fill="none" stroke-linecap="round"/>
      <path d="M8,6 Q-24,10 -34,28"  stroke="#15803d" stroke-width="6" fill="none" stroke-linecap="round"/>
      <!-- coconuts -->
      <circle cx="2"  cy="8"  r="4" fill="#c8820a"/>
      <circle cx="14" cy="7"  r="3.5" fill="#b87510"/>
    </g>
    <!-- Palm 2 (smaller) -->
    <g transform="translate(562,112)">
      <path d="M5,34 Q4,16 6,0"      stroke="#92400e" stroke-width="5" fill="none" stroke-linecap="round"/>
      <path d="M6,5 Q-14,0 -22,14"   stroke="#16a34a" stroke-width="6" fill="none" stroke-linecap="round"/>
      <path d="M6,5 Q26,0 34,14"     stroke="#15803d" stroke-width="6" fill="none" stroke-linecap="round"/>
      <path d="M6,5 Q-4,-14 4,-26"   stroke="#16a34a" stroke-width="6" fill="none" stroke-linecap="round"/>
      <path d="M6,5 Q16,-14 22,-24"  stroke="#15803d" stroke-width="5" fill="none" stroke-linecap="round"/>
      <circle cx="5" cy="7" r="3" fill="#c8820a"/>
    </g>
    <!-- Palm 3 (right side, near hill) -->
    <g transform="translate(1155,100)">
      <path d="M5,38 Q4,18 6,0"      stroke="#78350f" stroke-width="5" fill="none" stroke-linecap="round"/>
      <path d="M6,5 Q-16,-2 -22,14"  stroke="#16a34a" stroke-width="6" fill="none" stroke-linecap="round"/>
      <path d="M6,5 Q28,-2 34,14"    stroke="#15803d" stroke-width="6" fill="none" stroke-linecap="round"/>
      <path d="M6,5 Q-2,-18 6,-28"   stroke="#16a34a" stroke-width="5" fill="none" stroke-linecap="round"/>
      <path d="M6,5 Q18,-16 24,-24"  stroke="#15803d" stroke-width="5" fill="none" stroke-linecap="round"/>
    </g>

    <!-- ── LAMP POSTS ── -->
    <g fill="#374151">
      <rect x="460" y="110" width="3" height="36" rx="1"/>
      <rect x="447" y="110" width="16" height="3"  rx="1"/>
      <ellipse cx="447" cy="112" rx="5" ry="3" fill="#fef08a" opacity="0.7"/>

      <rect x="740" y="110" width="3" height="36" rx="1"/>
      <rect x="727" y="110" width="16" height="3"  rx="1"/>
      <ellipse cx="727" cy="112" rx="5" ry="3" fill="#fef08a" opacity="0.7"/>

      <rect x="1020" y="110" width="3" height="36" rx="1"/>
      <rect x="1007" y="110" width="16" height="3"  rx="1"/>
      <ellipse cx="1007" cy="112" rx="5" ry="3" fill="#fef08a" opacity="0.7"/>
    </g>

    <!-- ── BUS STOP SHELTER ── -->
    <rect x="424" y="120" width="40" height="26" rx="2" fill="#01aaa8" opacity="0.85"/>
    <rect x="424" y="116" width="42" height="5"  rx="2" fill="#017a78"/>
    <rect x="424" y="120" width="3"  height="26" fill="#015e5c"/>
    <rect x="461" y="120" width="3"  height="26" fill="#015e5c"/>
    <!-- bench -->
    <rect x="430" y="135" width="28" height="4"  rx="1" fill="#f0fafa"/>
    <rect x="433" y="139" width="4"  height="6"  rx="1" fill="#d0eaea"/>
    <rect x="451" y="139" width="4"  height="6"  rx="1" fill="#d0eaea"/>
    <!-- PKSB sign on shelter -->
    <rect x="428" y="122" width="32" height="10" rx="1" fill="white" opacity="0.2"/>
    <text x="432" y="130" font-family="Arial,sans-serif" font-size="7" font-weight="bold" fill="white">PKSB STOP</text>

    <!-- ── ROAD ── -->
    <rect y="146" width="1200" height="36" fill="url(#pRoad)"/>
    <!-- road top edge highlight -->
    <rect y="146" width="1200" height="2" fill="white" opacity="0.08"/>
    <!-- center dashes -->
    <rect y="161" x="0"    width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="90"   width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="180"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="270"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="360"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="450"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="540"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="630"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="720"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="810"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="900"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="990"  width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <rect y="161" x="1080" width="70" height="3.5" rx="1.5" fill="#fde047" opacity="0.85"/>
    <!-- kerb -->
    <rect y="182" width="1200" height="4" fill="#c0c8d0"/>
    <!-- pavement -->
    <rect y="186" width="1200" height="34" fill="#d8c898"/>
    <!-- pavement texture lines -->
    <line x1="0" y1="196" x2="1200" y2="196" stroke="#c4b47a" stroke-width="0.8" opacity="0.5"/>
    <line x1="0" y1="208" x2="1200" y2="208" stroke="#c4b47a" stroke-width="0.8" opacity="0.5"/>

    <!-- ── ANIMATED BUS (bigger, better) ── -->
    <g class="pksb-bus">
      <!-- Drop shadow -->
      <ellipse cx="100" cy="197" rx="98" ry="5" fill="#00000018"/>
      <!-- Main body -->
      <rect x="0"   y="148" width="200" height="48" rx="8" fill="url(#pBus)"/>
      <!-- White side stripe -->
      <rect x="0"   y="166" width="200" height="7"  fill="white" opacity="0.22"/>
      <!-- Roof line -->
      <rect x="4"   y="148" width="192" height="4"  rx="2" fill="#01c4c0" opacity="0.4"/>
      <!-- Windows row -->
      <rect x="10"  y="153" width="26" height="18" rx="3" fill="#d0f5f4" opacity="0.92"/>
      <rect x="42"  y="153" width="26" height="18" rx="3" fill="#d0f5f4" opacity="0.88"/>
      <rect x="74"  y="153" width="26" height="18" rx="3" fill="#d0f5f4" opacity="0.92"/>
      <rect x="106" y="153" width="26" height="18" rx="3" fill="#d0f5f4" opacity="0.85"/>
      <rect x="138" y="153" width="20" height="18" rx="3" fill="#d0f5f4" opacity="0.88"/>
      <!-- Window glare -->
      <rect x="11"  y="154" width="8" height="5" rx="1" fill="white" opacity="0.4"/>
      <rect x="43"  y="154" width="8" height="5" rx="1" fill="white" opacity="0.4"/>
      <rect x="75"  y="154" width="8" height="5" rx="1" fill="white" opacity="0.4"/>
      <rect x="107" y="154" width="8" height="5" rx="1" fill="white" opacity="0.4"/>
      <!-- Door (front right) -->
      <rect x="170" y="155" width="24" height="35" rx="3" fill="#016c6a"/>
      <rect x="173" y="158" width="8"  height="15" rx="2" fill="#b2f0ee" opacity="0.7"/>
      <rect x="183" y="158" width="8"  height="15" rx="2" fill="#b2f0ee" opacity="0.65"/>
      <!-- Headlight assembly -->
      <rect x="192" y="154" width="8"  height="10" rx="2" fill="#fef9c3" opacity="0.95"/>
      <rect x="192" y="154" width="8"  height="10" rx="2" fill="#fef08a" opacity="0.5"/>
      <!-- Taillights -->
      <rect x="0"   y="155" width="7"  height="10" rx="2" fill="#f87171" opacity="0.9"/>
      <rect x="0"   y="168" width="7"  height="7"  rx="1" fill="#fca5a5" opacity="0.7"/>
      <!-- PKSB branding text -->
      <text x="22" y="186" font-family="Arial,sans-serif" font-size="10" font-weight="bold" fill="white" opacity="0.95" letter-spacing="0.5">PHUKET SMART BUS</text>
      <!-- Teal brand line below text -->
      <rect x="22" y="189" width="128" height="2" rx="1" fill="white" opacity="0.3"/>
      <!-- Route number badge -->
      <rect x="158" y="178" width="34" height="16" rx="3" fill="#f5c518"/>
      <text x="166" y="190" font-family="Arial,sans-serif" font-size="9" font-weight="bold" fill="#1a1a2e">R1</text>
      <!-- Wheel arches (body recesses) -->
      <path d="M14,192 Q30,186 46,192" stroke="#016c6a" stroke-width="4" fill="none"/>
      <path d="M134,192 Q150,186 166,192" stroke="#016c6a" stroke-width="4" fill="none"/>
      <!-- Wheels (spinning) -->
      <g class="pksb-wheel1">
        <circle cx="30"  cy="196" r="14" fill="#1f2937"/>
        <circle cx="30"  cy="196" r="9"  fill="#374151"/>
        <circle cx="30"  cy="196" r="4"  fill="#9ca3af"/>
        <rect   x="26"   y="183" width="8" height="26" rx="2" fill="#4b5563" opacity="0.4"/>
        <rect   x="17"   y="192" width="26" height="8" rx="2" fill="#4b5563" opacity="0.4"/>
      </g>
      <g class="pksb-wheel2">
        <circle cx="150" cy="196" r="14" fill="#1f2937"/>
        <circle cx="150" cy="196" r="9"  fill="#374151"/>
        <circle cx="150" cy="196" r="4"  fill="#9ca3af"/>
        <rect   x="146"  y="183" width="8" height="26" rx="2" fill="#4b5563" opacity="0.4"/>
        <rect   x="137"  y="192" width="26" height="8" rx="2" fill="#4b5563" opacity="0.4"/>
      </g>
    </g>

    <!-- ── FOREGROUND GRASS TUFT details ── -->
    <path d="M0,186 Q4,182 8,186 Q12,182 16,186" stroke="#5a9a5a" stroke-width="2" fill="none" opacity="0.5"/>
    <path d="M60,186 Q64,181 68,186 Q72,181 76,186" stroke="#5a9a5a" stroke-width="2" fill="none" opacity="0.45"/>
    <path d="M820,186 Q824,182 828,186 Q832,182 836,186" stroke="#5a9a5a" stroke-width="2" fill="none" opacity="0.4"/>
    <path d="M1100,186 Q1104,181 1108,186 Q1112,181 1116,186" stroke="#5a9a5a" stroke-width="2" fill="none" opacity="0.45"/>
  </svg>
</div>

<!-- Footer -->
<footer class="bg-navy-brand text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-10">
      <a href="{{ lurl('home') }}" class="flex items-center gap-2" aria-label="Phuket Smart Bus — Home">
        <picture>
          <source srcset="{{ asset('images/logo.webp') }}" type="image/webp">
          <img src="{{ asset('images/logo.png') }}" alt="PKSB - Phuket Smart Bus" width="240" height="120" class="h-16 sm:h-20 w-auto brightness-0 invert">
        </picture>
      </a>
      <div class="flex items-center gap-3">
        <a href="https://facebook.com/PhuketSmartBus" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-blue-600 inline-flex items-center justify-center hover:opacity-90" aria-label="Facebook">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12a10 10 0 10-11.5 9.9V14.9H8V12h2.5V9.8c0-2.5 1.5-3.8 3.7-3.8 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.5V12H16l-.4 2.9h-2.1V22A10 10 0 0022 12z"/></svg>
        </a>
        <a href="https://line.me/R/ti/p/@pksb" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-green-500 inline-flex items-center justify-center hover:opacity-90" aria-label="LINE">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M19.4 9.6c0-3.6-3.6-6.5-8-6.5s-8 2.9-8 6.5c0 3.2 2.9 5.9 6.7 6.4.3.1.6.2.7.4.1.2.1.5 0 .7l-.1.6c0 .2-.1.6.5.3s3.5-2 4.7-3.4c.9-1 1.5-2 1.5-3z"/></svg>
        </a>
        <a href="https://wa.me/66863061257" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-green-600 inline-flex items-center justify-center hover:opacity-90" aria-label="WhatsApp">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 3.5A11.7 11.7 0 0012 0C5.4 0 .1 5.3.1 11.9c0 2.1.6 4.1 1.6 5.9L0 24l6.3-1.7a11.9 11.9 0 005.7 1.5h.1c6.6 0 11.9-5.3 11.9-11.9 0-3.2-1.2-6.2-3.5-8.4zM12 21.7c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4a9.8 9.8 0 01-1.5-5.2c0-5.5 4.5-9.9 9.9-9.9 2.6 0 5.1 1 7 2.9a9.9 9.9 0 012.9 7c0 5.5-4.5 9.8-9.9 9.8z"/></svg>
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-sm">
      <div>
        <h2 class="font-semibold mb-3 text-base">{{ __('footer.head_office') }}</h2>
        <address class="not-italic text-white/80 leading-relaxed">
          Phuket City Development Co., Ltd.<br>
          9/9 Moo 5, Wichit Subdistrict,<br>
          Mueang Phuket District,<br>
          Phuket Province 83000, Thailand<br>
          <a href="tel:+66863061257" class="hover:text-teal-brand">086 306 1257</a>
        </address>
      </div>
      <div>
        <h2 class="font-semibold mb-3 text-base">{{ __('footer.get_started') }}</h2>
        <ul class="space-y-2 text-white/80">
          <li><a href="{{ lurl('about') }}" class="hover:text-teal-brand">{{ __('nav.about') }}</a></li>
          <li><a href="{{ lurl('blog') }}" class="hover:text-teal-brand">{{ __('nav.destinations') }}</a></li>
          <li><a href="{{ lurl('timetable') }}" class="hover:text-teal-brand">{{ __('nav.timetable') }}</a></li>
          <li><a href="{{ lurl('tracking') }}" class="hover:text-teal-brand">{{ __('nav.tracking') }}</a></li>
          <li><a href="{{ lurl('pass') }}" class="hover:text-teal-brand">{{ __('nav.pass') }}</a></li>
        </ul>
      </div>
      <div>
        <h2 class="font-semibold mb-3 text-base">{{ __('footer.support') }}</h2>
        <ul class="space-y-2 text-white/80">
          <li><a href="{{ lurl('payment') }}" class="hover:text-teal-brand">{{ __('footer.payment_methods') }}</a></li>
          <li><a href="{{ lurl('contact') }}" class="hover:text-teal-brand">{{ __('nav.contact') }}</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-white/60">
      {{ __('footer.copyright', ['year' => date('Y')]) }}
    </div>
  </div>
</footer>

<script>
  (function () {
    const btn = document.getElementById('mobileMenuBtn');
    const menu = document.getElementById('mobileMenu');
    const open = document.getElementById('iconOpen');
    const close = document.getElementById('iconClose');
    if (btn && menu) {
      btn.addEventListener('click', () => {
        const isOpen = !menu.classList.toggle('hidden');
        btn.setAttribute('aria-expanded', String(isOpen));
        open.classList.toggle('hidden', isOpen);
        close.classList.toggle('hidden', !isOpen);
      });
    }

    // Language picker
    const langBtn = document.getElementById('langBtn');
    const langMenu = document.getElementById('langMenu');
    if (langBtn && langMenu) {
      langBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = langMenu.classList.toggle('hidden') === false;
        langBtn.setAttribute('aria-expanded', String(open));
      });
      document.addEventListener('click', (e) => {
        if (!langMenu.contains(e.target) && !langBtn.contains(e.target)) {
          langMenu.classList.add('hidden');
          langBtn.setAttribute('aria-expanded', 'false');
        }
      });
    }
  })();
</script>
</body>
</html>
