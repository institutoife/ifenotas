(() => {
    'use strict';
    const source = document.getElementById('simulator-chart-data');
    if (!source) return;
    const subjects = JSON.parse(source.textContent).map(item => ({...item,
        total: Number(item.total), failed: Number(item.failed), passed: Number(item.passed), pending: Number(item.pending),
    }));
    if (!subjects.length) return;
    const states = ['failed', 'passed', 'pending'];
    const labels = ['Reprobados', 'Ya aprobaron', 'En carrera'];
    const colors = ['#bb2525', '#26baa5', '#375f7a'];
    const byId = id => document.getElementById(id);
    const subjectControl = byId('chart-subject'), scaleControl = byId('chart-scale'), orderControl = byId('chart-order');
    const loading = byId('chart-loading');
    const percent = (count, total) => total ? count * 100 / total : 0;
    const formatted = n => n.toLocaleString('es-BO', {maximumFractionDigits: 1});
    const totals = subjects.reduce((sum, row) => {
        ['total', ...states].forEach(key => sum[key] += row[key]);
        return sum;
    }, {subject: 'Todas las materias filtradas', total: 0, failed: 0, passed: 0, pending: 0});
    let charts, ordered = [], ready = false;
    function failure() {
        loading.hidden = false;
        loading.textContent = 'No se pudieron cargar los gráficos. Consulta la tabla inferior o recarga la página.';
    }
    const timeout = setTimeout(failure, 15000);
    function selectedData() { return subjectControl.value === 'all' ? totals : subjects[Number(subjectControl.value)] || totals; }
    function detail() {
        const row = selectedData(), usePercent = scaleControl.value === 'percent';
        byId('chart-summary').textContent = `${row.subject} · ${row.total} consultas`;
        const pie = new google.visualization.DataTable();
        pie.addColumn('string', 'Estado'); pie.addColumn('number', 'Consultas'); pie.addColumn({type: 'string', role: 'tooltip'});
        const bars = new google.visualization.DataTable();
        bars.addColumn('string', 'Estado'); bars.addColumn('number', usePercent ? 'Porcentaje' : 'Consultas');
        bars.addColumn({type:'string', role:'style'}); bars.addColumn({type:'string', role:'annotation'}); bars.addColumn({type:'string', role:'tooltip'});
        states.forEach((key, i) => {
            const count = row[key], p = percent(count, row.total);
            const tip = `${labels[i]}: ${count} consultas (${formatted(p)}%)`;
            pie.addRow([labels[i], count, tip]);
            bars.addRow([labels[i], usePercent ? p : count, colors[i], usePercent ? `${formatted(p)}%` : String(count), tip]);
        });
        charts.pie.draw(pie, {colors, pieSliceText:'percentage', sliceVisibilityThreshold:0, fontName:'Arial', legend:{position:'bottom'}, chartArea:{left:12,top:12,width:'94%',height:'78%'}});
        charts.detail.draw(bars, {fontName:'Arial', legend:{position:'none'}, chartArea:{left:110,top:20,width:'62%',height:'78%'}, hAxis:{minValue:0,viewWindow:usePercent?{min:0,max:100}:{min:0},title:usePercent?'Porcentaje (%)':'Consultas'}, annotations:{alwaysOutside:true}});
    }
    function comparison() {
        const usePercent = scaleControl.value === 'percent';
        ordered = subjects.map((row,index) => ({...row,index})).sort((a,b) => {
            if (orderControl.value === 'subject') return a.subject.localeCompare(b.subject, 'es');
            if (orderControl.value === 'total') return b.total - a.total;
            return percent(b.failed,b.total) - percent(a.failed,a.total) || b.total - a.total;
        });
        const table = new google.visualization.DataTable();
        table.addColumn('string','Materia');
        states.forEach((key,i) => { table.addColumn('number',labels[i]); table.addColumn({type:'string',role:'tooltip'}); });
        ordered.forEach(row => {
            const values = [row.subject];
            states.forEach((key,i) => values.push(row[key], `${row.subject}\n${labels[i]}: ${row[key]} de ${row.total} (${formatted(percent(row[key],row.total))}%)`));
            table.addRow(values);
        });
        byId('subject-bars').style.height = `${Math.max(320,ordered.length * 58 + 100)}px`;
        charts.subjects.draw(table, {colors, fontName:'Arial', isStacked:usePercent?'percent':true, legend:{position:'top'}, chartArea:{left:Math.min(190,byId('subject-bars').clientWidth*.36),top:50,width:'62%',height:'75%'}, hAxis:usePercent?{minValue:0,maxValue:1,format:'percent',ticks:[0,.25,.5,.75,1]}:{minValue:0,title:'Consultas'}});
    }
    function redraw() { if (ready) { detail(); comparison(); } }
    const loader = document.createElement('script');
    loader.src = 'https://www.gstatic.com/charts/loader.js';
    loader.onerror = () => { clearTimeout(timeout); failure(); };
    loader.onload = () => {
        try {
            google.charts.load('current', {packages:['corechart'], language:'es'});
            google.charts.setOnLoadCallback(() => {
                clearTimeout(timeout);
                charts = {pie:new google.visualization.PieChart(byId('status-pie')),detail:new google.visualization.BarChart(byId('status-bars')),subjects:new google.visualization.BarChart(byId('subject-bars'))};
                Object.values(charts).forEach(chart => google.visualization.events.addListener(chart,'error',failure));
                google.visualization.events.addListener(charts.subjects,'select',() => {
                    const selection = charts.subjects.getSelection()[0];
                    if (selection?.row != null && ordered[selection.row]) {
                        subjectControl.value = String(ordered[selection.row].index);
                        detail();
                    }
                });
                ready = true; loading.hidden = true; redraw();
            });
        } catch (error) { clearTimeout(timeout); failure(); }
    };
    document.head.appendChild(loader);
    subjectControl.addEventListener('change', () => { if (ready) detail(); });
    scaleControl.addEventListener('change', redraw);
    orderControl.addEventListener('change', () => { if (ready) comparison(); });
    let resizeTimer;
    window.addEventListener('resize', () => { clearTimeout(resizeTimer); resizeTimer=setTimeout(redraw,150); });
})();
