import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import { test } from 'node:test';

const context = vm.createContext({ window: {}, Intl, Date });
const originalSource = readFileSync(new URL('../../public/js/ife-grade-engine.js', import.meta.url), 'utf8');
vm.runInContext(originalSource, context);
const engine = context.window.IfeGradeEngine;
const calculate = (first, second, mode = 'two') => engine.calculate({ mode, first, second,
    simulated: mode === 'one' ? second : Math.max(0, Math.min(100, 153 - first - second)), passScore: 153, highGradeThreshold: 70 });
const expected = [[29, 'VERY_GOOD'], [30, 'REINFORCEMENT'], [49, 'REINFORCEMENT'],
    [50, 'NEEDS_SUPPORT'], [64, 'NEEDS_SUPPORT'], [65, 'RISK'], [74, 'RISK'],
    [75, 'HIGH_RISK'], [84, 'HIGH_RISK'], [85, 'CRITICAL'], [94, 'CRITICAL'],
    [95, 'EXTREME'], [100, 'EXTREME'], [101, 'FAILED']];

for (const [required, key] of expected) {
    test(`boundary ${required}: ${key} in both modes and WhatsApp`, () => {
        for (const mode of ['one', 'two']) {
            const first = Math.min(100, 153 - required), second = 153 - required - first;
            const result = calculate(first, second, mode);
            assert.equal(result.requiredThird, required);
            assert.equal(result.status.key, key);
            const campaign = engine.campaignContext('2026-12-02', new Date('2026-11-01T12:00:00Z'));
            const recommendation = engine.supportRecommendation(result, 'Matemáticas', campaign);
            assert.match(recommendation.title + ' ' + recommendation.message, /Matemáticas/iu);
            assert.equal(recommendation.ctaLabel, result.status.ctaLabel);
            const url = new URL(engine.supportWhatsAppUrl('+59160902299', result, 'Matemáticas', campaign));
            assert.equal(url.pathname, '/59160902299');
            assert.match(url.search, /%/);
            const message = url.searchParams.get('text');
            assert.match(message, /Matemáticas/);
            assert.ok(message.includes(result.status.label));
            assert.ok(message.includes(`1.º trimestre (real): ${first}`));
            assert.ok(message.includes(`2.º trimestre (${mode === 'one' ? 'simulado' : 'real'}): ${second}`));
            if (key === 'FAILED') {
                assert.doesNotMatch(message, /necesit[oaí].*101/i);
                assert.match(message, /ni obteniendo 100/);
                assert.match(message, /recuperar mi base/);
            } else assert.ok(message.includes(`${required} en el 3.º trimestre`));
        }
    });
}

test('passed takes priority and never asks for 0', () => {
    assert.equal(engine.classifyAcademicNeed(150, true).key, 'PASSED');
    for (const mode of ['one', 'two']) {
        const result = calculate(100, 53, mode);
        assert.equal(result.requiredThird, 0);
        assert.equal(result.status.key, 'PASSED');
        assert.equal(result.status.label, 'YA PASASTE');
        const message = engine.buildSupportWhatsAppMessage(result, 'Física');
        assert.doesNotMatch(message, /necesit[oaí].*0/);
        assert.match(message, /asegurada la aprobación/);
        assert.match(message, /mejorar mi rendimiento/);
        if (mode === 'one') assert.match(message, /Con la segunda nota simulada/);
    }
});

const dates = [['2026-10-20', 43, 'early'], ['2026-11-02', 30, 'soon'],
    ['2026-11-17', 15, 'soon'], ['2026-11-18', 14, 'weeks'], ['2026-11-25', 7, 'weeks'],
    ['2026-11-26', 6, 'days'], ['2026-12-01', 1, 'days'], ['2026-12-02', 0, 'today'], ['2026-12-03', -1, 'after']];
for (const [date, days, phase] of dates) {
    test(`campaign ${date}: ${phase}, independent of classification`, () => {
        const campaign = engine.campaignContext('2026-12-02', new Date(`${date}T12:00:00Z`));
        assert.equal(campaign.daysRemaining, days);
        assert.equal(campaign.phase, phase);
        const result = calculate(55, 20);
        assert.equal(result.status.key, 'HIGH_RISK');
        const recommendation = engine.supportRecommendation(result, 'Química', campaign);
        if (phase === 'after') {
            assert.match(recommendation.message, /siguiente etapa/);
            assert.doesNotMatch(recommendation.message, /queda poco|antes del cierre/i);
            assert.equal(recommendation.ctaLabel, result.status.ctaLabel);
        } else if (phase === 'early') {
            assert.match(recommendation.message, /seguir esperando reduce tu margen/);
            assert.ok(!recommendation.message.includes(campaign.message));
        } else {
            const timing = {soon:'antes del cierre',weeks:'últimas semanas',days:'pocos días',today:'hoy que cierra'}[phase];
            assert.ok(recommendation.message.includes(timing));
        }
        assert.equal(recommendation.title, 'RIESGO ALTO: NECESITAS ACTUAR');
    });
}

test('campaign uses Bolivia calendar day and handles missing configuration', () => {
    assert.equal(engine.campaignContext('2026-12-02', new Date('2026-12-03T02:00:00Z')).phase, 'today');
    assert.equal(engine.campaignContext('2026-12-02', new Date('2026-12-03T04:00:00Z')).phase, 'after');
    for (const date of ['', 'invalid', '2026-02-30']) assert.equal(engine.campaignContext(date).phase, 'unconfigured');
});

test('passed and failed do not receive deadline pressure', () => {
    const campaign = engine.campaignContext('2026-12-02', new Date('2026-12-01T12:00:00Z'));
    for (const result of [calculate(100, 100), calculate(100, 30), calculate(10, 10)]) {
        assert.ok(!engine.supportRecommendation(result, 'Física', campaign).message.includes(campaign.message));
    }
});

test('all possible real grade pairs preserve the academic formula in both modes', () => {
    for (let first = 0; first <= 100; first++) for (let second = 0; second <= 100; second++) {
        const expected = Math.max(0, 153 - first - second);
        for (const mode of ['one', 'two']) {
            const result = calculate(first, second, mode);
            assert.equal(result.requiredThird, expected);
            assert.equal(result.average, result.total / 3);
            assert.equal(result.passAverage, 51);
            assert.equal(result.alreadyPassed, first + second >= 153);
            assert.equal(result.projectedPass, result.total >= 153);
        }
    }
});

test('URL special characters round trip safely', () => {
    const result = calculate(55, 20);
    const subject = 'Física & Química / Ñ';
    const url = new URL(engine.supportWhatsAppUrl('59160902299', result, subject));
    assert.ok(url.searchParams.get('text').includes(subject));
    assert.equal(engine.classifyAcademicNeed(75).color, '#FFA500');
    assert.equal(engine.classifyAcademicNeed(101).color, '#FF2C2C');
    const labels = new Set([engine.classifyAcademicNeed(0, true).ctaLabel, ...expected.map(([grade]) => engine.classifyAcademicNeed(grade).ctaLabel)]);
    assert.equal(labels.size, 8);
});

test('each state has the requested label and updated main CTA', () => {
    const contracts = [
        [0, true, 'YA PASASTE', 'QUIERO MEJORAR'],
        [24, false, 'VAS MUY BIEN', 'QUIERO MEJORAR'],
        [42, false, 'CONVIENE REFORZAR', 'QUIERO REFORZAR'],
        [58, false, 'NECESITA APOYO', 'QUIERO RECIBIR APOYO'],
        [68, false, 'EN RIESGO', 'NECESITO APOYO'],
        [78, false, 'RIESGO ALTO', 'QUIERO ACTUAR AHORA'],
        [89, false, 'SITUACIÓN CRÍTICA', 'NECESITO AYUDA AHORA'],
        [97, false, 'NECESIDAD EXTREMA', 'NECESITO APOYO URGENTE'],
        [112, false, 'YA NO ALCANZA', 'QUIERO RECUPERAR MI BASE'],
    ];
    for (const [required, passed, label, cta] of contracts) {
        const status = engine.classifyAcademicNeed(required, passed);
        assert.equal(status.label, label);
        assert.equal(status.ctaLabel, cta);
    }
});

test('one recommendation structure renders the nine requested dynamic titles and phrases', () => {
    const contracts = [
        [0, '¡FELICIDADES, YA PASASTE!', 'Sigue mejorando en Física y termina bien.'],
        [24, 'VAS MUY BIEN', 'Refuerza Física y mejora tu nota.'],
        [42, 'CONVIENE REFORZAR', 'Refuerza Física y evita llegar al final necesitando una nota más difícil.'],
        [58, 'NECESITAS APOYO', 'Refuerza Física antes de que se complique más.'],
        [68, 'ESTÁS EN RIESGO', 'En Física, cada punto empieza a importar; prepárate desde ahora.'],
        [78, 'RIESGO ALTO: NECESITAS ACTUAR', 'Necesitas una nota alta en Física; seguir esperando reduce tu margen.'],
        [89, 'SITUACIÓN CRÍTICA', 'Tu margen en Física es muy pequeño; prepárate cuanto antes.'],
        [97, 'NECESIDAD EXTREMA DE MEJORAR', 'Necesitas una nota excepcional en Física; casi no hay margen para equivocarte.'],
        [112, 'YA NO ALCANZA: RECUPERA TU BASE', 'Esta gestión ya no alcanza en Física; evita arrastrar las mismas dificultades.'],
    ];
    for (const [required, title, message] of contracts) {
        const first = required === 0 ? 100 : Math.min(100, 153 - required);
        const second = required === 0 ? 53 : 153 - required - first;
        const result = calculate(first, second);
        const recommendation = engine.supportRecommendation(result, 'Física');
        assert.equal(recommendation.title, title);
        assert.equal(recommendation.message, message);
        assert.equal(recommendation.severity, result.status.severity);
        assert.doesNotMatch(recommendation.title + ' ' + recommendation.message, /\d/);
        assert.doesNotMatch(recommendation.title + ' ' + recommendation.message, /\{subject/);
        const otherSubject = engine.supportRecommendation(result, 'Inglés');
        assert.match(otherSubject.title + ' ' + otherSubject.message, /Inglés/iu);
        assert.doesNotMatch(otherSubject.title + ' ' + otherSubject.message, /Física/iu);
    }
});

test('missing subjects produce clean generic copy', () => {
    assert.equal(engine.supportRecommendation(calculate(100, 53), '').message, 'Ahora puedes seguir mejorando.');
    assert.equal(engine.supportRecommendation(calculate(100, 11), '').title, 'CONVIENE REFORZAR');
    assert.equal(engine.supportRecommendation(calculate(95, 0), '').title, 'NECESITAS APOYO');
});

test('post-closing copy removes urgency while keeping positive and failed outcomes coherent', () => {
    const campaign = engine.campaignContext('2026-12-02', new Date('2026-12-03T12:00:00Z'));
    for (const result of [calculate(100, 53), calculate(100, 29), calculate(10, 10)]) {
        const recommendation = engine.supportRecommendation(result, 'Matemáticas', campaign);
        assert.equal(recommendation.title, result.status.supportTitle);
        assert.doesNotMatch(recommendation.message, /cierre|pocos días|urgente/i);
    }
    const extreme = calculate(56, 0);
    const recommendation = engine.supportRecommendation(extreme, 'Matemáticas', campaign);
    assert.equal(extreme.status.key, 'EXTREME');
    assert.equal(recommendation.title, 'NECESIDAD EXTREMA DE MEJORAR');
    assert.doesNotMatch(recommendation.message, /aprobar|excepcional|urgente/i);
    const message = engine.buildSupportWhatsAppMessage(extreme, 'Matemáticas', campaign);
    assert.match(message, /Mi situación: NECESIDAD EXTREMA/);
    assert.match(message, /siguiente etapa/);
    assert.doesNotMatch(message, /apoyo urgente/);
});

test('state badge colors meet text contrast', () => {
    const luminance = hex => {
        const channels = hex.replace('#', '').match(/../g).map(value => parseInt(value, 16) / 255)
            .map(value => value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4);
        return channels[0] * .2126 + channels[1] * .7152 + channels[2] * .0722;
    };
    for (const status of [engine.classifyAcademicNeed(0, true), ...expected.map(([grade]) => engine.classifyAcademicNeed(grade))]) {
        const values = [luminance(status.color), luminance(status.ink)].sort((a, b) => b - a);
        assert.ok((values[0] + .05) / (values[1] + .05) >= 4.5, status.key);
    }
});
