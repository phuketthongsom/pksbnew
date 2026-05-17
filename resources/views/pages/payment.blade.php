@extends('layouts.app')
@section('title', 'Payment — Tap, Go and Ride | Phuket Smart Bus')
@section('description', 'Pay for Phuket Smart Bus with contactless Visa/Mastercard, QR PromptPay, or cash. Three routes from FREE to 100฿. No app, no account required.')

@push('jsonld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "WebPage",
      "@@id": "{{ url('/payment') }}",
      "url": "{{ url('/payment') }}",
      "name": "Payment — Tap, Go and Ride | Phuket Smart Bus",
      "isPartOf": {"@@id": "{{ url('/') }}"},
      "breadcrumb": {"@@id": "{{ url('/payment') }}#breadcrumb"}
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ url('/payment') }}#breadcrumb",
      "itemListElement": [
        {"@@type":"ListItem","position":1,"name":"Home","item":"{{ url('/') }}"},
        {"@@type":"ListItem","position":2,"name":"Payment"}
      ]
    },
    {
      "@@type": "FAQPage",
      "mainEntity": [
        {
          "@@type": "Question",
          "name": "Do I need to download an app to ride Phuket Smart Bus?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "No. Just tap a contactless card or pay in cash on board. No app or account is required."
          }
        },
        {
          "@@type": "Question",
          "name": "Which cards are accepted on Phuket Smart Bus?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "Any contactless Visa, Mastercard or JCB. Apple Pay and Google Pay also work."
          }
        },
        {
          "@@type": "Question",
          "name": "Can I get a receipt on Phuket Smart Bus?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "Yes — ask the driver for a printed receipt, or take a photo of the on-screen confirmation."
          }
        },
        {
          "@@type": "Question",
          "name": "Is there a foreigner or tourist fare on Phuket Smart Bus?",
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": "No. The same fare applies to everyone — locals and visitors alike. Fares range from free (Dragon Line) to 50฿ (Bus Terminal–Patong) to 100฿ (Airport–Rawai)."
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
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold">{{ __('payment.title') }}</h1>
    <p class="mt-3 text-white/90">{{ __('payment.subtitle') }}</p>
  </div>
</section>

<section class="py-12 sm:py-16" aria-labelledby="methods-title">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 id="methods-title" class="sr-only">Payment Methods</h2>
    <div class="grid sm:grid-cols-3 gap-6">
      @foreach([
        ['Mastercard / Visa', 'Tap any contactless Visa, Mastercard or JCB card on board. No app needed.', '💳'],
        ['QR / PromptPay',    'Scan the on-board QR with any Thai banking app to pay in seconds.', '📱'],
        ['Cash',              'Exact fare appreciated. See the fare table below for each route.', '💵'],
      ] as [$t, $d, $i])
        <article class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-6 text-center">
          <div class="text-4xl mb-3" aria-hidden="true">{{ $i }}</div>
          <h3 class="font-bold text-navy-brand text-lg mb-2">{{ $t }}</h3>
          <p class="text-gray-600 text-sm">{{ $d }}</p>
        </article>
      @endforeach
    </div>
  </div>
</section>

<section class="bg-gray-50 py-12 sm:py-16" aria-labelledby="fares-title">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 id="fares-title" class="text-2xl sm:text-3xl font-bold text-navy-brand mb-6">{{ __('payment.fares') }}</h2>
    <div class="overflow-x-auto rounded-2xl shadow ring-1 ring-gray-100 bg-white">
      <table class="w-full text-left text-sm">
        <thead class="bg-teal-brand text-white">
          <tr>
            <th scope="col" class="px-4 py-3 font-semibold">{{ __('payment.col.route') }}</th>
            <th scope="col" class="px-4 py-3 font-semibold">{{ __('payment.col.single') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr><th scope="row" class="px-4 py-3 font-medium text-gray-800 text-left">Airport ⇄ Patong ⇄ Rawai</th><td class="px-4 py-3 text-gray-700">100 ฿ flat</td></tr>
          <tr><th scope="row" class="px-4 py-3 font-medium text-gray-800 text-left">Bus Terminal ⇄ Patong</th><td class="px-4 py-3 text-gray-700">50 ฿</td></tr>
          <tr><th scope="row" class="px-4 py-3 font-medium text-gray-800 text-left">Dragon Line</th><td class="px-4 py-3 font-semibold text-teal-brand">Free</td></tr>
        </tbody>
      </table>
    </div>
    <p class="text-xs text-gray-500 mt-3">{{ __('payment.note_kids') }}</p>

    <div class="mt-8 grid sm:grid-cols-2 gap-4">
      <a href="{{ lurl('pass') }}" class="block bg-teal-brand text-white rounded-2xl p-6 hover:bg-teal-600 transition">
        <div class="text-lg font-bold mb-1">{{ __('payment.cta.pass') }}</div>
        <p class="text-sm text-white/90">{{ __('payment.cta.pass_desc') }}</p>
      </a>
      <a href="{{ lurl('contact') }}" class="block bg-white text-navy-brand ring-1 ring-gray-200 rounded-2xl p-6 hover:bg-gray-50 transition">
        <div class="text-lg font-bold mb-1">{{ __('payment.cta.help') }}</div>
        <p class="text-sm text-gray-600">{{ __('payment.cta.help_desc') }}</p>
      </a>
    </div>
  </div>
</section>

<section class="py-12 sm:py-16" aria-labelledby="faq-title">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 id="faq-title" class="text-2xl sm:text-3xl font-bold text-navy-brand mb-6">{{ __('payment.faq') }}</h2>
    <div class="space-y-4">
      <details class="bg-white rounded-xl ring-1 ring-gray-100 p-4">
        <summary class="font-semibold cursor-pointer">Do I need to download an app?</summary>
        <p class="mt-2 text-sm text-gray-600">No. Just tap a contactless card or pay in cash on board.</p>
      </details>
      <details class="bg-white rounded-xl ring-1 ring-gray-100 p-4">
        <summary class="font-semibold cursor-pointer">Which cards are accepted?</summary>
        <p class="mt-2 text-sm text-gray-600">Any contactless Visa, Mastercard or JCB. Apple Pay and Google Pay also work.</p>
      </details>
      <details class="bg-white rounded-xl ring-1 ring-gray-100 p-4">
        <summary class="font-semibold cursor-pointer">Can I get a receipt?</summary>
        <p class="mt-2 text-sm text-gray-600">Yes — ask the driver for a printed receipt, or take a photo of the on-screen confirmation.</p>
      </details>
      <details class="bg-white rounded-xl ring-1 ring-gray-100 p-4">
        <summary class="font-semibold cursor-pointer">Is there a foreigner / tourist fare?</summary>
        <p class="mt-2 text-sm text-gray-600">No. The same fare applies to everyone — locals and visitors. See the fare table above for the price on each route.</p>
      </details>
    </div>
  </div>
</section>
@endsection
