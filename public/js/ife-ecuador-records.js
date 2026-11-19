(function (global) {
    'use strict';

    function create(root) {
        const button = root.querySelector('[data-ec-calculate]');
        const status = root.querySelector('[data-ec-save-status]');
        const saved = new Set(), attempts = new Map();
        let current = null, signature = '', busy = false;

        function submissionId() {
            if (global.crypto.randomUUID) return global.crypto.randomUUID();
            const bytes = global.crypto.getRandomValues(new Uint8Array(16));
            bytes[6] = (bytes[6] & 15) | 64;
            bytes[8] = (bytes[8] & 63) | 128;
            const hex = Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('');
            return `${hex.slice(0,8)}-${hex.slice(8,12)}-${hex.slice(12,16)}-${hex.slice(16,20)}-${hex.slice(20)}`;
        }

        function sync(context) {
            const next = context ? JSON.stringify(context) : '';
            if (next !== signature) status.textContent = '';
            signature = next;
            current = context;
            status.hidden = !context;
        }

        async function save() {
            if (!current || busy) return;
            if (saved.has(signature)) {
                status.textContent = 'Consulta ya guardada.';
                return;
            }
            const submittedSignature = signature;
            if (!attempts.has(submittedSignature)) {
                attempts.set(submittedSignature, {...current, mode:'two', submission_id:submissionId()});
            }
            const payload = attempts.get(submittedSignature);
            busy = true;
            button.disabled = true;
            button.textContent = 'Guardando…';
            status.textContent = 'Guardando consulta…';
            try {
                const response = await fetch(root.dataset.recordsUrl, {
                    method:'POST', credentials:'same-origin',
                    headers:{'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':root.dataset.csrf},
                    body:JSON.stringify(payload),
                });
                if (!response.ok) throw new Error(String(response.status));
                const data = await response.json();
                if (data.saved !== true) throw new Error('invalid_response');
                saved.add(submittedSignature);
                attempts.delete(submittedSignature);
                if (signature === submittedSignature) status.textContent = 'Consulta guardada.';
            } catch (error) {
                if (signature === submittedSignature) {
                    status.textContent = error.message === '419'
                        ? 'La sesión venció. Recarga la página para guardar la consulta.'
                        : error.message === '429'
                            ? 'Demasiados intentos. Espera un momento y pulsa Calcular para guardar.'
                            : 'No se pudo guardar. Pulsa Calcular para reintentar; tu resultado sigue disponible.';
                }
            } finally {
                busy = false;
                button.disabled = false;
                button.textContent = 'Calcular';
            }
        }

        return {sync, save};
    }

    global.IfeEcuadorRecords = {create};
}(window));
