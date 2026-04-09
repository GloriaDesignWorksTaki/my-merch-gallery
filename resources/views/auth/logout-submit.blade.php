<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('auth.loggingOut', [], app()->getLocale()) }}</title>
  </head>
  <body>
    <form id="logout-form" method="POST" action="/logout">
      @csrf
      <noscript>
        <p><button type="submit">{{ __('auth.logoutContinue', [], app()->getLocale()) }}</button></p>
      </noscript>
    </form>
    <script>
      document.getElementById('logout-form').submit();
    </script>
  </body>
</html>
