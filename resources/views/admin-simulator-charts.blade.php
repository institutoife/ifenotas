<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Estadísticas para video · IFE Notas</title><link rel="stylesheet" href="{{ asset('css/ife-statistics-video.css') }}?v=2"></head>
<body><main>
    <header class="page-header"><h1>Estadísticas para video</h1><nav><a href="{{ route('admin.simulator-records', $filters) }}">Cantidades y notas</a><a href="{{ route('admin') }}">Administración</a></nav></header>
    <details class="settings"><summary>Fechas, materia y base de la proyección</summary>
        <form method="GET"><label>Materia<select name="subject"><option value="">Todas</option>@foreach(config('ife.subjects') as $subject)<option @selected(($filters['subject'] ?? '') === $subject)>{{ $subject }}</option>@endforeach</select></label><label>Desde<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label><label>Hasta<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label><button>Actualizar</button><a href="{{ route('admin.simulator-charts') }}">Limpiar filtros</a></form>
        @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
        <p>Los porcentajes se calculan con {{ number_format($projection['sample'], 0, ',', '.') }} registros clasificados de la selección. Las cantidades estimadas distribuyen el valor actual del contador, no personas verificadas. El universo corresponde al contador acumulado, incluso si filtras la muestra por fechas.</p>
        <p>El contador existente comenzó con una base manual y suma accesos y consultas. No permite conocer cuántos visitantes únicos reales hubo. Los registros pueden contener varias consultas de la misma persona. Esta proyección es ilustrativa y no una estimación representativa de todos los visitantes.</p>
        <p>Los porcentajes internos de materias dividen por el total de su estado. Los enteros estimados se distribuyen por restos mayores, con desempates fijos. Los porcentajes visibles se ajustan a décimas para sumar 100,0%; nunca se calculan desde cantidades estimadas.</p>
    </details>
    <div class="toolbar"><label>Visualización<select id="video-state"><option value="general">GENERAL</option><option value="failed">APLAZADOS POR MATERIA</option><option value="pending">EN RIESGO POR MATERIA</option><option value="passed">APROBADOS POR MATERIA</option><optgroup label="COMPARATIVO POR MATERIA">@foreach($projection['comparisons'] as $subjectIndex=>$comparison)<option value="subject-{{ $subjectIndex }}">{{ $comparison['subject'] }}</option>@endforeach</optgroup></select></label><button type="button" id="video-fullscreen">Pantalla completa</button></div>
    <div class="page-controls"><button type="button" id="video-prev">← Materias anteriores</button><span id="video-page" role="status"></span><button type="button" id="video-next">Siguientes materias →</button></div>
    <div class="frame-container" id="overall-title">
        @include('partials.statistics-video-frame', ['frameKey'=>'general','title'=>'PANORAMA GENERAL','palette'=>[]])
        @foreach(['failed','pending','passed'] as $key)
            @include('partials.statistics-video-frame', ['frameKey'=>$key,'title'=>$statePresentation[$key]['label'].' POR MATERIA','palette'=>$statePresentation[$key]])
        @endforeach
        @foreach($projection['comparisons'] as $subjectIndex=>$comparison)
            @include('partials.statistics-subject-frame')
        @endforeach
    </div>
    @if(!$total)<p class="empty">Sin consultas para calcular porcentajes.</p>@endif
    <p id="video-message" class="note" role="status">Los números permanecen visibles sin depender de tooltips. Las barras usan Google Charts.</p>
    <details class="settings"><summary>Proyecciones y tablas ordenables</summary>@include('partials.statistics-real-tables')</details>
    <script src="{{ asset('js/ife-statistics-video.js') }}?v=2" defer></script>
</main></body></html>
