@foreach(['counts'=>'Cantidades proyectadas', 'percent'=>'Porcentajes por materia'] as $tableType=>$tableTitle)
<section class="panel"><h2>Datos del análisis · {{ $tableTitle }}</h2>
    <p class="note">Pulsa un encabezado para ordenar. Las cantidades son proyecciones; los porcentajes describen la distribución observada dentro de cada materia.</p>
    <div class="table-wrap"><table data-sortable>
        <thead><tr>@foreach(['Materia', 'Total proyectado', 'APLAZADOS', 'APROBADOS', 'EN RIESGO'] as $heading)<th scope="col" aria-sort="none"><button type="button" class="sort-button" data-column="{{ $loop->index }}" data-type="{{ $loop->first ? 'text' : 'number' }}">{{ $heading }} <span aria-hidden="true">↕</span></button></th>@endforeach</tr></thead>
        <tbody>@foreach($projection['comparisons'] as $row)<tr><th scope="row" data-value="{{ $row['subject'] }}">{{ $row['subject'] }}</th><td data-value="{{ $row['projected'] }}">{{ $row['projected'] === null ? '—' : number_format($row['projected'], 0, ',', '.') }}</td>@foreach(['failed','passed','pending'] as $key)@php($value = $row['states'][$key][$tableType === 'percent' ? 'display_percentage' : 'projected'])<td class="{{ $key }}" data-value="{{ $value }}">{{ $value === null ? '—' : number_format($value, $tableType === 'percent' ? 1 : 0, ',', '.').($tableType === 'percent' ? '%' : '') }}</td>@endforeach</tr>@endforeach</tbody>
        <tfoot><tr><th>Total</th><td>{{ $projection['projectionAvailable'] ? number_format($projection['universe'], 0, ',', '.') : '—' }}</td>@foreach(['failed','passed','pending'] as $key)@php($value = $projection['states'][$key][$tableType === 'percent' ? 'display_percentage' : 'projected'])<td class="{{ $key }}">{{ $value === null ? '—' : number_format($value, $tableType === 'percent' ? 1 : 0, ',', '.').($tableType === 'percent' ? '%' : '') }}</td>@endforeach</tr></tfoot>
    </table></div>
</section>
@endforeach
