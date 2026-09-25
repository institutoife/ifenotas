<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador - IFE Notas</title>
    <link rel="icon" href="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" type="image/svg+xml">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100svh;font-family:"Segoe UI",Arial,sans-serif;color:#375f7a;background:radial-gradient(circle at 0 0,rgba(38,186,165,.18),transparent 30rem),#f3fbfa}.page{width:min(920px,100%);margin:auto;padding:10px 10px 28px}.page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px}.logo{display:block;width:min(190px,48vw);height:auto;object-fit:contain}
        .page-head .creator-link{margin:0 0 0 auto;flex-shrink:0;font-size:.78rem}
        .page-head .logo{width:clamp(90px,30vw,190px);min-width:0}
        .brand-home{position:relative;padding-left:28px;flex-shrink:0}.home-icon{position:absolute;top:0;left:0;width:26px;height:26px;display:grid;place-items:center;color:#375f7a;background:#fff;border:1px solid #d6e8e5;border-radius:50%;text-decoration:none}.home-icon:hover{background:#e5f8f5}.home-icon:focus-visible{outline:3px solid #26baa5;outline-offset:2px}
        @media(max-width:480px){.page-head{gap:8px}.page-head .creator-link{gap:7px;padding:7px 9px;min-height:60px;font-size:.66rem}.page-head .tiktok-live-avatar{flex-basis:44px;width:44px;height:44px}.page-head .tiktok-live-picture{width:36px;height:36px}}
    </style>
</head>
<body>
    <main class="page">
        <header class="page-head"><div class="brand-home"><a class="home-icon" href="{{ Auth::check() ? route('dashboard') : route('auth') }}" aria-label="{{ Auth::check() ? 'Mi cuenta' : 'Inicio' }}" title="{{ Auth::check() ? 'Mi cuenta' : 'Inicio' }}"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-7 9 7M5 9v12h5v-7h4v7h5V9"/></svg></a><img class="logo" src="{{ asset('images/logo-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Logo de IFE Educabol"></div>@include('partials.creator-live-link')</header>
        @include('partials.grade-simulator', ['creatorInHeader' => true])
    </main>
</body>
</html>
