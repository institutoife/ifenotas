(function (global) {
    'use strict';

    function clamp(value, minimum, maximum) {
        return Math.max(minimum, Math.min(maximum, value));
    }

    // This ordered scale is the sole source for result, recommendation and WhatsApp.
    const statuses = [
        {
            key: 'PASSED', label: 'YA PASASTE', severity: 0, color: '#15803d', ink: '#ffffff',
            shortMessage: 'Ya tienes asegurada la aprobación, incluso con 0 en el 3.º trimestre.',
            supportTitle: '¡FELICIDADES, YA PASASTE!', supportMessage: 'Sigue mejorando en {subject} y termina bien.',
            supportGenericMessage: 'Ahora puedes seguir mejorando.', ctaLabel: 'QUIERO MEJORAR',
            whatsappRequest: 'Quisiera información para reforzar {subject} y mejorar mi rendimiento.',
        },
        {
            key: 'VERY_GOOD', below: 30, label: 'VAS MUY BIEN', severity: 0, color: '#15803d', ink: '#ffffff',
            shortMessage: 'Estás en una muy buena posición para aprobar.',
            supportTitle: 'VAS MUY BIEN', supportMessage: 'Refuerza {subject} y mejora tu nota.',
            ctaLabel: 'QUIERO MEJORAR', whatsappRequest: 'Quisiera apoyo para reforzar {subject} y mejorar mi nota.',
        },
        {
            key: 'REINFORCEMENT', below: 50, label: 'CONVIENE REFORZAR', severity: 1, color: '#a3b848', ink: '#172f23',
            shortMessage: 'Vas encaminado; reforzar ahora puede darte mayor seguridad.',
            supportTitle: 'CONVIENE REFORZAR', supportMessage: 'Refuerza {subject} y evita llegar al final necesitando una nota más difícil.',
            supportClosingMessage: 'Refuerza {subject} {deadline} para no acumular dificultades.',
            ctaLabel: 'QUIERO REFORZAR', whatsappRequest: 'Quisiera información para reforzar {subject}.',
        },
        {
            key: 'NEEDS_SUPPORT', below: 65, label: 'NECESITA APOYO', severity: 2, color: '#eab94f', ink: '#302510',
            shortMessage: 'Necesitas una nota importante para aprobar. Conviene prepararte.',
            supportTitle: 'NECESITAS APOYO', supportMessage: 'Refuerza {subject} antes de que se complique más.',
            supportClosingMessage: 'Refuerza {subject} {deadline} antes de que se complique más.',
            ctaLabel: 'QUIERO RECIBIR APOYO', whatsappRequest: 'Quisiera recibir apoyo escolar en {subject} para prepararme.',
        },
        {
            key: 'RISK', below: 75, label: 'EN RIESGO', severity: 3, color: '#f5b02e', ink: '#302510',
            shortMessage: 'Tu situación requiere atención. Necesitas prepararte bien para alcanzar la nota que te falta.',
            supportTitle: 'ESTÁS EN RIESGO', supportMessage: 'En {subject}, cada punto empieza a importar; prepárate desde ahora.',
            supportClosingMessage: 'Cada punto importa en {subject} {deadline}; prepara lo que te falta.',
            ctaLabel: 'NECESITO APOYO', whatsappRequest: 'Necesito apoyo para prepararme mejor en {subject}.',
        },
        {
            key: 'HIGH_RISK', below: 85, label: 'RIESGO ALTO', severity: 4, color: '#FFA500', ink: '#302510',
            shortMessage: 'Todavía puedes aprobar, pero necesitas una nota alta.',
            supportTitle: 'RIESGO ALTO: NECESITAS ACTUAR', supportMessage: 'Necesitas una nota alta en {subject}; seguir esperando reduce tu margen.',
            supportClosingMessage: 'Necesitas una nota alta en {subject} {deadline}; esperar reduce tu margen.',
            ctaLabel: 'QUIERO ACTUAR AHORA', whatsappRequest: 'Necesito apoyo para preparar la nota alta que necesito en {subject}.',
        },
        {
            key: 'CRITICAL', below: 95, label: 'SITUACIÓN CRÍTICA', severity: 5, color: '#c2410c', ink: '#ffffff',
            shortMessage: 'Necesitas una nota muy alta. Tu margen es reducido y conviene prepararte cuanto antes.',
            supportTitle: 'SITUACIÓN CRÍTICA', supportMessage: 'Tu margen en {subject} es muy pequeño; prepárate cuanto antes.',
            supportClosingMessage: 'Tu margen en {subject} es mínimo {deadline}; prepárate cuanto antes.',
            ctaLabel: 'NECESITO AYUDA AHORA', whatsappRequest: 'Necesito ayuda para preparar los temas de {subject} cuanto antes.',
        },
        {
            key: 'EXTREME', below: 101, label: 'NECESIDAD EXTREMA', severity: 6, color: '#b91c36', ink: '#ffffff',
            shortMessage: 'Todavía existe una posibilidad matemática, pero necesitas una nota excepcional.',
            supportTitle: 'NECESIDAD EXTREMA DE MEJORAR', supportMessage: 'Necesitas una nota excepcional en {subject}; casi no hay margen para equivocarte.',
            supportClosingMessage: 'Necesitas una nota excepcional en {subject} {deadline}; aprovecha el margen.',
            ctaLabel: 'NECESITO APOYO URGENTE', whatsappRequest: 'Necesito apoyo urgente en {subject} para preparar la nota excepcional que necesito.',
        },
        {
            key: 'FAILED', label: 'YA NO ALCANZA', severity: 7, color: '#FF2C2C', ink: '#231818',
            shortMessage: 'Ni sacando 100 alcanza.',
            supportTitle: 'YA NO ALCANZA: RECUPERA TU BASE', supportMessage: 'Esta gestión ya no alcanza en {subject}; evita arrastrar las mismas dificultades.',
            ctaLabel: 'QUIERO RECUPERAR MI BASE',
            whatsappRequest: 'Quisiera información para reforzar {subject}, recuperar mi base académica y preparar la siguiente etapa.',
        },
    ];

    function classifyAcademicNeed(requiredGrade, alreadyPassed = false) {
        if (!Number.isFinite(requiredGrade)) throw new TypeError('La nota necesaria debe ser finita.');
        const status = alreadyPassed ? statuses[0] : requiredGrade > 100 ? statuses[8]
            : statuses.slice(1, 8).find(item => requiredGrade < item.below);
        return { ...status, soft: status.color + '12', shadow: status.color + '30',
            textColor: ['REINFORCEMENT', 'NEEDS_SUPPORT', 'RISK', 'HIGH_RISK'].includes(status.key) ? '#805400' : status.color };
    }

    function calculate(options) {
        const mode = options.mode;
        const passScore = options.passScore;
        const highGradeThreshold = options.highGradeThreshold;
        const first = options.first;
        const second = mode === 'one' ? options.simulated : options.second;
        const third = mode === 'one'
            ? clamp(passScore - first - second, 0, 100)
            : options.simulated;
        const requiredThird = Math.max(0, passScore - first - second);
        const total = first + second + third;
        const difference = total - passScore;
        const alreadyPassed = first + second >= passScore;
        const status = classifyAcademicNeed(requiredThird, alreadyPassed);
        const passAverage = passScore / 3;

        return {
            mode,
            first,
            second,
            third,
            requiredThird,
            total,
            average: total / 3,
            passAverage,
            difference,
            status,
            alreadyPassed,
            projectedPass: total >= passScore,
            slider: mode === 'one'
                ? {
                    redEnd: clamp(passScore - first - 100, 0, 100),
                    orangeEnd: clamp(passScore - first - highGradeThreshold, 0, 100),
                    marker: clamp(passScore - first - 100, 0, 100),
                }
                : {
                    redEnd: clamp(requiredThird, 0, 100),
                    orangeEnd: clamp(requiredThird, 0, 100),
                    marker: clamp(requiredThird, 0, 100),
                },
        };
    }

    function formatAverage(value) {
        return value.toLocaleString('es-BO', {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1,
        });
    }

    function campaignContext(endDate, now = new Date(), timeZone = 'America/La_Paz') {
        const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(endDate || '');
        if (!match) return { phase: 'unconfigured', daysRemaining: null, message: '' };
        const end = Date.UTC(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
        if (new Date(end).toISOString().slice(0, 10) !== endDate) return { phase: 'unconfigured', daysRemaining: null, message: '' };
        const parts = new Intl.DateTimeFormat('en-US', { timeZone, year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(now);
        const part = type => Number(parts.find(item => item.type === type).value);
        const daysRemaining = Math.round((end - Date.UTC(part('year'), part('month') - 1, part('day'))) / 86400000);
        const [phase, message] = daysRemaining < 0 ? ['after', 'Refuerza tu base y prepárate para la siguiente etapa.']
            : daysRemaining === 0 ? ['today', 'Hoy es la fecha de cierre de la gestión.']
            : daysRemaining <= 6 ? ['days', 'Quedan pocos días antes del cierre.']
            : daysRemaining <= 14 ? ['weeks', 'Entramos en las últimas semanas.']
            : daysRemaining <= 30 ? ['soon', 'Queda poco para terminar la gestión.']
            : ['early', 'Aún estás a tiempo de reforzar.'];
        return { phase, daysRemaining, message };
    }

    function supportTemplate(template, subject, deadline = '') {
        const selected = String(subject || '').trim();
        return template.replaceAll('{subject}', selected || 'la materia')
            .replaceAll('{subjectSuffix}', selected ? ` ${selected}` : '')
            .replaceAll('{deadline}', deadline);
    }

    function supportRecommendation(result, subject, campaign = { phase: 'unconfigured', message: '' }) {
        const status = result.status;
        const after = campaign.phase === 'after';
        const gentle = status.severity === 0;
        const recover = status.key === 'FAILED';
        const nextStage = after && !gentle && !recover;
        const deadline = { soon: 'antes del cierre', weeks: 'en las últimas semanas',
            days: 'en los pocos días que quedan', today: 'hoy que cierra la gestión' }[campaign.phase];
        const template = !String(subject || '').trim() && status.supportGenericMessage
            ? status.supportGenericMessage : status.supportMessage;
        const message = nextStage ? 'Trabaja las dificultades en {subject} y prepara la siguiente etapa.'
            : !gentle && !recover && deadline ? status.supportClosingMessage : template;
        return {
            title: supportTemplate(status.supportTitle, subject).toLocaleUpperCase('es-BO'),
            message: supportTemplate(message, subject, deadline),
            ctaLabel: status.ctaLabel,
            severity: status.severity,
            campaignPhase: campaign.phase,
        };
    }

    function resultLines(result) {
        const simulated = result.mode === 'one';
        const lines = [`1.º trimestre (real): ${result.first}`,
            simulated ? `2.º trimestre (simulado): ${result.second}` : `2.º trimestre (real): ${result.second}`];
        if (result.status.key === 'PASSED') {
            lines.push('', simulated ? 'Con la segunda nota simulada, tendría asegurada la aprobación incluso con 0 en el 3.º trimestre.'
                : 'Según IFE Notas, ya tengo asegurada la aprobación.');
        } else if (result.status.key === 'FAILED') {
            lines.push('', simulated ? 'En este escenario simulado, no me alcanzaría ni obteniendo 100 en el 3.º trimestre.'
                : 'Según el cálculo de IFE Notas, esta gestión ya no me alcanza ni obteniendo 100 en el 3.º trimestre.');
        } else {
            lines.push('', `Según IFE Notas, ${simulated ? 'necesitaría' : 'necesito'} ${result.requiredThird} en el 3.º trimestre para aprobar.`);
        }
        lines.push('', `Mi situación${simulated ? ' en este escenario' : ''}: ${result.status.label}.`);
        return lines;
    }

    function buildWhatsAppMessage(result) {
        return ['IFE NOTAS', '', ...resultLines(result)].join('\n');
    }

    function buildSupportWhatsAppMessage(result, subject, campaign = { phase: 'unconfigured' }) {
        const subjectName = subject || 'la materia que necesito reforzar';
        const lines = ['Hola, vengo de IFE Notas.', '', `Necesito apoyo escolar en ${subjectName}.`, '', ...resultLines(result)];
        const nextStage = campaign.phase === 'after' && result.status.severity > 0;
        const request = supportTemplate(nextStage
            ? 'Quisiera información para reforzar {subject}, recuperar mi base académica y preparar la siguiente etapa.'
            : result.status.whatsappRequest, subjectName);
        return [...lines, '', request].join('\n');
    }

    function supportWhatsAppUrl(phone, result, subject, campaign) {
        return `https://wa.me/${String(phone).replace(/\D/g, '')}?text=${encodeURIComponent(buildSupportWhatsAppMessage(result, subject, campaign))}`;
    }

    global.IfeGradeEngine = { calculate, classifyAcademicNeed, formatAverage, campaignContext, supportRecommendation,
        buildWhatsAppMessage, buildSupportWhatsAppMessage, supportWhatsAppUrl };
}(window));
