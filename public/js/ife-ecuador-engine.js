(function (global) {
    'use strict';

    function parseGrade(value) {
        const text = String(value ?? '').trim();
        if (!/^(?:\d{1,2}(?:[.,]\d{1,2})?|[.,]\d{1,2})$/.test(text)) return null;
        const [whole, fraction = ''] = text.replace(',', '.').split('.');
        const cents = Number(whole || '0') * 100 + Number(fraction.padEnd(2, '0'));
        return cents >= 0 && cents <= 1000 ? cents : null;
    }

    function isGradeDraft(value) {
        // Allow an empty field or a decimal still being typed, but never an out-of-range grade.
        const text = String(value ?? '');
        if (!/^\d{0,2}(?:[.,]\d{0,2})?$/.test(text)) return false;
        const [whole, fraction = ''] = text.replace(',', '.').split('.');
        return Number(whole || '0') * 100 + Number(fraction.padEnd(2, '0')) <= 1000;
    }

    function formatGrade(cents, separator = ',') {
        if (!Number.isSafeInteger(cents) || cents < 0) throw new TypeError('La nota debe expresarse en centésimas enteras.');
        return `${Math.floor(cents / 100)}${separator}${String(cents % 100).padStart(2, '0')}`;
    }

    function calculate(firstCents, secondCents) {
        for (const value of [firstCents, secondCents]) {
            if (!Number.isSafeInteger(value) || value < 0 || value > 1000) throw new RangeError('Usa notas de 0 a 10, con hasta dos decimales.');
        }
        const requiredCents = 2100 - firstCents - secondCents;
        const maximumTotalCents = firstCents + secondCents + 1000;
        return {
            firstCents, secondCents, requiredCents, maximumTotalCents,
            canReachTarget: requiredCents <= 1000,
            // Rounding for display only. Eligibility always uses the exact integer total.
            maximumAverageCents: Math.floor((maximumTotalCents + 1) / 3),
        };
    }

    function describeResult(result) {
        // Reuse Bolivia's presentation scale, normalized from hundredths / 10 to 0–100.
        // This never participates in Ecuador's exact integer calculation or pass threshold.
        const status = global.IfeGradeEngine.classifyAcademicNeed(result.requiredCents / 10);
        return {...status, shortMessage: result.canReachTarget ? status.shortMessage
            : 'Ni sacando 10,00 alcanza el promedio de 7,00 en esta materia.'};
    }

    function defaultSlider(mode, firstCents, secondCents = 0) {
        return Math.max(0, Math.min(1000, 2100 - firstCents - (mode === 'one' ? 1000 : secondCents)));
    }

    function projectThird(result, thirdCents) {
        if (!Number.isSafeInteger(thirdCents) || thirdCents < 0 || thirdCents > 1000) throw new RangeError('El tercer trimestre debe estar entre 0 y 10.');
        return {canPass: result.firstCents + result.secondCents + thirdCents >= 2100, thirdCents};
    }

    function supportRecommendation(result, subject) {
        const recommendation = global.IfeGradeEngine.supportRecommendation({status: describeResult(result)}, subject);
        if (!result.canReachTarget) recommendation.message = `Con estas notas no alcanzas 7,00 en ${subject || 'esta materia'}; refuerza tu base para la siguiente etapa.`;
        return recommendation;
    }

    function buildWhatsAppMessage(result, { province = '', subject = '', mode = 'two', countryName = 'Ecuador' }) {
        const matter = subject.trim();
        const presentation = describeResult(result);
        const lines = [`Hola, vengo de IFE Notas ${countryName}.`];
        if (province) lines.push(`Mi provincia es ${province}.`);
        lines.push(matter ? `Materia: ${matter}.` : 'Cálculo para esta materia.',
            `1.º trimestre (real): ${formatGrade(result.firstCents)}`,
            `2.º trimestre (${mode === 'one' ? 'simulado' : 'real'}): ${formatGrade(result.secondCents)}`);
        if (mode === 'one') lines.push('La segunda nota es simulada; no es una nota real.');
        if (result.canReachTarget) {
            lines.push(`Según IFE Notas${mode === 'one' ? ', en este escenario simulado' : ''}, ${mode === 'one' ? 'necesitaría' : 'necesito'} ${formatGrade(result.requiredCents)} en el 3.º trimestre para llegar a 7,00 exactos de promedio en esta materia.`);
        } else {
            lines.push(`Con 10,00 en el tercer trimestre, el promedio máximo ${mode === 'one' ? 'de este escenario simulado ' : ''}sería ${formatGrade(result.maximumAverageCents)} (redondeado a dos decimales); no alcanza 7,00 exactos.`);
        }
        lines.push(`Mi situación${mode === 'one' ? ' en este escenario' : ''}: ${presentation.label}.`,
            `Quisiera apoyo escolar para reforzar ${matter || 'esta materia'} y mejorar mi rendimiento.`);
        return lines.join('\n\n');
    }

    function buildWhatsAppUrl(baseUrl, result, context) {
        const url = new URL(baseUrl);
        url.searchParams.set('text', buildWhatsAppMessage(result, context));
        return url.toString();
    }

    global.IfeEcuadorEngine = { parseGrade, isGradeDraft, formatGrade, calculate, describeResult, defaultSlider, projectThird, supportRecommendation, buildWhatsAppMessage, buildWhatsAppUrl };
}(window));
