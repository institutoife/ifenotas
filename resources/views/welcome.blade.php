<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFE Notas | Simulador de notas y apoyo escolar</title>
    <meta name="description" content="Calcula cuánto necesitas para pasar de curso, simula tus notas escolares y conoce las clases de apoyo escolar de IFE Educabol.">
    <link rel="canonical" href="{{ route('auth') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="IFE Notas | ¿Cuánto necesitas para pasar de curso?">
    <meta property="og:description" content="Simula tus notas y descubre cuánto necesitas en los próximos trimestres.">
    <meta property="og:url" content="{{ route('auth') }}">
    <meta property="og:image" content="{{ asset('images/logo-ife-educabol-instituto-formacion-educabol.png') }}">
    <meta property="og:image:alt" content="Logo de IFE Educabol">
    <link rel="icon" href="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--primary:#26baa5;--secondary:#375f7a;--deep:#03607d;--bg:#f3fbfa;--soft:#e5f8f5;--line:#cfe7e4;--text:#294c61;--muted:#5c7482;--white:#fff}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;font-family:"Segoe UI",Arial,sans-serif;color:var(--text);background:var(--bg);line-height:1.45}a{color:inherit}img{max-width:100%}.wrap{width:min(1160px,100%);margin:auto;padding-inline:16px}
        .site-head{display:flex;align-items:center;justify-content:space-between;gap:14px;min-height:78px}.brand-logo{display:block;width:min(210px,47vw);height:auto}.head-actions{display:flex;gap:7px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;border:0;border-radius:11px;padding:9px 15px;text-decoration:none;font-weight:900;transition:transform .16s,box-shadow .16s}.btn:hover{transform:translateY(-2px)}.btn-primary{background:var(--primary);color:#fff;box-shadow:0 9px 22px rgba(38,186,165,.24)}.btn-secondary{background:var(--secondary);color:#fff}.btn-soft{background:var(--soft);color:var(--secondary)}
        .hero{position:relative;overflow:hidden;border-radius:30px;background:linear-gradient(145deg,#fff 0%,#effcf9 56%,#d8f4ef 100%);box-shadow:0 26px 65px rgba(55,95,122,.13)}.hero::before{content:"";position:absolute;width:360px;height:360px;right:-130px;top:-150px;border-radius:50%;background:rgba(38,186,165,.14)}.hero-grid{position:relative;display:grid;grid-template-columns:minmax(0,1.15fr) minmax(260px,.85fr);align-items:end;gap:20px;padding:clamp(22px,5vw,54px) clamp(18px,5vw,54px) 0}.hero-copy{padding-bottom:clamp(24px,4vw,46px)}.eyebrow{display:inline-flex;border-radius:999px;background:var(--secondary);color:#fff;padding:6px 11px;font-size:.76rem;font-weight:1000;letter-spacing:.08em}.hero h1{margin:10px 0 0;color:var(--primary);font-size:clamp(2.7rem,8vw,5.7rem);line-height:.83;letter-spacing:-.05em}.hero h2{margin:12px 0 7px;color:var(--secondary);font-size:clamp(1.55rem,4.3vw,2.8rem);line-height:1.02}.hero-lead{margin:0;color:var(--muted);font-size:clamp(.96rem,2vw,1.15rem);font-weight:700}.hero-person{position:relative;z-index:1;display:block;width:min(430px,100%);max-height:590px;object-fit:contain;object-position:bottom;margin:0 auto}.mode-title{margin:21px 0 8px;font-size:clamp(1rem,2.5vw,1.3rem);font-weight:1000;text-transform:uppercase}.mode-links{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.mode-link{display:grid;grid-template-columns:auto 1fr;align-items:center;gap:10px;min-height:112px;border:3px solid var(--line);border-radius:16px;background:#fff;padding:10px;text-decoration:none;box-shadow:0 10px 24px rgba(55,95,122,.09);transition:.16s}.mode-link:hover{border-color:var(--primary);transform:translateY(-3px)}.mode-link b{color:var(--primary);font-size:clamp(3.6rem,9vw,5.4rem);line-height:.75}.mode-link strong{display:block;font-size:1rem}.mode-link small{display:block;margin-top:3px;color:var(--muted);font-size:.74rem;line-height:1.25}
        .bridge{margin:34px auto;padding:18px;text-align:center}.bridge strong{display:block;color:var(--primary);font-size:clamp(1.45rem,4vw,2.4rem)}.bridge span{color:var(--muted);font-weight:750}
        .section{padding:clamp(28px,6vw,66px) 0}.section-head{max-width:700px;margin-bottom:20px}.section-kicker{color:var(--primary);font-size:.78rem;font-weight:1000;letter-spacing:.12em;text-transform:uppercase}.section h2{margin:5px 0 6px;color:var(--secondary);font-size:clamp(2rem,5vw,3.4rem);line-height:1}.section p{margin:0;color:var(--muted)}
        .support{background:var(--secondary);color:#fff}.support .section-kicker,.support h2,.support p{color:#fff}.support-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:28px;align-items:center}.subject-pills{display:flex;flex-wrap:wrap;gap:8px;margin:17px 0}.subject-pills span{border:1px solid rgba(255,255,255,.35);border-radius:999px;background:rgba(255,255,255,.1);padding:7px 11px;font-size:.84rem;font-weight:850}.support-visual{display:grid;grid-template-columns:110px 1fr;gap:15px;align-items:center;border-radius:22px;background:#fff;color:var(--secondary);padding:18px}.support-icon{width:110px;height:122px;object-fit:contain}.support-visual strong{display:block;font-size:clamp(1.3rem,3vw,2rem);line-height:1.05}.support-visual span{display:block;margin-top:6px;color:var(--muted)}
        .service-groups{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.service-group{border:1px solid var(--line);border-radius:18px;background:#fff;padding:18px;box-shadow:0 12px 28px rgba(55,95,122,.08)}.service-group i{color:var(--primary);font-size:2rem}.service-group h3{margin:10px 0 6px;color:var(--secondary)}.service-group p{font-size:.9rem}
        .institutional{display:grid;grid-template-columns:minmax(220px,.7fr) 1.3fr;gap:28px;align-items:center;border-radius:26px;background:linear-gradient(135deg,var(--soft),#fff);padding:clamp(20px,5vw,45px)}.institutional-logo{width:min(320px,100%);height:auto}.social-links{display:flex;flex-wrap:wrap;gap:8px;margin-top:17px}.social-link{display:inline-flex;align-items:center;gap:7px;min-height:42px;border:1px solid var(--line);border-radius:10px;background:#fff;padding:7px 11px;text-decoration:none;font-weight:850}.social-link i{color:var(--primary);font-size:1.15rem}.contact-cta{margin-top:17px}
        footer{margin-top:38px;background:#203f52;color:#fff}.footer-grid{display:flex;align-items:center;justify-content:space-between;gap:20px;padding-block:24px}.footer-brand{display:flex;align-items:center;gap:12px}.footer-brand img{width:46px;height:52px;object-fit:contain}.footer-brand strong{display:block}.footer-brand span{display:block;color:#cfe2e9;font-size:.8rem}.footer-social{display:flex;gap:12px}.footer-social a{font-size:1.2rem}.whatsapp-float{position:fixed;z-index:30;right:16px;bottom:16px;width:56px;height:56px;border-radius:50%;display:grid;place-items:center;background:#25d366;color:#fff;text-decoration:none;font-size:1.8rem;box-shadow:0 12px 28px rgba(37,211,102,.36)}
        @media(max-width:800px){.hero-grid,.support-grid,.institutional{grid-template-columns:1fr}.hero-copy{padding-bottom:0}.hero-person{max-height:330px}.support-visual{grid-template-columns:82px 1fr}.support-icon{width:82px;height:92px}.service-groups{grid-template-columns:1fr}.institutional-logo{width:min(240px,70vw)}.footer-grid{align-items:flex-start}}
        @media(max-width:520px){.wrap{padding-inline:9px}.site-head{min-height:66px}.brand-logo{width:min(145px,39vw)}.head-actions{gap:4px}.head-actions .btn{min-height:38px;padding:6px 9px;font-size:.72rem}.hero{border-radius:20px}.hero-grid{padding:19px 11px 0;gap:9px}.mode-links{gap:6px}.mode-link{min-height:104px;padding:7px;gap:6px;border-radius:13px}.mode-link b{font-size:3.7rem}.mode-link strong{font-size:.82rem}.mode-link small{font-size:.64rem}.hero-person{max-height:280px}.bridge{margin:19px auto;padding:10px}.section{padding:32px 0}.support-visual{padding:12px}.institutional{border-radius:18px;padding:17px 13px}.footer-grid{display:grid}.whatsapp-float{right:10px;bottom:10px;width:52px;height:52px}}
    </style>
</head>
<body>
    <header class="wrap site-head">
        <a href="{{ route('auth') }}" aria-label="IFE Notas, página principal"><img class="brand-logo" src="{{ asset('images/logo-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Logo de IFE Educabol"></a>
        <nav class="head-actions" aria-label="Cuenta"><a class="btn btn-soft" href="{{ route('login.view') }}">Ingresar</a><a class="btn btn-secondary" href="{{ route('register.view') }}">Crear cuenta</a></nav>
    </header>

    <main>
        <section class="wrap">
            <div class="hero">
                <div class="hero-grid">
                    <div class="hero-copy">
                        <span class="eyebrow">SIMULADOR DE NOTAS ESCOLARES</span>
                        <h1>IFE NOTAS</h1>
                        <h2>¿Cuánto necesitas para pasar de curso?</h2>
                        <p class="hero-lead">Simula tus notas y descubre cuánto necesitas en los próximos trimestres.</p>
                        <div class="mode-title">¿Cuántas notas ya tienes?</div>
                        <div class="mode-links">
                            <a class="mode-link" href="{{ route('notes.simulator', ['mode' => 'one']) }}"><b>1</b><span><strong>UNA NOTA</strong><small>1.º real → simula 2.º → calcula 3.º</small></span></a>
                            <a class="mode-link" href="{{ route('notes.simulator', ['mode' => 'two']) }}"><b>2</b><span><strong>DOS NOTAS</strong><small>1.º y 2.º reales → simula 3.º</small></span></a>
                        </div>
                    </div>
                    <img class="hero-person" src="{{ asset('images/david-flores-ife-educabol-instituto-formacion-educabol.png') }}" alt="David Flores de IFE Educabol presentando la herramienta IFE Notas">
                </div>
            </div>
        </section>

        <div class="wrap bridge"><strong>Calcula. Identifica tu situación. Refuerza lo que necesitas.</strong><span>IFE Notas te orienta; IFE puede ayudarte a seguir mejorando.</span></div>

        <section class="support section">
            <div class="wrap support-grid">
                <div><div class="section-kicker">APRENDE CON ACOMPAÑAMIENTO</div><h2>Apoyo escolar</h2><p>Refuerzo personalizado para trabajar las materias donde tienes mayor dificultad.</p><div class="subject-pills"><span>Matemáticas</span><span>Física</span><span>Química</span><span>Lenguaje</span><span>Inglés</span><span>Lectura y escritura</span></div><a class="btn btn-primary" href="https://wa.me/{{ $ife['whatsapp'] }}?text={{ rawurlencode('Hola, quiero información sobre las clases de apoyo escolar de IFE.') }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> Consultar clases</a></div>
                <div class="support-visual"><img class="support-icon" src="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Icono educativo de IFE"><div><strong>Aprende a tu ritmo</strong><span>Fortalece bases, resuelve dudas y prepárate mejor.</span></div></div>
            </div>
        </section>

        <section class="wrap section">
            <header class="section-head"><div class="section-kicker">MÁS FORMAS DE APRENDER</div><h2>Servicios de IFE</h2><p>Programas académicos, tecnológicos y de desarrollo de habilidades.</p></header>
            <div class="service-groups">
                <article class="service-group"><i class="fa-solid fa-laptop-code"></i><h3>Tecnología</h3><p>Programación, robótica, computación, inteligencia artificial e impresión 3D.</p></article>
                <article class="service-group"><i class="fa-solid fa-book-open-reader"></i><h3>Comunicación</h3><p>Inglés, oratoria, lectura, escritura y comprensión lectora.</p></article>
                <article class="service-group"><i class="fa-solid fa-shapes"></i><h3>Creatividad y estrategia</h3><p>Diseño gráfico, ajedrez y técnicas para resolver el cubo Rubik.</p></article>
            </div>
        </section>

        <section class="wrap section">
            <div class="institutional">
                <img class="institutional-logo" src="{{ asset('images/logo-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Instituto de Formación Educabol IFE">
                <div><div class="section-kicker">INSTITUTO DE FORMACIÓN EDUCABOL</div><h2>Facilitamos tu educación</h2><p>Herramientas y formación para estudiantes, familias y personas que quieren desarrollar nuevas habilidades.</p><div class="social-links">
                    <a class="social-link" href="{{ $ife['social']['tiktok'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-tiktok"></i> TikTok</a>
                    <a class="social-link" href="{{ $ife['social']['facebook'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i> Facebook</a>
                    <a class="social-link" href="{{ $ife['social']['instagram'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i> Instagram</a>
                    <a class="social-link" href="{{ $ife['social']['youtube'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i> YouTube</a>
                </div></div>
            </div>
        </section>
    </main>

    <a class="whatsapp-float" href="https://wa.me/{{ $ife['whatsapp'] }}?text={{ rawurlencode('Hola, quiero más información sobre IFE.') }}" target="_blank" rel="noopener" aria-label="Consultar a IFE por WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
    <footer><div class="wrap footer-grid"><div class="footer-brand"><img src="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Icono de IFE"><div><strong>IFE · Instituto de Formación Educabol</strong><span>Facilitamos tu educación</span></div></div><div class="footer-social"><a href="{{ $ife['social']['tiktok'] }}" target="_blank" rel="noopener" aria-label="TikTok de IFE"><i class="fa-brands fa-tiktok"></i></a><a href="{{ $ife['social']['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook de IFE"><i class="fa-brands fa-facebook-f"></i></a><a href="{{ $ife['social']['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram de IFE"><i class="fa-brands fa-instagram"></i></a><a href="{{ $ife['social']['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube de IFE"><i class="fa-brands fa-youtube"></i></a></div></div></footer>
</body>
</html>
