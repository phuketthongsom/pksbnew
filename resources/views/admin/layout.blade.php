<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Admin') · PKSB Admin</title>
<meta name="robots" content="noindex,nofollow">
<link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
@vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 text-gray-800 antialiased font-sans min-h-screen">

@php
  $u = $adminUser ?? null;
  $roleMeta = \App\Services\UserRepository::ROLES[$u['role'] ?? ''] ?? ['label' => '—', 'color' => 'bg-gray-100 text-gray-700'];

  // One source of truth for the sidebar items + their permission gate.
  $navItems = [
    [
      'route' => 'admin.posts.index',
      'label' => 'Destinations',
      'match' => 'admin.posts.*',
      'can'   => fn () => current_admin_can('posts.manage') || current_admin_can('translations.edit'),
      'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-7.5-7-12a7 7 0 1114 0c0 4.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>',
    ],
    [
      'route' => 'admin.categories.index',
      'label' => 'Categories',
      'match' => 'admin.categories.*',
      'can'   => fn () => current_admin_can('posts.manage'),
      'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
    ],
    [
      'route' => 'admin.timetables.index',
      'label' => 'Timetables',
      'match' => 'admin.timetables.*',
      'can'   => fn () => current_admin_can('timetables.manage'),
      'icon'  => '<rect x="3" y="4" width="18" height="17" rx="2"/><path stroke-linecap="round" d="M3 9h18M8 3v4M16 3v4"/>',
    ],
    [
      'route' => 'admin.passes.index',
      'label' => 'Day Passes',
      'match' => 'admin.passes.*',
      'can'   => fn () => current_admin_can('passes.manage'),
      'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
    ],
    [
      'route' => 'admin.users.index',
      'label' => 'Users',
      'match' => 'admin.users.*',
      'can'   => fn () => current_admin_can('users.manage'),
      'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4 0-7 2-7 5v1h14v-1c0-3-3-5-7-5z"/>',
    ],
  ];
@endphp

<div class="flex min-h-screen">

  {{-- Sidebar --}}
  <aside id="adminSidebar"
         class="admin-sidebar fixed inset-y-0 left-0 z-40 w-64 bg-navy-brand text-white flex flex-col">
    {{-- Brand --}}
    <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
      <a href="{{ route('admin.posts.index') }}" class="flex items-center gap-3 min-w-0">
        <img src="{{ asset('images/logo.png') }}" alt="" class="h-9 w-auto brightness-0 invert flex-none">
        <span class="font-bold tracking-wide truncate">PKSB Admin</span>
      </a>
      <button id="adminSidebarClose" type="button"
              class="lg:hidden inline-flex items-center justify-center w-8 h-8 rounded-md hover:bg-white/10"
              aria-label="Close menu">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6"/></svg>
      </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto" aria-label="Admin">
      @foreach($navItems as $item)
        @if($item['can']())
          @php
            $active = request()->routeIs($item['match']);
            $base = 'flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition';
            $cls = $active
              ? 'bg-teal-brand text-white shadow-sm'
              : 'text-white/80 hover:bg-white/10 hover:text-white';
          @endphp
          <a href="{{ route($item['route']) }}" class="{{ $base }} {{ $cls }}" @if($active) aria-current="page" @endif>
            <svg class="w-5 h-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">{!! $item['icon'] !!}</svg>
            <span class="truncate">{{ $item['label'] }}</span>
          </a>
        @endif
      @endforeach

      <hr class="my-3 border-white/10">

      <a href="{{ route('home') }}" target="_blank" rel="noopener"
         class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-white/70 hover:bg-white/10 hover:text-white transition">
        <svg class="w-4 h-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
        </svg>
        <span class="truncate">View Site</span>
      </a>
    </nav>

    {{-- Build stamp — also fills the empty space when only 4 nav items are visible. --}}
    <div class="px-5 pb-2 text-[10px] text-white/30 select-none">
      PKSB CMS · v1.0 · {{ \Carbon\Carbon::now()->format('Y.m') }}
    </div>

    {{-- User card --}}
    @if($u)
      <div class="px-3 py-3 border-t border-white/10">
        <div class="flex items-center gap-3 px-2 py-2 rounded-md bg-white/5">
          <div class="w-9 h-9 rounded-full bg-teal-brand text-white flex items-center justify-center font-bold text-sm flex-none" aria-hidden="true">
            {{ strtoupper(mb_substr($u['name'] ?? $u['username'], 0, 1)) }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-sm font-semibold truncate">{{ $u['name'] ?? $u['username'] }}</div>
            <div class="flex items-center gap-1.5">
              <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded {{ $roleMeta['color'] }}">{{ $roleMeta['label'] }}</span>
              <span class="text-[11px] text-white/60 truncate">@<span>{{ $u['username'] }}</span></span>
            </div>
          </div>
          <form method="POST" action="{{ route('admin.logout') }}" class="flex-none">@csrf
            <button class="inline-flex items-center justify-center w-8 h-8 rounded-md text-white/60 hover:bg-white/10 hover:text-white" aria-label="Logout" title="Logout">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H9m0-9H5a2 2 0 00-2 2v14a2 2 0 002 2h4"/></svg>
            </button>
          </form>
        </div>
      </div>
    @endif
  </aside>

  {{-- Backdrop (mobile only) --}}
  <div id="adminSidebarBackdrop" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden"></div>

  {{-- Content --}}
  <div class="flex-1 lg:ml-64 min-w-0 flex flex-col">
    {{-- Top bar (mobile-only burger) --}}
    <header class="lg:hidden bg-white border-b border-gray-200 sticky top-0 z-20">
      <div class="px-4 py-3 flex items-center justify-between">
        <button id="adminSidebarOpen" type="button"
                class="inline-flex items-center justify-center w-9 h-9 rounded-md bg-gray-100 hover:bg-gray-200"
                aria-label="Open menu" aria-controls="adminSidebar">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <a href="{{ route('admin.posts.index') }}" class="flex items-center gap-2">
          <img src="{{ asset('images/logo.png') }}" alt="" class="h-7 w-auto">
          <span class="font-bold text-navy-brand text-sm">PKSB Admin</span>
        </a>
        <span class="w-9"></span>
      </div>
    </header>

    {{-- Flash + errors --}}
    @if(session('status'))
      <div class="px-4 sm:px-6 lg:px-8 pt-4">
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-md px-4 py-2 text-sm">
          {{ session('status') }}
        </div>
      </div>
    @endif

    @if($errors->any())
      <div class="px-4 sm:px-6 lg:px-8 pt-4">
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-md px-4 py-3 text-sm">
          <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      </div>
    @endif

    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
      @yield('content')
    </main>
  </div>
</div>

{{-- Confirm dialog must be in the DOM BEFORE the script reads it --}}
<div id="adminConfirm" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4" role="dialog" aria-modal="true" aria-labelledby="adminConfirmMsg">
  <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
    <p id="adminConfirmMsg" class="text-navy-brand font-semibold mb-5">Are you sure?</p>
    <div class="flex gap-2 justify-end">
      <button id="adminConfirmCancel" type="button" class="px-4 py-2 rounded-md bg-gray-100 text-navy-brand text-sm font-semibold hover:bg-gray-200">Cancel</button>
      <button id="adminConfirmOk" type="button" class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-semibold hover:bg-red-700">Confirm</button>
    </div>
  </div>
</div>

<script>
  (function () {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop');
    const openBtn = document.getElementById('adminSidebarOpen');
    const closeBtn = document.getElementById('adminSidebarClose');

    function open() {
      sidebar.classList.add('is-open');
      backdrop.classList.remove('hidden');
    }
    function close() {
      sidebar.classList.remove('is-open');
      backdrop.classList.add('hidden');
    }
    openBtn?.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    backdrop?.addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

    // Pretty confirm modal — replaces native confirm() so the destroy
    // dialog uses our own styled component (no more grey OS popup).
    const dlg = document.getElementById('adminConfirm');
    const msgEl = document.getElementById('adminConfirmMsg');
    const okBtn = document.getElementById('adminConfirmOk');
    const cancelBtn = document.getElementById('adminConfirmCancel');

    function ask(message) {
      return new Promise((resolve) => {
        msgEl.textContent = message;
        dlg.classList.remove('hidden');
        const cleanup = (val) => {
          dlg.classList.add('hidden');
          okBtn.removeEventListener('click', onOk);
          cancelBtn.removeEventListener('click', onCancel);
          dlg.removeEventListener('click', onBackdrop);
          resolve(val);
        };
        const onOk = () => cleanup(true);
        const onCancel = () => cleanup(false);
        const onBackdrop = (e) => { if (e.target === dlg) cleanup(false); };
        okBtn.addEventListener('click', onOk);
        cancelBtn.addEventListener('click', onCancel);
        dlg.addEventListener('click', onBackdrop);
        document.addEventListener('keydown', function esc(e) {
          if (e.key === 'Escape') { document.removeEventListener('keydown', esc); cleanup(false); }
        });
        okBtn.focus();
      });
    }

    document.querySelectorAll('form[onsubmit*="confirm("]').forEach((form) => {
      const m = form.getAttribute('onsubmit').match(/confirm\(['"](.*?)['"]\)/);
      const message = m ? m[1] : 'Are you sure?';
      form.removeAttribute('onsubmit');
      form.addEventListener('submit', async (e) => {
        if (form.dataset.confirmed === 'yes') return;
        e.preventDefault();
        if (await ask(message)) {
          form.dataset.confirmed = 'yes';
          form.submit();
        }
      });
    });

    // Cmd/Ctrl+S submits the page's primary form. Power-user request from the
    // UX audit — costs nothing and ops people will love it.
    document.addEventListener('keydown', (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 's') {
        const form = document.querySelector('form[method="POST"]');
        if (!form) return;
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"], button:not([type="button"]):not([type="reset"])');
        submit?.click();
      }
    });

    // Global save-state feedback: any admin form gets a one-click guard +
    // visual feedback on the primary submit button. Prevents double-uploads
    // on flaky 4G — Pream's flag from the UX audit.
    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (!(form instanceof HTMLFormElement)) return;
      const btn = form.querySelector('button[type="submit"], button:not([type="button"]):not([type="reset"])');
      if (!btn || btn.dataset.noSubmitGuard !== undefined) return;
      // Skip for buttons with explicit data-allow-resubmit (e.g. inline up/down arrows)
      if (btn.hasAttribute('data-allow-resubmit')) return;
      const original = btn.innerHTML;
      btn.disabled = true;
      btn.dataset.original = original;
      btn.innerHTML = `
        <span class="inline-flex items-center gap-2">
          <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="9" stroke-opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"/></svg>
          Saving…
        </span>`;
      // Restore if the user navigates back or the form errors out and stays put
      setTimeout(() => { btn.disabled = false; btn.innerHTML = original; }, 8000);
    });
  })();
</script>


</body>
</html>
