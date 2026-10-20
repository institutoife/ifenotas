(function () {
    'use strict';
    const root = document.querySelector('.ife-sim');
    if (!root || !window.IfeGradeEngine || !window.IfeSimulatorRecords) return;
    const engine = window.IfeGradeEngine;
    const find = selector => root.querySelector(selector);
    const picker = find('.mode-picker'), workspace = find('.sim-workspace');
    const firstInput = find('#ifeFirstTerm'), secondInput = find('#ifeSecondTerm');
    const stage = find('.result-stage'), range = find('.sim-range');
    const main = find('.main-result'), mainNumber = find('.main-number');
    const support = find('[data-support-cta]');
    const subject = find('[data-consult-subject]'), subjectField = subject.closest('label');
    const consultRow = find('[data-consult-controls]'), consultButton = find('[data-consult-button]');
    const passScore = Number(root.dataset.passScore), highGradeThreshold = Number(root.dataset.highGradeThreshold);
    let mode = null, currentResult = null, knownNotes = '', previousResult = '', gradientFirst = null;
    const records = window.IfeSimulatorRecords.create(root, update);
    const loadedAt = Date.now(), serverTime = Date.parse(root.dataset.serverTime);

    function campaign() {
        const now = new Date(Number.isFinite(serverTime) ? serverTime + Date.now() - loadedAt : Date.now());
        return engine.campaignContext(root.dataset.schoolYearEndDate, now, root.dataset.schoolTimezone);
    }

    function grade(input, key) {
        const raw = input.value.trim(), error = find(`[data-error="${key}"]`);
        let value = null, message = '';
        if (raw === '') {
            if (input.dataset.touched) message = 'Ingresa esta nota.';
        } else {
            const number = Number(raw);
            if (Number.isInteger(number) && number >= 0 && number <= 100) value = number;
            else message = 'Usa un entero de 0 a 100.';
        }
        input.classList.toggle('invalid', !!message);
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        error.textContent = message;
        return value;
    }

    function renderSupport(result) {
        const context = campaign(), recommendation = engine.supportRecommendation(result, subject.value, context);
        support.hidden = false;
        support.dataset.academicStatus = result.status.key;
        support.dataset.severity = String(recommendation.severity);
        support.dataset.campaignPhase = recommendation.campaignPhase;
        find('[data-support-title]').textContent = recommendation.title;
        find('[data-support-message]').textContent = recommendation.message;
        find('[data-support-label]').textContent = recommendation.ctaLabel;
        find('[data-support-link]').href = engine.supportWhatsAppUrl(root.dataset.supportWhatsapp, result, subject.value, context);
    }

    function selectMode(next) {
        mode = next;
        root.classList.toggle('mode-one', mode === 'one');
        root.classList.toggle('mode-two', mode === 'two');
        picker.hidden = true;
        workspace.hidden = false;
        find('.second-known').hidden = mode === 'one';
        find('.known-fields').classList.toggle('one', mode === 'one');
        find('.mode-current').textContent = mode === 'one' ? '1 NOTA' : '2 NOTAS';
        find('.slider-block').hidden = false;
        gradientFirst = null;
        find('[data-slider-label]').textContent = mode === 'one' ? 'SIMULA EL 2.º TRIMESTRE' : 'SIMULA EL 3.º TRIMESTRE';
        find('[data-legend-left]').textContent = mode === 'one' ? 'REPRUEBA' : '0 · REPRUEBA';
        find('[data-legend-right]').textContent = mode === 'one' ? 'APRUEBA' : 'APRUEBA · 100';
        range.setAttribute('aria-label', mode === 'one' ? 'Nota simulada del 2.º trimestre' : 'Nota simulada del 3.º trimestre');
        find('[data-role="second"]').textContent = mode === 'one' ? 'SIMULADO' : 'REAL';
        find('[data-role="third"]').textContent = 'NECESITAS';
        // Move the existing selector: one DOM control and one configured subject list.
        if (mode === 'one') find('[data-support-subject-slot]').append(subjectField);
        else consultRow.insertBefore(subjectField, consultButton);
        subjectField.querySelector('.consult-label').textContent = mode === 'one' ? '¿En qué materia necesitas apoyo?' : 'Materia';
        firstInput.focus();
        update();
    }

    function update() {
        if (!mode) return;
        const first = grade(firstInput, 'first');
        const second = mode === 'two' ? grade(secondInput, 'second') : 0;
        const ready = first !== null && (mode === 'one' || second !== null);
        records.update(mode, ready ? first : null, ready ? second : null);
        stage.classList.toggle('sim-pending', !ready);
        if (!ready) {
            stage.hidden = true; support.hidden = true; currentResult = null;
            knownNotes = ''; previousResult = '';
            return;
        }
        const notesKey = `${mode}:${first}:${second}`;
        if (notesKey !== knownNotes) {
            range.value = window.IfeSimulatorRecords.defaultSlider(mode, first, second, passScore);
            knownNotes = notesKey;
        }
        const showResult = mode !== 'two' || records.hasConsulted();
        stage.hidden = !showResult;
        if (!showResult) { currentResult = null; support.hidden = true; return; }
        currentResult = engine.calculate({ mode, first, second, simulated: Number(range.value), passScore, highGradeThreshold });
        const result = currentResult, status = result.status;
        root.dataset.academicStatus = status.key;
        root.style.setProperty('--general-state', status.color);
        root.style.setProperty('--general-soft', status.soft);
        root.style.setProperty('--general-text', status.textColor);
        root.style.setProperty('--general-ink', status.ink);
        const passed = status.key === 'PASSED', failed = status.key === 'FAILED';
        main.classList.toggle('impossible', failed);
        main.classList.toggle('passed', passed);
        find('.main-kicker').hidden = passed || failed;
        mainNumber.textContent = passed ? 'FELICIDADES' : failed ? 'APLAZADO' : result.requiredThird;
        find('.main-status b').textContent = status.label;
        find('[data-result-message]').textContent = status.shortMessage;
        const scenario = find('[data-scenario-message]');
        scenario.hidden = mode !== 'one';
        scenario.textContent = 'Escenario basado en tu segunda nota simulada. No es una nota real.';
        find('[data-note="first"]').textContent = result.first;
        find('[data-note="second"]').textContent = result.second;
        find('[data-note="third"]').textContent = passed ? '✓' : failed ? '—' : result.requiredThird;
        find('[data-role="third"]').textContent = passed ? 'ASEGURADO' : failed ? 'NO ALCANZA' : 'NECESITAS';
        const signature = `${mode}:${first}:${result.second}:${status.key}`;
        if (signature !== previousResult) {
            mainNumber.classList.remove('pulse');
            void mainNumber.offsetWidth;
            if (!passed && !failed) mainNumber.classList.add('pulse');
            previousResult = signature;
        }
        if (mode === 'one') {
            range.removeAttribute('aria-valuetext');
            find('[data-slider-value]').textContent = range.value;
            find('.slider-outcome').textContent = status.label;
            const marker = find('.pass-marker');
            marker.style.transform = 'translateX(-50%)';
            marker.style.left = `${result.slider.marker}%`;
            marker.hidden = result.slider.marker <= 0;
            find('[data-marker-label]').textContent = `${result.slider.marker} MÍNIMO`;
            // Keep the label in the track while the tick stays on the true minimum.
            const trackWidth = find('.sim-range-wrap').clientWidth;
            const halfLabel = marker.offsetWidth / 2;
            const markerX = trackWidth * result.slider.marker / 100;
            const labelShift = Math.max(halfLabel - markerX, Math.min(0, trackWidth - markerX - halfLabel));
            marker.querySelector('span').style.transform = `translateX(${labelShift}px)`;
            if (gradientFirst !== first) {
                const stops = Array.from({ length: 101 }, (_, simulated) => {
                    const scenario = engine.calculate({ mode: 'one', first, simulated, passScore, highGradeThreshold });
                    return `${scenario.status.color} ${simulated}%`;
                });
                find('.sim-zones').style.background = `linear-gradient(90deg,${stops.join(',')})`;
                gradientFirst = first;
            }
        } else {
            const slider = find('.slider-block'), marker = find('.pass-marker'), zones = find('.sim-zones');
            const outcome = result.projectedPass ? 'APRUEBA' : 'REPRUEBA';
            const color = result.projectedPass ? '#16a34a' : '#ef4444';
            slider.style.setProperty('--third-state', color);
            slider.dataset.projectedPass = String(result.projectedPass);
            find('.slider-outcome').textContent = `${result.third} · ${outcome}`;
            range.setAttribute('aria-valuetext', `${result.third} puntos en el 3.º trimestre: ${outcome.toLocaleLowerCase('es-BO')}.`);
            marker.hidden = passed || failed;
            find('[data-marker-label]').textContent = `${result.requiredThird} MÍNIMO`;
            // Native range thumbs travel between their half-width insets, not the track edges.
            // Align the minimum marker and the red/green boundary with that same position.
            const thumbSize = 58;
            const markerX = range.offsetLeft + thumbSize / 2 + Math.max(0, range.clientWidth - thumbSize) * result.slider.marker / 100;
            marker.style.left = `${markerX}px`;
            marker.style.transform = 'translateX(-50%)';
            const halfLabel = marker.offsetWidth / 2;
            const labelShift = Math.max(halfLabel - markerX, Math.min(0, find('.sim-range-wrap').clientWidth - markerX - halfLabel));
            marker.querySelector('span').style.transform = `translateX(${labelShift}px)`;
            const cutoff = Math.max(0, Math.min(100, (markerX - zones.offsetLeft) / Math.max(1, zones.clientWidth) * 100));
            zones.style.background = passed ? '#16a34a' : failed ? '#ef4444'
                : `linear-gradient(90deg,#ef4444 0 ${cutoff}%,#16a34a ${cutoff}% 100%)`;
        }
        renderSupport(result);
        find('.whatsapp-result').href = `https://wa.me/?text=${encodeURIComponent(engine.buildWhatsAppMessage(result))}`;
    }

    root.querySelectorAll('[data-mode]').forEach(button => button.addEventListener('click', () => selectMode(button.dataset.mode)));
    find('.change-mode').addEventListener('click', () => {
        workspace.hidden = true; picker.hidden = false; stage.hidden = true; support.hidden = true;
        mode = null; currentResult = null; knownNotes = ''; previousResult = '';
        records.update(null, null, null);
    });
    [firstInput, secondInput].forEach(input => {
        input.addEventListener('click', () => { input.value = ''; delete input.dataset.touched; update(); });
        input.addEventListener('input', () => { window.IfeSimulatorRecords.clampInput(input); update(); });
        input.addEventListener('keydown', event => { if (['e', 'E', '+', '-', '.'].includes(event.key)) event.preventDefault(); });
        input.addEventListener('blur', () => { input.dataset.touched = '1'; update(); });
    });
    range.addEventListener('input', update);
    let resizeFrame;
    window.addEventListener('resize', () => {
        if (!mode) return;
        window.cancelAnimationFrame(resizeFrame);
        resizeFrame = window.requestAnimationFrame(update);
    });
    // Keep campaign copy current even when a tab stays open across the closing date.
    window.setInterval(() => { if (currentResult) renderSupport(currentResult); }, 60000);
    const initialMode = new URLSearchParams(window.location.search).get('mode');
    if (initialMode === 'one' || initialMode === 'two') selectMode(initialMode);
}());
