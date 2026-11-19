(function () {
    'use strict';
    const engine = window.IfeEcuadorEngine;
    if (!engine || !window.IfeGradeEngine || !window.IfeEcuadorRecords) return;

    document.querySelectorAll('[data-ecuador-simulator]').forEach(root => {
        const find = selector => root.querySelector(selector);
        const first = find('[data-ec-first]'), second = find('[data-ec-second]');
        const simulated = find('[data-ec-simulated]'), range = find('[data-ec-range]');
        const subject = find('[data-ec-subject]'), province = find('[data-ec-province]');
        const otherSubject = find('[data-ec-other-subject]'), subjectField = find('[data-ec-subject-field]');
        const stage = find('[data-ec-stage]'), resultBox = find('[data-ec-result]');
        const support = find('[data-support-cta]'), whatsapp = find('[data-support-link]');
        const share = find('.whatsapp-result'), contextRow = find('[data-ec-context]');
        const calculate = find('[data-ec-calculate]');
        const records = window.IfeEcuadorRecords.create(root);
        whatsapp.setAttribute('data-ec-whatsapp', '');
        whatsapp.hidden = true;
        const acceptedGrades = new WeakMap([[first, first.value], [second, second.value], [simulated, simulated.value]]);
        let mode = null, calculated = false, knownNotes = '', gradientFirst = null, previousResult = '';

        function rememberGrade(input) {
            acceptedGrades.set(input, input.value);
            delete input.dataset.inputRejected;
        }

        function rejectGrade(input) {
            input.dataset.inputRejected = 'true';
            input.dataset.touched = 'true';
            if (mode === 'two') calculated = false;
            render();
        }

        function hideResult(keepSimulation = false) {
            resultBox.hidden = true;
            support.hidden = true;
            whatsapp.hidden = true;
            whatsapp.removeAttribute('href');
            share.removeAttribute('href');
            stage.hidden = !keepSimulation;
            find('[data-ec-note-third]').textContent = '—';
            find('.slider-outcome').textContent = '—';
        }

        function validate(input, key, force = false) {
            const cents = input.dataset.inputRejected === 'true' ? null : engine.parseGrade(input.value);
            const invalid = cents === null && (force || input.dataset.touched === 'true');
            input.setAttribute('aria-invalid', String(invalid));
            input.classList.toggle('invalid', invalid);
            find(`[data-ec-error="${key}"]`).textContent = invalid ? 'Usa una nota de 0 a 10, con hasta dos decimales.' : '';
            return cents;
        }

        function subjectName() {
            return subject.value === '__other__' ? otherSubject.value.trim() : subject.value;
        }

        function validateContext(force = false) {
            let valid = true;
            [[province, 'province', mode === 'two' && !province.value, 'Elige tu provincia.'],
                [subject, 'subject', mode === 'two' && !subject.value, 'Elige una materia.'],
                [otherSubject, 'other', subject.value === '__other__' && !otherSubject.value.trim(), 'Escribe el nombre de la materia.']
            ].forEach(([field, key, missing, message]) => {
                const show = missing && (force || field.dataset.touched === 'true');
                field.setAttribute('aria-invalid', String(!!show));
                find(`[data-ec-error="${key}"]`).textContent = show ? message : '';
                if (missing) valid = false;
            });
            return valid;
        }

        function setRange(cents) {
            range.value = engine.formatGrade(cents, '.');
            find('[data-slider-value]').textContent = engine.formatGrade(cents);
        }

        function positionMarker(minimumCents) {
            const marker = find('.pass-marker'), zones = find('.sim-zones'), wrap = find('.sim-range-wrap');
            marker.hidden = minimumCents < 0 || minimumCents > 1000;
            find('[data-marker-label]').textContent = `${engine.formatGrade(Math.max(0, minimumCents))} MÍNIMO`;
            const markerX = range.offsetLeft + 29 + Math.max(0, range.clientWidth - 58) * Math.max(0, Math.min(1000, minimumCents)) / 1000;
            marker.style.left = `${markerX}px`;
            const halfLabel = marker.offsetWidth / 2;
            const shift = Math.max(halfLabel - markerX, Math.min(0, wrap.clientWidth - markerX - halfLabel));
            marker.querySelector('span').style.transform = `translateX(${shift}px)`;
            return Math.max(0, Math.min(100, (markerX - zones.offsetLeft) / Math.max(1, zones.clientWidth) * 100));
        }

        function renderSlider(result, status) {
            const current = engine.parseGrade(range.value);
            find('[data-slider-value]').textContent = engine.formatGrade(current);
            if (mode === 'one') {
                positionMarker(1100 - result.firstCents);
                find('.slider-outcome').textContent = status.label;
                range.setAttribute('aria-valuetext', `${engine.formatGrade(current)} sobre 10 en el 2.º trimestre (simulado): ${status.label}.`);
                if (gradientFirst !== result.firstCents) {
                    const stops = Array.from({length:101}, (_, index) => {
                        const scenario = engine.calculate(result.firstCents, index * 10);
                        return `${engine.describeResult(scenario).color} ${index}%`;
                    });
                    find('.sim-zones').style.background = `linear-gradient(90deg,${stops.join(',')})`;
                    gradientFirst = result.firstCents;
                }
            } else {
                const projection = engine.projectThird(result, current);
                const outcome = projection.canPass ? 'APRUEBA' : 'REPRUEBA';
                const color = projection.canPass ? '#16a34a' : '#ef4444';
                find('.slider-block').style.setProperty('--third-state', color);
                find('.slider-block').dataset.projectedPass = String(projection.canPass);
                find('.slider-outcome').textContent = `${engine.formatGrade(current)} · ${outcome}`;
                range.setAttribute('aria-valuetext', `${engine.formatGrade(current)} sobre 10 en el 3.º trimestre: ${outcome.toLowerCase()}.`);
                const cutoff = positionMarker(result.requiredCents);
                find('.sim-zones').style.background = result.canReachTarget
                    ? `linear-gradient(90deg,#ef4444 0 ${cutoff}%,#16a34a ${cutoff}% 100%)` : '#ef4444';
            }
        }

        function render(force = false) {
            if (!mode) { hideResult(); return; }
            const firstCents = validate(first, 'first', force);
            const realSecond = mode === 'two' ? validate(second, 'second', force) : null;
            const key = `${mode}:${firstCents}:${realSecond}`;
            if (firstCents !== null && (mode === 'one' || realSecond !== null) && key !== knownNotes) {
                const defaultCents = engine.defaultSlider(mode, firstCents, realSecond);
                setRange(defaultCents);
                if (mode === 'one') {
                    simulated.value = engine.formatGrade(defaultCents);
                    rememberGrade(simulated);
                }
                knownNotes = key;
            }
            if (firstCents === null) knownNotes = '';
            const secondCents = mode === 'one' ? validate(simulated, 'simulated', force) : realSecond;
            const contextValid = validateContext(force);
            records.sync(mode === 'two' && firstCents !== null && realSecond !== null && contextValid ? {
                first_cents:firstCents, second_cents:realSecond, province:province.value,
                subject:subject.value, ...(subject.value === '__other__' ? {other_subject:otherSubject.value.trim()} : {}),
            } : null);
            find('[data-ec-note-first]').textContent = firstCents === null ? '—' : engine.formatGrade(firstCents);
            if (firstCents === null || secondCents === null || !contextValid || (mode === 'two' && !calculated)) {
                hideResult(mode === 'one' && firstCents !== null);
                return;
            }
            const result = engine.calculate(firstCents, secondCents), status = engine.describeResult(result);
            const matter = subjectName();
            root.dataset.academicStatus = status.key;
            root.style.setProperty('--general-state', status.color);
            root.style.setProperty('--general-soft', status.soft);
            root.style.setProperty('--general-text', status.textColor);
            root.style.setProperty('--general-ink', status.ink);
            resultBox.classList.toggle('impossible', !result.canReachTarget);
            find('[data-ec-result-label]').hidden = !result.canReachTarget;
            find('[data-ec-result-value]').textContent = result.canReachTarget ? engine.formatGrade(result.requiredCents) : 'APLAZADO';
            find('[data-ec-status]').textContent = status.label;
            find('[data-ec-status-message]').textContent = status.shortMessage;
            find('[data-ec-result-subject]').textContent = matter ? `Cálculo para ${matter}` : 'Cálculo para esta materia';
            find('[data-ec-result-explanation]').textContent = result.canReachTarget
                ? `Para llegar a 7,00 exactos de promedio en esta materia.${mode === 'one' ? ' Segunda nota simulada, no real.' : ''}`
                : result.maximumAverageCents >= 700
                    ? 'Aunque se vea como 7,00 al redondear, el promedio exacto queda por debajo de 7,00.'
                    : 'No alcanza 7,00 exactos de promedio en esta materia.';
            find('[data-ec-maximum]').hidden = result.canReachTarget;
            find('[data-ec-maximum-average]').textContent = engine.formatGrade(result.maximumAverageCents);
            find('[data-ec-note-third]').textContent = result.canReachTarget ? engine.formatGrade(result.requiredCents) : '—';
            const signature = `${mode}:${firstCents}:${secondCents}`;
            if (signature !== previousResult) {
                const number = find('[data-ec-result-value]');
                number.classList.remove('pulse');
                void number.offsetWidth;
                if (result.canReachTarget) number.classList.add('pulse');
                previousResult = signature;
            }
            stage.hidden = false;
            resultBox.hidden = false;
            renderSlider(result, status);
            const recommendation = engine.supportRecommendation(result, matter);
            support.dataset.severity = String(recommendation.severity);
            support.dataset.academicStatus = status.key;
            find('[data-support-title]').textContent = recommendation.title;
            find('[data-support-message]').textContent = recommendation.message;
            find('[data-support-label]').textContent = recommendation.ctaLabel;
            const messageContext = {province:province.value,subject:matter,mode,countryName:root.dataset.countryName};
            whatsapp.href = engine.buildWhatsAppUrl(root.dataset.whatsappBase, result, messageContext);
            whatsapp.setAttribute('aria-label', `${recommendation.ctaLabel}: ${root.dataset.sendLabel}`);
            share.href = engine.buildWhatsAppUrl('https://wa.me/?text=', result, messageContext);
            support.hidden = false;
            whatsapp.hidden = false;
        }

        function selectMode(next) {
            if (mode === next) return;
            mode = next;
            calculated = false;
            knownNotes = '';
            gradientFirst = null;
            previousResult = '';
            root.classList.toggle('mode-one', mode === 'one');
            root.classList.toggle('mode-two', mode === 'two');
            root.querySelectorAll('[data-ec-mode]').forEach(button => button.setAttribute('aria-pressed', String(button.value === mode)));
            find('[data-ec-fields]').hidden = false;
            find('[data-ec-second-real]').hidden = mode === 'one';
            find('.known-fields').classList.toggle('one', mode === 'one');
            find('.mode-current').textContent = mode === 'one' ? '1 NOTA' : '2 NOTAS';
            contextRow.hidden = mode === 'one';
            province.required = mode === 'two';
            province.disabled = mode !== 'two';
            subject.required = mode === 'two';
            if (mode === 'one') find('[data-ec-subject-slot]').append(subjectField);
            else contextRow.insertBefore(subjectField, calculate);
            subjectField.querySelector('.consult-label').textContent = mode === 'one' ? '¿En qué materia necesitas apoyo?' : 'Materia';
            find('[data-slider-label]').textContent = mode === 'one' ? 'SIMULA EL 2.º TRIMESTRE' : 'SIMULA EL 3.º TRIMESTRE';
            find('[data-legend-left]').textContent = mode === 'one' ? 'REPRUEBA' : '0 · REPRUEBA';
            find('[data-legend-right]').textContent = mode === 'one' ? 'APRUEBA' : 'APRUEBA · 10,00';
            range.setAttribute('aria-label', `Nota simulada del ${mode === 'one' ? '2.º' : '3.º'} trimestre, sobre 10`);
            [[first,'first'],[second,'second'],[simulated,'simulated'],[province,'province'],[subject,'subject'],[otherSubject,'other']].forEach(([field,key]) => {
                field.dataset.touched = 'false';
                field.setAttribute('aria-invalid','false');
                field.classList.remove('invalid');
                find(`[data-ec-error="${key}"]`).textContent = '';
            });
            render();
        }

        root.querySelectorAll('[data-ec-mode]').forEach(button => button.addEventListener('click', () => selectMode(button.value)));
        [[first,'first'],[second,'second'],[simulated,'simulated']].forEach(([input,key]) => {
            input.addEventListener('click', () => {
                input.value = '';
                rememberGrade(input);
                delete input.dataset.touched;
                if (mode === 'two') calculated = false;
                render();
            });
            input.addEventListener('beforeinput', event => {
                if (!event.cancelable || event.data === null || !event.inputType.startsWith('insert')) return;
                const start = input.selectionStart ?? input.value.length;
                const end = input.selectionEnd ?? start;
                const candidate = (input.value.slice(0, start) + event.data + input.value.slice(end)).trim();
                if (!engine.isGradeDraft(candidate)) {
                    event.preventDefault();
                    rejectGrade(input);
                }
            });
            input.addEventListener('input', () => {
                const candidate = input.value.trim();
                if (!engine.isGradeDraft(candidate)) {
                    input.value = acceptedGrades.get(input) ?? '';
                    rejectGrade(input);
                    return;
                }
                input.value = candidate;
                rememberGrade(input);
                if (input === simulated) {
                    const cents = engine.parseGrade(input.value);
                    if (cents !== null) setRange(cents);
                } else if (mode === 'two') calculated = false;
                render();
            });
            input.addEventListener('blur', () => {
                input.dataset.touched = 'true';
                const cents = validate(input,key);
                if (cents !== null) {
                    input.value = engine.formatGrade(cents);
                    rememberGrade(input);
                }
                render();
            });
        });
        range.addEventListener('input', () => {
            if (mode === 'one') {
                simulated.value = engine.formatGrade(engine.parseGrade(range.value));
                rememberGrade(simulated);
            }
            render();
        });
        [province,subject].forEach(field => field.addEventListener('change', () => {
            find('[data-ec-other-field]').hidden = subject.value !== '__other__';
            otherSubject.required = subject.value === '__other__';
            field.dataset.touched = 'true';
            if (mode === 'two') calculated = false;
            render();
        }));
        otherSubject.addEventListener('input', () => {if (mode === 'two') calculated = false;render();});
        otherSubject.addEventListener('blur', () => {otherSubject.dataset.touched = 'true';render();});
        find('[data-ec-form]').addEventListener('submit', event => {
            event.preventDefault();
            if (mode !== 'two') return;
            calculated = true;
            render(true);
            const invalid = root.querySelector('[aria-invalid="true"]:not([disabled])');
            if (invalid) invalid.focus();
            else if (!resultBox.hidden) records.save();
        });
        let resizeFrame;
        window.addEventListener('resize', () => {
            window.cancelAnimationFrame(resizeFrame);
            resizeFrame = window.requestAnimationFrame(() => render());
        });
        const initialMode = new URLSearchParams(window.location.search).get('mode');
        if (initialMode === 'one' || initialMode === 'two') selectMode(initialMode);
    });
}());
