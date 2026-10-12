import './bootstrap';

(() => {
    const root = document.documentElement;

    const applyTheme = (light) => {
        root.classList.toggle('light', light);
        try {
            localStorage.setItem('crm-theme-v2', light ? 'light' : 'dark');
            localStorage.removeItem('crm-theme');
        } catch (e) {}
    };

    document.querySelectorAll('#crm-theme-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            applyTheme(! root.classList.contains('light'));
        });
    });

    const sidebar = document.getElementById('crm-sidebar');
    const toggle = document.getElementById('crm-sidebar-toggle');
    const backdrop = document.getElementById('crm-sidebar-backdrop');
    const desktopMq = window.matchMedia('(min-width: 1024px)');
    let lastFocus = null;

    const isDesktop = () => desktopMq.matches;

    const setToggleChrome = ({ expanded, label, title }) => {
        if (! toggle) {
            return;
        }
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', title);
    };

    const setMobileOpen = (open) => {
        if (! sidebar) {
            return;
        }

        sidebar.classList.toggle('-translate-x-full', ! open);
        backdrop?.classList.toggle('hidden', ! open);
        backdrop?.setAttribute('aria-hidden', open ? 'false' : 'true');

        setToggleChrome({
            expanded: open,
            label: open ? 'Close navigation' : 'Open navigation',
            title: open ? 'Close navigation' : 'Open navigation',
        });

        if (open) {
            lastFocus = document.activeElement;
            const focusTarget = sidebar.querySelector('a, button') || sidebar;
            window.setTimeout(() => focusTarget.focus?.(), 0);
        } else if (lastFocus instanceof HTMLElement) {
            lastFocus.focus();
            lastFocus = null;
        } else {
            toggle?.focus();
        }
    };

    const setDesktopCollapsed = (collapsed) => {
        root.classList.toggle('sidebar-collapsed', collapsed);
        try {
            localStorage.setItem('crm-sidebar-collapsed', collapsed ? '1' : '0');
        } catch (e) {}

        setToggleChrome({
            expanded: ! collapsed,
            label: collapsed ? 'Show navigation' : 'Hide navigation',
            title: collapsed ? 'Show navigation' : 'Hide navigation',
        });

        // Desktop never uses the mobile overlay.
        sidebar?.classList.remove('-translate-x-full');
        backdrop?.classList.add('hidden');
        backdrop?.setAttribute('aria-hidden', 'true');
    };

    const syncMode = () => {
        if (! sidebar || ! toggle) {
            return;
        }

        if (isDesktop()) {
            const collapsed = root.classList.contains('sidebar-collapsed')
                || (() => {
                    try {
                        return localStorage.getItem('crm-sidebar-collapsed') === '1';
                    } catch (e) {
                        return false;
                    }
                })();
            setDesktopCollapsed(collapsed);
        } else {
            // Mobile drawer starts closed.
            root.classList.remove('sidebar-collapsed');
            setMobileOpen(false);
        }
    };

    toggle?.addEventListener('click', () => {
        if (! sidebar) {
            return;
        }

        if (isDesktop()) {
            setDesktopCollapsed(! root.classList.contains('sidebar-collapsed'));
            return;
        }

        const currentlyClosed = sidebar.classList.contains('-translate-x-full');
        setMobileOpen(currentlyClosed);
    });

    backdrop?.addEventListener('click', () => {
        if (! isDesktop()) {
            setMobileOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || isDesktop()) {
            return;
        }
        if (sidebar && ! sidebar.classList.contains('-translate-x-full')) {
            setMobileOpen(false);
        }
    });

    if (typeof desktopMq.addEventListener === 'function') {
        desktopMq.addEventListener('change', syncMode);
    } else if (typeof desktopMq.addListener === 'function') {
        desktopMq.addListener(syncMode);
    }

    syncMode();

    const input = document.getElementById('global-search');
    const panel = document.getElementById('global-search-suggest');
    if (! input || ! panel) {
        return;
    }

    let timer = null;
    const labels = {
        leads: 'Leads',
        accounts: 'Accounts',
        contacts: 'Contacts',
        opportunities: 'Opportunities',
        cases: 'Cases',
    };

    const hide = () => panel.classList.add('hidden');
    const show = (html) => {
        panel.innerHTML = html;
        panel.classList.toggle('hidden', ! html);
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) {
            hide();
            return;
        }
        timer = setTimeout(async () => {
            try {
                const url = new URL(input.dataset.suggestUrl, window.location.origin);
                url.searchParams.set('q', q);
                const res = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                const groups = data.groups || {};
                let html = '';
                for (const [key, items] of Object.entries(groups)) {
                    if (! items.length) {
                        continue;
                    }
                    html += `<div class="border-b border-border px-3 py-2 font-semibold text-primary">${labels[key] || key}</div>`;
                    for (const item of items) {
                        html += `<a href="${item.url}" class="block px-3 py-2 text-text no-underline hover:bg-page" role="option"><span class="font-medium">${item.label}</span><span class="block text-xs text-muted">${item.meta || ''}</span></a>`;
                    }
                }
                if (! html) {
                    html = '<p class="px-3 py-2 text-muted">No matches</p>';
                }
                show(html);
            } catch (e) {
                hide();
            }
        }, 250);
    });

    document.addEventListener('click', (e) => {
        if (! panel.contains(e.target) && e.target !== input) {
            hide();
        }
    });
})();
