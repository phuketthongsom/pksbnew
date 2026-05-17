@extends('admin.layout')
@section('title', 'Timetables')

@section('content')
<div class="mb-6">
  <h1 class="text-2xl font-bold text-navy-brand">Route Timetables</h1>
  <p class="text-sm text-gray-500">Upload one or more timetable images per route. They appear on the public <a href="{{ route('timetable') }}" target="_blank" class="text-teal-brand hover:underline">/timetable</a> page in the order you add them.</p>
</div>

<div class="space-y-8">
  @foreach($routes as $r)
    <section class="bg-white rounded-2xl shadow ring-1 ring-gray-100 overflow-hidden">
      <header class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
        <div>
          <h2 class="font-bold text-navy-brand">{{ $r['label'] }}</h2>
          <p class="text-xs text-gray-500">
            Fare: <strong>{{ $r['flat_fare'] }}</strong> ·
            Key: <code class="text-[11px]">{{ $r['key'] }}</code> ·
            {{ count($r['images']) }} image{{ count($r['images']) === 1 ? '' : 's' }}
          </p>
        </div>
        @if($r['updated_at'])
          <span class="text-[10px] text-gray-400">Last update {{ $r['updated_at'] }}</span>
        @endif
      </header>

      <div class="p-4 sm:p-6 space-y-6">
        {{-- Existing images --}}
        @if(count($r['images']) === 0)
          <div class="rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 p-8 text-center text-sm text-gray-500">
            No timetable images yet for this route.
          </div>
        @else
          @php
            $localeMeta = [
              'en' => ['flag' => '🇬🇧', 'label' => 'English'],
              'th' => ['flag' => '🇹🇭', 'label' => 'ไทย'],
              'zh' => ['flag' => '🇨🇳', 'label' => '中文'],
              'ru' => ['flag' => '🇷🇺', 'label' => 'Русский'],
            ];
          @endphp
          <div class="grid sm:grid-cols-2 gap-4">
            @foreach($r['images'] as $idx => $img)
              @php
                $captionMap = is_array($img['caption'] ?? null) ? $img['caption'] : ['en' => (string)($img['caption'] ?? '')];
                $isFirst = $idx === 0;
                $isLast = $idx === count($r['images']) - 1;
              @endphp
              <article class="rounded-lg overflow-hidden ring-1 ring-gray-200 bg-white flex flex-col">
                <div class="relative">
                  <a href="{{ asset($img['path']) }}" target="_blank" class="block bg-gray-50 border-b border-gray-100">
                    <img src="{{ asset($img['path']) }}?v={{ md5($img['uploaded_at'] ?? '') }}"
                         alt="{{ \App\Services\TimetableRepository::localizedCaption($captionMap, 'en') ?: $r['label'] }}"
                         loading="lazy"
                         class="w-full h-48 object-contain bg-gray-50">
                  </a>
                  <span class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-navy-brand text-white">#{{ $idx + 1 }}</span>
                  <div class="absolute top-2 right-2 flex gap-1">
                    <form method="POST" action="{{ route('admin.timetables.images.reorder', [$r['key'], $img['id']]) }}">
                      @csrf
                      <input type="hidden" name="direction" value="up">
                      <button type="submit" {{ $isFirst ? 'disabled' : '' }}
                              class="w-7 h-7 rounded bg-white/90 text-navy-brand text-xs font-bold shadow {{ $isFirst ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white' }}"
                              title="Move up" aria-label="Move up" data-allow-resubmit>↑</button>
                    </form>
                    <form method="POST" action="{{ route('admin.timetables.images.reorder', [$r['key'], $img['id']]) }}">
                      @csrf
                      <input type="hidden" name="direction" value="down">
                      <button type="submit" {{ $isLast ? 'disabled' : '' }}
                              class="w-7 h-7 rounded bg-white/90 text-navy-brand text-xs font-bold shadow {{ $isLast ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white' }}"
                              title="Move down" aria-label="Move down" data-allow-resubmit>↓</button>
                    </form>
                  </div>
                </div>

                {{-- Per-locale caption editor --}}
                <form method="POST" action="{{ route('admin.timetables.images.caption', [$r['key'], $img['id']]) }}"
                      class="p-3 space-y-2 flex-1 flex flex-col">
                  @csrf
                  <label class="block text-[11px] font-semibold text-gray-600">Captions per language <span class="font-normal text-gray-400">(English shows as fallback)</span></label>
                  @foreach($localeMeta as $loc => $info)
                    <div class="flex items-start gap-2">
                      <span class="text-base leading-7 w-6 text-center">{{ $info['flag'] }}</span>
                      <input type="text" name="caption[{{ $loc }}]" maxlength="500"
                             placeholder="{{ $info['label'] }} caption…"
                             value="{{ $captionMap[$loc] ?? '' }}"
                             class="flex-1 rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
                    </div>
                  @endforeach
                  <div class="flex items-center justify-between gap-2 mt-auto pt-1">
                    <span class="text-[10px] text-gray-400">Added {{ $img['uploaded_at'] }}</span>
                    <button type="submit" class="px-2.5 py-1 rounded text-[11px] font-semibold bg-gray-100 text-navy-brand hover:bg-gray-200">Save captions</button>
                  </div>
                </form>

                <form method="POST" action="{{ route('admin.timetables.images.destroy', [$r['key'], $img['id']]) }}"
                      onsubmit="return confirm('Delete this image permanently?');"
                      class="border-t border-gray-100">
                  @csrf @method('DELETE')
                  <button type="submit" class="w-full px-3 py-2 text-[11px] font-semibold bg-white text-red-700 hover:bg-red-50">Delete image</button>
                </form>
              </article>
            @endforeach
          </div>
        @endif

        {{-- Upload form --}}
        <form method="POST" action="{{ route('admin.timetables.images.store', $r['key']) }}"
              enctype="multipart/form-data"
              class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 space-y-3">
          @csrf
          <div>
            <label for="img-{{ $r['key'] }}" class="block text-xs font-semibold text-gray-700 mb-1">
              Add image(s) <span class="font-normal text-gray-500">(JPG / PNG / WebP, max 8 MB each — pick multiple to add at once)</span>
            </label>
            <input id="img-{{ $r['key'] }}" type="file" name="images[]" accept="image/*" multiple required
                   class="block w-full text-sm text-gray-700 file:mr-3 file:px-3 file:py-2 file:rounded-md file:border-0 file:bg-teal-brand file:text-white file:text-sm file:font-semibold hover:file:bg-teal-600">
          </div>
          <div>
            <label for="cap-{{ $r['key'] }}" class="block text-xs font-semibold text-gray-700 mb-1">
              Caption for this batch <span class="font-normal text-gray-500">(optional — applied to every image you upload now; you can edit each one individually after)</span>
            </label>
            <input id="cap-{{ $r['key'] }}" type="text" name="caption" maxlength="500"
                   placeholder="e.g. Effective May 2026 — Weekday schedule"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
          </div>
          <button type="submit" class="w-full px-4 py-2 rounded-md bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600 transition">
            Upload to {{ $r['label'] }}
          </button>
        </form>
      </div>
    </section>
  @endforeach
</div>
@endsection
