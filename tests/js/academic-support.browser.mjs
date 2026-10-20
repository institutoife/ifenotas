// Dependency-free Chrome integration checks. Start `php artisan serve --port=8000` first.
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { existsSync, mkdirSync, mkdtempSync, realpathSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { once } from 'node:events';

const baseUrl = process.env.IFE_TEST_BASE_URL || 'http://127.0.0.1:8000';
const chrome = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
assert.ok(existsSync(chrome), 'Set CHROME_PATH to your local Chrome executable.');
const tempRoot = realpathSync(tmpdir());
const profile = mkdtempSync(join(tempRoot, 'ifenotas-chrome-'));
const screenshotDir = resolve('storage/app/support-qa');
mkdirSync(screenshotDir, { recursive: true });
const browser = spawn(chrome, ['--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
    '--remote-debugging-port=0', '--remote-debugging-address=127.0.0.1', `--user-data-dir=${profile}`, 'about:blank'], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
let socket, commands = new Map(), nextId = 1;
let checks = 0;
const errors = [];
try {
    const endpoint = await new Promise((resolve, reject) => {
        let stderr = '';
        const timeout = setTimeout(() => reject(new Error('Chrome did not start: ' + stderr.slice(-1000))), 20000);
        browser.on('error', error => { clearTimeout(timeout); reject(error); });
        browser.stderr.on('data', data => {
            stderr += data;
            const match = /DevTools listening on (ws:\/\/[^\s]+)/.exec(stderr);
            if (match) { clearTimeout(timeout); resolve(match[1]); }
        });
    });
    const debugUrl = new URL(endpoint);
    const target = await (await fetch(`http://127.0.0.1:${debugUrl.port}/json/new?about:blank`, { method: 'PUT' })).json();
    socket = new WebSocket(target.webSocketDebuggerUrl);
    await once(socket, 'open');
    socket.addEventListener('message', event => {
        const message = JSON.parse(event.data);
        if (message.id) {
            const command = commands.get(message.id);
            commands.delete(message.id);
            clearTimeout(command?.timeout);
            if (message.error) command?.reject(new Error(JSON.stringify(message.error)));
            else command?.resolve(message.result);
        } else if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails.text + ': ' + message.params.exceptionDetails.exception?.description);
    });
    const send = (method, params = {}) => new Promise((resolve, reject) => {
        const id = nextId++;
        const timeout = setTimeout(() => { commands.delete(id); reject(new Error('Timed out: ' + method)); }, 15000);
        commands.set(id, { resolve, reject, timeout });
        socket.send(JSON.stringify({ id, method, params }));
    });
    const evaluate = async expression => {
        const response = await send('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true });
        if (response.exceptionDetails) throw new Error(response.exceptionDetails.exception?.description || response.exceptionDetails.text);
        return response.result.value;
    };
    await send('Runtime.enable');
    await send('Page.enable');
    // Exercise result and save UX without inserting artificial consultation records.
    await send('Page.addScriptToEvaluateOnNewDocument', { source: `
        window.__realDateNow = Date.now.bind(Date);
        const clockObserver = new MutationObserver(() => {
            const root = document.querySelector('.ife-sim');
            if (root) { root.dataset.serverTime = '2026-10-20T12:00:00-04:00'; clockObserver.disconnect(); }
        });
        clockObserver.observe(document, { childList: true, subtree: true });
        window.__savedConsultations = [];
        const originalFetch = window.fetch.bind(window);
        window.fetch = (url, options) => {
            if (String(url).includes('/simulador-notas/registros')) {
                window.__savedConsultations.push(JSON.parse(options.body));
                return Promise.resolve(new Response(JSON.stringify({saved:true,status:'pending'}), {status:201,headers:{'Content-Type':'application/json'}}));
            }
            return originalFetch(url, options);
        };
    ` });
    await send('Page.navigate', { url: `${baseUrl}/simulador-notas?mode=two` });
    for (let tries = 0; tries < 100; tries++) {
        if (await evaluate("document.readyState === 'complete' && !!document.querySelector('.mode-two')")) break;
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    assert.equal(await evaluate('!!window.IfeGradeEngine'), true, 'Engine loaded');
    const cases = [[0, 'PASSED', 'YA PASASTE'], [24, 'VERY_GOOD', 'VAS MUY BIEN'],
        [42, 'REINFORCEMENT', 'CONVIENE REFORZAR'], [58, 'NEEDS_SUPPORT', 'NECESITA APOYO'],
        [68, 'RISK', 'EN RIESGO'], [78, 'HIGH_RISK', 'RIESGO ALTO'],
        [85, 'CRITICAL', 'SITUACIÓN CRÍTICA'], [89, 'CRITICAL', 'SITUACIÓN CRÍTICA'],
        [97, 'EXTREME', 'NECESIDAD EXTREMA'], [100, 'EXTREME', 'NECESIDAD EXTREMA'], [101, 'FAILED', 'YA NO ALCANZA']];
    const widths = [320, 360, 390, 412, 430, 1280];
    const expectedCtas = {PASSED:'QUIERO MEJORAR',VERY_GOOD:'QUIERO MEJORAR',REINFORCEMENT:'QUIERO REFORZAR',
        NEEDS_SUPPORT:'QUIERO RECIBIR APOYO',RISK:'NECESITO APOYO',HIGH_RISK:'QUIERO ACTUAR AHORA',
        CRITICAL:'NECESITO AYUDA AHORA',EXTREME:'NECESITO APOYO URGENTE',FAILED:'QUIERO RECUPERAR MI BASE'};
    const expectedTitles = {PASSED:'¡FELICIDADES, YA PASASTE!',VERY_GOOD:'VAS MUY BIEN',REINFORCEMENT:'CONVIENE REFORZAR',
        NEEDS_SUPPORT:'NECESITAS APOYO',RISK:'ESTÁS EN RIESGO',HIGH_RISK:'RIESGO ALTO: NECESITAS ACTUAR',
        CRITICAL:'SITUACIÓN CRÍTICA',EXTREME:'NECESIDAD EXTREMA DE MEJORAR',FAILED:'YA NO ALCANZA: RECUPERA TU BASE'};
    async function screenshot(name) {
        await new Promise(resolve => setTimeout(resolve, 200));
        const { contentSize } = await send('Page.getLayoutMetrics');
        const shot = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true,
            clip: { x: 0, y: 0, width: contentSize.width, height: contentSize.height, scale: 1 } });
        writeFileSync(join(screenshotDir, name + '.png'), Buffer.from(shot.data, 'base64'));
    }
    for (const width of widths) {
        await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 600 });
        for (const [required, key, label] of cases) {
            const first = required === 0 ? 100 : Math.min(100, 153 - required);
            const second = required === 0 ? 53 : 153 - required - first;
            await evaluate(`(async () => {
                const root = document.querySelector('.ife-sim');
                root.querySelector('#ifeFirstTerm').value = ${first};
                root.querySelector('#ifeSecondTerm').value = ${second};
                root.querySelector('[data-consult-department]').value = 'La Paz';
                const subject = root.querySelector('[data-consult-subject]');
                subject.value = 'Matemáticas'; subject.dispatchEvent(new Event('change'));
                root.querySelector('[data-consult-button]').click();
                await new Promise(resolve => setTimeout(resolve, 10));
            })()`);
            const state = await evaluate(`(() => {
                const root = document.querySelector('.ife-sim');
                const action = root.querySelector('[data-support-link]');
                const rect = action.getBoundingClientRect();
                return { status:root.dataset.academicStatus, label:root.querySelector('.main-status b').textContent,
                    number:root.querySelector('.main-number').textContent, hidden:root.querySelector('.main-kicker').hidden,
                    stage:root.querySelector('.result-stage').hidden, support:root.querySelector('[data-support-cta]').hidden,
                    supportStatus:root.querySelector('[data-support-cta]').dataset.academicStatus,
                    supportTitle:root.querySelector('[data-support-title]').textContent,
                    supportMessage:root.querySelector('[data-support-message]').textContent,
                    repeatedData:root.querySelector('[data-support-cta]').querySelectorAll('input,select,.notes-row,.main-number,[data-note]').length,
                    structure:Array.from(root.querySelector('[data-support-cta]').children).map(element=>element.tagName).join(','),
                    cta:root.querySelector('[data-support-label]').textContent,
                    buttonColor:getComputedStyle(action).backgroundColor, buttonTextColor:getComputedStyle(action).color,
                    icon:!!action.querySelector('svg.support-whatsapp-icon path'),
                    share:action.nextElementSibling.textContent, shareLast:action.nextElementSibling.nextElementSibling === null,
                    sliderHidden:root.querySelector('.slider-block').hidden, url:action.href,
                    sliderValue:Number(root.querySelector('.sim-range').value),
                    sliderLabel:root.querySelector('[data-slider-label]').textContent,
                    sliderOutcome:root.querySelector('.slider-outcome').textContent,
                    markerHidden:root.querySelector('.pass-marker').hidden,
                    markerLabel:root.querySelector('[data-marker-label]').textContent,
                    zones:root.querySelector('.sim-zones').style.background,
                    duplicateNotesHidden:getComputedStyle(root.querySelector('.notes-row')).display === 'none',
                    overflow:document.documentElement.scrollWidth > innerWidth,
                    touchHeight:rect.height, left:rect.left, right:rect.right,
                    imageCount:root.querySelectorAll('.mode-two-visual img').length,
                    payload:window.__savedConsultations.at(-1) };
            })()`);
            assert.equal(state.status, key, `${width}px / ${required}`);
            assert.equal(state.label, label);
            assert.equal(state.supportStatus, key);
            assert.equal(state.supportTitle, expectedTitles[key]);
            assert.equal(state.cta, expectedCtas[key]);
            assert.equal(state.repeatedData, 0);
            assert.equal(state.structure, 'P,H2,P,A,A');
            assert.equal(state.buttonColor, 'rgb(37, 211, 102)');
            assert.equal(state.buttonTextColor, 'rgb(255, 255, 255)');
            assert.equal(state.icon, true);
            assert.equal(state.share, 'Compartir nota');
            assert.equal(state.shareLast, true);
            assert.doesNotMatch(state.supportTitle + ' ' + state.supportMessage, /\d/);
            assert.equal(state.stage, false);
            assert.equal(state.support, false);
            assert.equal(state.sliderHidden, false);
            assert.equal(state.duplicateNotesHidden, true);
            assert.equal(state.sliderLabel, 'SIMULA EL 3.º TRIMESTRE');
            assert.equal(state.sliderValue, Math.min(100, required));
            assert.equal(state.sliderOutcome, `${Math.min(100, required)} · ${key === 'FAILED' ? 'REPRUEBA' : 'APRUEBA'}`);
            assert.equal(state.markerHidden, key === 'PASSED' || key === 'FAILED');
            if (!state.markerHidden) assert.equal(state.markerLabel, `${required} MÍNIMO`);
            if (key === 'PASSED') assert.equal(state.zones, 'rgb(22, 163, 74)');
            else if (key === 'FAILED') assert.equal(state.zones, 'rgb(239, 68, 68)');
            else {
                assert.ok(state.zones.includes('rgb(239, 68, 68)'));
                assert.ok(state.zones.includes('rgb(22, 163, 74)'));
            }
            assert.equal(state.number, key === 'PASSED' ? 'FELICIDADES' : key === 'FAILED' ? 'APLAZADO' : String(required));
            assert.equal(state.hidden, key === 'PASSED' || key === 'FAILED');
            assert.equal(state.overflow, false, `Overflow at ${width}px / ${key}`);
            assert.ok(state.left >= 0 && state.right <= width && state.touchHeight >= 78);
            assert.equal(state.imageCount, 0);
            assert.equal(state.payload.first, first);
            assert.equal(state.payload.second, second);
            assert.equal(state.payload.subject, 'Matemáticas');
            const url = new URL(state.url);
            assert.equal(url.pathname, '/59160902299');
            assert.ok(url.searchParams.get('text').includes(label));
            assert.match(url.searchParams.get('text'), /2.º trimestre \(real\)/);
            checks++;
            if ([390, 1280].includes(width) && ['PASSED', 'NEEDS_SUPPORT', 'HIGH_RISK', 'EXTREME', 'FAILED'].includes(key)) await screenshot(`${width}-${key.toLowerCase()}`);
            const simulated = required === 0 ? 100 : Math.max(0, Math.min(100, required - 1));
            const simulation = await evaluate(`(() => {
                const root = document.querySelector('.ife-sim'), range = root.querySelector('.sim-range');
                const saves = window.__savedConsultations.length;
                range.value = '${simulated}';range.dispatchEvent(new Event('input'));
                return {status:root.dataset.academicStatus, number:root.querySelector('.main-number').textContent,
                    outcome:root.querySelector('.slider-outcome').textContent, saves:window.__savedConsultations.length-saves,
                    supportUrl:root.querySelector('[data-support-link]').href};
            })()`);
            assert.equal(simulation.status, key, 'Sliding must not reclassify the real academic need');
            assert.equal(simulation.number, state.number);
            assert.equal(simulation.outcome, `${simulated} · ${first + second + simulated >= 153 ? 'APRUEBA' : 'REPRUEBA'}`);
            assert.equal(simulation.saves, 0, 'Simulated third grades must not add real consultation records');
            assert.equal(simulation.supportUrl, state.url, 'The support message keeps the real grades and required score');
        }
    }
    await evaluate(`(async () => {
        const root=document.querySelector('.ife-sim');
        root.querySelector('#ifeFirstTerm').value='34';root.querySelector('#ifeSecondTerm').value='34';
        const subject=root.querySelector('[data-consult-subject]');subject.value='Matemáticas';subject.dispatchEvent(new Event('change'));
        root.querySelector('[data-consult-button]').click();await new Promise(resolve=>setTimeout(resolve,10));
        root.querySelector('.sim-range').focus();
    })()`);
    await send('Input.dispatchKeyEvent', {type:'keyDown',key:'ArrowLeft',code:'ArrowLeft',windowsVirtualKeyCode:37});
    await send('Input.dispatchKeyEvent', {type:'keyUp',key:'ArrowLeft',code:'ArrowLeft',windowsVirtualKeyCode:37});
    assert.equal(await evaluate("document.querySelector('.sim-range').value"), '84');
    assert.equal(await evaluate("document.querySelector('.slider-outcome').textContent"), '84 · REPRUEBA');
    await send('Input.dispatchKeyEvent', {type:'keyDown',key:'ArrowRight',code:'ArrowRight',windowsVirtualKeyCode:39});
    await send('Input.dispatchKeyEvent', {type:'keyUp',key:'ArrowRight',code:'ArrowRight',windowsVirtualKeyCode:39});
    assert.equal(await evaluate("document.querySelector('.slider-outcome').textContent"), '85 · APRUEBA');
    await send('Emulation.setDeviceMetricsOverride', {width:390,height:900,deviceScaleFactor:1,mobile:true});
    await screenshot('390-third-term-85');
    checks++;
    // The real subject list is reused in one-note mode and remains synchronized.
    await evaluate(`document.querySelector('.change-mode').click();document.querySelector('[data-mode="one"]').click();`);
    for (const width of widths) {
        await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 600 });
        await evaluate(`(() => {
            const first = document.querySelector('#ifeFirstTerm');first.value = '48';first.dispatchEvent(new Event('input'));
            const range = document.querySelector('.sim-range');range.value = '40';range.dispatchEvent(new Event('input'));
            const subject = document.querySelector('[data-consult-subject]');subject.value = 'Física';subject.dispatchEvent(new Event('change'));
        })()`);
        const state = await evaluate(`(() => {
            const root=document.querySelector('.ife-sim');
            return {status:root.dataset.academicStatus, number:root.querySelector('.main-number').textContent,
                sliderHidden:root.querySelector('.slider-block').hidden, controlsHidden:root.querySelector('[data-consult-controls]').hidden,
                subjectInControls:!!root.querySelector('[data-support-subject-slot] [data-consult-subject]'),
                subjectInSupport:!!root.querySelector('[data-support-cta] [data-consult-subject]'),
                subjectCount:root.querySelectorAll('[data-consult-subject]').length,
                url:root.querySelector('[data-support-link]').href, overflow:document.documentElement.scrollWidth > innerWidth};
        })()`);
        assert.equal(state.status, 'RISK');assert.equal(state.number, '65');
        assert.equal(state.sliderHidden, false);assert.equal(state.controlsHidden, true);
        assert.equal(state.subjectInControls, true);assert.equal(state.subjectInSupport, false);assert.equal(state.subjectCount, 1);assert.equal(state.overflow, false, `One-note overflow at ${width}px`);
        const message = new URL(state.url).searchParams.get('text');
        assert.match(message, /Física/);assert.match(message, /2.º trimestre \(simulado\): 40/);
        assert.match(message, /necesitaría 65/);
        checks++;
        if (width === 390) await screenshot('390-one-note');
    }
    await evaluate(`(() => {const first=document.querySelector('#ifeFirstTerm');first.value='100';first.dispatchEvent(new Event('input'));const range=document.querySelector('.sim-range');range.value='53';range.dispatchEvent(new Event('input'));})()`);
    assert.equal(await evaluate("document.querySelector('.main-number').textContent"), 'FELICIDADES');
    assert.doesNotMatch(await evaluate("decodeURIComponent(document.querySelector('[data-support-link]').href)"), /necesit[oaí].*0/);
    await evaluate("document.querySelector('.change-mode').click();document.querySelector('[data-mode=two]').click();");
    assert.equal(await evaluate("!!document.querySelector('[data-consult-controls] [data-consult-subject]')"), true);
    const subjects = await evaluate("Array.from(document.querySelector('[data-consult-subject]').options).map(option=>option.value).filter(Boolean)");
    for (const subject of subjects) {
        const encoded = JSON.stringify(subject);
        await evaluate(`(async () => {
            document.querySelector('#ifeFirstTerm').value='95';document.querySelector('#ifeSecondTerm').value='0';
            const select=document.querySelector('[data-consult-subject]');select.value=${encoded};select.dispatchEvent(new Event('change'));
            document.querySelector('[data-consult-button]').click();await new Promise(resolve=>setTimeout(resolve,10));
        })()`);
        const url = new URL(await evaluate("document.querySelector('[data-support-link]').href"));
        assert.ok(url.searchParams.get('text').includes(subject));
        assert.equal(await evaluate("document.querySelector('[data-support-title]').textContent"), 'NECESITAS APOYO');
        assert.ok((await evaluate("document.querySelector('[data-support-message]').textContent")).includes(subject));
        checks++;
    }
    for (const [date, phrase] of [['2026-11-02','antes del cierre'],['2026-11-18','últimas semanas'],['2026-12-01','pocos días'],['2026-12-02','hoy que cierra'],['2026-12-03','siguiente etapa']]) {
        const state = await evaluate(`(async () => {
            const root = document.querySelector('.ife-sim');
            const offset = Date.parse('${date}T12:00:00-04:00') - Date.parse(root.dataset.serverTime);
            Date.now = () => window.__realDateNow() + offset;
            root.querySelector('#ifeFirstTerm').value='55';root.querySelector('#ifeSecondTerm').value='20';
            const subject = root.querySelector('[data-consult-subject]');subject.value='Matemáticas';subject.dispatchEvent(new Event('change'));
            root.querySelector('[data-consult-button]').click();await new Promise(resolve=>setTimeout(resolve,10));
            return {status:root.dataset.academicStatus,title:root.querySelector('[data-support-title]').textContent,
                message:root.querySelector('[data-support-message]').textContent};
        })()`);
        assert.equal(state.status, 'HIGH_RISK');
        assert.equal(state.title, 'RIESGO ALTO: NECESITAS ACTUAR');
        assert.ok(state.message.includes(phrase));
        checks++;
    }
    await send('Emulation.setEmulatedMedia', {features:[{name:'prefers-reduced-motion',value:'reduce'}]});
    assert.equal(await evaluate("getComputedStyle(document.querySelector('[data-support-link]')).animationName"), 'none');
    assert.equal(await evaluate("getComputedStyle(document.querySelector('[data-support-link]')).transform"), 'none');
    await send('Emulation.setEmulatedMedia', {features:[{name:'prefers-reduced-motion',value:'no-preference'}]});
    assert.equal(await evaluate("getComputedStyle(document.querySelector('[data-support-link]')).animationName"), 'supportPam');
    await evaluate("document.querySelector('#ifeFirstTerm').value='80';document.querySelector('#ifeFirstTerm').dispatchEvent(new Event('input'));");
    assert.equal(await evaluate("document.querySelector('.result-stage').hidden"), true, 'Hide stale result when notes change');
    assert.equal(await evaluate("document.querySelector('[data-support-cta]').hidden"), true, 'Hide stale support link when notes change');
    assert.deepEqual(errors, [], 'No JavaScript runtime errors');
    console.log(`${checks} browser scenarios passed: nine states at 320, 360, 390, 412, 430 and 1280px; both modes, save payloads, WhatsApp, no overflow. Screenshots: ${screenshotDir}`);
    await send('Browser.close').catch(() => {});
} finally {
    socket?.close();
    browser.kill();
    // Only remove the isolated profile created by this test, never a real browser profile.
    if (dirname(profile) === tempRoot && profile.startsWith(join(tempRoot, 'ifenotas-chrome-'))) {
        try { rmSync(profile, { recursive: true, force: true }); } catch { /* Chrome may still be releasing its files. */ }
    }
}
