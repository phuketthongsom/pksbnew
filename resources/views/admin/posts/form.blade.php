@extends('admin.layout')
@section('title', $post ? 'Edit · '.$post['title'] : 'New Destination')

@php
  $isNew = $post === null;
  $action = $isNew ? route('admin.posts.store') : route('admin.posts.update', $post['slug']);
  $locales = [
    'en' => ['flag' => '🇬🇧', 'label' => 'English'],
    'th' => ['flag' => '🇹🇭', 'label' => 'ไทย'],
    'zh' => ['flag' => '🇨🇳', 'label' => '中文'],
    'ru' => ['flag' => '🇷🇺', 'label' => 'Русский'],
  ];
  $tx = $post['translations'] ?? [];
  // Seed English from legacy top-level fields if no translations.en yet
  if ($post && empty($tx['en'])) {
      $tx['en'] = [
          'title' => $post['title'] ?? '',
          'excerpt' => $post['excerpt'] ?? '',
          'body' => $post['body'] ?? '',
          'nearest_stop' => $post['nearest_stop'] ?? '',
      ];
  }
@endphp

@section('content')
<div class="flex items-center justify-between mb-4 sm:mb-6">
  <div>
    <a href="{{ route('admin.posts.index') }}" class="text-sm text-gray-500 hover:text-teal-brand">← All destinations</a>
    <h1 class="text-2xl font-bold text-navy-brand mt-1">{{ $isNew ? 'New Destination' : 'Edit Destination' }}</h1>
  </div>
  @if(!$isNew)
    <a href="{{ route('blog.show', $post['slug']) }}" target="_blank" class="text-sm text-teal-brand hover:text-teal-700">View on site ↗</a>
  @endif
</div>

{{-- Mobile-only quick jump nav. Desktop sees everything inline. --}}
<nav class="sticky top-14 z-10 bg-gray-100/95 backdrop-blur -mx-4 sm:-mx-6 lg:hidden mb-4 px-4 py-2 border-b border-gray-200" aria-label="Form sections">
  <div class="flex gap-1 text-xs font-semibold overflow-x-auto">
    <a href="#sec-content" class="px-3 py-1.5 rounded-full bg-teal-brand text-white whitespace-nowrap">Content</a>
    @if(current_admin_can('posts.manage'))
      <a href="#sec-settings" class="px-3 py-1.5 rounded-full bg-white text-navy-brand ring-1 ring-gray-200 whitespace-nowrap">Settings</a>
      <a href="#sec-photos" class="px-3 py-1.5 rounded-full bg-white text-navy-brand ring-1 ring-gray-200 whitespace-nowrap">Photos</a>
      @if(!$isNew && !empty($post['gallery']))
        <a href="#sec-gallery" class="px-3 py-1.5 rounded-full bg-white text-navy-brand ring-1 ring-gray-200 whitespace-nowrap">Gallery ({{ count($post['gallery']) }})</a>
      @endif
    @endif
  </div>
</nav>

@if($errors->any())
  <div class="mb-4 rounded-xl bg-red-50 ring-1 ring-red-200 p-4">
    <p class="text-sm font-semibold text-red-700 mb-1">Please fix the following errors:</p>
    <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6 pb-20 lg:pb-0">
  @csrf
  @if(!$isNew) @method('PUT') @endif

  {{-- Main fields --}}
  <div id="sec-content" class="lg:col-span-2 space-y-5 bg-white rounded-2xl shadow ring-1 ring-gray-100 p-4 sm:p-6 scroll-mt-20">

    @if($isNew)
      <div>
        <label for="f-slug" class="block text-sm font-semibold text-gray-700 mb-1">Slug <span class="font-normal text-gray-500">(optional — auto from English title)</span></label>
        <input id="f-slug" name="slug" maxlength="200" placeholder="phuket-old-town"
               value="{{ old('slug') }}"
               class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
      </div>
    @endif

    {{-- Language tabs --}}
    <div>
      <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
        <span class="block text-sm font-semibold text-gray-700">Translations</span>
        <div class="inline-flex rounded-md ring-1 ring-gray-300 overflow-x-auto text-xs font-semibold w-full sm:w-auto" role="tablist" aria-label="Language" data-no-submit-guard>
          @foreach($locales as $code => $info)
            <button type="button" data-langtab="{{ $code }}" data-allow-resubmit
                    class="lang-tab px-3 py-2 sm:py-1.5 flex-1 sm:flex-none whitespace-nowrap {{ $code === 'en' ? 'bg-teal-brand text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border-r border-gray-200 last:border-r-0">
              {{ $info['flag'] }} {{ strtoupper($code) }}
            </button>
          @endforeach
        </div>
      </div>

      @foreach($locales as $code => $info)
        {{-- lang attr lets screen-readers switch voices and tells Chrome which dictionary to use --}}
        <div lang="{{ $code }}" class="lang-pane space-y-4 {{ $code !== 'en' ? 'hidden' : '' }}" data-langpane="{{ $code }}">
          <div class="flex items-start justify-between gap-2">
            <p class="text-xs text-gray-500">
              {{ $info['flag'] }} <strong>{{ $info['label'] }}</strong>
              @if($code === 'en')
                — required (English is the fallback for empty translations).
              @else
                — optional. Leave blank to fall back to English.
              @endif
            </p>
          </div>
          <div>
            <label for="f-title-{{ $code }}" class="block text-xs font-semibold text-gray-700 mb-1">Title</label>
            <input id="f-title-{{ $code }}" name="translations[{{ $code }}][title]" maxlength="200"
                   {{ $code === 'en' ? 'required' : '' }}
                   value="{{ old("translations.$code.title", $tx[$code]['title'] ?? '') }}"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
          </div>
          <div>
            <label for="f-excerpt-{{ $code }}" class="block text-xs font-semibold text-gray-700 mb-1">Excerpt</label>
            <textarea id="f-excerpt-{{ $code }}" name="translations[{{ $code }}][excerpt]" rows="2" maxlength="300"
                      {{ $code === 'en' ? 'required' : '' }}
                      class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">{{ old("translations.$code.excerpt", $tx[$code]['excerpt'] ?? '') }}</textarea>
          </div>
          <div>
            <label for="f-stop-{{ $code }}" class="block text-xs font-semibold text-gray-700 mb-1">Nearest PKSB stop</label>
            <input id="f-stop-{{ $code }}" name="translations[{{ $code }}][nearest_stop]" maxlength="200"
                   {{ $code === 'en' ? 'required' : '' }}
                   value="{{ old("translations.$code.nearest_stop", $tx[$code]['nearest_stop'] ?? '') }}"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
          </div>

          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-semibold text-gray-700">Body</label>
              <div class="inline-flex rounded-md ring-1 ring-gray-300 overflow-hidden text-xs font-semibold" role="tablist">
                <button type="button" data-modetab="visual" data-target="{{ $code }}"
                        class="mode-tab px-3 py-1 bg-teal-brand text-white">Visual</button>
                <button type="button" data-modetab="html" data-target="{{ $code }}"
                        class="mode-tab px-3 py-1 bg-white text-gray-700 hover:bg-gray-50">HTML</button>
              </div>
            </div>
            <div class="quill-wrap" data-quill-for="{{ $code }}">
              <div class="quill-editor bg-white rounded-md border border-gray-300 min-h-[260px]"></div>
            </div>
            <textarea name="translations[{{ $code }}][body]" rows="12" data-body-for="{{ $code }}"
                      {{ $code === 'en' ? 'required' : '' }}
                      class="hidden w-full rounded-md border border-gray-300 px-3 py-2 font-mono text-sm focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">{{ old("translations.$code.body", $tx[$code]['body'] ?? '') }}</textarea>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Sidebar --}}
  <aside class="space-y-5">
    @if(current_admin_can('posts.manage'))
      <div id="sec-settings" class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-6 space-y-4 scroll-mt-20">
        @php $allCategories = app(\App\Services\CategoriesRepository::class)->all(); @endphp
        <div>
          <label for="f-category" class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
          <select id="f-category" name="category"
                  class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
            <option value="">— none —</option>
            @foreach($allCategories as $cat)
              @php $currentCat = old('category', $post['category'] ?? ''); @endphp
              <option value="{{ $cat['slug'] }}" @selected($currentCat === $cat['slug'])>{{ $cat['name'] }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label for="f-area" class="block text-sm font-semibold text-gray-700 mb-1">Area</label>
          <input id="f-area" name="area" required maxlength="100" placeholder="Patong, Old Town…"
                 value="{{ old('area', $post['area'] ?? '') }}"
                 class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
        </div>
        <div>
          <label for="f-route" class="block text-sm font-semibold text-gray-700 mb-1">Recommended route</label>
          <select id="f-route" name="route_recommendation" required
                  class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
            @php $current = old('route_recommendation', $post['route_recommendation'] ?? 'rawai'); @endphp
            <option value="rawai"  @selected($current==='rawai')>Airport → Rawai</option>
            <option value="patong" @selected($current==='patong')>Bus Terminal → Patong</option>
            <option value="dragon" @selected($current==='dragon')>Dragon Line</option>
            <option value="all"    @selected($current==='all')>All Routes</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label for="f-min" class="block text-sm font-semibold text-gray-700 mb-1">Reading min</label>
            <input id="f-min" name="reading_minutes" type="number" min="1" max="60" required
                   value="{{ old('reading_minutes', $post['reading_minutes'] ?? 5) }}"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
          </div>
          <div>
            <label for="f-date" class="block text-sm font-semibold text-gray-700 mb-1">Published <span class="font-normal text-gray-500">(future date = scheduled)</span></label>
            <input id="f-date" name="published_at" type="date" required
                   value="{{ old('published_at', $post['published_at'] ?? date('Y-m-d')) }}"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
          </div>
        </div>
      </div>

      <div id="sec-photos" class="bg-white rounded-2xl shadow ring-1 ring-gray-100 p-4 sm:p-6 scroll-mt-20">
        <label for="f-photos" class="block text-sm font-semibold text-gray-700 mb-1">Photos {{ $isNew ? '' : '(add more)' }}</label>

        {{-- Drag-drop zone (clicks open the file dialog) --}}
        <label for="f-photos" id="photoDrop"
               class="relative block rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center cursor-pointer hover:bg-gray-100 transition">
          <svg class="mx-auto w-8 h-8 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.9A5.5 5.5 0 0117.9 9.3 4.5 4.5 0 0117 18M12 12v9m-3-3l3 3 3-3"/></svg>
          <span class="block text-sm text-gray-700"><strong class="text-teal-brand">Tap to choose</strong> or drag photos here</span>
          <span class="block text-xs text-gray-500 mt-1">JPG / PNG / WebP, up to 8 MB each. First photo becomes the cover.</span>
          <input id="f-photos" name="photos[]" type="file" accept="image/*" multiple class="sr-only">
        </label>

        <div id="photoPreviews" class="grid grid-cols-3 gap-2 mt-3 hidden"></div>
      </div>
      <script>
        // Live thumbnail previews + drag-drop + multi-select accumulation.
        (function () {
          const input = document.getElementById('f-photos');
          const grid = document.getElementById('photoPreviews');
          const drop = document.getElementById('photoDrop');
          if (!input || !grid || !drop) return;

          // Keep a running DataTransfer so selecting files multiple times accumulates them.
          let dt = new DataTransfer();

          function mergeFiles(newFiles) {
            const existing = new Set(Array.from(dt.files).map(f => f.name + f.size));
            Array.from(newFiles).forEach(f => {
              if (f.type.startsWith('image/') && !existing.has(f.name + f.size)) {
                dt.items.add(f);
              }
            });
            input.files = dt.files;
            renderPreviews();
          }

          function renderPreviews() {
            grid.innerHTML = '';
            const files = Array.from(dt.files);
            grid.classList.toggle('hidden', files.length === 0);
            // Update drop zone label
            drop.querySelector('span').textContent = files.length
              ? `${files.length} photo${files.length > 1 ? 's' : ''} selected — tap to add more`
              : 'Tap to choose or drag photos here';
            files.forEach((file, i) => {
              const url = URL.createObjectURL(file);
              const tile = document.createElement('div');
              tile.className = 'relative aspect-square rounded-md overflow-hidden ring-1 ring-gray-200 bg-gray-50 group';
              tile.innerHTML = `<img src="${url}" class="w-full h-full object-cover" alt="">
                ${i === 0 ? '<span class="absolute top-1 left-1 px-1.5 py-0.5 text-[9px] font-bold bg-yellow-400 text-gray-900 rounded">Cover</span>' : ''}
                <button type="button" data-idx="${i}" class="remove-photo absolute top-1 right-1 w-5 h-5 rounded-full bg-red-600 text-white text-xs leading-none hidden group-hover:flex items-center justify-center">✕</button>
                <span class="absolute bottom-0 inset-x-0 px-1 py-0.5 text-[9px] text-white bg-black/60 truncate">${file.name}</span>`;
              grid.appendChild(tile);
            });
            // Remove individual photo
            grid.querySelectorAll('.remove-photo').forEach(btn => {
              btn.addEventListener('click', () => {
                const idx = parseInt(btn.dataset.idx);
                const newDt = new DataTransfer();
                Array.from(dt.files).forEach((f, i) => { if (i !== idx) newDt.items.add(f); });
                dt = newDt;
                input.files = dt.files;
                renderPreviews();
              });
            });
          }

          input.addEventListener('change', () => mergeFiles(input.files));

          ['dragenter', 'dragover'].forEach((evt) => drop.addEventListener(evt, (e) => {
            e.preventDefault();
            drop.classList.add('border-teal-brand', 'bg-teal-50');
          }));
          ['dragleave', 'drop'].forEach((evt) => drop.addEventListener(evt, (e) => {
            e.preventDefault();
            drop.classList.remove('border-teal-brand', 'bg-teal-50');
          }));
          drop.addEventListener('drop', (e) => {
            const files = e.dataTransfer?.files;
            if (!files || !files.length) return;
            mergeFiles(files);
          });
        })();
      </script>
    @else
      <div class="bg-amber-50 ring-1 ring-amber-200 rounded-2xl p-5 text-sm text-amber-900">
        You're signed in as a <strong>translator</strong>. You can edit per-language fields above; metadata and photo uploads are read-only.
      </div>
    @endif

    {{-- Desktop: save button in sidebar (visible above the fold thanks to sticky parent below). --}}
    <button type="submit" class="hidden lg:block w-full px-4 py-2.5 rounded-md bg-teal-brand text-white font-semibold hover:bg-teal-600 transition">
      {{ $isNew ? 'Create destination' : 'Save changes' }}
    </button>
  </aside>

  {{-- Mobile-only sticky save bar — always visible at the bottom of the screen,
       so the operator never has to scroll back to the top to commit changes. --}}
  <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-gray-200 px-4 py-3 shadow-lg">
    <button type="submit" class="w-full px-4 py-2.5 rounded-md bg-teal-brand text-white font-semibold hover:bg-teal-600 transition">
      {{ $isNew ? 'Create destination' : 'Save changes' }}
    </button>
  </div>
</form>

@if(!$isNew)
  <section id="sec-gallery" class="mt-10 bg-white rounded-2xl shadow ring-1 ring-gray-100 p-6 scroll-mt-20">
    <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
      <h2 class="text-lg font-bold text-navy-brand">Gallery ({{ count($post['gallery'] ?? []) }})</h2>
      <p class="text-sm text-gray-600">To add more photos, use the <strong>Photos</strong> field above.</p>
    </div>

    @if(empty($post['gallery']))
      <p class="text-sm text-gray-500 text-center py-8 bg-gray-50 rounded-lg">No photos yet. Use the <strong>Photos</strong> field above to upload.</p>
    @else
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($post['gallery'] as $img)
          @php $isCover = ($post['cover'] ?? '') === $img; @endphp
          <div class="rounded-lg overflow-hidden ring-1 ring-gray-200 bg-gray-50 flex flex-col">
            <div class="relative aspect-square bg-gray-100">
              <img src="{{ asset($img) }}" alt="" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
              @if($isCover)
                <span class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-yellow-brand text-navy-brand shadow">★ Cover</span>
              @endif
            </div>
            <div class="flex">
              <form method="POST" action="{{ route('admin.posts.cover', $post['slug']) }}" class="flex-1">
                @csrf
                <input type="hidden" name="path" value="{{ $img }}">
                <button type="submit" {{ $isCover ? 'disabled' : '' }}
                        class="w-full px-2 py-2 text-xs font-semibold border-r border-gray-200
                               {{ $isCover ? 'bg-yellow-50 text-yellow-700 cursor-default' : 'bg-white text-navy-brand hover:bg-gray-50' }}">
                  {{ $isCover ? '★ Cover' : 'Set as cover' }}
                </button>
              </form>
              <form method="POST" action="{{ route('admin.posts.photos.destroy', $post['slug']) }}"
                    onsubmit="return confirm('Delete this photo permanently?');" class="flex-1">
                @csrf @method('DELETE')
                <input type="hidden" name="path" value="{{ $img }}">
                <button type="submit" class="w-full px-2 py-2 text-xs font-semibold bg-white text-red-700 hover:bg-red-50">
                  Delete
                </button>
              </form>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </section>
@endif

{{-- Quill 2 (free, MIT, no API key) — one instance per language.
     SECURITY: SRI hashes pinned so a compromised jsDelivr can't inject JS into
     the admin context. Bump these when upgrading the version. --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"
      integrity="sha384-ecIckRi4QlKYya/FQUbBUjS4qp65jF/J87Guw5uzTbO1C1Jfa/6kYmd6dXUF6D7i"
      crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"
        integrity="sha384-utBUCeG4SYaCm4m7GQZYr8Hy8Fpy3V4KGjBZaf4WTKOcwhCYpt/0PfeEe3HNlwx8"
        crossorigin="anonymous"></script>
<style>
  .quill-editor .ql-editor { min-height: 240px; font-size: 15px; }
  .quill-editor .ql-editor h2 { font-size: 1.4em; font-weight: 700; margin: .8em 0 .4em; color: #1a1a2e; }
  .quill-editor .ql-editor p { margin: .5em 0; }
  .ql-toolbar.ql-snow { border-color: #d1d5db; border-top-left-radius: .375rem; border-top-right-radius: .375rem; background: #fafafa; }
  .ql-container.ql-snow { border-color: #d1d5db; border-bottom-left-radius: .375rem; border-bottom-right-radius: .375rem; }
</style>
<script>
(function () {
  const editors = {};

  // Build one Quill per language pane
  document.querySelectorAll('.quill-wrap').forEach((wrap) => {
    const code = wrap.dataset.quillFor;
    const ta = document.querySelector(`textarea[data-body-for="${code}"]`);
    const editorEl = wrap.querySelector('.quill-editor');
    const q = new Quill(editorEl, {
      theme: 'snow',
      placeholder: 'Write the destination guide here…',
      modules: {
        toolbar: [
          [{ header: [false, 2, 3] }],
          ['bold', 'italic', 'underline'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          ['link', 'blockquote'],
          ['clean'],
        ],
      },
    });
    q.clipboard.dangerouslyPasteHTML(ta.value || '');
    q.on('text-change', () => {
      const html = q.root.innerHTML.trim();
      ta.value = (html === '<p><br></p>') ? '' : html;
    });
    editors[code] = { quill: q, ta, wrap };
  });

  // Language tab switching
  const langTabs = document.querySelectorAll('.lang-tab');
  const langPanes = document.querySelectorAll('.lang-pane');
  langTabs.forEach((btn) => {
    btn.addEventListener('click', () => {
      const code = btn.dataset.langtab;
      langTabs.forEach((b) => {
        const active = b.dataset.langtab === code;
        b.classList.toggle('bg-teal-brand', active);
        b.classList.toggle('text-white', active);
        b.classList.toggle('bg-white', !active);
        b.classList.toggle('text-gray-700', !active);
      });
      langPanes.forEach((p) => p.classList.toggle('hidden', p.dataset.langpane !== code));
    });
  });

  // Visual / HTML mode switching per pane
  document.querySelectorAll('.mode-tab').forEach((btn) => {
    btn.addEventListener('click', () => {
      const code = btn.dataset.target;
      const mode = btn.dataset.modetab; // visual | html
      const editor = editors[code];
      if (!editor) return;

      if (mode === 'html') {
        // Sync from Quill before showing textarea
        const html = editor.quill.root.innerHTML.trim();
        editor.ta.value = (html === '<p><br></p>') ? '' : html;
        editor.wrap.classList.add('hidden');
        editor.ta.classList.remove('hidden');
      } else {
        // Repaint Quill from textarea (so HTML edits stick)
        editor.quill.clipboard.dangerouslyPasteHTML(editor.ta.value || '');
        editor.wrap.classList.remove('hidden');
        editor.ta.classList.add('hidden');
      }
      // Toggle button styles within this pane
      const pane = document.querySelector(`.lang-pane[data-langpane="${code}"]`);
      pane.querySelectorAll('.mode-tab').forEach((b) => {
        const active = b.dataset.modetab === mode;
        b.classList.toggle('bg-teal-brand', active);
        b.classList.toggle('text-white', active);
        b.classList.toggle('bg-white', !active);
        b.classList.toggle('text-gray-700', !active);
      });
    });
  });

  // Final sync on submit so visual-mode content lands in the textarea
  document.querySelector('form').addEventListener('submit', () => {
    Object.values(editors).forEach((e) => {
      if (!e.wrap.classList.contains('hidden')) {
        const html = e.quill.root.innerHTML.trim();
        e.ta.value = (html === '<p><br></p>') ? '' : html;
      }
    });
  });
})();
</script>
@endsection
