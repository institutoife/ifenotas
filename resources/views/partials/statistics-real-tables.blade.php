    @foreach(['counts'=>'Cantidades', 'percent'=>'Porcentajes'] as $tableType=>$tableTitle)
    <section class="panel"><h2>Datos del análisis · {{ $tableTitle }}</h2>
        <p class="note">Pulsa cualquier encabezado para alternar entre orden ascendente y descendente. @if($tableType === 'percent') Cada porcentaje usa el total de consultas de su materia. @endif</p>
        <div class="table-wrap"><table data-sortable>
            <thead><tr>@foreach(['Materia', 'Total de consultas', 'APLAZADOS', 'APROBADOS', 'EN RIESGO'] as $heading)<th scope="col" aria-sort="none"><button type="button" class="sort-button" data-column="{{ $loop->index }}" data-type="{{ $loop->first ? 'text' : 'number' }}">{{ $heading }} <span aria-hidden="true">↕</span></button></th>@endforeach</tr></thead>
            <tbody>@foreach($subjectStats as $stat)<tr><th scope="row" data-value="{{ $stat['subject'] }}">{{ $stat['subject'] }}</th><td data-value="{{ $stat['total'] }}">{{ $stat['total'] }}</td>@foreach(['failed','passed','pending'] as $key)<td class="{{ $key }}" data-value="{{ $tableType === 'percent' ? $stat[$key.'_percent'] : $stat[$key] }}">{{ $tableType === 'percent' ? number_format($stat[$key.'_percent'], 1, ',', '.').'%' : $stat[$key] }}</td>@endforeach</tr>@endforeach</tbody>
            <tfoot><tr><th>Total filtrado</th><td>{{ $total }}</td>@foreach(['failed','passed','pending'] as $key)<td class="{{ $key }}">{{ $tableType === 'percent' ? number_format($percentages[$key], 1, ',', '.').'%' : ($counts[$key] ?? 0) }}</td>@endforeach</tr></tfoot>
        </table></div>
    </section>
    @endforeach

