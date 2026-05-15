<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel académico</title>
    <style>
        :root{--bg:#f5f7f8;--card:#fff;--text:#1f2933;--muted:#66717d;--line:#d9e0e6;--primary:#1f7a6e;--soft:#e7f3f1;--danger:#fdecec;--warn:#fff5e6;--ok:#ecf8f6}
        *{box-sizing:border-box}
        body{margin:0;font-family:"Segoe UI",sans-serif;color:var(--text);background:var(--bg)}
        .app{max-width:980px;margin:0 auto;padding:16px}
        .top,.card{background:var(--card);border:1px solid var(--line);border-radius:12px}
        .top{padding:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
        .title{font-size:1.05rem;font-weight:700}
        .meta{margin-top:3px;color:var(--muted);font-size:.9rem}
        .card{margin-top:12px;padding:14px}
        .tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
        .btn{border:1px solid var(--line);border-radius:10px;padding:9px 12px;font-weight:600;background:#fff;cursor:pointer;color:var(--text);text-decoration:none}
        .btn-primary{background:var(--primary);border-color:var(--primary);color:#fff}
        .btn-soft{background:var(--soft);border-color:var(--soft);color:var(--primary)}
        .tab.active{background:var(--primary);border-color:var(--primary);color:#fff}
        .panel{display:none}.panel.active{display:block}
        .label{display:block;margin:10px 0 6px;font-size:.92rem;color:var(--muted)}
        input,select{width:100%;border:1px solid var(--line);border-radius:10px;padding:10px;background:#fff}
        .msg{margin-top:10px;border-radius:10px;padding:11px;background:#f2f5f7}
        .status-baja{background:var(--danger)}.status-regular{background:var(--warn)}.status-buena,.status-excelente{background:var(--ok)}
        .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:10px}
        .stat{background:#f8fafb;border:1px solid var(--line);border-radius:10px;padding:10px;text-align:center}
        .big{font-size:1.3rem;font-weight:700}
        .chatbox{height:250px;overflow:auto;background:#fafcfd;border:1px solid var(--line);border-radius:10px;padding:10px}
        .bubble{max-width:86%;padding:8px 10px;border-radius:10px;margin:7px 0;font-size:.9rem}
        .bot{background:#edf2f6}.me{background:#e8f3f1;margin-left:auto}
        .history-item{border-top:1px solid var(--line);padding:8px 0;font-size:.9rem;color:var(--muted)}
        .actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
        @media(max-width:860px){.stats{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="app">
    <div class="top">
        <div>
            <div class="title">Calculadora académica</div>
            <div class="meta">{{ $user->phone }} | Mínimo anual: {{ $passScore }} / 300</div>
        </div>
        <div class="actions" style="margin-top:0;">
            @if($user->is_admin)<a href="{{ route('admin') }}" class="btn btn-soft">Panel admin</a>@endif
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-primary" type="submit">Salir</button></form>
        </div>
    </div>

    @if(session('saved'))<div class="card">{{ session('saved') }}</div>@endif

    <div class="card">
        <div class="tabs">
            <button class="btn tab active" data-tab="calculo" type="button">Calcular</button>
            <button class="btn tab" data-tab="simulador" type="button">Simulador</button>
            <button class="btn tab" data-tab="chat" type="button">Chat</button>
        </div>

        <section id="calculo" class="panel active">
            <label class="label">Materia</label>
            <select id="subject"><option>Matemáticas</option><option>Física</option><option>Química</option><option>Lenguaje</option><option>Inglés</option><option>Historia</option><option>Biología</option></select>
            <label class="label">Nota del primer trimestre (0-100)</label>
            <input id="firstTerm" type="number" min="0" max="100" value="40">
            <button class="btn btn-primary" id="calculateBtn" style="margin-top:10px;" type="button">Calcular</button>
            <div id="message" class="msg"></div>
            <div class="actions">
                <button class="btn btn-soft" id="saveBtn" type="button">Guardar</button>
                <a class="btn btn-soft" id="whatsappResult" target="_blank" href="https://wa.me/59171324941">Enviar por WhatsApp</a>
            </div>
        </section>

        <section id="simulador" class="panel">
            <div class="stats">
                <div class="stat"><div>Promedio necesario</div><div class="big" id="avgNeed">57</div></div>
                <div class="stat"><div>Segundo trimestre</div><div class="big" id="secondValue">60</div></div>
                <div class="stat"><div>Tercer trimestre</div><div class="big" id="thirdNeed">53</div></div>
            </div>
            <label class="label">Simulación del segundo trimestre</label>
            <input id="secondTerm" type="range" min="0" max="100" value="60" {{ $user->is_follower_unlocked ? '' : 'disabled' }}>
            @if(!$user->is_follower_unlocked)
                <div class="msg status-regular">El simulador está bloqueado para seguidores.
                    <div class="actions">
                        <a class="btn btn-soft" target="_blank" href="https://wa.me/59160902299?text=te%20sigo">Enviar "te sigo"</a>
                        <form method="POST" action="{{ route('request.enable') }}">@csrf<button class="btn btn-soft" type="submit">Solicitar habilitación</button></form>
                    </div>
                </div>
            @endif
            <div class="msg" id="simMessage"></div>
        </section>

        <section id="chat" class="panel">
            <div class="chatbox" id="chatbox"></div>
            <button class="btn btn-soft" id="replay" type="button" style="margin-top:10px;">Reiniciar</button>
            <h4 style="margin:14px 0 8px;">Historial</h4>
            <div>
                @forelse($histories as $item)
                    <div class="history-item"><strong>{{ $item->subject }}</strong> | T1: {{ $item->first_term }} | T2: {{ $item->second_term }} | T3: {{ $item->third_term_needed }}<div>{{ $item->summary }}</div></div>
                @empty
                    <div class="history-item">Aún no guardaste simulaciones.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<form id="saveForm" method="POST" action="{{ route('calculations.save') }}" style="display:none;">@csrf
    <input name="subject" id="fSubject"><input name="first_term" id="fFirst"><input name="second_term" id="fSecond"><input name="third_term_needed" id="fThird"><input name="status" id="fStatus"><input name="summary" id="fSummary">
</form>

<script>
const PASS_SCORE = {{ $passScore }};
const subject = document.getElementById('subject');
const firstTerm = document.getElementById('firstTerm');
const secondTerm = document.getElementById('secondTerm');
const message = document.getElementById('message');
const simMessage = document.getElementById('simMessage');
const whatsappResult = document.getElementById('whatsappResult');
const chatbox = document.getElementById('chatbox');

function clamp(v){ return Math.max(0, Math.min(100, Number(v) || 0)); }

function suggestionByNote(note){
    if (note < 45) return {level:'baja', text:'Estás en zona de riesgo. Te recomendamos apoyo escolar para reforzar esta materia.'};
    if (note < 65) return {level:'regular', text:'Vas en nivel regular. Conviene reforzar para mejorar tu promedio.'};
    if (note < 85) return {level:'buena', text:'Buen rendimiento. Mantén el ritmo y practica de forma constante.'};
    return {level:'excelente', text:'Excelente nivel. Puedes adelantar temas y perfeccionar conocimientos.'};
}

function evaluate(){
    const t1 = clamp(firstTerm.value);
    const t2 = clamp(secondTerm.value || 0);
    const avgNeed = Math.ceil((PASS_SCORE - t1) / 2);
    const t3NeedRaw = PASS_SCORE - (t1 + t2);
    const third = Math.max(0, t3NeedRaw);
    const sug = suggestionByNote(t1);
    const summary = `Necesitas aproximadamente ${avgNeed > 100 ? 'más de 100' : avgNeed} puntos en el segundo y tercer trimestre para aprobar.`;

    document.getElementById('avgNeed').textContent = avgNeed > 100 ? '100+' : avgNeed;
    document.getElementById('secondValue').textContent = t2;
    document.getElementById('thirdNeed').textContent = third;

    message.className = `msg status-${sug.level}`;
    message.textContent = `${summary} ${sug.text}`;
    simMessage.textContent = `Si sacas ${t2} en el segundo trimestre, necesitas ${third} en el tercero.`;

    const waText = `Materia: ${subject.value}\nNota del primer trimestre: ${t1}\nResultado: ${summary}\nSugerencia: ${sug.text}`;
    whatsappResult.href = `https://wa.me/59171324941?text=${encodeURIComponent(waText)}`;

    return { t1, t2, third, summary: `${summary} ${sug.text}`, level: sug.level };
}

function pushBubble(text, cls='bot'){ const d=document.createElement('div'); d.className=`bubble ${cls}`; d.textContent=text; chatbox.appendChild(d); chatbox.scrollTop=chatbox.scrollHeight; }
function runChat(){ chatbox.innerHTML=''; const d=evaluate(); pushBubble('Hola. ¿Qué materia deseas calcular?'); pushBubble(subject.value,'me'); pushBubble('¿Cuánto sacaste en el primer trimestre?'); pushBubble(String(d.t1),'me'); pushBubble(`Si obtienes ${d.t2} en el segundo trimestre, necesitarás ${d.third} en el tercero.`); pushBubble(d.summary); }

document.querySelectorAll('[data-tab]').forEach((btn)=>{ btn.addEventListener('click',()=>{ document.querySelectorAll('[data-tab]').forEach(b=>b.classList.remove('active')); btn.classList.add('active'); document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active')); document.getElementById(btn.dataset.tab).classList.add('active'); }); });

[firstTerm, secondTerm, subject].forEach(el => el.addEventListener('input', () => { evaluate(); runChat(); }));
document.getElementById('calculateBtn').addEventListener('click', () => { evaluate(); runChat(); });
document.getElementById('replay').addEventListener('click', runChat);
document.getElementById('saveBtn').addEventListener('click', () => {
    const data = evaluate();
    document.getElementById('fSubject').value = subject.value;
    document.getElementById('fFirst').value = data.t1;
    document.getElementById('fSecond').value = data.t2;
    document.getElementById('fThird').value = data.third;
    document.getElementById('fStatus').value = data.level === 'baja' ? 'risk' : (data.level === 'regular' ? 'warning' : (data.level === 'excelente' ? 'passed' : 'ok'));
    document.getElementById('fSummary').value = data.summary;
    document.getElementById('saveForm').submit();
});

evaluate();
runChat();
</script>
</body>
</html>
