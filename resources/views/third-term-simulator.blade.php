<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador - IFE Notas</title>
    <link rel="icon" href="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" type="image/svg+xml">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100svh;font-family:"Segoe UI",Arial,sans-serif;color:#375f7a;background:radial-gradient(circle at 0 0,rgba(38,186,165,.18),transparent 30rem),#f3fbfa}.page{width:min(920px,100%);margin:auto;padding:10px 10px 28px}.page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px}.logo{display:block;width:min(190px,48vw);height:auto;object-fit:contain}.back{min-height:40px;display:inline-flex;align-items:center;border:1px solid #d6e8e5;border-radius:999px;background:#fff;color:#375f7a;padding:7px 11px;text-decoration:none;font-size:.78rem;font-weight:900}
    </style>
</head>
<body>
    <main class="page">
        <header class="page-head"><img class="logo" src="{{ asset('images/logo-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Logo de IFE Educabol"><a class="back" href="{{ Auth::check() ? route('dashboard') : route('auth') }}">{{ Auth::check() ? 'Mi cuenta' : 'Inicio' }}</a></header>
        @include('partials.grade-simulator')
    </main>
</body>
</html>
