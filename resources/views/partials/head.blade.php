<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

<script>
    window.VAPID_PUBLIC_KEY = '{{ config('webpush.vapid_public_key', '') }}';
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@if (file_exists(public_path('build/resources/js/push-notifications.js')))
    @vite('resources/js/push-notifications.js')
@endif
@fluxAppearance
