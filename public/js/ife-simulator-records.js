(function (global) {
    'use strict';
    function clampInput(input) {
        if (input.value.trim() === '') return;
        const value = Number(input.value);
        if (Number.isFinite(value)) input.value = String(Math.max(0, Math.min(100, Math.trunc(value))));
    }
    function defaultSlider(mode, first, second, passScore) {
        return Math.max(0, Math.min(100, mode === 'two' ? passScore - first - second : passScore - first - 100));
    }
    function create(root) {
        const status = root.querySelector('[data-save-status]');
        const controls = root.querySelector('[data-consult-controls]');
        const subject = root.querySelector('[data-consult-subject]');
        const button = root.querySelector('[data-consult-button]');
        let current = null, busy = false, pending = null, signature = '';
        function uuid() {
            if (global.crypto?.randomUUID) return global.crypto.randomUUID();
            const bytes = global.crypto.getRandomValues(new Uint8Array(16));
            bytes[6] = (bytes[6] & 15) | 64;
            bytes[8] = (bytes[8] & 63) | 128;
            const hex = Array.from(bytes, n => n.toString(16).padStart(2, '0')).join('');
            return `${hex.slice(0,8)}-${hex.slice(8,12)}-${hex.slice(12,16)}-${hex.slice(16,20)}-${hex.slice(20)}`;
        }
        function sync() {
            const next = current ? `${current.first}:${current.second}:${subject.value}` : '';
            if (next !== signature) { signature = next; pending = null; status.textContent = ''; }
            button.disabled = busy;
        }
        function update(mode, first, second) {
            controls.hidden = mode !== 'two';
            status.hidden = mode !== 'two';
            current = mode === 'two' && Number.isInteger(first) && Number.isInteger(second)
                && first >= 0 && first <= 100 && second >= 0 && second <= 100 ? {first, second} : null;
            sync();
        }
        subject.addEventListener('change', sync);
        button.addEventListener('click', async () => {
            if (busy) return;
            if (!current) { status.textContent = 'Completa las dos notas entre 0 y 100.'; return; }
            if (!subject.value) { status.textContent = 'Elige una materia antes de consultar.'; subject.focus(); return; }
            pending ??= {...current, subject: subject.value, submission_id: uuid()};
            const payload = pending, submittedSignature = signature;
            busy = true; button.disabled = true; button.textContent = 'Consultando…';
            status.textContent = 'Guardando consulta…';
            try {
                const response = await fetch(root.dataset.recordsUrl, {
                    method: 'POST', credentials: 'same-origin',
                    headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':root.dataset.csrf},
                    body: JSON.stringify(payload),
                });
                if (!response.ok) throw new Error(String(response.status));
                if (signature === submittedSignature) {
                    pending = null;
                    status.textContent = 'Consulta guardada.';
                }
            } catch (error) {
                if (signature === submittedSignature) status.textContent = error.message === '419'
                    ? 'La sesión venció. Recarga la página para consultar.'
                    : 'No se pudo guardar. Pulsa Consultar para reintentar.';
            } finally {
                busy = false; button.disabled = false; button.textContent = 'Consultar';
            }
        });
        return {update};
    }
    global.IfeSimulatorRecords = {create, clampInput, defaultSlider};
}(window));
