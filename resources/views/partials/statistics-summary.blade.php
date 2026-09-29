<link rel="stylesheet" href="{{ asset('css/ife-statistics-summary.css') }}?v=3">
<div class="ife-summary">
    <div class="ife-summary-heading"><span class="ife-estimate">Estimación</span><span>Panorama general</span><button type="button" class="ife-replay" data-replay-charts>↻ Repetir animación</button></div>
    @if($projection['projectionAvailable'])
        @php($passed = $projection['states']['passed']['percentage'])
        @php($pending = $projection['states']['pending']['percentage'])
        <div class="ife-summary-layout">
            <div class="ife-donut" role="img" aria-label="Distribución de aprobados, en riesgo y aplazados; cifras detalladas en las tarjetas" style="--chart:conic-gradient({{ $statePresentation['passed']['color'] }} 0% {{ $passed }}%, {{ $statePresentation['pending']['color'] }} {{ $passed }}% {{ $passed + $pending }}%, {{ $statePresentation['failed']['color'] }} {{ $passed + $pending }}% 100%)"><div><span>TOTAL ESTIMADO</span><strong>{{ number_format($projection['universe'], 0, ',', '.') }}</strong></div></div>
            <div class="ife-summary-cards">
                @foreach(['failed','passed','pending'] as $key)
                    @php($state = $projection['states'][$key])
                    <article class="ife-summary-card ife-state-{{ $key }}" style="--accent:{{ $statePresentation[$key]['color'] }};--ink:{{ $statePresentation[$key]['text'] }}">
                        <header>{{ $statePresentation[$key]['label'] }}<span>{{ number_format($state['display_percentage'], 1, ',', '.') }}%</span></header>
                        <strong>{{ number_format($state['projected'], 0, ',', '.') }}</strong>
                        <div class="ife-summary-track" aria-hidden="true"><span style="width:{{ $state['percentage'] }}%"></span></div>
                    </article>
                @endforeach
            </div>
        </div>
        <section class="ife-columns" aria-labelledby="ife-columns-title">
            <h3 id="ife-columns-title">Comparativo general</h3>
            <p class="ife-columns-caption">Porcentaje del total · escala de 0 a 100%</p>
            <div class="ife-columns-grid">
                @foreach(['failed','passed','pending'] as $key)
                    @php($state = $projection['states'][$key])
                    <div class="ife-column ife-state-{{ $key }}" style="--accent:{{ $statePresentation[$key]['color'] }}">
                        <strong class="ife-column-percent">{{ number_format($state['display_percentage'], 1, ',', '.') }}%</strong>
                        <div class="ife-column-track" aria-hidden="true"><span style="height:{{ $state['percentage'] }}%"></span></div>
                        <strong class="ife-column-label">{{ $statePresentation[$key]['label'] }}</strong>
                        <b class="ife-column-count">{{ number_format($state['projected'], 0, ',', '.') }}</b>
                    </div>
                @endforeach
            </div>
        </section>
    @else<p>Sin consultas suficientes para mostrar la estimación.</p>@endif
    
</div>

<script src="{{ asset('js/ife-chart-motion.js') }}?v=1" defer></script>
