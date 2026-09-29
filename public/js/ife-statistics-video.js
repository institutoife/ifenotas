(() => {
    'use strict';
    const frames = [...document.querySelectorAll('[data-frame]')];
    const state = document.getElementById('video-state');
    const prev = document.getElementById('video-prev'), next = document.getElementById('video-next');
    const message = document.getElementById('video-message');
    const positions = {}, graphs = new Map();
    let chartsReady = false;
    const active = () => frames.find(frame => frame.dataset.frame === state.value);
    function draw() {
        if (!chartsReady) return;
        active().querySelectorAll('.video-bar').forEach(node => {
            if (!node.getClientRects().length) return;
            let chart = graphs.get(node);
            if (!chart) { chart = new google.visualization.BarChart(node); graphs.set(node, chart); }
            const data = google.visualization.arrayToDataTable([['', 'Porcentaje', 'Resto hasta 100%'], ['', Number(node.dataset.value), 100 - Number(node.dataset.value)]]);
            chart.draw(data, {isStacked:true,colors:[node.dataset.color,'#B5C6D2'],backgroundColor:'#B5C6D2',height:32,legend:'none',enableInteractivity:false,chartArea:{left:0,top:0,width:'100%',height:'100%'},bar:{groupWidth:'100%'},hAxis:{viewWindow:{min:0,max:100},textPosition:'none',gridlines:{color:'transparent'},baselineColor:'transparent'},vAxis:{textPosition:'none',baselineColor:'transparent'}});
        });
    }
    function render() {
        frames.forEach(frame => frame.hidden = frame !== active());
        const pages = [...active().querySelectorAll('[data-page]')], index = positions[state.value] || 0;
        pages.forEach((page, i) => page.hidden = i !== index);
        prev.disabled = index <= 0; next.disabled = index >= pages.length - 1;
        prev.hidden = next.hidden = pages.length < 2;
        const label = pages.length > 1 ? `${index + 1} / ${pages.length}` : '';
        document.getElementById('video-page').textContent = label;
        active().querySelector('[data-page-label]').textContent = label ? `· Página ${label}` : '';
        frames.forEach(frame => {
            frame.querySelectorAll('[data-projected]').forEach(node => {
                const value = node.dataset.projected;
                node.textContent = value === '' ? '—' : Number(value).toLocaleString('es-BO');
            });
            frame.querySelectorAll('.count-label').forEach(node => node.textContent = 'estimados');
            frame.querySelector('.projection-label').textContent = 'Proyección basada en registros de IFE Notas';
        });
        requestAnimationFrame(() => { draw(); window.ifeChartMotion?.(active()); });
    }
    state.addEventListener('change', render);
    document.getElementById('video-replay').addEventListener('click', () => window.ifeChartMotion?.(active()));
    prev.addEventListener('click', () => { positions[state.value] = Math.max(0, (positions[state.value] || 0)-1); render(); });
    next.addEventListener('click', () => { positions[state.value] = Math.min(active().querySelectorAll('[data-page]').length-1, (positions[state.value] || 0)+1); render(); });
    document.getElementById('video-fullscreen').addEventListener('click', async () => {
        try { if (!active().requestFullscreen) throw Error(); await active().requestFullscreen(); }
        catch { message.textContent = 'Tu navegador no permite pantalla completa. Puedes capturar la composición vertical directamente.'; }
    });
    document.querySelectorAll('[data-exit]').forEach(button => button.addEventListener('click', () => document.exitFullscreen?.()));
    document.addEventListener('fullscreenchange', () => { document.querySelectorAll('[data-exit]').forEach(b => b.hidden = !document.fullscreenElement); requestAnimationFrame(draw); });
    let resizeTimer; window.addEventListener('resize', () => { clearTimeout(resizeTimer); resizeTimer=setTimeout(draw,150); });
    document.querySelectorAll('[data-sortable]').forEach(table => table.querySelectorAll('.sort-button').forEach(button => button.addEventListener('click', () => {
        const th=button.closest('th'),ascending=th.getAttribute('aria-sort')!=='ascending',column=Number(button.dataset.column);
        table.querySelectorAll('thead th').forEach(h=>h.setAttribute('aria-sort','none'));th.setAttribute('aria-sort',ascending?'ascending':'descending');
        const rows=[...table.tBodies[0].rows];rows.sort((a,b)=>{const x=a.cells[column].dataset.value,y=b.cells[column].dataset.value;return (button.dataset.type==='number'?Number(x)-Number(y):x.localeCompare(y,'es'))*(ascending?1:-1)});rows.forEach(row=>table.tBodies[0].appendChild(row));
    })));
    render();
    const loader=document.createElement('script');loader.src='https://www.gstatic.com/charts/loader.js';
    loader.onload=()=>{try{google.charts.load('current',{packages:['corechart'],language:'es'});google.charts.setOnLoadCallback(()=>{chartsReady=true;draw();window.ifeChartMotion?.(active())});}catch{message.textContent='Las barras y cifras siguen disponibles sin Google Charts.';}};
    loader.onerror=()=>{message.textContent='Las barras y cifras siguen disponibles sin Google Charts.';};document.head.appendChild(loader);
})();
