@extends('admin.layout')
@section('title', 'Categories')

@section('content')
<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
  <div>
    <h1 class="text-2xl font-bold text-navy-brand">Categories</h1>
    <p class="text-sm text-gray-500 mt-0.5">Manage the blog destination categories shown on the public site.</p>
  </div>
</div>

@if(session('status'))
  <div class="mb-4 px-4 py-3 rounded-lg bg-teal-50 text-teal-800 text-sm font-medium ring-1 ring-teal-200">
    {{ session('status') }}
  </div>
@endif

{{-- Existing categories --}}
@if(empty($categories))
  <div class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-10 text-center text-gray-500 text-sm mb-6">
    No categories yet. Add one below.
  </div>
@else
  <div class="space-y-3 mb-8">
    @php
      $allPosts = app(\App\Services\PostsRepository::class)->all(includeScheduled: true);
      $postCounts = [];
      foreach ($allPosts as $p) {
          $cat = $p['category'] ?? '';
          $postCounts[$cat] = ($postCounts[$cat] ?? 0) + 1;
      }
    @endphp

    @foreach($categories as $cat)
      <div class="bg-white rounded-2xl shadow ring-1 ring-gray-100 overflow-hidden">

        {{-- Summary row --}}
        <div class="flex items-center gap-4 px-5 py-4">
          {{-- Thumbnail or color swatch --}}
          @if(!empty($cat['hero_image']))
            <div class="w-14 h-10 rounded-lg overflow-hidden flex-none ring-1 ring-gray-200">
              <img src="{{ asset($cat['hero_image']) }}" alt="{{ $cat['name'] }}"
                   class="w-full h-full object-cover">
            </div>
          @else
            <div class="w-10 h-10 rounded-full flex-none ring-1 ring-gray-200"
                 style="background-color: {{ $cat['accent'] ?? '#01aaa8' }}"></div>
          @endif

          <div class="flex-1 min-w-0">
            <p class="font-semibold text-navy-brand">{{ $cat['name'] }}</p>
            <p class="text-sm text-gray-500 truncate">{{ $cat['tagline'] ?? '' }}</p>
          </div>

          <div class="flex items-center gap-3 flex-none">
            <span class="text-xs text-gray-400 bg-gray-100 rounded-full px-2.5 py-1">
              {{ $postCounts[$cat['slug']] ?? 0 }} post{{ ($postCounts[$cat['slug']] ?? 0) === 1 ? '' : 's' }}
            </span>
            <button type="button"
                    onclick="document.getElementById('edit-{{ $cat['slug'] }}').classList.toggle('hidden')"
                    class="text-sm font-semibold text-teal-brand hover:text-teal-700 transition">
              Edit
            </button>
            <form method="POST" action="{{ route('admin.categories.destroy', $cat['slug']) }}"
                  onsubmit="return confirm('Delete category &quot;{{ addslashes($cat['name']) }}&quot;? Posts will not be deleted but will lose this category.')">
              @csrf @method('DELETE')
              <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-700 transition">
                Delete
              </button>
            </form>
          </div>
        </div>

        {{-- Inline edit form --}}
        <div id="edit-{{ $cat['slug'] }}" class="hidden px-5 py-5 bg-gray-50 border-t border-gray-100">
          <form method="POST" action="{{ route('admin.categories.update', $cat['slug']) }}"
                enctype="multipart/form-data"
                class="space-y-4">
            @csrf @method('PUT')

            {{-- Row 1: Name, Tagline, Color --}}
            <div class="grid sm:grid-cols-3 gap-3 items-end">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Name</label>
                <input name="name" required maxlength="100"
                       value="{{ $cat['name'] }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tagline</label>
                <input name="tagline" maxlength="200"
                       value="{{ $cat['tagline'] ?? '' }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Accent color</label>
                <div class="flex items-center gap-2">
                  <input type="color" name="accent"
                         value="{{ $cat['accent'] ?? '#01aaa8' }}"
                         class="h-9 w-14 rounded border border-gray-300 p-0.5 cursor-pointer edit-color-picker">
                  <input type="text" name="accent_text"
                         value="{{ $cat['accent'] ?? '#01aaa8' }}"
                         maxlength="7"
                         class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm font-mono focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none edit-color-text">
                </div>
              </div>
            </div>

            {{-- Row 2: Thumbnail --}}
            <div class="flex items-start gap-4">
              {{-- Current thumbnail preview --}}
              <div class="flex-none">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Thumbnail</label>
                @if(!empty($cat['hero_image']))
                  <div class="w-28 h-20 rounded-lg overflow-hidden ring-1 ring-gray-200 bg-gray-100 mb-2" id="preview-wrap-{{ $cat['slug'] }}">
                    <img id="preview-{{ $cat['slug'] }}"
                         src="{{ asset($cat['hero_image']) }}"
                         alt="thumbnail" class="w-full h-full object-cover">
                  </div>
                @else
                  <div class="w-28 h-20 rounded-lg overflow-hidden ring-1 ring-gray-200 bg-gray-100 mb-2 flex items-center justify-center" id="preview-wrap-{{ $cat['slug'] }}">
                    <img id="preview-{{ $cat['slug'] }}" src="" alt="" class="w-full h-full object-cover hidden">
                    <span id="no-thumb-{{ $cat['slug'] }}" class="text-xs text-gray-400">No image</span>
                  </div>
                @endif
                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white border border-gray-300 text-xs font-semibold text-gray-700 hover:border-teal-brand hover:text-teal-brand cursor-pointer transition">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                  Choose image
                  <input type="file" name="thumbnail" accept="image/*" class="sr-only"
                         onchange="previewThumb(this, '{{ $cat['slug'] }}')">
                </label>
                <p class="mt-1 text-[11px] text-gray-400">JPEG/PNG/WebP · max 5 MB</p>
              </div>

              {{-- Save button on the right --}}
              <div class="flex-1 flex justify-end items-end h-full pt-6">
                <button type="submit"
                        class="px-5 py-2 rounded-md bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600 transition">
                  Save changes
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>
    @endforeach
  </div>
@endif

{{-- Add new category --}}
<div class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-5">
  <h2 class="text-base font-bold text-navy-brand mb-4">Add category</h2>
  <form method="POST" action="{{ route('admin.categories.store') }}"
        enctype="multipart/form-data"
        class="space-y-4">
    @csrf

    {{-- Row 1: Name, Tagline, Color --}}
    <div class="grid sm:grid-cols-3 gap-3 items-end">
      <div>
        <label for="new-name" class="block text-xs font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
        <input id="new-name" name="name" required maxlength="100"
               placeholder="e.g. Nightlife"
               value="{{ old('name') }}"
               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
      </div>
      <div>
        <label for="new-tagline" class="block text-xs font-semibold text-gray-700 mb-1">Tagline</label>
        <input id="new-tagline" name="tagline" maxlength="200"
               placeholder="Short one-liner for the category card"
               value="{{ old('tagline') }}"
               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
      </div>
      <div>
        <label for="new-accent" class="block text-xs font-semibold text-gray-700 mb-1">Accent color</label>
        <div class="flex items-center gap-2">
          <input type="color" id="new-accent" name="accent"
                 value="{{ old('accent', '#01aaa8') }}"
                 class="h-9 w-14 rounded border border-gray-300 p-0.5 cursor-pointer">
          <input type="text" name="accent_text"
                 id="new-accent-text"
                 value="{{ old('accent', '#01aaa8') }}"
                 maxlength="7"
                 class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm font-mono focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
        </div>
      </div>
    </div>

    {{-- Row 2: Thumbnail + Add button --}}
    <div class="flex items-start gap-4">
      <div class="flex-none">
        <label class="block text-xs font-semibold text-gray-700 mb-1">Thumbnail <span class="text-gray-400 font-normal">(optional)</span></label>
        <div class="w-28 h-20 rounded-lg overflow-hidden ring-1 ring-gray-200 bg-gray-100 mb-2 flex items-center justify-center">
          <img id="new-preview" src="" alt="" class="w-full h-full object-cover hidden">
          <span id="new-no-thumb" class="text-xs text-gray-400">No image</span>
        </div>
        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white border border-gray-300 text-xs font-semibold text-gray-700 hover:border-teal-brand hover:text-teal-brand cursor-pointer transition">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
          Choose image
          <input type="file" name="thumbnail" accept="image/*" class="sr-only"
                 onchange="previewNewThumb(this)">
        </label>
        <p class="mt-1 text-[11px] text-gray-400">JPEG/PNG/WebP · max 5 MB</p>
      </div>

      <div class="flex-1 flex justify-end items-end pt-6">
        <button type="submit"
                class="px-5 py-2 rounded-md bg-teal-brand text-white text-sm font-semibold hover:bg-teal-600 transition">
          Add category
        </button>
      </div>
    </div>

  </form>
</div>

<script>
// Preview selected thumbnail for existing category edit forms
function previewThumb(input, slug) {
  if (!input.files || !input.files[0]) return;
  const url = URL.createObjectURL(input.files[0]);
  const img = document.getElementById('preview-' + slug);
  const noThumb = document.getElementById('no-thumb-' + slug);
  img.src = url;
  img.classList.remove('hidden');
  if (noThumb) noThumb.classList.add('hidden');
}

// Preview for new category form
function previewNewThumb(input) {
  if (!input.files || !input.files[0]) return;
  const url = URL.createObjectURL(input.files[0]);
  const img = document.getElementById('new-preview');
  const noThumb = document.getElementById('new-no-thumb');
  img.src = url;
  img.classList.remove('hidden');
  if (noThumb) noThumb.classList.add('hidden');
}

// Sync color picker ↔ text field for edit forms
document.querySelectorAll('.edit-color-picker').forEach(picker => {
  const txt = picker.nextElementSibling;
  picker.addEventListener('input', () => { if (txt) txt.value = picker.value; });
  if (txt) txt.addEventListener('input', () => {
    if (/^#[0-9a-fA-F]{3,6}$/.test(txt.value)) picker.value = txt.value;
  });
});

// Sync for add-new form
const newPicker = document.getElementById('new-accent');
const newTxt    = document.getElementById('new-accent-text');
if (newPicker && newTxt) {
  newPicker.addEventListener('input', () => { newTxt.value = newPicker.value; });
  newTxt.addEventListener('input', () => {
    if (/^#[0-9a-fA-F]{3,6}$/.test(newTxt.value)) newPicker.value = newTxt.value;
  });
}
</script>
@endsection
