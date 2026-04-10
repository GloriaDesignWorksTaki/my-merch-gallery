@php
  $htmlTheme = auth()->check() ? (auth()->user()->theme ?? 'light') : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"@if($htmlTheme !== null) data-theme="{{ $htmlTheme }}"@endif @if($htmlTheme === 'dark') class="dark"@endif>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
      (function () {
        var el = document.documentElement;
        if (el.hasAttribute('data-theme')) return;
        var t = 'light';
        try {
          t = localStorage.getItem('mg-theme') || 'light';
        } catch (e) {}
        if (t !== 'light' && t !== 'dark' && t !== 'primary') t = 'light';
        el.setAttribute('data-theme', t);
        if (t === 'dark') {
          el.classList.add('dark');
        } else {
          el.classList.remove('dark');
        }
        try {
          localStorage.setItem('mg-theme', t);
        } catch (e) {}
      })();
    </script>
    <title inertia>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preload" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"></noscript>
    @routes
    @vite(['resources/js/app.ts'])
    @inertiaHead
  </head>
  <body class="font-sans antialiased">
    @inertia
  </body>
</html>