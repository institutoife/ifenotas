<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ife notas</title>
  <link rel="icon" href="{{ asset('images/ife.ico') }}" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{--bg:#f3fbfa;--card:#ffffff;--text:rgb(55,95,122);--muted:#587486;--line:#d6ebe8;--primary:rgb(38,186,165);--primary-dark:rgb(55,95,122);--primary-soft:#e6f8f5}
    @font-face{font-family:"GlyphaLTStd";src:url("/fonts/GlyphaLTStd-Bold.otf") format("opentype");font-weight:700;font-style:normal;font-display:swap}
    *{box-sizing:border-box}
    body{margin:0;font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text);line-height:1.45}
    .container{max-width:920px;margin:0 auto;padding:20px 16px}
    .panel{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:20px;box-shadow:0 14px 36px rgba(55,95,122,.08)}
    .hero{display:grid;grid-template-columns:minmax(0,1fr) 240px;gap:18px;align-items:center}
    .hero-brand{display:flex;align-items:center;gap:12px;margin-bottom:12px}
    .hero-isologo{width:54px;height:54px;object-fit:contain;border-radius:12px;border:1px solid var(--line)}
    .logo{display:block;width:min(260px,100%);height:auto;margin-bottom:12px}
    .author{width:100%;aspect-ratio:1;object-fit:cover;object-position:center 18%;border-radius:12px;border:1px solid var(--line);box-shadow:0 12px 30px rgba(55,95,122,.12)}
    h1,h2{margin:0 0 10px}
    p{margin:0;color:var(--muted)}
    .hero-copy{min-height:3.7em;font-size:1.14rem;font-weight:600;color:var(--primary-dark)}
    .hero-copy::after{content:"";display:inline-block;width:2px;height:1em;margin-left:3px;background:var(--primary);vertical-align:-.12em;animation:blink .85s steps(1) infinite}
    @keyframes blink{50%{opacity:0}}
    .actions{margin-top:16px;display:flex;gap:10px;flex-wrap:wrap}
    .btn{display:inline-block;text-decoration:none;border-radius:10px;padding:10px 14px;font-weight:600}
    .btn-primary{background:var(--primary);color:#fff}
    .btn-soft{background:var(--primary-soft);color:var(--primary-dark)}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:18px}
    .wide-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:14px}
    .item{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px}
    .item-soft{background:var(--primary-soft)}
    .service-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:14px}
    .service{background:#fff;border:1px solid var(--line);border-radius:10px;padding:16px;display:grid;gap:10px;align-content:start;text-align:center;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
    .service:hover{transform:translateY(-5px);border-color:var(--primary);box-shadow:0 16px 32px rgba(38,186,165,.18)}
    .service:hover .service-icon{color:var(--primary)}
    .service-icon{width:100%;color:var(--primary-dark);display:grid;place-items:center;font-size:3rem;line-height:1;font-weight:900;transition:color .2s ease,transform .2s ease}
    .service:hover .service-icon{transform:scale(1.08)}
    .service strong{display:block;color:var(--text);margin-bottom:4px}
    .service span{display:block;color:var(--muted);font-size:.92rem}
    .service .btn{width:100%;text-align:center;margin-top:2px;background:var(--primary);color:#fff}
    .section{margin-top:14px}
    .list{margin:8px 0 0;padding-left:18px;color:var(--muted)}
    .list li{margin:4px 0}
    footer{padding:22px 0;text-align:center;color:var(--muted)}
    .brand-name{font-family:"GlyphaLTStd","Segoe UI",sans-serif;text-transform:lowercase;letter-spacing:0}
    .social-card{display:grid;grid-template-columns:120px 1fr;gap:16px;align-items:center}
    .social-brand{display:flex;align-items:center;gap:10px;margin-bottom:8px}
    .social-brand img{width:42px;height:42px;object-fit:contain;border-radius:10px;border:1px solid var(--line)}
    .social-author{width:120px;height:120px;object-fit:cover;object-position:center 18%;border-radius:50%;border:3px solid var(--primary);box-shadow:0 12px 26px rgba(55,95,122,.14)}
    .social{display:inline-flex;align-items:center;gap:9px;margin-top:12px;color:#fff;background:#111;border-radius:10px;padding:10px 14px;font-weight:700;text-decoration:none;box-shadow:4px 4px 0 #25f4ee,-4px -4px 0 #fe2c55;transition:transform .2s ease,box-shadow .2s ease}
    .social:hover{transform:translateY(-2px);box-shadow:6px 6px 0 #25f4ee,-6px -6px 0 #fe2c55}
    .social i{font-size:1.35rem}
    @media(max-width:760px){.grid,.wide-grid,.service-grid,.hero,.social-card{grid-template-columns:1fr}.author{max-width:220px}.social-author{width:150px;height:150px}}
  </style>
</head>
<body>
  <main class="container">
    <section class="panel">
      <div class="hero">
        <div>
          <div class="hero-brand">
            <img class="hero-isologo" src="{{ asset('images/isologo.jpg') }}" alt="Isologo de ife notas">
            <img class="logo" src="{{ asset('images/logo.png') }}" alt="ife notas">
          </div>
          <p class="hero-copy" data-typewriter="Para empezar crea tu cuenta. Dentro tendrás el cálculo de notas, el simulador y el chat para saber cuánto necesitas en los próximos trimestres."></p>
          <div class="actions">
            <a class="btn btn-primary" href="{{ route('login.view') }}">Iniciar sesión</a>
            <a class="btn btn-soft" href="{{ route('register.view') }}">Crear cuenta</a>
          </div>
        </div>
        <img class="author" src="{{ asset('images/david.png') }}" alt="David, autor de ife notas">
      </div>
    </section>

    <section class="grid section">
      <article class="item">
        <h2 style="font-size:1.1rem;">Qué puedes hacer</h2>
        <ul class="list">
          <li>Calcular lo necesario para pasar de curso.</li>
          <li>Simular escenarios para el 2do y 3er trimestre.</li>
          <li>Guardar resultados para revisarlos después.</li>
        </ul>
      </article>
      <article class="item">
        <h2 style="font-size:1.1rem;">Contacto rápido</h2>
        <p>Si necesitas apoyo escolar, puedes escribirnos directamente.</p>
        <div class="actions" style="margin-top:12px;">
          <a class="btn btn-soft" href="https://wa.me/59171324941" target="_blank">WhatsApp</a>
        </div>
      </article>
    </section>

    <section class="panel section">
      <div class="social-card">
        <img class="social-author" src="{{ asset('images/david.png') }}" alt="David, autor de ife notas">
        <div>
          <div class="social-brand">
            <img src="{{ asset('images/isologo.jpg') }}" alt="Isologo de ife notas">
            <h2>Síguenos en redes sociales</h2>
          </div>
          <p>Publicamos tips rápidos para estudiantes, familias y colegios.</p>
          <a class="social" href="https://www.tiktok.com/@ife_educabol" target="_blank" rel="noopener">
            <i class="fa-brands fa-tiktok"></i>
            TikTok @ife_educabol
          </a>
        </div>
      </div>
    </section>

    <section class="section">
      <h2>Otros servicios que te pueden interesar</h2>
      <p>ITE también ofrece cursos y actividades educativas para fortalecer habilidades académicas, tecnológicas y creativas.</p>
      <div class="service-grid">
        <article class="service"><div class="service-icon"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>Apoyo escolar</strong><span>Refuerzo personalizado por materias y niveles.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Apoyo%20escolar%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-code"></i></div><div><strong>Programación</strong><span>Crea aplicaciones, sitios web y soluciones digitales.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Programacion%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-robot"></i></div><div><strong>Robótica</strong><span>Construye y programa robots mientras aprendes tecnología.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Robotica%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-chess-knight"></i></div><div><strong>Ajedrez</strong><span>Desarrolla estrategia, concentración y pensamiento lógico.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Ajedrez%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-cube"></i></div><div><strong>Cubo Rubik</strong><span>Técnicas para resolver el cubo y mejorar la agilidad mental.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Cubo%20Rubik%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-desktop"></i></div><div><strong>Computación</strong><span>Herramientas informáticas para estudiar y trabajar mejor.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Computacion%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-microphone-lines"></i></div><div><strong>Oratoria</strong><span>Comunicación efectiva y seguridad al hablar en público.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Oratoria%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-language"></i></div><div><strong>Inglés</strong><span>Aprendizaje dinámico enfocado en comunicación real.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Ingles%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-book-open-reader"></i></div><div><strong>Lectura y escritura</strong><span>Comprensión lectora y expresión escrita.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Lectura%20y%20escritura%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-pen-nib"></i></div><div><strong>Diseño gráfico</strong><span>Comunicación visual y creatividad digital.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Diseno%20grafico%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-brain"></i></div><div><strong>Inteligencia artificial</strong><span>Herramientas IA para aprender, automatizar y crear.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Inteligencia%20artificial%20deme%20mas%20informacion">Consultar</a></article>
        <article class="service"><div class="service-icon"><i class="fa-solid fa-cubes"></i></div><div><strong>Impresión 3D</strong><span>Diseño e impresión de ideas en objetos reales.</span></div><a class="btn" target="_blank" rel="noopener" href="https://wa.me/59171324941?text=estoy%20interesado%20en%20el%20servicio%20Impresion%203D%20deme%20mas%20informacion">Consultar</a></article>
      </div>
    </section>

    <section class="panel section item-soft">
      <h2>Próximamente</h2>
      <p>Estamos desarrollando la versión para cuando ya tengas notas del primer y segundo trimestre. Esa herramienta calculará directamente cuánto necesitas en el tercer trimestre para pasar de curso.</p>
    </section>
  </main>
  <footer><span class="brand-name">ife notas</span></footer>
  <script>
    const typewriter = document.querySelector('[data-typewriter]');
    if (typewriter) {
      const text = typewriter.dataset.typewriter || '';
      let index = 0;

      function writeText() {
        typewriter.textContent = text.slice(0, index);
        index += 1;

        if (index <= text.length) {
          setTimeout(writeText, 38);
        }
      }

      writeText();
    }
  </script>
</body>
</html>
