<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IFE Notas</title>
    <link rel="icon" href="{{ asset('images/icono-ife-educabol-instituto-formacion-educabol.svg') }}" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        :root{--primary:#26baa5;--secondary:#375f7a;--bg:#f5f9f9;--line:#d8e5e6;--muted:#647985}*{box-sizing:border-box}body{margin:0;font-family:"Segoe UI",Arial,sans-serif;background:var(--bg);color:var(--secondary)}button,input,select{font:inherit}.app{width:min(1100px,100%);margin:auto;padding:12px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:12px}.brand{display:flex;align-items:center;gap:9px}.brand img{display:block;width:min(185px,43vw);height:auto;object-fit:contain}.user-phone{font-size:.72rem;color:var(--muted);font-weight:800}.top-actions{display:flex;align-items:center;gap:6px}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--secondary);padding:7px 11px;text-decoration:none;font-weight:900;cursor:pointer}.btn-primary{border-color:var(--primary);background:var(--primary);color:#fff}.btn-dark{background:var(--secondary);color:#fff}.nav{display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin:10px 0}.nav-btn{min-height:44px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--secondary);font-weight:950;cursor:pointer}.nav-btn.active{border-color:var(--primary);background:var(--primary);color:#fff}.panel{display:none}.panel.active{display:block}.panel-card{border:1px solid var(--line);border-radius:18px;background:#fff;padding:clamp(12px,2.5vw,22px);box-shadow:0 14px 34px rgba(55,95,122,.09)}.panel-head{margin-bottom:13px}.panel-head h1{margin:0;font-size:clamp(1.5rem,5vw,2.3rem)}.panel-head p{margin:4px 0 0;color:var(--muted)}.form-stack{display:grid;gap:11px}.profile-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}label{display:block;margin-bottom:4px;font-size:.78rem;font-weight:900}input,select{width:100%;min-height:44px;border:1px solid var(--line);border-radius:10px;background:#fff;color:#253f50;padding:9px 11px;outline:none}input:focus,select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(38,186,165,.12)}.profile-note{margin-top:10px;border-left:4px solid var(--primary);border-radius:9px;background:#eefaf8;padding:10px;font-weight:750}.history{display:grid;gap:7px}.history-item{border:1px solid var(--line);border-radius:11px;background:#fff;padding:10px}.history-item div{margin-top:3px;color:var(--muted);font-size:.86rem}.select2-container{width:100%!important}.select2-container .select2-selection--single{height:44px!important;border-color:var(--line)!important;border-radius:10px!important}.select2-selection__rendered{line-height:42px!important}.select2-selection__arrow{height:42px!important}
        @media(max-width:620px){.app{padding:7px}.topbar{align-items:flex-start}.user-phone{display:none}.top-actions .admin-link{display:none}.btn{min-height:38px;padding:6px 9px;font-size:.76rem}.nav{margin:7px 0}.nav-btn{min-height:40px;font-size:.74rem}.panel-card{border-radius:14px;padding:10px}.profile-fields{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="app">
    <header class="topbar">
        <div class="brand"><img src="{{ asset('images/logo-ife-educabol-instituto-formacion-educabol.svg') }}" alt="Logo de IFE Educabol"><span class="user-phone">{{ $user->phone }}</span></div>
        <div class="top-actions">@if($user->is_admin)<a href="{{ route('admin') }}" class="btn admin-link">Admin</a>@endif<form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-dark" type="submit">Salir</button></form></div>
    </header>

    <nav class="nav" aria-label="Secciones"><button class="nav-btn active" type="button" data-panel="notas">NOTAS</button><button class="nav-btn" type="button" data-panel="historial">HISTORIAL</button><button class="nav-btn" type="button" data-panel="perfil">MI PERFIL</button></nav>

    @if(session('status'))<div class="profile-note">{{ session('status') }}</div>@endif

    <main>
        <section class="panel active" id="notas">@include('partials.grade-simulator')</section>

        <section class="panel" id="historial"><div class="panel-card"><header class="panel-head"><h1>Historial</h1><p>Resultados guardados anteriormente.</p></header><div class="history">
            @forelse($histories as $item)<article class="history-item"><strong>{{ $item->subject }}</strong>@if($item->school) · {{ $item->school->nombre }}@endif · T1: {{ $item->first_term }} · T2: {{ $item->second_term }} · T3: {{ $item->third_term_needed }}<div>{{ $item->summary }}</div></article>@empty<div class="history-item">Aún no tienes resultados guardados.</div>@endforelse
        </div></div></section>

        <section class="panel" id="perfil"><div class="panel-card"><header class="panel-head"><h1>Mi perfil</h1><p>Actualiza tus datos personales y colegio.</p></header><form class="form-stack" id="profileForm">
            <div class="profile-fields">
                <div><label for="profileName">Nombre</label><input id="profileName" value="{{ $user->name }}" required></div><div><label for="profilePhone">Teléfono</label><input id="profilePhone" value="{{ $user->phone }}" readonly></div>
                <div><label for="profileEmail">Email opcional</label><input id="profileEmail" type="email" value="{{ Str::endsWith($user->email, '@notes.local') ? '' : $user->email }}"></div><div><label for="profileGrade">Curso / grado</label><input id="profileGrade" value="{{ $user->grade_level }}"></div>
                <div><label for="guardianName">Apoderado</label><input id="guardianName" value="{{ $user->guardian_name }}"></div><div><label for="guardianPhone">Teléfono del apoderado</label><input id="guardianPhone" value="{{ $user->guardian_phone }}" inputmode="tel"></div>
            </div>
            <div><label for="schoolSelect">Colegio opcional</label><select id="schoolSelect">@if($user->school)<option value="{{ $user->school->id }}" selected>{{ $user->school->nombre }} · RUE: {{ $user->school->codigo_rue }}</option>@endif</select></div>
            <button class="btn btn-primary" type="submit">Guardar perfil</button><button class="btn" id="clearSchoolBtn" type="button">Quitar colegio</button>
        </form><div class="profile-note" id="profileStatus">@if($user->school)Colegio actual: {{ $user->school->nombre }}@else Puedes usar la app sin seleccionar colegio.@endif</div></div></section>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script><script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const csrfToken=document.querySelector('meta[name="csrf-token"]').content;let schoolId=@json($user->school_id);let schoolText=@json($user->school?->nombre ?? '');
document.querySelectorAll('[data-panel]').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('[data-panel]').forEach(item=>item.classList.toggle('active',item===button));document.querySelectorAll('.panel').forEach(panel=>panel.classList.toggle('active',panel.id===button.dataset.panel))}));
async function postJson(url,data){const response=await fetch(url,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},body:JSON.stringify(data)});if(!response.ok)throw new Error('No se pudo guardar.');return response.json()}
function profilePayload(){return{name:document.getElementById('profileName').value.trim(),email:document.getElementById('profileEmail').value.trim()||null,grade_level:document.getElementById('profileGrade').value.trim()||null,guardian_name:document.getElementById('guardianName').value.trim()||null,guardian_phone:document.getElementById('guardianPhone').value.trim()||null,school_id:schoolId||null}}
document.getElementById('profileForm').addEventListener('submit',async event=>{event.preventDefault();const status=document.getElementById('profileStatus');try{await postJson(@json(route('profile.update')),profilePayload());status.textContent=schoolText?`Perfil guardado. Colegio: ${schoolText}`:'Perfil guardado.'}catch(error){status.textContent=error.message}});
document.getElementById('clearSchoolBtn').addEventListener('click',async()=>{schoolId=null;schoolText='';$('#schoolSelect').val(null).trigger('change');const status=document.getElementById('profileStatus');try{await postJson(@json(route('profile.update')),profilePayload());status.textContent='Colegio eliminado.'}catch(error){status.textContent=error.message}});
$(function(){$('#schoolSelect').select2({placeholder:'Busca por nombre o RUE',minimumInputLength:2,ajax:{url:@json(route('schools.search')),dataType:'json',delay:250,data:params=>({q:params.term||''}),processResults:data=>data},templateSelection:item=>item.name||item.text||'Colegio seleccionado',language:{inputTooShort:()=> 'Escribe al menos 2 caracteres',searching:()=> 'Buscando...',noResults:()=> 'Sin resultados'}}).on('select2:select',event=>{schoolId=event.params.data.id;schoolText=event.params.data.name||event.params.data.text||''})});
</script>
</body>
</html>
