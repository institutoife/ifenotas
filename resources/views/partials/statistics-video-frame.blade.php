<article class="video-frame" data-frame="{{ $frameKey }}" @if($frameKey !== 'general') hidden @endif style="--state-color:{{ $palette['color'] ?? '#375F7A' }};--state-text:{{ $palette['text'] ?? '#FFFFFF' }}">
    <header class="frame-header"><span>IFE NOTAS</span><button type="button" class="fullscreen-exit" data-exit hidden>Salir de pantalla completa</button><h2>{{ $title }}</h2><p>{{ $frameKey === 'general' ? 'Distribución de registros clasificados' : '100% = todos los registros de este estado' }}</p></header>
    <div class="frame-content">
    @if($frameKey === 'general')
        @if($projection['sample'])
            @foreach($statePresentation as $key=>$style)
                @php($state = $projection['states'][$key])
                <section class="general-state" style="--state-color:{{ $style['color'] }};--state-text:{{ $style['text'] }}">
                    <h3>{{ $style['label'] }}</h3><strong class="big-percent">{{ number_format($state['display_percentage'], 1, ',', '.') }}%</strong>
                    <div><b class="big-count" data-projected="{{ $state['projected'] }}">{{ $state['projected'] === null ? '—' : number_format($state['projected'], 0, ',', '.') }}</b><span class="count-label">proyectados</span></div>
                </section>
            @endforeach
        @else<div class="empty">Sin registros clasificados todavía.</div>@endif
    @else
        @forelse(array_chunk($projection['states'][$frameKey]['subjects'], 3) as $pageIndex=>$page)
            <div class="subject-page" data-page="{{ $pageIndex }}" @if($pageIndex > 0) hidden @endif>
            @foreach($page as $row)
                <section class="subject-row"><h3>{{ $row['subject'] }}</h3>
                    <div class="row-numbers"><strong class="big-percent">{{ number_format($row['display_percentage'], 1, ',', '.') }}%</strong><div><b class="big-count" data-projected="{{ $row['projected'] }}">{{ $row['projected'] === null ? '—' : number_format($row['projected'], 0, ',', '.') }}</b><span class="count-label">proyectados</span></div></div>
                    <div class="video-bar" data-value="{{ $row['percentage'] }}" data-color="{{ $palette['color'] }}" aria-hidden="true"><span style="width:{{ $row['percentage'] }}%"></span></div>
                </section>
            @endforeach
            </div>
        @empty<div class="empty">Sin registros de {{ mb_strtolower($palette['label']) }} todavía.</div>@endforelse
    @endif
    </div>
    <footer class="frame-footer">
        @if($frameKey !== 'general')<div class="state-total">{{ $palette['label'] }}: <b data-projected="{{ $projection['states'][$frameKey]['projected'] }}">{{ $projection['states'][$frameKey]['projected'] === null ? '—' : number_format($projection['states'][$frameKey]['projected'], 0, ',', '.') }}</b> <span class="count-label">proyectados</span></div>@endif
        <strong class="projection-label">{{ $projection['projectionAvailable'] ? 'Proyección basada en registros de IFE Notas' : 'Proyección no disponible' }}</strong>
        <p>Muestra: {{ number_format($projection['sample'], 0, ',', '.') }} registros · Universo: {{ $projection['universe'] === null ? 'no disponible' : number_format($projection['universe'], 0, ',', '.') }} visitas del contador</p>
        <small>El contador incluye una base inicial y consultas; no representa visitantes únicos verificados.</small>
        <p class="filter-caption">{{ $filters['subject'] ?? 'Todas las materias' }} · {{ $filters['from'] ?? 'Inicio' }} — {{ $filters['to'] ?? 'Hoy' }} <span data-page-label></span></p>
    </footer>
</article>
