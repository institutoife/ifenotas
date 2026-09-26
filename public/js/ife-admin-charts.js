(() => {
    'use strict';
    document.querySelectorAll('[data-sortable]').forEach(table => {
        table.querySelectorAll('.sort-button').forEach(button => button.addEventListener('click', () => {
            const header=button.closest('th'), ascending=header.getAttribute('aria-sort')!=='ascending';
            table.querySelectorAll('thead th').forEach(th=>{th.setAttribute('aria-sort','none');th.querySelector('span').textContent='?'});
            header.setAttribute('aria-sort',ascending?'ascending':'descending');button.querySelector('span').textContent=ascending?'?':'?';
            const column=Number(button.dataset.column), numeric=button.dataset.type==='number';
            const rows=Array.from(table.tBodies[0].rows);
            rows.sort((a,b)=>{const x=a.cells[column].dataset.value,y=b.cells[column].dataset.value;return (numeric?Number(x)-Number(y):x.localeCompare(y,'es',{numeric:true,sensitivity:'base'}))*(ascending?1:-1)});
            rows.forEach(row=>table.tBodies[0].appendChild(row));
        }));
    });
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
    function stateComparison() {
        const usePercent=scaleControl.value==='percent';
        states.forEach((key,i)=>{
            const total=totals[key], pieNode=byId(key+'-pie'), barNode=byId(key+'-bars');
            byId(key+'-empty').hidden=total>0;pieNode.hidden=!total;barNode.hidden=!total;
            if(!total)return;
            const rows=subjects.map((row,index)=>({...row,index})).sort((a,b)=>usePercent?percent(b[key],b.total)-percent(a[key],a.total):b[key]-a[key]);
            const pie=new google.visualization.DataTable(),bars=new google.visualization.DataTable();
            pie.addColumn('string','Materia');pie.addColumn('number','Consultas');pie.addColumn({type:'string',role:'tooltip'});
            bars.addColumn('string','Materia');bars.addColumn('number',labels[i]);bars.addColumn({type:'string',role:'annotation'});bars.addColumn({type:'string',role:'tooltip'});
            rows.forEach(row=>{
                pie.addRow([row.subject,row[key],row.subject+': '+row[key]+' consultas ('+formatted(percent(row[key],total))+'% de '+labels[i]+')']);
                const p=percent(row[key],row.total);
                bars.addRow([row.subject,usePercent?p:row[key],usePercent?formatted(p)+'%':String(row[key]),row.subject+': '+row[key]+' de '+row.total+' ('+formatted(p)+'% de la materia)']);
            });
            const base=colors[i].slice(1).match(/../g).map(n=>parseInt(n,16));
            const shades=rows.map((_,index)=>{const t=rows.length>1?index/(rows.length-1)*.55:0;return '#'+base.map(n=>Math.round(n+(255-n)*t).toString(16).padStart(2,'0')).join('')});
            charts[key+'Pie'].draw(pie,{colors:shades,pieSliceText:'percentage',sliceVisibilityThreshold:0,legend:{position:'bottom'},chartArea:{left:10,top:10,width:'95%',height:'72%'}});
            barNode.style.height=Math.max(320,rows.length*46+90)+'px';
            charts[key+'Bars'].draw(bars,{colors:[colors[i]],legend:{position:'none'},chartArea:{left:Math.min(150,barNode.clientWidth*.35),top:20,width:'57%',height:'80%'},hAxis:{viewWindow:usePercent?{min:0,max:100}:{min:0},title:usePercent?'% dentro de cada materia':'Consultas'},annotations:{alwaysOutside:true}});
        });
    }
    function redraw() { if (ready) { detail(); comparison(); stateComparison(); } }
    const loader = document.createElement('script');
    loader.src = 'https://www.gstatic.com/charts/loader.js';
    loader.onerror = () => { clearTimeout(timeout); failure(); };
    loader.onload = () => {
        try {
            google.charts.load('current', {packages:['corechart'], language:'es'});
            google.charts.setOnLoadCallback(() => {
                clearTimeout(timeout);
                charts = {pie:new google.visualization.PieChart(byId('status-pie')),detail:new google.visualization.BarChart(byId('status-bars')),subjects:new google.visualization.BarChart(byId('subject-bars'))};
                states.forEach(key=>{charts[key+'Pie']=new google.visualization.PieChart(byId(key+'-pie'));charts[key+'Bars']=new google.visualization.BarChart(byId(key+'-bars'));});
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
