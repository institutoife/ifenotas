<article class="video-frame" data-frame="subject-{{ $subjectIndex }}" hidden style="--state-color:#375F7A;--state-text:#FFFFFF">
    <header class="frame-header"><span>IFE NOTAS</span><button type="button" class="fullscreen-exit" data-exit hidden>Salir de pantalla completa</button><h2>{{ $comparison['subject'] }}</h2><p>Comparativo de estados · 100% = esta materia</p></header>
    <div class="frame-content"><div class="subject-page" data-page="0">
        @foreach($statePresentation as $key=>$style)
            @php($cell = $comparison['states'][$key])
            <section class="subject-row" style="--state-color:{{ $style['color'] }};--state-text:{{ $style['text'] }}">
                <h3>{{ $style['label'] }}</h3>
                <div class="row-numbers"><strong class="big-percent">{{ number_format($cell['display_percentage'], 1, ',', '.') }}%</strong><div><b class="big-count" data-projected="{{ $cell['projected'] }}">{{ $cell['projected'] === null ? '—' : number_format($cell['projected'], 0, ',', '.') }}</b><span class="count-label">estimados</span></div></div>
                <div class="video-bar" data-value="{{ $cell['percentage'] }}" data-color="{{ $style['color'] }}" aria-hidden="true"><span style="width:{{ $cell['percentage'] }}%"></span></div>
            </section>
        @endforeach
    </div></div>
    <footer class="frame-footer"><div class="state-total">TOTAL: <b data-projected="{{ $comparison['projected'] }}">{{ $comparison['projected'] === null ? '—' : number_format($comparison['projected'], 0, ',', '.') }}</b> estimados</div><strong class="projection-label">Proyección basada en registros de IFE Notas</strong><p>Porcentajes calculados dentro de esta materia.</p><small>El contador incluye una base inicial y consultas; no representa visitantes únicos verificados.</small><p class="filter-caption">{{ $filters['from'] ?? 'Inicio' }} — {{ $filters['to'] ?? 'Hoy' }} <span data-page-label></span></p></footer>
</article>
