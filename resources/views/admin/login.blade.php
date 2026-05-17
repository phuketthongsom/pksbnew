<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login · PKSB</title>
<meta name="robots" content="noindex,nofollow">
<link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
@vite(['resources/css/app.css'])
</head>
<body class="relative min-h-screen flex items-center justify-center px-4 antialiased font-sans overflow-hidden">
  {{-- Subtle photographic backdrop, gradient on top so the form stays the focus --}}
  <picture class="absolute inset-0 -z-10" aria-hidden="true">
    <source srcset="{{ asset('images/bus-mastercard.webp') }}" type="image/webp">
    <img src="{{ asset('images/bus-mastercard.jpg') }}" alt="" class="w-full h-full object-cover">
  </picture>
  <div class="absolute inset-0 -z-10 bg-gradient-to-br from-teal-brand/95 to-cyan-700/95" aria-hidden="true"></div>

  <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl p-8">
    <div class="flex flex-col items-center mb-6">
      <img src="{{ asset('images/logo.png') }}" alt="PKSB" class="h-16 w-auto mb-3">
      <h1 class="text-xl font-bold text-navy-brand">PKSB Admin</h1>
      <p class="text-sm text-gray-500">Sign in to manage destinations</p>
    </div>

    @if($errors->any())
      <div class="bg-red-50 text-red-700 text-sm rounded-md px-3 py-2 mb-4">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
      @csrf
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input id="username" name="username" type="text" autocomplete="username" required autofocus
               autocapitalize="off" autocorrect="off" spellcheck="false" inputmode="text"
               value="{{ old('username') }}"
               class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
      </div>
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required
               class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-teal-brand focus:ring-1 focus:ring-teal-brand outline-none">
      </div>
      <button type="submit" class="w-full px-4 py-2.5 rounded-md bg-teal-brand text-white font-semibold hover:bg-teal-600 transition">
        Sign in
      </button>
    </form>
  </div>
</body>
</html>
