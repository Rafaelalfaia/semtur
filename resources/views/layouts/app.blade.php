<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="utf-8">
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1, viewport-fit=cover"
  >
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name','SEMTUR'))</title>
  <meta name="theme-color" content="#F6F7F9">

  @vite(['resources/css/app.css','resources/js/app.js'])

  @stack('head')
</head>
<body
  class="min-h-[100dvh] flex flex-col antialiased bg-slate-50 dark:bg-slate-900
         text-slate-900 dark:text-slate-100">

  <div id="app-shell" class="flex-1 flex flex-col min-h-[100dvh]">
    @yield('content')
  </div>

  @stack('scripts')
</body>
</html>
