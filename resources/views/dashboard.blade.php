<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ife notas</title>
    <link rel="icon" href="{{ asset('images/ife.ico') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        :root{
            --primary:#26BAA5;
            --secondary:#375F7A;
            --white:#fff;
            --black:#111;
            --bg:#f6f8f9;
            --muted:#6b7280;
            --line:#d9e1e5;
            --soft:#eef8f7;
            --ok:#26BAA5;
            --warn:#f59e0b;
            --danger:#ef4444;
        }
        @font-face{
            font-family:"GlyphaLTStd";
            src:url("/fonts/GlyphaLTStd-Bold.otf") format("opentype");
            font-weight:700;
            font-style:normal;
            font-display:swap;
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:"Segoe UI",Arial,sans-serif;background:var(--bg);color:var(--secondary)}
        .app{max-width:1120px;margin:0 auto;padding:18px}
        .topbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:18px}
        .brand{display:flex;align-items:center;gap:10px;font-weight:800}
        .mark{width:40px;height:40px;border-radius:10px;background:var(--white);border:1px solid var(--line);display:grid;place-items:center;overflow:hidden}
        .mark img{width:100%;height:100%;object-fit:contain}
        .brand-logo{display:block;width:142px;max-height:42px;object-fit:contain;object-position:left center}
        .brand-name{font-family:"GlyphaLTStd","Segoe UI",Arial,sans-serif;text-transform:lowercase;font-weight:700;letter-spacing:0}
        .meta{font-size:.88rem;color:var(--muted);margin-top:2px}
        .top-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
        .btn{border:1px solid var(--line);border-radius:12px;padding:10px 14px;background:var(--white);color:var(--secondary);font-weight:800;text-decoration:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px}
        .btn-primary{background:var(--primary);border-color:var(--primary);color:var(--white)}
        .btn-dark{background:var(--secondary);border-color:var(--secondary);color:var(--white)}
        .btn-full{width:100%}
        .nav{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:14px}
        .nav-btn{min-height:54px;border:1px solid var(--line);background:var(--white);border-radius:14px;color:var(--secondary);font-weight:900;cursor:pointer}
        .nav-btn.active{background:var(--secondary);border-color:var(--secondary);color:var(--white)}
        .section{display:none;background:var(--white);border:1px solid var(--line);border-radius:18px;padding:18px;box-shadow:0 18px 42px rgba(55,95,122,.08)}
        .section.active{display:block}
        .section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px}
        h1,h2,h3,p{margin:0}
        h1{font-size:clamp(1.45rem,3vw,2.2rem);line-height:1.05;color:var(--secondary)}
        h2{font-size:1.25rem;color:var(--secondary)}
        .lead{color:var(--muted);margin-top:6px;max-width:620px}
        .calc-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px;align-items:start}
        .form-stack{display:grid;gap:14px}
        label{font-size:.86rem;font-weight:800;color:var(--secondary)}
        input,select{width:100%;border:1px solid var(--line);border-radius:14px;background:var(--white);color:var(--secondary);min-height:50px;padding:12px 13px;font:inherit}
        input:focus,select:focus{outline:3px solid rgba(38,186,165,.18);border-color:var(--primary)}
        .note-input{font-size:1.65rem;font-weight:900;min-height:68px}
        .note-field{display:grid;gap:7px}
        .note-field .note-input:placeholder-shown{animation:notePulse 1.55s ease-in-out infinite;border-color:rgba(38,186,165,.65);box-shadow:0 0 0 0 rgba(38,186,165,.26)}
        .note-hint{display:inline-flex;align-items:center;gap:8px;color:var(--primary);font-size:.9rem;font-weight:900;animation:hintNudge 1.4s ease-in-out infinite}
        .note-hint::before{content:"";width:9px;height:9px;border-radius:50%;background:var(--primary)}
        .note-field:has(.note-input:not(:placeholder-shown)) .note-hint{display:none}
        @keyframes notePulse{50%{box-shadow:0 0 0 8px rgba(38,186,165,.12)}}
        @keyframes hintNudge{50%{transform:translateX(5px)}}
        .result-box{background:var(--soft);border:1px solid var(--line);border-radius:16px;padding:16px}
        .result-number{font-size:clamp(2.2rem,7vw,4.4rem);line-height:1;font-weight:950;color:var(--secondary);letter-spacing:0}
        .result-label{font-weight:900;margin-top:6px}
        .suggestion{margin-top:12px;color:var(--black);line-height:1.42}
        .support-card{background:var(--soft);border:1px solid var(--line);border-radius:16px;padding:16px;min-height:150px;display:grid;align-content:center;gap:10px}
        .support-person{display:flex;align-items:center;gap:12px}
        .support-person img{width:58px;height:58px;border-radius:14px;object-fit:cover;object-position:center 18%;border:2px solid var(--white);box-shadow:0 8px 20px rgba(55,95,122,.16)}
        .support-title{font-size:.85rem;font-weight:900;color:var(--primary);text-transform:uppercase}
        .support-text{font-size:1.25rem;line-height:1.25;font-weight:900;color:var(--secondary)}
        .profile-grid{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:18px;align-items:start}
        .profile-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .profile-fields .full{grid-column:1 / -1}
        .profile-note{background:var(--soft);border:1px solid var(--line);border-radius:16px;padding:16px;color:var(--secondary);font-weight:800;line-height:1.35}
        .sim-layout{display:grid;gap:18px}
        .score-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
        .score{border:1px solid var(--line);border-radius:16px;background:var(--white);padding:16px;text-align:center}
        .score span{display:block;font-size:.82rem;color:var(--muted);font-weight:800}
        .score strong{display:block;font-size:clamp(2rem,7vw,4.5rem);line-height:1;color:var(--secondary);margin-top:6px}
        .score-input{width:100%;border:0;background:transparent;color:var(--secondary);font-size:clamp(2rem,7vw,4.5rem);line-height:1;font-weight:950;text-align:center;margin-top:6px;min-height:72px;padding:0}
        .score-input:focus{outline:3px solid rgba(38,186,165,.18);border-radius:12px}
        .sim-note-card{animation:notePulse 1.55s ease-in-out infinite;border-color:rgba(38,186,165,.65)}
        .sim-note-card.has-note{animation:none;border-color:var(--line)}
        .sim-note-card .note-hint{justify-content:center;margin-top:8px}
        .sim-note-card.has-note .note-hint{display:none}
        .range-panel{background:var(--soft);border:1px solid var(--line);border-radius:18px;padding:18px}
        .locked-panel{background:var(--soft);border:1px solid var(--line);border-radius:18px;padding:22px;display:grid;gap:12px;max-width:680px}
        .locked-title{font-size:1.3rem;font-weight:950;color:var(--secondary)}
        .locked-actions{display:flex;gap:10px;flex-wrap:wrap}
        input[type=range]{accent-color:var(--primary);padding:0;border:0;background:transparent;min-height:42px}
        .state-message{border:1px solid var(--line);border-left:8px solid var(--secondary);background:var(--white);border-radius:14px;padding:14px;font-weight:800}
        .state-message.passed{border-color:rgba(38,186,165,.35);border-left-color:var(--ok);background:rgba(38,186,165,.1);color:var(--secondary)}
        .state-message.warning{border-color:rgba(245,158,11,.35);border-left-color:var(--warn);background:rgba(245,158,11,.12);color:var(--black)}
        .state-message.risk{border-color:rgba(239,68,68,.35);border-left-color:var(--danger);background:rgba(239,68,68,.1);color:var(--black)}
        .chat-link{color:var(--primary);font-weight:900;text-decoration:underline;text-underline-offset:3px}
        .chat-shell{display:grid;grid-template-rows:minmax(340px,54vh) auto;gap:12px}
        .chatbox{overflow:auto;border:1px solid var(--line);background:#fbfcfc;border-radius:18px;padding:14px}
        .bubble{max-width:min(84%,640px);padding:12px 14px;border-radius:16px;margin:9px 0;line-height:1.4}
        .bubble.bot{background:var(--white);border:1px solid var(--line)}
        .bubble.me{background:var(--primary);color:var(--white);margin-left:auto}
        .typing-indicator{display:inline-flex;gap:5px;align-items:center;min-width:42px}
        .typing-indicator span{width:7px;height:7px;border-radius:50%;background:var(--muted);opacity:.35;animation:typingPulse 1s infinite ease-in-out}
        .typing-indicator span:nth-child(2){animation-delay:.16s}
        .typing-indicator span:nth-child(3){animation-delay:.32s}
        @keyframes typingPulse{
            0%,80%,100%{transform:translateY(0);opacity:.35}
            40%{transform:translateY(-4px);opacity:1}
        }
        .chat-form{display:grid;grid-template-columns:1fr auto;gap:10px}
        .chat-input-wrap{position:relative}
        .chat-input-wrap input{padding-right:38px}
        .chat-input-wrap::after{content:"";position:absolute;right:16px;top:50%;width:9px;height:9px;border-radius:50%;background:var(--primary);transform:translateY(-50%);animation:chatPulse 1.4s ease-in-out infinite}
        .chat-input-wrap:focus-within::after,.chat-input-wrap.has-text::after{display:none}
        .chat-input-wrap input:placeholder-shown{animation:notePulse 1.55s ease-in-out infinite;border-color:rgba(38,186,165,.65)}
        @keyframes chatPulse{50%{transform:translateY(-50%) scale(1.5);opacity:.35}}
        .history{margin-top:18px;border-top:1px solid var(--line);padding-top:12px}
        .history-item{padding:10px 0;border-bottom:1px solid var(--line);font-size:.92rem;color:var(--muted)}
        .select2-container{width:100%!important}
        .select2-container .select2-selection--single{height:58px;border:1px solid var(--line);border-radius:14px;display:flex;align-items:center}
        .select2-container--default .select2-selection--single .select2-selection__rendered{color:var(--secondary);line-height:58px;padding-left:13px;padding-right:34px}
        .select2-container--default .select2-selection--single .select2-selection__arrow{height:58px}
        .select2-dropdown{border-color:var(--line);border-radius:14px;overflow:hidden}
        .select2-container--default .select2-results__option--highlighted[aria-selected]{background:var(--primary);color:var(--white)}
        .select2-container--default .select2-results__option--selected{background:var(--soft);color:var(--secondary)}
        .school-option{padding:4px 0;color:var(--secondary)}
        .school-option strong{display:block;color:var(--secondary)}
        .school-option span{display:block;color:var(--muted);font-size:.82rem;margin-top:2px}
        @media(max-width:860px){
            .app{padding:12px}
            .topbar{align-items:flex-start}
            .nav{grid-template-columns:1fr}
            .section{padding:14px;border-radius:16px}
            .section-head{display:block}
            .calc-grid{grid-template-columns:1fr}
            .profile-grid{grid-template-columns:1fr}
            .profile-fields{grid-template-columns:1fr}
            .support-card{min-height:130px}
            .score-grid{grid-template-columns:1fr}
            .chat-form{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
<div class="app">
    <header class="topbar">
        <div class="brand">
            <div class="mark"><img src="{{ asset('images/isologo.jpg') }}" alt=""></div>
            <div>
                <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="ife notas">
                <div class="meta">{{ $user->phone }} </div>
            </div>
        </div>
        <div class="top-actions">
            <a href="{{ route('live.notes') }}" class="btn btn-primary" target="_blank" rel="noopener">LIVE</a>
            @if($user->is_admin)<a href="{{ route('admin') }}" class="btn">Admin</a>@endif
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-dark" type="submit">Salir</button></form>
        </div>
    </header>

    @if(session('status'))
        <div class="profile-note">{{ session('status') }}</div>
    @endif

    <nav class="nav" aria-label="Acciones principales">
        <button class="nav-btn active" data-target="calcular" type="button">CALCULAR</button>
        <button class="nav-btn" data-target="simulador" type="button">SIMULADOR</button>
        <button class="nav-btn" data-target="chat" type="button">CHATO</button>
        <button class="nav-btn" data-target="historial" type="button">HISTORIAL</button>
        <button class="nav-btn" data-target="perfil" type="button">MI PERFIL</button>
    </nav>

    <main>
        <section class="section active" id="calcular">
            <div class="section-head">
                <div>
                    <h1>Calcula tu ruta para pasar de curso</h1>
                    <p class="lead">Ingresa tu nota y materia. Guardaremos el resultado para que puedas revisarlo cuando quieras.</p>
                </div>
            </div>

            <div class="calc-grid">
                <form class="form-stack" id="calculateForm">
                    <div class="note-field">
                        <label for="firstTerm">Nota</label>
                        <input class="note-input" id="firstTerm" type="number" min="0" max="100" step="1" placeholder="Ingresa tu nota del primer trimestre" inputmode="numeric" required>
                        <div class="note-hint">Escribe aquí tu nota de 0 a 100</div>
                    </div>
                    <div>
                        <label for="subject">Materia</label>
                        <select id="subject" required>
                            <option>Matemática</option>
                            <option>Física</option>
                            <option>Química</option>
                            <option>Lenguaje</option>
                            <option>Ciencias Sociales</option>
                            <option>Educación Física</option>
                            <option>Educación Musical</option>
                            <option>Artes Plásticas</option>
                            <option>Técnica Tecnológica</option>
                            <option>Ciencias Naturales</option>
                            <option>Filosofía y Sicología</option>
                            <option>Valores Religión</option>
                            <option>Lengua Extranjera</option>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-full" type="submit">Calcular y guardar</button>
                </form>

                <aside class="support-card" aria-live="polite">
                    <div class="support-person">
                        <img src="{{ asset('images/david.png') }}" alt="David, autor de ife notas">
                        <div class="support-title">Apoyo escolar</div>
                    </div>
                    <div class="support-text" id="supportRotator">Podemos ayudarte a subir tus notas con clases de apoyo personalizadas.</div>
                </aside>
            </div>
        </section>

        <section class="section" id="perfil">
            <div class="section-head">
                <div>
                    <h1>Mi perfil</h1>
                    <p class="lead">El colegio es opcional. Puedes guardarlo para que tus cálculos queden mejor organizados.</p>
                </div>
            </div>

            <div class="profile-grid">
                <form class="form-stack" id="profileForm">
                    <div class="profile-fields">
                        <div>
                            <label for="profileName">Nombre del estudiante</label>
                            <input id="profileName" name="name" value="{{ $user->name }}" placeholder="Tu nombre completo" required>
                        </div>
                        <div>
                            <label for="profilePhone">Teléfono de cuenta</label>
                            <input id="profilePhone" value="{{ $user->phone }}" readonly>
                        </div>
                        <div>
                            <label for="profileEmail">Email opcional</label>
                            <input id="profileEmail" name="email" type="email" value="{{ Str::endsWith($user->email, '@notes.local') ? '' : $user->email }}" placeholder="tu@email.com">
                        </div>
                        <div>
                            <label for="profileGrade">Curso / grado</label>
                            <input id="profileGrade" name="grade_level" value="{{ $user->grade_level }}" placeholder="Ej: 4to de secundaria">
                        </div>
                        <div>
                            <label for="guardianName">Nombre del apoderado</label>
                            <input id="guardianName" name="guardian_name" value="{{ $user->guardian_name }}" placeholder="Opcional">
                        </div>
                        <div>
                            <label for="guardianPhone">Teléfono del apoderado</label>
                            <input id="guardianPhone" name="guardian_phone" value="{{ $user->guardian_phone }}" inputmode="tel" placeholder="Opcional">
                        </div>
                    </div>
                    <div>
                        <label for="schoolSelect">Colegio opcional</label>
                        <select id="schoolSelect">
                            @if($user->school)
                                <option value="{{ $user->school->id }}" selected>{{ $user->school->nombre }} · RUE: {{ $user->school->codigo_rue }}</option>
                            @endif
                        </select>
                    </div>
                    <button class="btn btn-primary btn-full" type="submit">Guardar perfil</button>
                    <button class="btn btn-full" id="clearSchoolBtn" type="button">Quitar colegio</button>
                </form>

                <aside class="profile-note" id="profileStatus">
                    @if($user->school)
                        Colegio actual: {{ $user->school->nombre }}
                    @else
                        No tienes colegio guardado. Puedes usar la app sin seleccionar uno.
                    @endif
                </aside>
            </div>
        </section>

        <section class="section" id="simulador">
            <div class="section-head">
                <div>
                    <h1>Simula segundo y tercer trimestre</h1>
                    <p class="lead">La nota y materia se heredan desde Calcular. Ajusta el segundo trimestre y mira el tercero en tiempo real.</p>
                </div>
            </div>

            @unless($user->is_follower_unlocked)
                <div class="locked-panel">
                    <div class="locked-title">Tu acceso se habilitará automáticamente.</div>
                    <p class="lead">Actualiza la página en unos segundos para usar el simulador.</p>
                </div>
            @else

            <div class="sim-layout">
                <div class="score-grid">
                        <div class="score sim-note-card" id="simNoteCard">
                            <span>Primer trimestre</span>
                            <input class="score-input" id="simFirstInput" type="number" min="0" max="100" step="1" inputmode="numeric" placeholder="Nota">
                            <div class="note-hint">Ingresa tu nota para empezar la simulación</div>
                        </div>
                    <div class="score"><span>Segundo trimestre</span><strong id="simSecond">50</strong></div>
                    <div class="score"><span>Tercer trimestre necesario</span><strong id="simThird">0</strong></div>
                </div>

                <div class="range-panel">
                    <label for="secondTerm">Nota del segundo trimestre</label>
                    <input id="secondTerm" type="range" min="0" max="100" value="50">
                    <div class="state-message" id="simMessage">Mueve la barra para simular escenarios.</div>
                </div>
            </div>
            @endunless
        </section>

        <section class="section" id="chat">
            <div class="section-head">
                <div>
                    <h1>Chat académico</h1>
                    <p class="lead">Una conversación rápida para calcular otra materia o reforzar tu plan de estudio.</p>
                </div>
                @if($user->is_follower_unlocked)
                    <div class="top-actions">
                        <button class="btn" data-target="historial" type="button">Historial</button>
                        <a class="btn btn-primary" href="https://www.tiktok.com/@ife_educabol" target="_blank" rel="noopener">Ver perfil</a>
                    </div>
                @endif
            </div>

            @unless($user->is_follower_unlocked)
                <div class="locked-panel">
                    <div class="locked-title">Tu acceso se habilitará automáticamente.</div>
                    <p class="lead">Actualiza la página en unos segundos para usar el chat académico.</p>
                </div>
            @else

                <div class="chat-shell">
                    <div class="chatbox" id="chatbox"></div>
                    <form class="chat-form" id="chatForm">
                        <div class="chat-input-wrap" id="chatInputWrap">
                            <input id="chatInput" autocomplete="off" placeholder="Escribe tu respuesta">
                        </div>
                        <button class="btn btn-dark" type="submit">Responder</button>
                    </form>
                </div>
            @endunless

        </section>

        <section class="section" id="historial">
            <div class="section-head">
                <div>
                    <h1>Historial</h1>
                    <p class="lead">Tus simulaciones y cálculos guardados aparecen en esta vista.</p>
                </div>
            </div>

            <div class="history">
                @forelse($histories as $item)
                    <div class="history-item">
                        <strong>{{ $item->subject }}</strong>
                        @if($item->school)
                            · {{ $item->school->nombre }}
                        @endif
                        · T1: {{ $item->first_term }} · T2: {{ $item->second_term }} · T3: {{ $item->third_term_needed }}
                        <div>{{ $item->summary }}</div>
                    </div>
                @empty
                    <div class="history-item">Aún no guardaste simulaciones.</div>
                @endforelse
            </div>
        </section>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const PASS_SCORE = {{ $passScore }};
const USER_NAME = @json(trim($user->name ?? ''));
const IS_FOLLOWER_UNLOCKED = @json($user->is_follower_unlocked);
const routes = {
    schools: @json(route('schools.search')),
    profile: @json(route('profile.update')),
    calculations: @json(route('calculations.save')),
    simulations: @json(route('simulations.save')),
    aiChat: @json(route('ai.chat'))
};
const csrf = @json(csrf_token());
const state = {
    subject: 'Matemática',
    first: 0,
    second: 50,
    schoolId: @json($user->school_id),
    schoolText: @json($user->school ? $user->school->nombre . ' · RUE: ' . $user->school->codigo_rue : '')
};
const SUBJECTS = [
    'Matemática',
    'Física',
    'Química',
    'Lenguaje',
    'Ciencias Sociales',
    'Educación Física',
    'Educación Musical',
    'Artes Plásticas',
    'Técnica Tecnológica',
    'Ciencias Naturales',
    'Filosofía y Sicología',
    'Valores Religión',
    'Lengua Extranjera'
];

const firstTerm = document.getElementById('firstTerm');
const subject = document.getElementById('subject');
const secondTerm = document.getElementById('secondTerm');
const simFirstInput = document.getElementById('simFirstInput');
const simNoteCard = document.getElementById('simNoteCard');
const simSecond = document.getElementById('simSecond');
const simThird = document.getElementById('simThird');
const simMessage = document.getElementById('simMessage');
const chatbox = document.getElementById('chatbox');
const chatInput = document.getElementById('chatInput');
const chatInputWrap = document.getElementById('chatInputWrap');
const supportRotator = document.getElementById('supportRotator');
const profileStatus = document.getElementById('profileStatus');
const profileName = document.getElementById('profileName');
const profileEmail = document.getElementById('profileEmail');
const profileGrade = document.getElementById('profileGrade');
const guardianName = document.getElementById('guardianName');
const guardianPhone = document.getElementById('guardianPhone');
let chatStep = 'subject';
let chatDraft = { subject: '', first: null };
let aiHistory = [];
let chatStarted = false;
let chatTyping = false;
let lastChatActivity = Date.now();
let saveTimer;
let supportIndex = 0;
const SUPPORT_MESSAGES = [
    'Podemos ayudarte a subir tus notas con clases de apoyo personalizadas.',
    'Prepárate mejor para el segundo y tercer trimestre con acompañamiento escolar.',
    'Refuerza las materias difíciles antes de que se acumulen los temas.',
    'Agenda apoyo escolar y estudia con una ruta clara para pasar de curso.',
    'También podemos ayudarte a adelantar temas y mejorar tu promedio.'
];

function clamp(value){ return Math.max(0, Math.min(100, parseInt(value, 10) || 0)); }
function normalizeText(value){
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}
function levenshtein(a, b){
    const matrix = Array.from({ length: b.length + 1 }, (_, row) => [row]);
    for (let col = 0; col <= a.length; col++) matrix[0][col] = col;
    for (let row = 1; row <= b.length; row++) {
        for (let col = 1; col <= a.length; col++) {
            matrix[row][col] = b[row - 1] === a[col - 1]
                ? matrix[row - 1][col - 1]
                : Math.min(matrix[row - 1][col - 1] + 1, matrix[row][col - 1] + 1, matrix[row - 1][col] + 1);
        }
    }
    return matrix[b.length][a.length];
}
function similarSubjects(input){
    const query = normalizeText(input);
    if (query === '') return [];

    return SUBJECTS.map((name) => {
        const normalized = normalizeText(name);
        let score = 0;

        if (normalized === query) score = 100;
        else if (normalized.startsWith(query)) score = 85;
        else if (normalized.includes(query)) score = 70;
        else {
            const distance = levenshtein(query, normalized);
            score = Math.round((1 - distance / Math.max(query.length, normalized.length)) * 100);
        }

        return { name, score };
    })
        .filter((item) => item.score >= 55)
        .sort((a, b) => b.score - a.score)
        .slice(0, 3);
}
function strictGrade(value){
    const clean = String(value || '').trim();
    if (!/^\d+$/.test(clean)) return null;

    const grade = Number(clean);
    return grade >= 0 && grade <= 100 ? grade : null;
}
function sanitizeGradeInput(input){
    const clean = String(input.value || '').replace(/\D/g, '').slice(0, 3);
    if (clean === '') {
        input.value = '';
        return 0;
    }

    const grade = Math.min(100, Number(clean));
    input.value = String(grade);

    return grade;
}
function totalNeeded(){ return Math.max(0, PASS_SCORE - state.first); }
function averageNeeded(){ return Math.max(0, Math.ceil(totalNeeded() / 2)); }
function thirdNeeded(){ return Math.max(0, PASS_SCORE - state.first - state.second); }
function statusFor(third){
    if (third === 0) return 'passed';
    if (third <= 70) return 'passed';
    if (third <= 100) return 'warning';
    return 'risk';
}
function recommendation(){
    const third = thirdNeeded();
    if (third === 0) return 'Verde: con este escenario ya tienes lo necesario para pasar de curso.';
    if (third <= 70) return 'Verde: todo va bien. Con este escenario estas encaminado para pasar de curso.';
    if (third <= 100) return 'Naranja: estas cerca de aplazarte. Necesitas reforzar para asegurar la materia.';
    return 'Rojo: con este escenario no te alcanza, porque necesitarias mas de 100 en el tercer trimestre.';
}
function updateSimulator(options = {}){
    if (!IS_FOLLOWER_UNLOCKED || !simFirstInput || !secondTerm || !simSecond || !simThird || !simMessage) return;

    const syncForm = options.syncForm ?? true;
    state.first = simFirstInput.value === '' ? 0 : sanitizeGradeInput(simFirstInput);
    state.subject = subject.value || state.subject;
    state.second = clamp(secondTerm.value);

    const third = thirdNeeded();

    if (syncForm) firstTerm.value = state.first;
    simSecond.textContent = state.second;
    simThird.textContent = third > 100 ? '100+' : third;
    simMessage.className = `state-message ${statusFor(third)}`;
    simMessage.textContent = recommendation();
    simNoteCard?.classList.toggle('has-note', state.first > 0);
}
function syncSimulatorFromForm(){
    if (!simFirstInput) return;

    simFirstInput.value = firstTerm.value === '' ? '' : String(state.first);
    simNoteCard?.classList.toggle('has-note', state.first > 0);
}
function supportMessage(){
    if (state.first < 45) return 'Te recomendamos reforzar esta materia con apoyo escolar. Escríbenos desde nuestros perfiles oficiales.';
    if (state.first < 70) return 'Podemos ayudarte a prepararte mejor para el segundo y tercer trimestre.';
    return 'También podemos ayudarte a adelantar temas y perfeccionar tu nivel.';
}
function syncUI(){
    const clean = String(firstTerm.value || '').replace(/\D/g, '').slice(0, 3);
    if (clean === '') {
        state.first = 0;
        state.subject = subject.value;
        state.second = secondTerm ? clamp(secondTerm.value) : state.second;
        syncSimulatorFromForm();
        updateSimulator({ syncForm: false });
        return;
    }

    state.first = Math.min(100, Number(clean));
    firstTerm.value = String(state.first);
    state.subject = subject.value;
    state.second = secondTerm ? clamp(secondTerm.value) : state.second;
    syncSimulatorFromForm();
    updateSimulator({ syncForm: false });
}
async function postJson(url, payload){
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(payload)
    });
    if (!response.ok) throw new Error('No se pudo guardar.');
    return response.json();
}
function calculationPayload(){
    return {
        kind: 'calculation',
        subject: state.subject,
        school_id: state.schoolId,
        first_term: state.first,
        second_term: 0,
        third_term_needed: averageNeeded(),
        status: statusFor(thirdNeeded()),
        summary: `Necesita ${totalNeeded()} puntos en total y aproximadamente ${averageNeeded()} por trimestre.`,
        meta: {
            total_needed: totalNeeded(),
            school_text: state.schoolText
        }
    };
}
function simulationPayload(){
    return {
        subject: state.subject,
        first_term: state.first,
        second_term: state.second,
        third_term_needed: thirdNeeded(),
        status: statusFor(thirdNeeded()),
        summary: recommendation()
    };
}
function saveSimulationSoon(){
    if (!IS_FOLLOWER_UNLOCKED) return;
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
        if (state.first > 0) postJson(routes.simulations, simulationPayload()).catch(() => {});
    }, 700);
}
function addBubble(text, type = 'bot'){
    lastChatActivity = Date.now();
    const bubble = document.createElement('div');
    bubble.className = `bubble ${type}`;
    bubble.appendChild(formatChatText(text));
    chatbox.appendChild(bubble);
    chatbox.scrollTop = chatbox.scrollHeight;

    if (type === 'bot' || type === 'me') {
        aiHistory.push({
            role: type === 'bot' ? 'assistant' : 'user',
            content: text
        });
        aiHistory = aiHistory.slice(-10);
    }
}
function typingDots(){
    const dots = document.createElement('span');
    dots.className = 'typing-indicator';
    dots.setAttribute('aria-label', 'Escribiendo');
    dots.innerHTML = '<span></span><span></span><span></span>';

    return dots;
}
function wait(ms){
    return new Promise((resolve) => setTimeout(resolve, ms));
}
async function typeIntoBubble(bubble, text){
    bubble.textContent = '';

    const plain = String(text || '');
    for (let index = 0; index < plain.length; index++) {
        bubble.textContent += plain[index];
        chatbox.scrollTop = chatbox.scrollHeight;
        await wait(12);
    }

    bubble.textContent = '';
    bubble.appendChild(formatChatText(text));
}
async function addBotBubbleAnimated(text){
    chatTyping = true;
    lastChatActivity = Date.now();
    const bubble = document.createElement('div');
    bubble.className = 'bubble bot';
    bubble.appendChild(typingDots());
    chatbox.appendChild(bubble);
    chatbox.scrollTop = chatbox.scrollHeight;

    await wait(450);
    await typeIntoBubble(bubble, text);

    aiHistory.push({ role: 'assistant', content: text });
    aiHistory = aiHistory.slice(-10);
    chatTyping = false;
}
function greetingMessage(){
    const firstName = USER_NAME.split(/\s+/).filter(Boolean)[0] || '';
    const greeting = firstName ? `Hola, ${firstName}.` : 'Hola.';

    return `${greeting} ¿Con qué materia quisieras empezar?`;
}
function outOfScopeMessage(){
    return 'Estoy especializado en decirte cuántos puntos necesitas para pasar de curso. Para otros temas, revisa nuestros perfiles oficiales. Comunícate al número del perfil de TikTok.';
}
function reminderMessage(){
    return '¿Qué otra materia quieres saber cuántos puntos te hacen falta para pasar de curso?';
}
function isProbablyOutOfScope(text){
    const normalized = normalizeText(text);
    const subjectWords = SUBJECTS.flatMap((name) => normalizeText(name).split(/\s+/));
    const allowedWords = [
        'nota', 'notas', 'punto', 'puntos', 'curso', 'pasar', 'aprobar', 'aplazar', 'materia',
        'trimestre', 'primer', 'segundo', 'tercer', 'calcular', 'simular', 'cuanto', 'cuantos',
        'necesito', 'necesita', 'me', 'falta', 'faltan', 'saque', 'sacar', 'promedio', 'apoyo',
        ...subjectWords
    ];

    return normalized.split(/\s+/).filter(Boolean).every((word) => !allowedWords.includes(word));
}
function sanitizeAiReply(text){
    let clean = String(text || '')
        .replace(/para alcanzar la nota m[ií]nima(?:\s+de\s+\d+)?/gi, 'para pasar de curso')
        .replace(/nota m[ií]nima(?:\s+anual)?(?:\s+de\s+\d+)?/gi, 'puntos necesarios para pasar de curso');

    clean = clean.replace(/\[([^\]]+)\]\(https:\/\/wa\.me\/[^\s)]*\)?/g, '$1');
    clean = clean.replace(/https:\/\/wa\.me\/[^\s]*/g, '');

    return clean.replace(/[ \t]{2,}/g, ' ').replace(/\n{3,}/g, '\n\n').trim();
}
function formatChatText(text){
    const fragment = document.createDocumentFragment();
    const parts = sanitizeAiReply(text).split(/(\*\*[^*]+\*\*)/g);

    parts.forEach((part) => {
        if (part.startsWith('**') && part.endsWith('**')) {
            const strong = document.createElement('strong');
            strong.textContent = part.slice(2, -2);
            fragment.appendChild(strong);
            return;
        }

        fragment.appendChild(document.createTextNode(part));
    });

    return fragment;
}
async function askAi(message){
    const response = await fetch(routes.aiChat, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({
            message,
            history: aiHistory.slice(-8),
            context: {
                subject: state.subject,
                first_term: state.first,
                total_needed: totalNeeded(),
                average_needed: averageNeeded(),
                pass_score: PASS_SCORE
            }
        })
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.message || 'No se pudo conectar con la IA.');
    }

    return sanitizeAiReply(data.message || 'Estoy listo para ayudarte con tus notas.');
}
async function addAiBubble(message){
    chatTyping = true;
    lastChatActivity = Date.now();
    const typing = document.createElement('div');
    typing.className = 'bubble bot';
    typing.appendChild(typingDots());
    chatbox.appendChild(typing);
    chatbox.scrollTop = chatbox.scrollHeight;

    try {
        const reply = await askAi(message);
        await typeIntoBubble(typing, reply);
        aiHistory.push({ role: 'assistant', content: reply });
        aiHistory = aiHistory.slice(-10);
    } catch (error) {
        typing.textContent = error.message || 'No pude conectar con la IA en este momento.';
    } finally {
        chatTyping = false;
    }
}
async function startChat(){
    chatbox.innerHTML = '';
    aiHistory = [];
    chatDraft = { subject: '', first: null };
    chatStep = 'subject';
    updateChatInput();
    await addBotBubbleAnimated(greetingMessage());
    chatStarted = true;
}
function updateChatInput(){
    if (chatStep === 'note') {
        chatInput.type = 'number';
        chatInput.min = '0';
        chatInput.max = '100';
        chatInput.step = '1';
        chatInput.inputMode = 'numeric';
        chatInput.placeholder = 'Escribe una nota de 0 a 100';
        return;
    }

    chatInput.type = 'text';
    chatInput.removeAttribute('min');
    chatInput.removeAttribute('max');
    chatInput.removeAttribute('step');
    chatInput.inputMode = 'text';
    chatInput.placeholder = chatStep === 'subject' ? 'Ejemplo: matemática, química, ciencias sociales' : 'Escribe tu respuesta';
}
async function handleChatAnswer(answer){
    if (!answer) return;
    lastChatActivity = Date.now();
    addBubble(answer, 'me');

    if (chatStep === 'restart') {
        chatDraft = { subject: '', first: null };
        await addAiBubble('El estudiante quiere hacer otra consulta. Pídele la materia de forma breve.');
        chatStep = 'subject';
        updateChatInput();
        return;
    }
    if (chatStep === 'subject') {
        const matches = similarSubjects(answer);

        if (matches.length === 0) {
            if (isProbablyOutOfScope(answer)) {
                await addBotBubbleAnimated(outOfScopeMessage());
            } else {
                await addBotBubbleAnimated(`Escribe una materia válida: ${SUBJECTS.join(', ')}.`);
            }
            updateChatInput();

            return;
        }

        chatDraft.subject = matches[0].name;
        await addBotBubbleAnimated(`Materia: ${chatDraft.subject}. ¿Cuál fue tu nota del primer trimestre?`);
        chatStep = 'note';
        updateChatInput();
        return;
    }
    if (chatStep === 'note') {
        const grade = strictGrade(answer);

        if (grade === null) {
            await addAiBubble('El estudiante ingresó una nota inválida. Explícale que debe ser un número entero entre 0 y 100 y pídele intentarlo de nuevo.');
            updateChatInput();

            return;
        }

        chatDraft.first = grade;
        state.subject = chatDraft.subject;
        state.first = chatDraft.first;
        subject.value = chatDraft.subject;
        firstTerm.value = chatDraft.first;
        syncUI();
        const needed = Math.max(0, PASS_SCORE - chatDraft.first);
        await addAiBubble(`Materia: ${chatDraft.subject}. Nota primer trimestre: ${chatDraft.first}. Puntos restantes para pasar de curso entre segundo y tercer trimestre: ${needed}. Promedio aproximado por trimestre restante: ${Math.ceil(needed / 2)}. Explica el resultado de forma directa y ofrece apoyo escolar sin incluir números ni enlaces externos. Termina con: Comunícate al número del perfil de TikTok.`);
        chatStep = 'restart';
        updateChatInput();
    }
}

function showSection(target){
    document.querySelectorAll('.nav-btn').forEach((item) => {
        item.classList.toggle('active', item.dataset.target === target);
    });
    document.querySelectorAll('.section').forEach((item) => item.classList.remove('active'));
    document.getElementById(target).classList.add('active');
    if (target === 'simulador' && IS_FOLLOWER_UNLOCKED) updateSimulator();
    if (target === 'chat' && IS_FOLLOWER_UNLOCKED && !chatStarted) startChat();
}
document.querySelectorAll('[data-target]').forEach((button) => {
    button.addEventListener('click', () => {
        showSection(button.dataset.target);
    });
});

$(function(){
    $('#schoolSelect').select2({
        placeholder: 'Busca por nombre o RUE',
        minimumInputLength: 2,
        ajax: {
            url: routes.schools,
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term || '' }),
            processResults: data => data
        },
        templateResult: function(item){
            if (!item.id) return item.text;

            const root = document.createElement('div');
            root.className = 'school-option';

            const name = document.createElement('strong');
            name.textContent = item.name || item.text || '';
            root.appendChild(name);

            const location = document.createElement('span');
            location.textContent = `RUE: ${item.rue || '-'} · ${item.location || '-'}`;
            root.appendChild(location);

            if (item.details) {
                const details = document.createElement('span');
                details.textContent = item.details;
                root.appendChild(details);
            }

            return $(root);
        },
        templateSelection: item => item.name || item.text || 'Colegio seleccionado',
        language: {
            inputTooShort: () => 'Escribe al menos 2 caracteres',
            searching: () => 'Buscando colegios...',
            noResults: () => 'No se encontraron colegios'
        }
    }).on('select2:select', function(event){
        const item = event.params.data;
        state.schoolId = item.id;
        state.schoolText = item.rue ? `${item.name || item.text} · RUE: ${item.rue}` : (item.name || item.text || '');
    });
});

firstTerm.addEventListener('input', syncUI);
subject.addEventListener('change', syncUI);
if (simFirstInput) {
    simFirstInput.addEventListener('input', updateSimulator);
    simFirstInput.addEventListener('change', updateSimulator);
}
if (secondTerm) {
    secondTerm.addEventListener('input', updateSimulator);
    secondTerm.addEventListener('change', () => { updateSimulator(); saveSimulationSoon(); });
    secondTerm.addEventListener('pointerup', () => { updateSimulator(); saveSimulationSoon(); });
}

document.getElementById('calculateForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    syncUI();

    if (state.first > 0) {
        postJson(routes.calculations, calculationPayload()).catch(() => {});
    }

    if (supportRotator) {
        supportRotator.textContent = `Resultado guardado. ${supportMessage()}`;
    }
});

document.getElementById('profileForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    await postJson(routes.profile, {
        name: profileName.value.trim(),
        email: profileEmail.value.trim() || null,
        grade_level: profileGrade.value.trim() || null,
        guardian_name: guardianName.value.trim() || null,
        guardian_phone: guardianPhone.value.trim() || null,
        school_id: state.schoolId || null
    });
    profileStatus.textContent = state.schoolText
        ? `Perfil guardado. Colegio: ${state.schoolText}`
        : 'Perfil guardado sin colegio. Puedes usar la app normalmente.';
});

document.getElementById('clearSchoolBtn').addEventListener('click', async () => {
    state.schoolId = null;
    state.schoolText = '';
    $('#schoolSelect').val(null).trigger('change');
    await postJson(routes.profile, {
        name: profileName.value.trim(),
        email: profileEmail.value.trim() || null,
        grade_level: profileGrade.value.trim() || null,
        guardian_name: guardianName.value.trim() || null,
        guardian_phone: guardianPhone.value.trim() || null,
        school_id: null
    });
    profileStatus.textContent = 'Colegio eliminado. Puedes usar la app sin seleccionar uno.';
});

const chatForm = document.getElementById('chatForm');
if (chatForm && chatInput) {
    chatInput.addEventListener('input', () => {
        chatInputWrap?.classList.toggle('has-text', chatInput.value.trim() !== '');
    });

    chatForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        await handleChatAnswer(chatInput.value.trim());
        chatInput.value = '';
        chatInputWrap?.classList.remove('has-text');
    });
}

syncUI();
setInterval(() => {
    supportIndex = (supportIndex + 1) % SUPPORT_MESSAGES.length;
    supportRotator.textContent = SUPPORT_MESSAGES[supportIndex];
}, 4500);
setInterval(() => {
    const chatIsActive = document.getElementById('chat').classList.contains('active');
    const shouldRemind = chatStarted && chatIsActive && !chatTyping && Date.now() - lastChatActivity > 90000;

    if (shouldRemind) {
        addBotBubbleAnimated(reminderMessage());
    }
}, 15000);
</script>
</body>
</html>
