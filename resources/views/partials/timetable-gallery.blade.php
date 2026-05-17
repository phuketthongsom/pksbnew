{{--
  Timetable image gallery partial — vertical stack.
  Props:
    $images      — array of image records from TimetableRepository
    $routeLabel  — translated route name for alt text fallback
--}}

<div class="space-y-6">
  @foreach($images as $idx => $img)
    @php
      $caption = \App\Services\TimetableRepository::localizedCaption($img['caption'] ?? null);
      $src     = asset($img['path']) . '?v=' . md5($img['uploaded_at'] ?? '');
    @endphp
    <figure class="bg-white rounded-2xl ring-1 ring-gray-100 overflow-hidden shadow">
      @if(count($images) > 1)
        <div class="px-5 py-2.5 border-b border-gray-100 flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-navy-brand text-white text-[10px] font-bold">{{ $idx + 1 }}</span>
          @if($caption)
            <span class="text-xs text-gray-600">{{ $caption }}</span>
          @endif
        </div>
      @endif
      <a href="{{ asset($img['path']) }}" target="_blank" title="View full size">
        <img src="{{ $src }}" alt="{{ $caption ?: $routeLabel }}"
             class="w-full h-auto block" loading="{{ $idx === 0 ? 'eager' : 'lazy' }}">
      </a>
      @if($caption && count($images) === 1)
        <figcaption class="px-5 py-3 text-xs text-gray-600 border-t border-gray-100 bg-gray-50">{{ $caption }}</figcaption>
      @endif
    </figure>
  @endforeach
</div>
