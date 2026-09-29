(() => {
    const btn = document.getElementById('dashboard-refresh-btn');
    const grid = document.getElementById('dashboard-grid');
    if (!btn || !grid) {
        return;
    }

    const url = btn.dataset.refreshUrl;
    const intervalMinutes = Number(btn.dataset.refreshInterval || 0);
    let timer = null;
    let busy = false;

    const refresh = async () => {
        if (busy || document.hidden) {
            return;
        }

        busy = true;
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');

        try {
            const res = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!res.ok) {
                throw new Error('Refresh failed');
            }

            const data = await res.json();
            if (typeof data.html === 'string') {
                grid.innerHTML = data.html;
                if (typeof window.crmBootCharts === 'function') {
                    window.crmBootCharts(grid);
                }
            }
        } catch (e) {
            // Keep existing widgets on failure.
        } finally {
            busy = false;
            btn.disabled = false;
            btn.removeAttribute('aria-busy');
        }
    };

    const startTimer = () => {
        stopTimer();
        if (!intervalMinutes || intervalMinutes < 1) {
            return;
        }

        timer = window.setInterval(() => {
            if (!document.hidden) {
                refresh();
            }
        }, intervalMinutes * 60 * 1000);
    };

    const stopTimer = () => {
        if (timer !== null) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    btn.addEventListener('click', () => {
        refresh();
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopTimer();
        } else {
            startTimer();
        }
    });

    startTimer();
})();
