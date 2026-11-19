// Start an isolated local application server first; see docs/ecuador.md.
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { existsSync, mkdirSync, mkdtempSync, realpathSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { once } from 'node:events';

const baseUrl = process.env.IFE_TEST_BASE_URL || 'http://127.0.0.1:8001';
const chrome = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
assert.ok(existsSync(chrome), 'Set CHROME_PATH to your Chrome executable.');
const tempRoot = realpathSync(tmpdir());
const profile = mkdtempSync(join(tempRoot, 'ifenotas-ecuador-'));
const screenshotDir = resolve('storage/app/ecuador-qa');
mkdirSync(screenshotDir, { recursive: true });
const browser = spawn(chrome, ['--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
    '--remote-debugging-port=0', '--remote-debugging-address=127.0.0.1', `--user-data-dir=${profile}`, 'about:blank'],
{ windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
let socket, nextId = 1, checks = 0;
const commands = new Map(), errors = [];
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
        } else if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails.exception?.description || message.params.exceptionDetails.text);
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
    const ec = 'document.querySelector("[data-ecuador-simulator]")';
    const click = selector => evaluate(`document.querySelector(${JSON.stringify(selector)}).click()`);
    const fill = (selector, value, blur = false) => evaluate(`(() => {
        const field = document.querySelector(${JSON.stringify(selector)});
        field.value = ${JSON.stringify(value)}; field.dispatchEvent(new Event('input', {bubbles:true}));
        if (${blur}) field.dispatchEvent(new Event('blur'));
    })()`);
    const choose = (selector, value) => evaluate(`(() => {
        const field=document.querySelector(${JSON.stringify(selector)});field.value=${JSON.stringify(value)};
        field.dispatchEvent(new Event('change',{bubbles:true}));
    })()`);
    async function waitFor(expression) {
        for (let tries=0;tries<150;tries++) {
            if (await evaluate(expression)) return;
            await new Promise(resolve=>setTimeout(resolve,100));
        }
        throw new Error('Page did not become ready: '+expression);
    }
    const state = () => evaluate(`(() => {
        const root = ${ec}, action = root.querySelector('[data-ec-whatsapp]');
        return {hidden:root.querySelector('[data-ec-result]').hidden,
            value:root.querySelector('[data-ec-maximum]').hidden ? root.querySelector('[data-ec-result-value]').textContent : root.querySelector('[data-ec-maximum-average]').textContent,
            mainValue:root.querySelector('[data-ec-result-value]').textContent,
            explanation:root.querySelector('[data-ec-result-explanation]').textContent, href:action.getAttribute('href'),
            status:root.querySelector('[data-ec-status]').textContent, statusMessage:root.querySelector('[data-ec-status-message]').textContent,
            actionHidden:action.hidden, subject:root.querySelector('[data-ec-result-subject]').textContent,
            range:root.querySelector('[data-ec-range]').value, simulated:root.querySelector('[data-ec-simulated]').value};
    })()`);
    const appearance = () => evaluate(`(() => {
        const properties={'.page':['width','padding'],'.page-head':['gap'],'.sim-workspace':['borderWidth','borderRadius','padding','backgroundImage'],
            '.mode-card':['borderRadius','padding','minHeight'],'.main-result':['borderWidth','borderRadius','backgroundColor'],
            '.sim-zones':['height','borderRadius'],'.support-action':['backgroundColor','borderRadius','minHeight','padding','fontSize']};
        const result={};for(const [selector,names] of Object.entries(properties)){
            const style=getComputedStyle(document.querySelector(selector));result[selector]=Object.fromEntries(names.map(name=>[name,style[name]]));
        }const thumb=getComputedStyle(document.querySelector('.sim-range'),'::-webkit-slider-thumb');
        result.thumb={width:thumb.width,height:thumb.height,borderRadius:thumb.borderRadius};return result;
    })()`);
    const classicSnapshots = new Map();
    async function screenshot(name) {
        const box = await evaluate(name.endsWith('-countries')
            ? `(() => {const r=document.querySelector('.ife-countries').getBoundingClientRect();return {x:0,y:r.top+scrollY,width:innerWidth,height:r.height};})()`
            : `({x:0,y:0,width:innerWidth,height:Math.max(innerHeight,document.documentElement.scrollHeight)})`);
        const shot = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: {...box, scale:1} });
        writeFileSync(join(screenshotDir, name + '.png'), Buffer.from(shot.data, 'base64'));
    }
    await send('Runtime.enable');
    await send('Page.enable');
    await send('Page.addScriptToEvaluateOnNewDocument',{source:`
        const originalFetch=window.fetch.bind(window);
        window.fetch=(url,options)=>String(url).includes('/simulador-notas/registros')
            ? Promise.resolve(new Response(JSON.stringify({saved:true,status:'pending'}),{status:201,headers:{'Content-Type':'application/json'}}))
            : originalFetch(url,options);
    `});
    await send('Emulation.setDeviceMetricsOverride', {width:390,height:844,deviceScaleFactor:1,mobile:true});
    await send('Page.navigate', {url:`${baseUrl}/`});
    await waitFor("document.readyState==='complete' && !!document.querySelector('[data-country-choice=ecuador]')");
    assert.equal(await evaluate("!!document.querySelector('.hero-art') && !!document.querySelector('#supportTitle') && !!document.querySelector('#servicesTitle') && !!document.querySelector('footer')"),true,'Existing home preserved');
    assert.equal(await evaluate("document.querySelectorAll('.ife-countries').length"),1,'Only one added country section');
    assert.equal(await evaluate("document.querySelectorAll('.ife-sim,[data-ecuador-simulator]').length"),0,'No inline simulators on home');
    assert.equal(await evaluate("new URL(document.querySelector('.hero-start').href).searchParams.get('mode')"),'two','Existing hero link preserved');
    assert.equal(await evaluate("new URL(document.querySelector('[data-country-choice=bolivia]').href).pathname"),'/simulador-notas');
    assert.equal(await evaluate("new URL(document.querySelector('[data-country-choice=ecuador]').href).pathname"),'/ecuador/simulador-notas');
    assert.equal(await evaluate("[...document.querySelectorAll('.country-option img')].every(img=>img.complete&&img.naturalWidth>0)"), true, 'Local flags loaded');
    checks++;
    for (const width of [320,360,390,430,768,1280]) {
        await send('Emulation.setDeviceMetricsOverride',{width,height:844,deviceScaleFactor:1,mobile:width<600});
        assert.equal(await evaluate('document.documentElement.scrollWidth>innerWidth'),false);
        assert.equal(await evaluate("[...document.querySelectorAll('.country-option')].every(a=>a.getBoundingClientRect().height>=44)"),true);
        if ([320,390,1280].includes(width)) await screenshot(`${width}-countries`);
        checks++;
    }
    await click('[data-country-choice=ecuador]');
    await waitFor("location.pathname==='/ecuador/simulador-notas' && document.readyState==='complete' && !!window.IfeEcuadorEngine");
    assert.equal(await evaluate("new URL(document.querySelector('.country-back').href).pathname"),'/');
    assert.equal(await evaluate(`${ec}.querySelector('[data-ec-modes]').hidden`), false);
    assert.deepEqual(await evaluate("[...document.querySelectorAll('[data-ec-mode]')].map(button=>button.textContent.trim())"),['TENGO 1 NOTA','TENGO 2 NOTAS']);
    assert.equal(await evaluate(`${ec}.querySelector('[data-ec-regime]')`), null,'No school regime step');
    await click('[data-ec-mode][value=two]');
    assert.equal(await evaluate(`(() => {
        const root=${ec}, selectors=['[data-ec-first]','[data-ec-second]','[data-ec-province]','[data-ec-subject]','[data-ec-calculate]'];
        return selectors.every((s,index)=>index===0||!!(root.querySelector(selectors[index-1]).compareDocumentPosition(root.querySelector(s))&Node.DOCUMENT_POSITION_FOLLOWING));
    })()`),true,'Notes first, then province, subject and Calculate');
    await fill('[data-ec-first]','6,56');
    await fill('[data-ec-second]','6,83');
    await click('[data-ec-calculate]');
    assert.equal((await state()).hidden,true,'Province and subject required in two-note mode');
    assert.equal(await evaluate("document.activeElement.hasAttribute('data-ec-province')"),true);
    await choose('[data-ec-province]','El Oro');
    await click('[data-ec-calculate]');
    assert.equal(await evaluate("document.activeElement.hasAttribute('data-ec-subject')"),true);
    await choose('[data-ec-subject]','Matemática');
    checks++;
    // Exercise actual browser insertion (including paste/IME replacement path), not only assigned values.
    const focusAndClear = async selector => {
        await evaluate(`(()=>{const field=document.querySelector(${JSON.stringify(selector)});field.focus();field.click();})()`);
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(selector)}).value`),'','Click clears the grade');
    };
    for (const selector of ['[data-ec-first]','[data-ec-second]']) {
        for (const invalid of ['11','10.01','-1','6,567','1e1','abc']) {
            await focusAndClear(selector);
            await send('Input.insertText',{text:invalid});
            assert.equal(await evaluate(`document.querySelector(${JSON.stringify(selector)}).value`),'','Invalid pasted/typed grade cannot remain in field');
            assert.equal(await evaluate(`document.querySelector(${JSON.stringify(selector)}).getAttribute('aria-invalid')`),'true');
            assert.equal((await state()).hidden,true);
            checks++;
        }
        await focusAndClear(selector);
        await send('Input.insertText',{text:'6'});
        await send('Input.insertText',{text:','});
        await send('Input.insertText',{text:'5'});
        await send('Input.insertText',{text:'6'});
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(selector)}).value`),'6,56');
        await send('Input.insertText',{text:'7'});
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(selector)}).value`),'6,56','Third decimal rejected');
        await click('[data-ec-calculate]');
        assert.equal((await state()).hidden,true,'A rejected edit cannot calculate the retained previous text');
        await focusAndClear(selector);
        await send('Input.insertText',{text:'10.00'});
        assert.equal(await evaluate(`document.querySelector(${JSON.stringify(selector)}).value`),'10.00');
        checks++;
    }
    await fill('[data-ec-first]','6,56');await fill('[data-ec-second]','6,83');await click('[data-ec-calculate]');
    assert.equal((await state()).value,'7,61');
    for (const [first, second, expected, status] of [['6,56','6.83','7,61','RIESGO ALTO'],['5,50','5,50','10,00','NECESIDAD EXTREMA'],['5,00','5,00','6,67','YA NO ALCANZA'],['5,50','5,49','7,00','YA NO ALCANZA']]) {
        await fill('[data-ec-first]', first);
        await fill('[data-ec-second]', second);
        assert.equal((await state()).hidden, true, 'Two real notes calculate only on request');
        await click('[data-ec-calculate]');
        const result = await state();
        assert.equal(result.hidden, false);
        assert.equal(result.value, expected);
        assert.equal(result.status,status);
        assert.ok(result.statusMessage.length>0,'Result uses Bolivia-style situation messages');
        assert.equal(result.subject, 'Cálculo para Matemática');
        const url = new URL(result.href), text = url.searchParams.get('text');
        assert.equal(url.hostname, 'wa.me');
        assert.equal(url.pathname, '/59160902299');
        assert.doesNotMatch(text,/régimen|undefined/i);
        assert.match(text, /Mi provincia es El Oro/);
        assert.match(text, /Matemática/);
        assert.match(text, /1.º trimestre \(real\)/);
        assert.match(text, /2.º trimestre \(real\)/);
        assert.match(text, /Mi situación/);
        assert.ok(text.includes(expected));
        if (status === 'YA NO ALCANZA') assert.equal(result.mainValue,'APLAZADO');
        if (first === '5,00') assert.match(result.explanation, /No alcanza 7,00 exactos/);
        if (second === '5,49') assert.match(result.explanation, /promedio exacto queda por debajo/);
        checks++;
    }
    await fill('[data-ec-first]','6,56');await fill('[data-ec-second]','6,83');await click('[data-ec-calculate]');
    assert.equal((await state()).range,'7.61','Third-term knob defaults to the exact required grade');
    await fill('[data-ec-range]','7.60');
    assert.match(await evaluate("document.querySelector('.slider-outcome').textContent"),/7,60 · REPRUEBA/);
    await fill('[data-ec-range]','7.61');
    assert.match(await evaluate("document.querySelector('.slider-outcome').textContent"),/7,61 · APRUEBA/);
    assert.match(await evaluate("document.querySelector('[data-marker-label]').textContent"),/7,61 MÍNIMO/);
    assert.ok((await evaluate("document.querySelector('.sim-zones').style.background")).includes('rgb(239, 68, 68)'));
    assert.ok((await evaluate("document.querySelector('.sim-zones').style.background")).includes('rgb(22, 163, 74)'));
    checks++;
    await choose('[data-ec-subject]','__other__');
    await click('[data-ec-calculate]');
    assert.equal((await state()).hidden,true,'Other requires a name');
    assert.equal(await evaluate("document.activeElement.hasAttribute('data-ec-other-subject')"),true);
    await fill('[data-ec-other-subject]','Dibujo & Diseño');
    await click('[data-ec-calculate]');
    assert.equal((await state()).subject,'Cálculo para Dibujo & Diseño');
    assert.match(new URL((await state()).href).searchParams.get('text'),/Dibujo & Diseño/);
    await choose('[data-ec-subject]','Matemática');
    checks++;
    for (const invalid of ['-1','10.01','6,567','1e1','abc','']) {
        await fill('[data-ec-first]', invalid);
        await click('[data-ec-calculate]');
        const result = await state();
        assert.equal(result.hidden, true);
        assert.equal(result.actionHidden, true);
        assert.equal(result.href, null, 'No stale WhatsApp after invalid input');
        assert.equal(await evaluate("document.querySelector('[data-ec-first]').getAttribute('aria-invalid')"), 'true');
        checks++;
    }

    await click('[data-ec-mode][value=one]');
    await fill('[data-ec-first]', '6.56', true);
    await fill('[data-ec-simulated]', '6,83', true);
    await focusAndClear('[data-ec-simulated]');
    assert.equal((await state()).hidden,true);
    await send('Input.insertText',{text:'12'});
    assert.equal((await state()).simulated,'','Simulated grades use the same limits');
    await send('Input.insertText',{text:'6,83'});
    assert.equal((await state()).value, '7,61');
    assert.equal((await state()).range, '6.83');
    let text = new URL((await state()).href).searchParams.get('text');
    assert.doesNotMatch(text,/régimen|undefined/i);
    assert.match(text, /1.º trimestre \(real\): 6,56/);
    assert.match(text, /2.º trimestre \(simulado\): 6,83/);
    assert.match(text, /no es una nota real/);
    assert.match(text, /necesitaría 7,61/);
    await fill('[data-ec-range]', '7.25');
    assert.equal((await state()).simulated, '7,25');
    assert.equal((await state()).value, '7,19');
    await evaluate("document.querySelector('[data-ec-range]').focus()");
    await send('Input.dispatchKeyEvent',{type:'keyDown',key:'ArrowRight',code:'ArrowRight',windowsVirtualKeyCode:39});
    await send('Input.dispatchKeyEvent',{type:'keyUp',key:'ArrowRight',code:'ArrowRight',windowsVirtualKeyCode:39});
    assert.equal((await state()).simulated,'7,26','Slider supports keyboard changes');
    assert.equal((await state()).value,'7,18');
    await fill('[data-ec-simulated]', '6,123', true);
    assert.equal((await state()).hidden, true);
    await click('[data-ec-mode][value=two]');
    await fill('[data-ec-first]', '', true);
    await click('[data-ec-calculate]');
    assert.equal(await evaluate('document.activeElement.hasAttribute("data-ec-first")'), true, 'Hidden fields must not retain errors or steal focus');
    await click('[data-ec-mode][value=one]');
    await fill('[data-ec-first]', '6,56', true);
    await fill('[data-ec-simulated]', '6.83', true);
    checks++;

    for (const width of [320,360,390,412,430,768,1280]) {
        await send('Emulation.setDeviceMetricsOverride', {width,height:900,deviceScaleFactor:1,mobile:width<600});
        for (const mode of ['one','two']) {
            await click(`[data-ec-mode][value=${mode}]`);
            assert.equal(await evaluate("document.querySelector('[data-ec-first]').value"),'6,56','First grade retained');
            assert.equal(await evaluate("document.querySelector('[data-ec-province]').value"),'El Oro','Province retained');
            assert.equal(await evaluate("document.querySelector('[data-ec-modes]').hidden"),false,'Mode choices stay available');
            if (mode === 'two') { await fill('[data-ec-second]', '6,83', true); await click('[data-ec-calculate]'); }
            else await fill('[data-ec-simulated]','6,83',true);
            await new Promise(resolve=>setTimeout(resolve,190));
            const layout = await evaluate(`(() => {
                const root=${ec}, button=root.querySelector('[data-ec-whatsapp]'), rect=button.getBoundingClientRect();
                return {overflow:document.documentElement.scrollWidth>innerWidth,
                    button:{left:rect.left,right:rect.right,height:rect.height},color:getComputedStyle(button).backgroundColor,
                    text:getComputedStyle(button).color,animation:getComputedStyle(button).animationName,
                    selected:getComputedStyle(root.querySelector('[data-ec-mode][aria-pressed=true]')).backgroundColor,
                    title:getComputedStyle(document.querySelector('.country-heading h1')).color};
            })()`);
            assert.equal(layout.overflow, false, `${width}px/${mode}: no horizontal overflow`);
            assert.ok(layout.button.left>=0 && layout.button.right<=width && layout.button.height>=78);
            assert.equal(layout.color, 'rgb(37, 211, 102)');
            assert.equal(layout.text, 'rgb(255, 255, 255)');
            assert.equal(layout.selected, 'rgb(38, 186, 165)');
            assert.equal(layout.title, 'rgb(55, 95, 122)');
            assert.equal(layout.animation, 'supportPam', 'Same classic WhatsApp button animation');
            if (mode==='two') classicSnapshots.set(width,await appearance());
            if ([320,390,1280].includes(width)) await screenshot(`${width}-${mode}`);
            checks++;
        }
    }
    for (const width of [320,390,1280]) {
        await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<600});
        for (const required of [100,299,300,500,650,750,850,950,1000,1001,1100]) {
            await fill('[data-ec-first]','10,00');
            await fill('[data-ec-second]',((1100-required)/100).toFixed(2));
            await click('[data-ec-calculate]');
            assert.equal((await state()).hidden,false);
            assert.equal(await evaluate('document.documentElement.scrollWidth>innerWidth'),false,`${width}px: required ${required}`);
            const textFits=await evaluate(`(()=>{
                const field=document.querySelector('[data-ec-first]'),style=getComputedStyle(field),canvas=document.createElement('canvas'),context=canvas.getContext('2d');
                context.font=style.fontWeight+' '+style.fontSize+' '+style.fontFamily;
                return context.measureText(field.value).width<=field.clientWidth-parseFloat(style.paddingLeft)-parseFloat(style.paddingRight);
            })()`);
            assert.equal(textFits,true,`${width}px: all five characters of 10,00 fit`);
            if (required>1000) {
                assert.equal((await state()).mainValue,'APLAZADO');
                assert.equal(await evaluate("document.querySelector('.pass-marker').hidden"),true);
                assert.equal(await evaluate("document.querySelector('.sim-zones').style.background"),'rgb(239, 68, 68)');
            }
            if ([320,390].includes(width)&&[1000,1100].includes(required)) await screenshot(`${width}-${required===1000?'extreme':'failed'}`);
            checks++;
        }
    }
    await click('.country-back');
    await waitFor("location.pathname==='/' && document.readyState==='complete' && !!document.querySelector('[data-country-choice=bolivia]')");
    await click('[data-country-choice=bolivia]');
    await waitFor("location.pathname==='/simulador-notas' && document.readyState==='complete' && !!window.IfeGradeEngine");
    assert.equal(await evaluate("document.querySelector('.mode-picker').hidden"),false);
    assert.deepEqual(await evaluate("[...document.querySelectorAll('[data-mode]')].map(button=>button.textContent.trim())"),['TENGO 1 NOTA','TENGO 2 NOTAS']);
    assert.equal(await evaluate("new URL(document.querySelector('.country-back').href).pathname"),'/');
    checks++;
    for (const mode of ['one','two']) {
        await send('Page.navigate',{url:`${baseUrl}/simulador-notas?mode=${mode}`});
        await waitFor(`document.readyState==='complete' && !!document.querySelector('.ife-sim.mode-${mode}')`);
        assert.equal(await evaluate("document.querySelector('.sim-workspace').hidden"),false);
        assert.equal(await evaluate("document.querySelector('.mode-picker').hidden"),false);
        assert.equal(await evaluate(`document.querySelector('[data-mode=${mode}]').getAttribute('aria-pressed')`),'true');
        checks++;
    }
    for(const [width,expected] of classicSnapshots){
        await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<600});
        await evaluate(`(async()=>{
            const root=document.querySelector('.ife-sim');
            root.querySelector('#ifeFirstTerm').value='75';root.querySelector('#ifeSecondTerm').value='0';
            root.querySelector('[data-consult-department]').value='La Paz';
            const subject=root.querySelector('[data-consult-subject]');subject.value='Matemáticas';subject.dispatchEvent(new Event('change'));
            root.querySelector('[data-consult-button]').click();await new Promise(resolve=>setTimeout(resolve,190));
        })()`);
        assert.deepEqual(await appearance(),expected,`${width}px: Ecuador and Bolivia use the same classic appearance`);
        checks++;
    }
    assert.deepEqual(errors, [], 'No JavaScript runtime errors');
    console.log(`${checks} browser scenarios passed; country navigation, required province/subject, Other, retained mode data, exact calculations, live slider, WhatsApp and mobile layouts. Screenshots: ${screenshotDir}`);
    await send('Browser.close').catch(() => {});
} finally {
    socket?.close();
    browser.kill();
    // Validate the absolute target before removing this test's disposable Chrome profile.
    if (dirname(profile) === tempRoot && profile.startsWith(join(tempRoot, 'ifenotas-ecuador-'))) {
        try { rmSync(profile, {recursive:true,force:true}); } catch { /* Chrome may still be releasing files. */ }
    }
}
