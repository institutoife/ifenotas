<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ife notas - live</title>
    <link rel="icon" href="{{ asset('images/ife.ico') }}" type="image/x-icon">
    <style>
        :root{
            --primary:#26baa5;
            --secondary:#375f7a;
            --bg:#f5fbfb;
            --card:#ffffff;
            --line:#d7e7e5;
            --muted:#5d7482;
            --ok:#16a34a;
            --warn:#f59e0b;
            --danger:#ef4444;
            --state:#f59e0b;
            --state-soft:rgba(245,158,11,.14);
            --state-shadow:rgba(245,158,11,.36);
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            font-family:"Segoe UI",Arial,sans-serif;
            background:radial-gradient(circle at top left, rgba(38,186,165,.16), transparent 34%), var(--bg);
            color:var(--secondary);
        }
        .live-shell{
            width:min(1180px,100%);
            min-height:100vh;
            margin:0 auto;
            padding:clamp(14px,2.4vw,28px);
            display:grid;
            grid-template-rows:auto auto 1fr auto;
            gap:clamp(14px,2vw,24px);
        }
        .live-header{
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:clamp(72px,10vh,118px);
        }
        .live-logo{
            display:block;
            width:min(360px,70vw);
            max-height:110px;
            object-fit:contain;
        }
        .note-grid{
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:clamp(10px,1.6vw,18px);
        }
        .note-card{
            position:relative;
            overflow:hidden;
            min-height:clamp(158px,22vh,230px);
            border:4px solid var(--line);
            border-radius:22px;
            background:var(--card);
            box-shadow:0 18px 40px rgba(55,95,122,.10);
            padding:clamp(12px,1.8vw,20px);
            display:grid;
            grid-template-rows:auto 1fr;
            gap:8px;
            transition:border-color .25s ease, box-shadow .25s ease, transform .25s ease;
        }
        .note-card.changed{animation:cardPop .6s ease}
        .note-card::after{
            content:"";
            position:absolute;
            inset:auto 0 0;
            height:10px;
            background:var(--state);
            opacity:.86;
            transform:scaleX(.75);
            transform-origin:left;
            transition:background .25s ease, transform .35s ease;
        }
        .note-card.filled::after{transform:scaleX(1)}
        .note-card.ok{border-color:rgba(22,163,74,.72);box-shadow:0 20px 44px rgba(22,163,74,.16)}
        .note-card.warning{border-color:rgba(245,158,11,.82);box-shadow:0 20px 44px rgba(245,158,11,.18)}
        .note-card.risk{border-color:rgba(239,68,68,.78);box-shadow:0 20px 44px rgba(239,68,68,.16)}
        .note-label{
            font-size:clamp(1rem,2.2vw,1.55rem);
            font-weight:950;
            color:var(--muted);
            text-transform:uppercase;
            text-align:center;
        }
        .note-input{
            width:100%;
            min-width:0;
            border:0;
            background:transparent;
            color:var(--state);
            font-size:clamp(4.2rem,15vw,10.8rem);
            line-height:.86;
            font-weight:1000;
            text-align:center;
            outline:none;
            padding:0;
            caret-color:var(--secondary);
            transition:color .25s ease, transform .25s ease;
        }
        .note-input::placeholder{color:rgba(55,95,122,.23)}
        .note-input:focus{transform:scale(1.03)}
        .note-value{
            width:100%;
            min-width:0;
            color:var(--state);
            font-size:clamp(4.2rem,15vw,10.8rem);
            line-height:.86;
            font-weight:1000;
            text-align:center;
            transition:color .25s ease, transform .25s ease;
        }
        .note-value.changed{animation:cardPop .6s ease}
        .over-label{
            position:absolute;
            top:clamp(42px,5vw,58px);
            right:clamp(10px,1.4vw,18px);
            display:none;
            border-radius:999px;
            background:var(--danger);
            color:#fff;
            padding:6px 10px;
            font-size:clamp(.82rem,1.6vw,1.15rem);
            font-weight:1000;
            line-height:1;
            box-shadow:0 10px 22px rgba(239,68,68,.28);
        }
        .over-label.show{display:inline-flex}
        .progress-zone{
            display:grid;
            gap:clamp(10px,1.8vw,18px);
            align-content:center;
            padding:clamp(12px,2vw,22px) 0;
        }
        .total-score{
            text-align:center;
            color:var(--secondary);
            font-size:clamp(1.45rem,3.8vw,3.6rem);
            line-height:1;
            font-weight:1000;
            text-transform:uppercase;
        }
        .total-score strong{
            color:var(--state);
            font-size:clamp(3rem,9vw,7.5rem);
            line-height:.86;
            transition:color .25s ease;
        }
        .status-title{
            text-align:center;
            font-size:clamp(3.1rem,9.5vw,8.8rem);
            line-height:.9;
            font-weight:1000;
            color:var(--state);
            text-transform:uppercase;
            transition:color .25s ease;
        }
        .progress-wrap{
            position:relative;
            padding:clamp(34px,5vw,56px) 0 clamp(24px,4vw,42px);
        }
        .progress-track{
            position:relative;
            height:clamp(36px,6.5vw,72px);
            border-radius:999px;
            background:var(--state);
            box-shadow:inset 0 0 0 6px rgba(255,255,255,.72), 0 20px 48px var(--state-shadow);
            overflow:hidden;
            transition:background .25s ease, box-shadow .25s ease;
        }
        .progress-fill{
            width:0%;
            height:100%;
            background:rgba(255,255,255,.28);
            border-right:8px solid rgba(255,255,255,.85);
            transition:width .55s cubic-bezier(.2,.9,.2,1);
        }
        .progress-marker{
            position:absolute;
            top:50%;
            left:0%;
            width:clamp(54px,8vw,94px);
            height:clamp(54px,8vw,94px);
            border:8px solid #fff;
            border-radius:50%;
            background:var(--state);
            box-shadow:0 16px 34px var(--state-shadow), 0 0 0 9px rgba(255,255,255,.42);
            transform:translate(-50%,-50%);
            transition:left .55s cubic-bezier(.2,.9,.2,1), background .25s ease, box-shadow .25s ease;
            pointer-events:none;
        }
        .progress-marker.changed{animation:markerPulse .7s ease}
        .range-control{
            position:absolute;
            inset:0 0 auto;
            top:clamp(34px,5vw,56px);
            width:100%;
            height:clamp(36px,6.5vw,72px);
            margin:0;
            padding:0;
            border:0;
            opacity:.001;
            cursor:pointer;
        }
        .progress-labels{
            display:flex;
            justify-content:space-between;
            gap:10px;
            margin-top:14px;
            color:var(--muted);
            font-size:clamp(.9rem,1.8vw,1.25rem);
            font-weight:900;
        }
        .summary{
            border:4px solid var(--state);
            border-radius:24px;
            background:linear-gradient(135deg,var(--state-soft),#fff 68%);
            box-shadow:0 20px 44px var(--state-shadow);
            padding:clamp(14px,2.4vw,28px);
            display:grid;
            grid-template-columns:1.1fr .9fr;
            gap:clamp(12px,2vw,22px);
            align-items:center;
            transition:border-color .25s ease, box-shadow .25s ease, background .25s ease;
        }
        .missing{
            text-align:center;
        }
        .missing span,.target-box span{
            display:block;
            color:var(--muted);
            font-size:clamp(.92rem,1.9vw,1.25rem);
            font-weight:950;
            text-transform:uppercase;
        }
        .missing strong{
            display:block;
            color:var(--state);
            font-size:clamp(4rem,12vw,9rem);
            line-height:.9;
            font-weight:1000;
            transition:color .25s ease;
        }
        .target-grid{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:12px;
        }
        .target-box{
            min-height:130px;
            border:3px solid rgba(55,95,122,.13);
            border-radius:18px;
            background:rgba(255,255,255,.78);
            padding:14px;
            text-align:center;
            display:grid;
            align-content:center;
        }
        .target-box strong{
            color:var(--secondary);
            font-size:clamp(3rem,8vw,6.5rem);
            line-height:.9;
            font-weight:1000;
        }
        .helper-text{
            grid-column:1 / -1;
            text-align:center;
            color:var(--secondary);
            font-size:clamp(1.05rem,2.2vw,1.7rem);
            font-weight:950;
        }
        .flash .status-title,.flash .summary,.flash .note-input{animation:flashIn .55s ease}
        .flash .total-score{animation:flashIn .55s ease}
        @keyframes cardPop{
            0%{transform:scale(1)}
            42%{transform:scale(1.035)}
            100%{transform:scale(1)}
        }
        @keyframes markerPulse{
            0%{transform:translate(-50%,-50%) scale(1)}
            45%{transform:translate(-50%,-50%) scale(1.24)}
            100%{transform:translate(-50%,-50%) scale(1)}
        }
        @keyframes flashIn{
            0%{filter:brightness(1)}
            36%{filter:brightness(1.18)}
            100%{filter:brightness(1)}
        }
        @media(max-width:780px){
            .live-shell{gap:12px}
            .note-grid{grid-template-columns:1fr}
            .note-card{min-height:128px}
            .note-input,.note-value{font-size:clamp(4.4rem,28vw,7rem)}
            .summary{grid-template-columns:1fr}
            .target-box{min-height:104px}
        }
        @media(max-height:720px) and (min-width:781px){
            .live-shell{gap:10px}
            .live-header{min-height:66px}
            .live-logo{max-height:64px}
            .note-card{min-height:142px}
            .summary{padding:14px}
            .target-box{min-height:96px}
        }
    </style>
</head>
<body>
    <main class="live-shell" id="liveShell">
        <header class="live-header">
            <img class="live-logo" src="{{ asset('images/ife.png') }}" alt="ife notas">
        </header>

        <section class="note-grid" aria-label="Notas trimestrales">
            <label class="note-card" id="cardFirst">
                <span class="note-label">Nota primer trimestre</span>
                <input class="note-input" id="firstTerm" type="number" min="0" max="100" inputmode="numeric" value="0">
            </label>
            <div class="note-card" id="cardSecond">
                <span class="note-label">Nota segundo trimestre</span>
                <div class="note-value" id="secondTerm">77</div>
            </div>
            <div class="note-card" id="cardThird">
                <span class="note-label">Nota tercer trimestre necesaria</span>
                <div class="note-value" id="thirdTerm">76</div>
                <span class="over-label" id="thirdOverLabel">más de 100</span>
            </div>
        </section>

        <section class="progress-zone" aria-live="polite">
            <div class="total-score">Suma total <strong id="totalScore">153</strong></div>
            <div class="status-title" id="statusTitle">Ingresa la nota</div>
            <div class="progress-wrap">
                <div class="progress-track">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-marker" id="progressMarker"></div>
                <input class="range-control" id="secondRange" type="range" min="0" max="100" value="77" aria-label="Nota del segundo trimestre">
                <div class="progress-labels">
                    <span>0</span>
                    <span>Segundo trimestre</span>
                    <span>100</span>
                </div>
            </div>
        </section>

        <section class="summary" id="summaryPanel">
            <div class="missing">
                <span>Puntos que faltan desde el primer trimestre</span>
                <strong id="missingPoints">{{ $passScore }}</strong>
            </div>
            <div class="target-grid">
                <div class="target-box">
                    <span>Escenario segundo</span>
                    <strong id="targetSecond">77</strong>
                </div>
                <div class="target-box">
                    <span>Debe sacar en tercero</span>
                    <strong id="targetThird">76</strong>
                </div>
                <div class="helper-text" id="helperText">Con primer trimestre 0, segundo 77 y tercero 76 suma 153.</div>
            </div>
        </section>
    </main>

    <script>
        const PASS_SCORE = {{ $passScore }};
        const liveShell = document.getElementById('liveShell');
        const firstTerm = document.getElementById('firstTerm');
        const secondTerm = document.getElementById('secondTerm');
        const thirdTerm = document.getElementById('thirdTerm');
        const thirdOverLabel = document.getElementById('thirdOverLabel');
        const secondRange = document.getElementById('secondRange');
        const cardFirst = document.getElementById('cardFirst');
        const cardSecond = document.getElementById('cardSecond');
        const cardThird = document.getElementById('cardThird');
        const progressFill = document.getElementById('progressFill');
        const progressMarker = document.getElementById('progressMarker');
        const totalScore = document.getElementById('totalScore');
        const statusTitle = document.getElementById('statusTitle');
        const missingPoints = document.getElementById('missingPoints');
        const targetSecond = document.getElementById('targetSecond');
        const targetThird = document.getElementById('targetThird');
        const helperText = document.getElementById('helperText');
        let lastSecond = Number(secondRange.value);
        let lastThird = Number(thirdTerm.textContent);

        function sanitize(input){
            const clean = String(input.value || '').replace(/\D/g, '').slice(0, 3);
            if (clean === '') {
                input.value = '0';
                return 0;
            }

            const value = Math.min(100, Number(clean));
            input.value = String(value);
            return value;
        }

        function statusForThird(thirdNeeded){
            if (thirdNeeded <= 70) return 'ok';
            if (thirdNeeded <= 100) return 'warning';
            return 'risk';
        }

        function stateText(thirdNeeded){
            if (thirdNeeded <= 0) return 'Ya alcanza';
            if (thirdNeeded <= 70) return 'Aprueba';
            if (thirdNeeded <= 100) return 'En riesgo';
            return 'Reprueba';
        }

        function setTheme(kind){
            const colors = {
                ok: ['#16a34a', 'rgba(22,163,74,.13)', 'rgba(22,163,74,.32)'],
                warning: ['#f59e0b', 'rgba(245,158,11,.15)', 'rgba(245,158,11,.36)'],
                risk: ['#ef4444', 'rgba(239,68,68,.13)', 'rgba(239,68,68,.32)']
            };
            const [state, soft, shadow] = colors[kind] || colors.warning;
            document.documentElement.style.setProperty('--state', state);
            document.documentElement.style.setProperty('--state-soft', soft);
            document.documentElement.style.setProperty('--state-shadow', shadow);
        }

        function splitRemaining(first){
            const remaining = Math.max(0, PASS_SCORE - first);
            return [Math.ceil(remaining / 2), Math.floor(remaining / 2)];
        }

        function flash(element, className = 'changed'){
            element.classList.remove(className);
            void element.offsetWidth;
            element.classList.add(className);
        }

        function applyCardState(card, value){
            const kind = value >= 51 ? 'ok' : (value >= 40 ? 'warning' : 'risk');
            card.classList.add('filled');
            card.classList.remove('ok', 'warning', 'risk');
            card.classList.add(kind);
        }

        function update(changedElement = null){
            const first = sanitize(firstTerm);
            const [defaultSecond] = splitRemaining(first);
            const second = Math.max(0, Math.min(100, Number(secondRange.value || defaultSecond)));
            const thirdNeeded = Math.max(0, PASS_SCORE - first - second);
            const shownThird = thirdNeeded > 100 ? '100' : String(thirdNeeded);
            const missingAfterFirst = Math.max(0, PASS_SCORE - first);
            const percent = second;
            const kind = statusForThird(thirdNeeded);
            const displayTotal = first + second + Math.min(100, thirdNeeded);

            setTheme(kind);
            progressFill.style.width = `${percent}%`;
            progressMarker.style.left = `${percent}%`;
            totalScore.textContent = displayTotal;
            statusTitle.textContent = stateText(thirdNeeded);
            secondTerm.textContent = second;
            thirdTerm.textContent = shownThird;
            thirdOverLabel.classList.toggle('show', thirdNeeded > 100);
            missingPoints.textContent = missingAfterFirst;
            targetSecond.textContent = second;
            targetThird.textContent = shownThird;
            helperText.textContent = thirdNeeded > 100
                ? `Con primer trimestre ${first} y segundo ${second}, ni sacando 100 en tercero llega a 153.`
                : `Con primer trimestre ${first}, segundo ${second} y tercero ${shownThird} suma 153.`;

            applyCardState(cardFirst, first);
            applyCardState(cardSecond, second);
            cardThird.classList.add('filled');
            cardThird.classList.remove('ok', 'warning', 'risk');
            cardThird.classList.add(kind);

            if (changedElement) {
                flash(changedElement);
                flash(progressMarker);
                flash(liveShell, 'flash');
            } else if (second !== lastSecond || thirdNeeded !== lastThird) {
                flash(progressMarker);
            }

            if (second !== lastSecond) flash(secondTerm);
            if (thirdNeeded !== lastThird) flash(thirdTerm);
            lastSecond = second;
            lastThird = thirdNeeded;
        }

        function resetScenarioFromFirst(){
            const first = sanitize(firstTerm);
            const [defaultSecond] = splitRemaining(first);
            secondRange.value = Math.min(100, defaultSecond);
            update(cardFirst);
        }

        firstTerm.addEventListener('input', resetScenarioFromFirst);
        firstTerm.addEventListener('change', resetScenarioFromFirst);
        firstTerm.addEventListener('focus', () => firstTerm.select());
        secondRange.addEventListener('input', () => update(cardSecond));
        secondRange.addEventListener('change', () => update(cardSecond));
        progressMarker.addEventListener('animationend', () => {
            progressMarker.classList.remove('changed');
        });

        update();
    </script>
</body>
</html>
