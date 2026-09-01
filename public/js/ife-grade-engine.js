(function (global) {
    'use strict';

    function clamp(value, minimum, maximum) {
        return Math.max(minimum, Math.min(maximum, value));
    }

    function classify(requiredThird, highGradeThreshold) {
        if (requiredThird > 100) {
            return { key: 'impossible', label: 'REPROBADO', tone: 'red' };
        }

        if (requiredThird > highGradeThreshold) {
            return { key: 'high-risk', label: 'RIESGO ALTO', tone: 'orange' };
        }

        return { key: 'calm', label: 'VAS BIEN', tone: 'green' };
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
        const status = classify(requiredThird, highGradeThreshold);
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

    function buildWhatsAppMessage(result) {
        const lines = ['IFE NOTAS', '', `1.º trimestre: ${result.first}`];

        if (result.mode === 'one') {
            lines.push(`2.º trimestre simulado: ${result.second}`);
            if (result.requiredThird > 100) {
                lines.push('3.º trimestre necesario: más de 100');
            } else {
                lines.push(`3.º trimestre necesario: ${result.requiredThird}`);
            }
        } else {
            lines.push(`2.º trimestre: ${result.second}`);
            if (result.requiredThird > 100) {
                lines.push('3.º trimestre necesario: más de 100');
            } else {
                lines.push(`3.º trimestre necesario: ${result.requiredThird}`);
            }
        }

        const messages = {
            calm: [
                '🟢 Vas bien.',
                'Si quieres mejorar tus notas o reforzar alguna materia, en IFE contamos con clases de apoyo escolar.',
            ],
            'high-risk': [
                '🟠 Necesitas una nota alta para aprobar.',
                'Te recomendamos reforzar desde ahora las materias donde tienes mayor dificultad. En IFE contamos con clases de apoyo escolar.',
            ],
            impossible: [
                '🔴 Con las notas actuales, ni obteniendo 100 alcanza el promedio mínimo calculado.',
                'Te recomendamos buscar apoyo cuanto antes para reforzar las materias donde tienes mayor dificultad. En IFE contamos con clases de apoyo escolar.',
            ],
        };
        const [resultMessage, recommendation] = messages[result.status.key];
        lines.push('', resultMessage, '', recommendation);
        return lines.join('\n');
    }

    global.IfeGradeEngine = { calculate, formatAverage, buildWhatsAppMessage };
}(window));
