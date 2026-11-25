/* Theme-aware Chart.js helper.
 * biChart('canvasId', colors => ({ data, options })) — re-renders when the theme changes. */
(function () {
    function palette() {
        const cs = getComputedStyle(document.documentElement);
        const v = (n) => cs.getPropertyValue(n).trim();
        const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        return {
            primary: v('--bi-primary'),
            primarySoftSolid: dark ? 'rgba(129,140,248,.55)' : 'rgba(79,70,229,.78)',
            success: v('--bi-success'),
            warning: v('--bi-warning'),
            danger: v('--bi-danger'),
            info: v('--bi-info'),
            text: v('--bi-text-muted'),
            grid: dark ? 'rgba(148,163,184,.12)' : 'rgba(15,23,42,.06)',
            surface: v('--bi-surface'),
            border: v('--bi-border'),
            heading: v('--bi-heading'),
        };
    }

    function merge(target, src) {
        for (const k in src) {
            if (src[k] && typeof src[k] === 'object' && !Array.isArray(src[k])) {
                target[k] = merge(target[k] || {}, src[k]);
            } else {
                target[k] = src[k];
            }
        }
        return target;
    }

    window.biChart = function (id, build) {
        const el = document.getElementById(id);
        if (!el || !window.Chart) return;
        let chart;
        const render = () => {
            const c = palette();
            Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
            Chart.defaults.font.size = 12;
            Chart.defaults.color = c.text;
            const cfg = build(c);
            cfg.type = cfg.type || 'bar';
            const base = {
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } },
                        tooltip: { backgroundColor: c.heading, titleColor: c.surface, bodyColor: c.surface, padding: 10, cornerRadius: 8, boxPadding: 4 }
                    },
                    scales: {}
                }
            };
            const scales = (cfg.options && cfg.options.scales) || { x: {}, y: {} };
            for (const key of Object.keys(scales).concat(['x'])) {
                base.options.scales[key] = { grid: { color: c.grid, drawBorder: false }, border: { display: false }, ticks: { color: c.text } };
            }
            base.options.scales.x.grid = { display: false };
            if (chart) chart.destroy();
            chart = new Chart(el, merge(base, cfg));
        };
        render();
        document.addEventListener('themechange', render);
    };
})();
