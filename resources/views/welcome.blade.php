<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFE Notas | Calcula si aprobaste o cuántos puntos te faltan</title>
    <meta name="description" content="Calcula si ya pasaste, te aplazaste o cuántos puntos te faltan para aprobar y encuentra apoyo escolar en IFE Educabol.">
    <link rel="canonical" href="{{ route('auth') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="IFE Notas | Calcula tu situación escolar">
    <meta property="og:description" content="Calcula si ya pasaste, te aplazaste o cuántos puntos te faltan para aprobar.">
    <meta property="og:url" content="{{ route('auth') }}">
    <meta property="og:image" content="{{ asset('images/logo-ife-educabol-instituto-formacion-educabol.png') }}">
    <meta property="og:image:alt" content="Logo de IFE Educabol">
    <link rel="icon" href="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--primary:#26baa5;--primary-dark:#159987;--secondary:#375f7a;--deep:#203f52;--bg:#f4faf9;--soft:#e5f8f5;--line:#cfe7e4;--text:#294c61;--muted:#5c7482;--white:#fff;--whatsapp:#25d366}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;overflow-x:hidden;font-family:"Segoe UI",Arial,sans-serif;color:var(--text);background:var(--bg);line-height:1.45}a{color:inherit}img{max-width:100%}button,input{font:inherit}.wrap{width:min(1120px,100%);margin:auto;padding-inline:14px}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
        .site-head{display:flex;align-items:center;justify-content:space-between;gap:10px;min-height:70px}.brand-logo{display:block;width:min(178px,43vw);height:auto}.head-actions{display:flex;gap:6px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;border:0;border-radius:12px;padding:9px 14px;text-decoration:none;font-weight:900;cursor:pointer;transition:transform .16s ease,box-shadow .16s ease,background .16s ease}.btn:hover{transform:translateY(-2px)}.btn:active{transform:translateY(0) scale(.98)}.btn:focus-visible,.hero-start:focus-visible,.subject-link:focus-visible,.service-link:focus-visible,.social-link:focus-visible{outline:4px solid rgba(38,186,165,.25);outline-offset:3px}.btn-secondary{background:var(--secondary);color:#fff}.btn-soft{background:var(--soft);color:var(--secondary)}
        .hero{padding:0 14px clamp(36px,6vw,68px)}.hero-scene{display:flex;flex-direction:column;position:relative;isolation:isolate;min-height:650px;max-width:1180px;margin:auto;overflow:hidden;border-radius:28px;background:var(--deep);box-shadow:0 25px 60px rgba(32,63,82,.22)}.hero-art{position:absolute;z-index:-2;inset:0;width:100%;height:100%;max-width:none;object-fit:cover;object-position:center}.hero-shade{position:absolute;z-index:-1;inset:0;background:linear-gradient(90deg,rgba(18,39,52,.98) 0%,rgba(18,39,52,.93) 35%,rgba(18,39,52,.72) 49%,rgba(18,39,52,.08) 72%)}.hero-content{display:flex;flex:1;width:53%;min-height:0;flex-direction:column;justify-content:center;padding:38px clamp(26px,5vw,62px) 0}.hero h1{margin:20px 0;color:#fff;font-size:clamp(2.35rem,4.8vw,4.15rem);font-weight:760;line-height:.99;letter-spacing:-.042em;text-wrap:balance}.hero h1 strong{font-weight:1000}.community-row{display:flex;align-items:center;gap:12px}.visitor-circle{display:grid;place-items:center;width:152px;height:152px;flex:0 0 152px;border-radius:50%;background:var(--primary);color:#fff;text-align:center;box-shadow:0 12px 34px rgba(38,186,165,.28)}.visitor-value{display:block;font-size:clamp(1.45rem,2.4vw,2rem);font-weight:1000;line-height:1}.visitor-label{display:block;max-width:112px;margin-top:7px;font-size:.66rem;font-weight:1000;line-height:1.15;letter-spacing:.08em;text-transform:uppercase}.tiktok-stat{display:inline-flex;align-items:center;gap:8px;min-height:42px;border:1px solid rgba(255,255,255,.3);border-radius:999px;background:rgba(255,255,255,.1);padding:8px 12px;color:#fff;text-decoration:none;font-size:.78rem;font-weight:900;backdrop-filter:blur(8px)}.tiktok-stat strong{font-size:1rem}.creator-link{display:inline-flex;width:max-content;max-width:100%;align-items:center;gap:9px;min-height:44px;margin-bottom:20px;border:1px solid #fff;border-radius:999px;background:#fff;padding:8px 15px;color:#17171b;text-decoration:none;font-size:.84rem;font-weight:900;box-shadow:0 8px 24px rgba(0,0,0,.18);transition:.16s}.creator-link:hover{transform:translateY(-2px);box-shadow:0 11px 28px rgba(0,0,0,.24)}.creator-link:active{transform:scale(.98)}.creator-link:focus-visible{outline:4px solid rgba(38,186,165,.4);outline-offset:3px}.creator-link i{font-size:1.15rem}.hero-action{display:flex;flex-shrink:0;justify-content:center;width:100%;padding:12px clamp(14px,4vw,48px) 32px}.hero-start{display:flex;align-items:center;justify-content:center;align-self:center;gap:14px;width:calc(100% - 12px);max-width:420px;min-height:68px;margin-inline:auto;border:2px solid #25D366;border-radius:16px;background:#25D366;padding:17px 21px;color:#103b2c;text-align:center;text-decoration:none;font-size:clamp(1.1rem,2vw,1.3rem);font-weight:950;cursor:pointer;box-shadow:0 10px 26px rgba(37,211,102,.25);transform-origin:center;transition:transform .18s,background .18s,box-shadow .18s;animation:heroStartPam 8s ease-in-out infinite}.hero-start>span:first-child{display:inline-flex;align-items:center;gap:12px}.hero-start>span:last-child{font-size:1.5em;line-height:1}.hero-start:hover,.hero-start:focus-visible{animation:none;background:#32e176;border-color:#32e176;transform:scale(1.025);box-shadow:0 14px 32px rgba(37,211,102,.4)}.hero-start:active{animation:none;transform:scale(.985)}.hero-start:focus-visible{outline:3px solid #fff;outline-offset:4px}
        .section{padding:clamp(42px,7vw,76px) 0}.section-title{margin:0;color:var(--secondary);font-size:clamp(2rem,6vw,3.7rem);font-weight:1000;line-height:1;letter-spacing:-.035em}.support{background:var(--secondary);color:#fff}.support-grid{display:grid;grid-template-columns:minmax(210px,.75fr) minmax(0,1.25fr);align-items:center;gap:clamp(20px,5vw,58px)}.support-person{display:block;width:min(310px,100%);max-height:410px;margin:0 auto -1px;object-fit:contain;object-position:bottom}.support .section-title{color:#fff}.subject-grid{display:flex;flex-wrap:wrap;gap:9px;margin-top:22px}.subject-link{display:inline-flex;align-items:center;gap:8px;min-height:48px;border:1px solid rgba(255,255,255,.34);border-radius:999px;background:rgba(255,255,255,.1);padding:9px 14px;color:#fff;text-decoration:none;font-weight:900;transition:.16s}.subject-link:hover{border-color:#fff;background:#fff;color:var(--secondary);transform:translateY(-2px)}.subject-link:active{transform:scale(.98)}.subject-link i{color:#57e38b}
        .services-head{display:flex;align-items:end;justify-content:space-between;gap:18px;margin-bottom:22px}.services-count{color:var(--muted);font-size:.78rem;font-weight:950;letter-spacing:.08em;text-transform:uppercase}.service-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px}.service-card{display:flex;min-width:0;min-height:248px;flex-direction:column;align-items:center;text-align:center;border:1px solid var(--line);border-radius:18px;background:#fff;padding:22px 16px 16px;box-shadow:0 9px 25px rgba(55,95,122,.07);transition:transform .16s ease,border-color .16s ease}.service-card:hover{border-color:var(--primary);transform:translateY(-3px)}.service-icon{display:grid;place-items:center;width:84px;height:84px;margin-inline:auto;border-radius:22px;background:var(--soft);color:var(--primary);font-size:2.8rem}.service-card h3{margin:16px 0 18px;color:var(--secondary);font-size:1.12rem;line-height:1.2;text-wrap:balance}.service-link{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:48px;margin-top:auto;border-radius:11px;background:var(--secondary);padding:11px 10px;color:#fff;text-decoration:none;font-size:.9rem;font-weight:900;line-height:1.25;transition:transform .16s,background .16s}.service-link:hover{background:#28495f;transform:translateY(-1px)}.service-link:active{transform:scale(.98)}.service-link:focus-visible{outline:3px solid var(--secondary);outline-offset:4px}.service-link i{color:inherit;font-size:1.15em}
        .institutional{display:grid;grid-template-columns:minmax(180px,.7fr) minmax(0,1.3fr);align-items:center;gap:clamp(24px,5vw,58px);border-top:1px solid var(--line)}.institutional-logo{display:block;width:min(300px,100%);height:auto}.institutional-copy{max-width:610px}.institutional-copy p{margin:10px 0 0;color:var(--muted)}.social-links{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}.social-link{display:inline-flex;align-items:center;gap:8px;min-height:44px;border:1px solid var(--line);border-radius:12px;background:#fff;padding:8px 12px;text-decoration:none;font-weight:900;transition:.16s}.social-link:hover{border-color:var(--primary);transform:translateY(-2px)}.social-link i{color:var(--primary);font-size:1.1rem}
        footer{background:var(--deep);color:#fff}.footer-grid{display:flex;align-items:center;justify-content:space-between;gap:20px;padding-block:23px}.footer-brand{display:flex;align-items:center;gap:11px}.footer-brand img{display:block;width:44px;height:48px;object-fit:contain}.footer-brand strong{display:block}.footer-brand span{display:block;color:#cfe2e9;font-size:.78rem}.footer-social{display:flex;gap:14px}.footer-social a{font-size:1.15rem}.whatsapp-float{position:fixed;z-index:30;right:14px;bottom:14px;display:grid;place-items:center;width:54px;height:54px;border-radius:50%;background:var(--whatsapp);color:#fff;text-decoration:none;font-size:1.7rem;box-shadow:0 12px 28px rgba(37,211,102,.34)}
        @media(max-width:900px){.hero-content{width:61%}.hero-shade{background:linear-gradient(90deg,rgba(18,39,52,.98) 0%,rgba(18,39,52,.91) 46%,rgba(18,39,52,.3) 76%)}}
        @media(max-width:820px){.support-grid{grid-template-columns:minmax(150px,.55fr) minmax(0,1.45fr)}.service-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.institutional{grid-template-columns:1fr}.institutional-logo{width:min(230px,70vw)}}
        @media(max-width:620px){.wrap{padding-inline:11px}.site-head{min-height:64px}.brand-logo{width:min(145px,40vw)}.head-actions{gap:4px}.head-actions .btn{min-height:40px;padding:6px 9px;font-size:.72rem}.hero{padding:0 0 34px}.hero-scene{min-height:680px;border-radius:0}.hero-art{object-position:62% center}.hero-shade{background:linear-gradient(180deg,rgba(18,39,52,.24) 0%,rgba(18,39,52,.78) 30%,rgba(18,39,52,.98) 61%)}.hero-content{width:100%;min-height:0;justify-content:flex-end;padding:22px 14px 0}.community-row{gap:8px}.visitor-circle{width:122px;height:122px;flex-basis:122px}.visitor-value{font-size:1.4rem}.visitor-label{max-width:96px;font-size:.57rem}.tiktok-stat{font-size:.68rem;padding:7px 9px}.hero h1{margin:14px 0;font-size:clamp(2rem,10vw,2.75rem)}.creator-link{margin-bottom:16px;font-size:.78rem}.hero-start{max-width:none;min-height:64px;padding:15px 17px}.support-grid{grid-template-columns:1fr}.support-copy{order:1}.support-person{order:2;max-height:300px;margin-bottom:-42px}.subject-grid{gap:7px}.subject-link{min-height:46px;padding:8px 12px;font-size:.86rem}.services-head{display:block}.services-count{display:block;margin-top:7px}.service-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.service-card{min-height:236px;border-radius:15px;padding:18px 12px 12px}.service-icon{width:76px;height:76px;border-radius:20px;font-size:2.5rem}.service-card h3{font-size:1.02rem}.service-link{font-size:.88rem}.footer-grid{align-items:flex-start}.whatsapp-float{right:10px;bottom:10px;width:52px;height:52px}}
        @media(max-width:350px){.wrap{padding-inline:8px}.head-actions .btn{padding-inline:7px;font-size:.68rem}.hero-content{padding-inline:10px}.hero h1{font-size:1.92rem}.tiktok-stat{max-width:155px}.service-card{padding:10px}.footer-grid{display:grid}}
        .hero-scene{width:95%;max-width:none}
        @media(max-width:620px){.hero-scene{width:100%}.hero-art{object-position:68% center}.hero-shade{background:linear-gradient(180deg,rgba(18,39,52,0) 0%,rgba(18,39,52,0) 34%,rgba(18,39,52,.34) 50%,rgba(18,39,52,.92) 72%,rgba(18,39,52,.98) 100%)}.hero h1{text-shadow:0 2px 14px rgba(0,0,0,.65)}}
        .creator-link{gap:12px;padding:7px 15px 7px 9px;min-height:70px}.creator-link>span:not(.tiktok-live-avatar){min-width:0}.tiktok-live-avatar{position:relative;display:grid;place-items:center;flex:0 0 52px;width:52px;height:52px;border:2px solid #ff1768;border-radius:50%;background:#fff;box-shadow:0 0 0 2px #fff;animation:tiktok-live-pan 3.2s ease-in-out infinite}.tiktok-live-avatar::before{content:"";position:absolute;inset:-5px;border:1px solid #ff3785;border-radius:50%;animation:tiktok-live-ring 1.8s ease-out infinite;pointer-events:none}.tiktok-live-picture{display:grid;place-items:center;width:44px;height:44px;overflow:hidden;border-radius:50%;background:#fff}.tiktok-live-picture img{display:block;width:32px;height:35px;object-fit:contain;animation:tiktok-live-zoom 3.2s ease-in-out infinite}.tiktok-live-badge{position:absolute;z-index:1;top:-3px;left:50%;transform:translateX(-50%);border:1px solid #fff;border-radius:4px;background:#ff1768;padding:1px 5px;color:#fff;font-size:9px;font-weight:900;line-height:1.2;letter-spacing:.02em}.creator-link>.fa-tiktok{flex-shrink:0}
        @keyframes tiktok-live-pan{0%,100%{transform:translate(-1px,1px) scale(.96)}50%{transform:translate(1px,-1px) scale(1.04)}}
        @keyframes tiktok-live-zoom{0%,100%{transform:translate(-1px,1px) scale(1)}50%{transform:translate(1px,-1px) scale(1.1)}}
        @keyframes tiktok-live-ring{0%{transform:scale(.96);opacity:.8}100%{transform:scale(1.15);opacity:0}}
        @keyframes heroStartPam{0%,32%,64%,80%,86%,94%,100%{transform:scale(1);box-shadow:0 10px 26px rgba(37,211,102,.25)}16%,48%,76%{transform:scale(1.018);box-shadow:0 12px 30px rgba(37,211,102,.38)}83%{transform:scale(1.035);box-shadow:0 14px 36px rgba(37,211,102,.48)}90%{transform:scale(1.022);box-shadow:0 12px 30px rgba(37,211,102,.38)}}
        @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*::before,*::after{transition:none!important;animation:none!important}.hero-start{transform:none!important}}
    </style>
</head>
<body>
    <header class="wrap site-head">
        <a href="{{ route('auth') }}" aria-label="IFE Notas, página principal"><img class="brand-logo" src="{{ asset('images/logo-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Logo de IFE Educabol"></a>
        <nav class="head-actions" aria-label="Cuenta"><a class="btn btn-soft" href="{{ route('login.view') }}">Ingresar</a><a class="btn btn-secondary" href="{{ route('register.view') }}">Crear cuenta</a></nav>
    </header>

    <main>
        <section class="hero" aria-labelledby="heroTitle">
            <div class="hero-scene">
                <img class="hero-art" src="{{ asset('images/hero-boletin-ife-notas.png') }}" alt="Boletín de calificaciones acompañado de lápices, borrador, tajador, regla, cuaderno y calculadora" width="1536" height="1024" fetchpriority="high">
                <div class="hero-shade" aria-hidden="true"></div>
                <div class="hero-content">
                    <div class="community-row" aria-label="Comunidad de IFE Notas">
                        <div class="visitor-circle"><div><strong class="visitor-value">{{ number_format($visitorCount, 0, ',', '.') }}</strong><span class="visitor-label">personas ya usaron IFE Notas</span></div></div>
                        @if($tiktokFollowers !== null)
                            <a class="tiktok-stat" href="{{ $ife['social']['tiktok'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-tiktok" aria-hidden="true"></i><span><strong>{{ number_format($tiktokFollowers, 0, ',', '.') }}</strong><br>seguidores</span></a>
                        @endif
                    </div>
                    <h1 id="heroTitle">Calcula si ya pasaste, <strong>te aplazaste</strong> o cuántos puntos te faltan para aprobar.</h1>
                    <a class="creator-link" href="{{ $ife['social']['tiktok'] }}" target="_blank" rel="noopener">
                        <span class="tiktok-live-avatar" aria-hidden="true">
                            <span class="tiktok-live-picture"><img src="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" alt="" width="32" height="35"></span>
                            <span class="tiktok-live-badge">LIVE</span>
                        </span>
                        <span>Sigue al creador de esta herramienta</span><span aria-hidden="true">↗</span>
                        
                    </a>
                </div>
                <div class="hero-action">
                    <a class="hero-start" href="{{ route('notes.simulator') }}"><span><i class="fa-solid fa-calculator" aria-hidden="true"></i> Calcular mi nota</span><span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>

        <section class="support section" aria-labelledby="supportTitle">
            <div class="wrap support-grid">
                <img class="support-person" src="{{ asset('images/david-flores-ife-educabol-instituto-formacion-educabol.png') }}" alt="David Flores de IFE Educabol ofreciendo apoyo escolar" loading="lazy" width="670" height="772">
                <div class="support-copy">
                    <h2 class="section-title" id="supportTitle">¿Qué materia necesitas reforzar?</h2>
                    <div class="subject-grid">
                        @foreach($ife['subjects'] as $subject)
                            <a class="subject-link" href="{{ \App\Support\IfeWhatsApp::subjectUrl($ife['whatsapp'], $subject) }}" target="_blank" rel="noopener"><span>{{ $subject }}</span><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="section wrap" aria-labelledby="statisticsTitle">
            <h2 class="section-title" id="statisticsTitle">Así van las notas</h2>
            @include('partials.statistics-summary')
        </section>

        <section class="section wrap" aria-labelledby="servicesTitle">
            <header class="services-head"><h2 class="section-title" id="servicesTitle">Servicios de IFE</h2><span class="services-count">{{ count($ife['services']) }} opciones de formación</span></header>
            <div class="service-grid">
                @foreach($ife['services'] as $service)
                    <article class="service-card">
                        <span class="service-icon"><i class="fa-solid {{ $service['icon'] }}" aria-hidden="true"></i></span>
                        <h3>{{ $service['name'] }}</h3>
                        <a class="service-link" href="{{ \App\Support\IfeWhatsApp::serviceUrl($ife['whatsapp'], $service['name']) }}" target="_blank" rel="noopener" aria-label="Consultar sobre {{ $service['name'] }} por WhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span>Me interesa</span></a>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="section wrap institutional" aria-labelledby="institutionalTitle">
            <img class="institutional-logo" src="{{ asset('images/logo-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Instituto de Formación Educabol IFE" loading="lazy">
            <div class="institutional-copy"><h2 class="section-title" id="institutionalTitle">Instituto de Formación Educabol</h2><p>Facilitamos tu educación.</p><div class="social-links">
                <a class="social-link" href="{{ $ife['social']['tiktok'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-tiktok" aria-hidden="true"></i> TikTok</a>
                <a class="social-link" href="{{ $ife['social']['facebook'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i> Facebook</a>
                <a class="social-link" href="{{ $ife['social']['instagram'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-instagram" aria-hidden="true"></i> Instagram</a>
                <a class="social-link" href="{{ $ife['social']['youtube'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-youtube" aria-hidden="true"></i> YouTube</a>
            </div></div>
        </section>
    </main>

    <a class="whatsapp-float" href="{{ \App\Support\IfeWhatsApp::generalUrl($ife['whatsapp']) }}" target="_blank" rel="noopener" aria-label="Consultar a IFE por WhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
    <footer><div class="wrap footer-grid"><div class="footer-brand"><img src="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Icono de IFE"><div><strong>IFE · Instituto de Formación Educabol</strong><span>Facilitamos tu educación</span></div></div><div class="footer-social"><a href="{{ $ife['social']['tiktok'] }}" target="_blank" rel="noopener" aria-label="TikTok de IFE"><i class="fa-brands fa-tiktok"></i></a><a href="{{ $ife['social']['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook de IFE"><i class="fa-brands fa-facebook-f"></i></a><a href="{{ $ife['social']['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram de IFE"><i class="fa-brands fa-instagram"></i></a><a href="{{ $ife['social']['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube de IFE"><i class="fa-brands fa-youtube"></i></a></div></div></footer>
</body>
</html>
