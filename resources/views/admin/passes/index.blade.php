@extends('admin.layout')
@section('title', 'Day Passes')

@php
  $localeMeta = [
    'en' => ['flag' => '🇬🇧', 'label' => 'English'],
    'th' => ['flag' => '🇹🇭', 'label' => 'ไทย'],
    'zh' => ['flag' => '🇨🇳', 'label' => '中文'],
    'ru' => ['flag' => '🇷🇺', 'label' => 'Русский'],
  ];
@endphp

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl font-bold text-navy-brand">Day Passes</h1>
    <p class="text-sm text-gray-500">Shown on the public <a href="{{ route('pass') }}" target="_blank" class="text-teal-brand hover:underline">/pass</a> page.</p>
  </div>
  {{-- D-S7: real button, matches "+ New Destination" weight on posts page --}}
  <button type="button" id="newPassBtn"
          class="inline-flex items-center justify-center px-4 py-2.5 rounded-md bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600 sm:self-start whitespace-nowrap">
    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
    New Pass
  </button>
</div>

{{-- New-pass form (hidden by default, revealed by the New Pass button) --}}
<div id="newPassForm" class="hidden bg-white rounded-2xl shadow ring-1 ring-gray-100 mb-8 p-6">
  <h2 class="text-lg font-bold text-navy-brand mb-4">Create a new pass</h2>
  <form method="POST" action="{{ route('admin.passes.store') }}" class="grid sm:grid-cols-3 gap-4">
    @csrf
    <div class="sm:col-span-3">
      <label class="block text-xs font-semibold text-gray-700 mb-1">Name (English) <span class="text-gray-400 font-normal">— add other languages after creating</span></label>
      <input type="text" name="translations[en][name]" required maxlength="120" placeholder="e.g. 14 Day Pass"
             class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
    </div>
    <div>
      <label class="block text-xs font-semibold text-gray-700 mb-1">Price (THB)</label>
      <input type="number" name="price" required min="0" max="1000000" placeholder="2200"
             class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
    </div>
    <div>
      <label class="block text-xs font-semibold text-gray-700 mb-1">Duration (days)</label>
      <input type="number" name="duration_days" required min="1" max="365" placeholder="14"
             class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
    </div>
    <div class="flex items-end gap-2">
      <button type="button" id="cancelNewPass" class="flex-1 px-4 py-2 rounded-md bg-gray-100 text-navy-brand text-sm font-semibold hover:bg-gray-200">Cancel</button>
      <button type="submit" class="flex-1 px-4 py-2 rounded-md bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600">Create</button>
    </div>
  </form>
</div>

@if(empty($passes))
  <div class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-10 text-center">
    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-teal-50 text-teal-brand flex items-center justify-center">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    </div>
    <h2 class="font-bold text-navy-brand mb-1">No passes yet</h2>
    <p class="text-sm text-gray-600 mb-4">Click <strong>+ New Pass</strong> to add your first pass.</p>
  </div>
@else

<div class="space-y-3">
  @foreach($passes as $idx => $p)
    @php
      $isFirst = $idx === 0;
      $isLast = $idx === count($passes) - 1;
      $tx = $p['translations'] ?? [];
      $coverIsUploaded = !empty($p['cover']) && str_starts_with($p['cover'], 'storage/');
      $coverHasImage = !empty($p['cover']);
    @endphp

    {{-- D-S6: passes are summary rows by default; click "Edit" to expand the inline form. --}}
    <section class="bg-white rounded-2xl shadow ring-1 ring-gray-100 overflow-hidden" data-pass-card>
      <header class="px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-3 min-w-0 flex-1">
          <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-navy-brand text-white flex-none">#{{ $idx + 1 }}</span>
          @if(!empty($p['cover']))
            <img src="{{ asset($p['cover']) }}" alt="" class="w-10 h-10 rounded object-cover bg-gray-100 flex-none hidden sm:block">
          @endif
          <div class="min-w-0 flex-1">
            <h2 class="font-bold text-navy-brand truncate">{{ $tx['en']['name'] ?? '(unnamed)' }}</h2>
            <div class="text-xs text-gray-500 mt-0.5">{{ number_format($p['price']) }} ฿ · {{ $p['duration_days'] }} day{{ $p['duration_days'] === 1 ? '' : 's' }}</div>
          </div>
        </div>
        <div class="flex items-center gap-1 flex-none">
          <form method="POST" action="{{ route('admin.passes.reorder', $p['id']) }}" class="inline">
            @csrf
            <input type="hidden" name="direction" value="up">
            <button type="submit" {{ $isFirst ? 'disabled' : '' }}
                    class="w-8 h-8 rounded text-sm font-bold {{ $isFirst ? 'opacity-40 cursor-not-allowed bg-gray-50 text-gray-400' : 'bg-gray-100 text-navy-brand hover:bg-gray-200' }}"
                    title="Move up" aria-label="Move up" data-allow-resubmit>↑</button>
          </form>
          <form method="POST" action="{{ route('admin.passes.reorder', $p['id']) }}" class="inline">
            @csrf
            <input type="hidden" name="direction" value="down">
            <button type="submit" {{ $isLast ? 'disabled' : '' }}
                    class="w-8 h-8 rounded text-sm font-bold {{ $isLast ? 'opacity-40 cursor-not-allowed bg-gray-50 text-gray-400' : 'bg-gray-100 text-navy-brand hover:bg-gray-200' }}"
                    title="Move down" aria-label="Move down" data-allow-resubmit>↓</button>
          </form>
          <button type="button" data-pass-toggle
                  class="ml-2 px-3 py-1.5 rounded-md text-xs font-semibold bg-gray-100 text-navy-brand hover:bg-gray-200"
                  aria-expanded="false">
            <span data-pass-toggle-text>Edit</span>
          </button>
          <form method="POST" action="{{ route('admin.passes.destroy', $p['id']) }}" class="inline"
                onsubmit="return confirm('Delete this pass permanently?');">
            @csrf @method('DELETE')
            <button class="px-3 py-1.5 rounded-md text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100">Delete</button>
          </form>
        </div>
      </header>

      <form method="POST" action="{{ route('admin.passes.update', $p['id']) }}" enctype="multipart/form-data"
            class="hidden border-t border-gray-100 grid lg:grid-cols-3 gap-6 p-4 sm:p-6"
            data-pass-form>
        @csrf @method('PUT')

        {{-- Translations --}}
        <div class="lg:col-span-2 space-y-3">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Names &amp; descriptions per language</h3>
          @foreach($localeMeta as $loc => $info)
            <div class="rounded-md ring-1 ring-gray-100 p-3 bg-gray-50">
              <div class="flex items-center gap-2 mb-2">
                <span class="text-base">{{ $info['flag'] }}</span>
                <span class="text-sm font-semibold text-navy-brand">{{ $info['label'] }}</span>
                @if($loc === 'en')
                  <span class="text-[10px] text-gray-500">required — fallback for empty translations</span>
                @endif
              </div>
              <div class="grid sm:grid-cols-2 gap-2">
                <input type="text" name="translations[{{ $loc }}][name]"
                       maxlength="120" placeholder="Name in {{ $info['label'] }}"
                       {{ $loc === 'en' ? 'required' : '' }}
                       value="{{ $tx[$loc]['name'] ?? '' }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
                <input type="text" name="translations[{{ $loc }}][description]"
                       maxlength="300" placeholder="One-line description"
                       value="{{ $tx[$loc]['description'] ?? '' }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
              </div>
            </div>
          @endforeach
        </div>

        {{-- Sidebar: price/duration/cover --}}
        <aside class="space-y-5">
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Price (THB)</label>
            <input type="number" name="price" required min="0" max="1000000"
                   value="{{ $p['price'] }}"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Duration (days)</label>
            <input type="number" name="duration_days" required min="1" max="365"
                   value="{{ $p['duration_days'] }}"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Cover image</label>
            @if($coverHasImage)
              <div class="rounded-lg overflow-hidden ring-1 ring-gray-200 bg-gray-50 mb-2">
                <img src="{{ asset($p['cover']) }}" alt="" class="w-full h-32 object-contain">
              </div>
              @if($coverIsUploaded)
                <label class="inline-flex items-center gap-2 text-xs mb-2">
                  <input type="checkbox" name="cover_action" value="clear" class="rounded">
                  <span class="text-red-700 font-semibold">Remove this image (revert to default)</span>
                </label>
              @endif
            @else
              <p class="text-xs text-gray-500 mb-2">Using default — bus photo with "{{ $tx['en']['name'] ?? 'Pass' }}" overlay.</p>
            @endif
            <input type="file" name="cover" accept="image/*"
                   class="block w-full text-xs text-gray-700 file:mr-3 file:px-3 file:py-2 file:rounded-md file:border-0 file:bg-teal-brand file:text-white file:text-xs file:font-semibold hover:file:bg-teal-600">
            <p class="text-[11px] text-gray-500 mt-1">Optional — JPG/PNG/WebP, max 8 MB. Replaces the default card design with your own (like the 30-day pass image).</p>
          </div>

          <button type="submit" class="w-full px-4 py-2.5 rounded-md bg-teal-brand text-white font-semibold hover:bg-teal-600 transition">
            Save changes
          </button>
        </aside>
      </form>
    </section>
  @endforeach
</div>
@endif

<script>
  (function () {
    // New-pass form reveal
    const newBtn = document.getElementById('newPassBtn');
    const newForm = document.getElementById('newPassForm');
    const cancel = document.getElementById('cancelNewPass');
    if (newBtn && newForm) {
      newBtn.addEventListener('click', () => {
        newForm.classList.remove('hidden');
        newForm.querySelector('input[type="text"]')?.focus();
        newBtn.classList.add('hidden');
      });
      cancel?.addEventListener('click', () => {
        newForm.classList.add('hidden');
        newBtn.classList.remove('hidden');
      });
    }

    // Per-card expand/collapse
    document.querySelectorAll('[data-pass-card]').forEach((card) => {
      const toggle = card.querySelector('[data-pass-toggle]');
      const form = card.querySelector('[data-pass-form]');
      const label = card.querySelector('[data-pass-toggle-text]');
      if (!toggle || !form || !label) return;
      toggle.addEventListener('click', () => {
        const open = form.classList.toggle('hidden') === false;
        toggle.setAttribute('aria-expanded', String(open));
        label.textContent = open ? 'Close' : 'Edit';
      });
    });
  })();
</script>
@endsection
