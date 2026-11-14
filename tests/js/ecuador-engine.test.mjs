import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import { test } from 'node:test';

const context = vm.createContext({ window: {}, URL });
vm.runInContext(readFileSync(new URL('../../public/js/ife-grade-engine.js', import.meta.url), 'utf8'), context);
vm.runInContext(readFileSync(new URL('../../public/js/ife-ecuador-engine.js', import.meta.url), 'utf8'), context);
const engine = context.window.IfeEcuadorEngine;

test('accepts comma or dot and converts decimal text to exact integer hundredths', () => {
    for (const [text, cents] of [['6,56',656],['6.83',683],['0',0],['10,00',1000],['10.0',1000],['7',700],[' 5,5 ',550],['.25',25],[',25',25],['0.01',1]]) {
        assert.equal(engine.parseGrade(text), cents, text);
    }
    for (const text of ['', ' ', '10.01', '11', '-1', '+1', '6,560', '6.831', '6,', '6.', '1e1', 'Infinity', 'NaN', '6,5.1', '6 56', 'abc']) {
        assert.equal(engine.parseGrade(text), null, text);
    }
});

test('6,56 and 6,83 require exactly 7,61', () => {
    const result = engine.calculate(engine.parseGrade('6,56'), engine.parseGrade('6,83'));
    assert.equal(result.requiredCents, 761);
    assert.equal(result.canReachTarget, true);
    assert.equal(result.firstCents + result.secondCents + result.requiredCents, 2100);
    assert.equal(engine.formatGrade(result.requiredCents), '7,61');
});

test('5,50 and 5,50 require 10,00 and remain mathematically possible', () => {
    const result = engine.calculate(550, 550);
    assert.equal(result.requiredCents, 1000);
    assert.equal(result.canReachTarget, true);
    assert.equal(engine.formatGrade(result.requiredCents), '10,00');
});

test('5,00 and 5,00 have maximum average 6,67 with 10,00 in third term', () => {
    const result = engine.calculate(500, 500);
    assert.equal(result.canReachTarget, false);
    assert.equal(result.requiredCents, 1100);
    assert.equal(result.maximumTotalCents, 2000);
    assert.equal(result.maximumAverageCents, 667);
});

test('rounding a maximum average to 7,00 does not change exact eligibility', () => {
    const result = engine.calculate(550, 549);
    assert.equal(result.requiredCents, 1001);
    assert.equal(result.canReachTarget, false);
    assert.equal(result.maximumAverageCents, 700);
    assert.equal(result.maximumTotalCents, 2099);
});

test('all grade extremes and cent precision are preserved', () => {
    for (const first of [0,1,99,100,499,550,656,683,999,1000]) {
        for (const second of [0,1,99,100,499,550,656,683,999,1000]) {
            const result = engine.calculate(first, second);
            assert.equal(result.requiredCents, 2100 - first - second);
            assert.equal(result.canReachTarget, first + second >= 1100);
            assert.ok(Number.isSafeInteger(result.requiredCents));
        }
    }
    assert.equal(engine.calculate(1000, 1000).requiredCents, 100);
    for (const invalid of [null, undefined, NaN, Infinity, -1, 1001, 656.5, '656']) {
        assert.throws(() => engine.calculate(invalid, 700));
        assert.throws(() => engine.calculate(700, invalid));
    }
});

test('WhatsApp follows Bolivia phrasing with province, subject and exact grades, without a regime', () => {
    const result = engine.calculate(656, 683);
    const message = engine.buildWhatsAppMessage(result, {province:'El Oro',subject:'Matemática',mode:'two'});
    for (const text of ['IFE Notas Ecuador','Mi provincia es El Oro','Matemática','1.º trimestre (real): 6,56','2.º trimestre (real): 6,83','7,61','7,00 exactos','Mi situación: RIESGO ALTO']) assert.ok(message.includes(text));
    assert.doesNotMatch(message, /régimen|undefined/i);
    assert.doesNotMatch(message, /simulé|simulad[oa]/i);
    const url = new URL(engine.buildWhatsAppUrl('https://wa.me/59160902299?text=', result, {province:'Santo Domingo de los Tsáchilas',subject:'Física & Química'}));
    assert.equal(url.pathname, '/59160902299');
    assert.ok(url.searchParams.get('text').includes('Física & Química'));
    assert.ok(url.searchParams.get('text').includes('Santo Domingo de los Tsáchilas'));
});

test('one-note WhatsApp identifies simulation and never invents a second real grade', () => {
    const message = engine.buildWhatsAppMessage(engine.calculate(656,683), {mode:'one'});
    assert.match(message, /Cálculo para esta materia/);
    assert.match(message, /2.º trimestre \(simulado\): 6,83/);
    assert.match(message, /no es una nota real/);
    assert.match(message, /necesitaría 7,61/);
    assert.doesNotMatch(message, /notas reales/);
});

test('impossible results send the maximum average instead of an impossible target', () => {
    for (const mode of ['one','two']) {
        const message = engine.buildWhatsAppMessage(engine.calculate(500,500), {mode});
        assert.match(message, /6,67/);
        assert.match(message, /no alcanza 7,00 exactos/);
        assert.doesNotMatch(message, /necesit[oaí].*11,00/);
        if (mode === 'one') assert.match(message, /escenario simulado/);
    }
});

test('Ecuador reuses Bolivia messages at the corresponding scale without changing its exact calculation', () => {
    for (const [requiredCents, expected] of [[299,'VERY_GOOD'],[300,'REINFORCEMENT'],[499,'REINFORCEMENT'],
        [500,'NEEDS_SUPPORT'],[649,'NEEDS_SUPPORT'],[650,'RISK'],[749,'RISK'],[750,'HIGH_RISK'],
        [849,'HIGH_RISK'],[850,'CRITICAL'],[949,'CRITICAL'],[950,'EXTREME'],[1000,'EXTREME'],[1001,'FAILED']]) {
        const result = engine.calculate(1000,1100-requiredCents);
        const before=JSON.stringify(result), presentation=engine.describeResult(result);
        assert.equal(presentation.key,expected, String(requiredCents));
        assert.equal(JSON.stringify(result),before,'Presentation cannot change the calculation');
        if (result.canReachTarget) assert.equal(presentation.shortMessage,context.window.IfeGradeEngine.classifyAcademicNeed(requiredCents/10).shortMessage);
        else assert.match(presentation.shortMessage,/10,00/);
    }
    assert.notEqual(engine.describeResult(engine.calculate(1000,1000)).key,'PASSED','Two grades of 10 still require 1,00 to average exactly 7,00');
});

test('classic sliders default to the exact minimum and third-term projection never rounds a failing grade up', () => {
    assert.equal(engine.defaultSlider('one',656),444);
    assert.equal(engine.defaultSlider('one',0),1000);
    assert.equal(engine.defaultSlider('two',656,683),761);
    assert.equal(engine.defaultSlider('two',550,550),1000);
    assert.equal(engine.defaultSlider('two',500,500),1000);
    const result=engine.calculate(656,683);
    assert.equal(engine.projectThird(result,760).canPass,false);
    assert.equal(engine.projectThird(result,761).canPass,true);
    assert.equal(engine.projectThird(engine.calculate(550,550),999).canPass,false);
    assert.equal(engine.projectThird(engine.calculate(550,549),1000).canPass,false);
    for (const invalid of [-1,1001,7.61,'761',NaN]) assert.throws(()=>engine.projectThird(result,invalid));
});

test('classic Ecuador support uses Bolivia titles and CTA without inventing a school closing date', () => {
    const result=engine.calculate(656,683), advice=engine.supportRecommendation(result,'Matemática');
    assert.equal(advice.title,'RIESGO ALTO: NECESITAS ACTUAR');
    assert.equal(advice.ctaLabel,'QUIERO ACTUAR AHORA');
    assert.match(advice.message,/Matemática/);
    assert.doesNotMatch(advice.message,/undefined|cierre|días que quedan/);
    const failed=engine.supportRecommendation(engine.calculate(500,500),'Matemática');
    assert.equal(failed.ctaLabel,'QUIERO RECUPERAR MI BASE');
    assert.match(failed.message,/no alcanzas 7,00/);
});
