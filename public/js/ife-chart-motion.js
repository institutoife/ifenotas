(() => {
    'use strict';
    if (window.ifeChartMotion) return;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const running = new Set();
    function animate(node, keyframes, delay = 0) {
        if (reduced.matches || !node.animate) return;
        node.getAnimations().forEach(animation => animation.cancel());
        const animation = node.animate(keyframes, {duration:1100, delay, easing:'cubic-bezier(.22,1,.36,1)', fill:'backwards'});
        running.add(animation);
        animation.finished.catch(() => {}).finally(() => running.delete(animation));
    }
    function play(root) {
        if (!root || reduced.matches) return;
        root.querySelectorAll('.ife-donut').forEach(node => animate(node, [{transform:'scale(.75) rotate(-65deg)',opacity:0},{transform:'scale(1) rotate(0)',opacity:1}]));
        root.querySelectorAll('.ife-summary-track span, .ife-column-track>span').forEach((node,index) => {
            const vertical = node.parentElement.classList.contains('ife-column-track');
            node.style.transformOrigin = vertical ? 'center bottom' : 'left center';
            animate(node, [{transform:vertical?'scaleY(0)':'scaleX(0)'},{transform:vertical?'scaleY(1)':'scaleX(1)'}], index*90);
        });
        root.querySelectorAll('.video-bar').forEach((node,index) => {
            if (node.getClientRects().length) animate(node,[{clipPath:'inset(0 100% 0 0)'},{clipPath:'inset(0 0% 0 0)'}],index*100);
        });
        root.querySelectorAll('.ife-summary-card header span, .ife-column-percent, .general-state, .subject-row .row-numbers').forEach((node,index) => {
            if (node.getClientRects().length) animate(node,[{opacity:0,transform:'translateY(16px)'},{opacity:1,transform:'translateY(0)'}],index*75);
        });
    }
    window.ifeChartMotion = play;
    reduced.addEventListener('change', () => { if (reduced.matches) running.forEach(animation => animation.cancel()); });
    document.querySelectorAll('.ife-summary').forEach(root => {
        root.querySelector('[data-replay-charts]')?.addEventListener('click', () => play(root));
        // Each graph starts when it enters the viewport, including the lower columns.
        const groups = root.querySelectorAll('.ife-summary-layout, .ife-columns');
        if (!('IntersectionObserver' in window)) { groups.forEach(play); return; }
        const observer = new IntersectionObserver(entries => entries.forEach(entry => {
            if (entry.isIntersecting) { play(entry.target); observer.unobserve(entry.target); }
        }), {threshold:.15});
        groups.forEach(group => observer.observe(group));
    });
})();
