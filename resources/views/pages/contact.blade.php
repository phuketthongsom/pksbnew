@extends('layouts.app')
@section('title', 'Contact Phuket Smart Bus — LINE @pksb · 086 306 1257 | PKSB')
@section('description', 'Reach Phuket Smart Bus via LINE @pksb, phone 086 306 1257, Facebook or WhatsApp. Head office in Wichit, Mueang Phuket. We reply fast.')

@push('jsonld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "ContactPage",
      "@@id": "{{ url('/contact') }}",
      "url": "{{ url('/contact') }}",
      "name": "Contact Phuket Smart Bus",
      "description": "Get in touch with Phuket Smart Bus via LINE @pksb, phone, Facebook or WhatsApp. We reply fast.",
      "isPartOf": {"@@id": "{{ url('/') }}"},
      "breadcrumb": {"@@id": "{{ url('/contact') }}#breadcrumb"},
      "contactOption": [
        {
          "@@type": "ContactPoint",
          "contactType": "customer support",
          "telephone": "+66863061257",
          "availableLanguage": ["English","Thai"],
          "contactOption": "TollFree",
          "hoursAvailable": {
            "@@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
            "opens": "06:00",
            "closes": "18:00"
          }
        }
      ]
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ url('/contact') }}#breadcrumb",
      "itemListElement": [
        {"@@type":"ListItem","position":1,"name":"Home","item":"{{ url('/') }}"},
        {"@@type":"ListItem","position":2,"name":"Contact Us"}
      ]
    }
  ]
}
</script>
@endpush

@section('content')
<section class="relative pt-32 pb-16 bg-gradient-to-br from-teal-brand to-cyan-700 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold">{{ __('contact.title') }}</h1>
    <p class="mt-4 max-w-2xl text-white/90">{{ __('contact.subtitle') }}</p>
  </div>
</section>

<section class="py-12 sm:py-16">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-10">
    <div class="space-y-3">
      <a href="https://line.me/R/ti/p/@pksb" target="_blank" rel="noopener"
         class="flex items-center gap-4 p-4 rounded-2xl bg-white ring-1 ring-gray-100 shadow-sm hover:shadow-md transition">
        <span class="w-12 h-12 rounded-full bg-green-500 text-white flex items-center justify-center font-bold text-lg" aria-hidden="true">L</span>
        <span>
          <span class="block text-xs text-gray-500">{{ __('contact.line_fast') }}</span>
          <span class="block text-lg font-semibold text-navy-brand">@pksb</span>
        </span>
      </a>

      <a href="tel:+66863061257"
         class="flex items-center gap-4 p-4 rounded-2xl bg-white ring-1 ring-gray-100 shadow-sm hover:shadow-md transition">
        <span class="w-12 h-12 rounded-full bg-teal-brand text-white flex items-center justify-center" aria-hidden="true">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.3a2 2 0 011.94 1.5l1 4a2 2 0 01-.5 1.94L8 12.5a11 11 0 005.5 5.5l2-1.74a2 2 0 011.94-.5l4 1A2 2 0 0123 18.7V21a2 2 0 01-2 2A18 18 0 013 5z"/></svg>
        </span>
        <span>
          <span class="block text-xs text-gray-500">{{ __('contact.phone') }}</span>
          <span class="block text-lg font-semibold text-navy-brand">086 306 1257</span>
        </span>
      </a>

      <a href="https://wa.me/66863061257" target="_blank" rel="noopener"
         class="flex items-center gap-4 p-4 rounded-2xl bg-white ring-1 ring-gray-100 shadow-sm hover:shadow-md transition">
        <span class="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center font-bold" aria-hidden="true">W</span>
        <span>
          <span class="block text-xs text-gray-500">{{ __('contact.whatsapp') }}</span>
          <span class="block text-lg font-semibold text-navy-brand">+66 86 306 1257</span>
        </span>
      </a>

      <a href="https://facebook.com/PhuketSmartBus" target="_blank" rel="noopener"
         class="flex items-center gap-4 p-4 rounded-2xl bg-white ring-1 ring-gray-100 shadow-sm hover:shadow-md transition">
        <span class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold" aria-hidden="true">f</span>
        <span>
          <span class="block text-xs text-gray-500">{{ __('contact.facebook') }}</span>
          <span class="block text-lg font-semibold text-navy-brand">Phuket Smart Bus</span>
        </span>
      </a>

      <div class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50">
        <span class="w-12 h-12 rounded-full bg-gray-800 text-white flex items-center justify-center" aria-hidden="true">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
        <address class="not-italic text-sm">
          <span class="block text-xs text-gray-500">{{ __('contact.head_office') }}</span>
          <span class="block text-base font-medium text-navy-brand">9/9 Moo 5, Wichit, Mueang Phuket, 83000</span>
        </address>
      </div>
    </div>

    <form action="https://line.me/R/ti/p/@pksb" method="get" class="bg-gray-50 rounded-2xl p-6 sm:p-8 space-y-4" aria-label="{{ __('contact.send_message') }}">
      <h2 class="text-lg font-bold text-navy-brand">{{ __('contact.send_message') }}</h2>
      <p class="text-sm text-gray-600 -mt-2">{{ __('contact.form.note') }}</p>

      <div>
        <label for="cf-name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.form.name') }}</label>
        <input id="cf-name" name="name" autocomplete="name" required
          class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
      </div>
      <div>
        <label for="cf-email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.form.email') }}</label>
        <input id="cf-email" type="email" name="email" autocomplete="email" required
          class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
      </div>
      <div>
        <label for="cf-msg" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.form.message') }}</label>
        <textarea id="cf-msg" name="message" rows="4" required
          class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none"></textarea>
      </div>
      <button type="submit" class="w-full px-5 py-3 rounded-md bg-teal-brand text-white font-semibold hover:bg-teal-600 transition">
        {{ __('contact.form.submit') }}
      </button>
      <p class="text-xs text-gray-500">{{ __('contact.form.or_call') }} <a href="tel:+66863061257" class="text-teal-700 underline">086 306 1257</a>.</p>
    </form>
  </div>
</section>
@endsection
