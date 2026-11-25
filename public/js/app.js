/* Business Invoicing — shared UI behaviour */
(function () {
    'use strict';

    const root = document.documentElement;

    /* ---------- Top progress bar on navigation ---------- */
    const bar = document.getElementById('navProgress');
    const startProgress = () => { if (bar) { bar.classList.remove('done'); bar.classList.add('run'); } };
    const resetProgress = () => { if (bar) { bar.classList.remove('run'); bar.classList.add('done'); } };
    document.addEventListener('click', function (e) {
        const a = e.target.closest('a[href]');
        if (!a || e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || a.hasAttribute('download') || a.dataset.noProgress !== undefined) return;
        if (/\/(invoice|excel)(\?|$)/.test(href)) return; // file downloads stay on the page
        startProgress();
    });
    window.addEventListener('pageshow', function () {
        resetProgress();
        // Re-enable buttons when coming back with the browser's back button.
        document.querySelectorAll('[data-busy]').forEach(restoreButton);
    });

    /* ---------- Mobile navigation ---------- */
    const nav = document.getElementById('mainnav'), toggle = document.getElementById('navToggle');
    toggle && toggle.addEventListener('click', function () {
        const open = !nav.classList.contains('open');
        nav.classList.toggle('open', open);
        toggle.setAttribute('aria-expanded', open);
        toggle.innerHTML = '<i class="bi ' + (open ? 'bi-x-lg' : 'bi-list') + ' fs-5"></i>';
    });

    /* ---------- Theme ---------- */
    const themeBtn = document.getElementById('themeToggle');
    const syncIcon = () => { if (themeBtn) themeBtn.querySelector('i').className = root.getAttribute('data-bs-theme') === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars'; };
    syncIcon();
    themeBtn && themeBtn.addEventListener('click', function () {
        const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-bs-theme', next);
        try { localStorage.setItem('bi-theme', next); } catch (e) {}
        syncIcon();
        document.dispatchEvent(new CustomEvent('themechange'));
    });

    /* ---------- Toasts ---------- */
    const icons = { success: 'bi-check-lg', danger: 'bi-x-lg', warning: 'bi-exclamation-lg', info: 'bi-info-lg' };
    function wireToast(el) {
        const close = () => { el.style.transition = 'opacity .2s, transform .2s'; el.style.opacity = 0; el.style.transform = 'translateY(-6px)'; setTimeout(() => el.remove(), 200); };
        el.querySelector('.btn-close').addEventListener('click', close);
        setTimeout(close, 4500);
    }
    window.toast = function (message, tone) {
        tone = tone || 'success';
        const el = document.createElement('div');
        el.className = 'app-toast';
        el.setAttribute('role', 'status');
        el.innerHTML = '<span class="toast-icon tone-' + tone + '"><i class="bi ' + (icons[tone] || icons.info) + '"></i></span><div></div><button type="button" class="btn-close" aria-label="Close"></button>';
        el.querySelector('div').textContent = message;
        document.getElementById('toasts').appendChild(el);
        wireToast(el);
    };
    document.querySelectorAll('#toasts .app-toast').forEach(wireToast);

    /* ---------- Busy state for submit buttons (prevents double submits) ---------- */
    function busyButton(btn) {
        if (!btn || btn.dataset.busy) return;
        btn.dataset.busy = btn.innerHTML;
        btn.style.minWidth = btn.offsetWidth + 'px';
        btn.disabled = true;
        const label = btn.dataset.busyLabel || (btn.classList.contains('act') || btn.classList.contains('dropdown-item') ? '' : 'Saving…');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>' + (label ? ' ' + label : '');
    }
    function restoreButton(btn) {
        if (!btn.dataset.busy) return;
        btn.innerHTML = btn.dataset.busy;
        btn.disabled = false;
        delete btn.dataset.busy;
    }

    /* ---------- Confirm modal + submit handling ---------- */
    const modalEl = document.getElementById('confirmModal');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    let pending = null;

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.dataset.confirm && !form.dataset.confirmed && modal) {
            e.preventDefault();
            pending = form;
            const tone = form.dataset.tone || 'danger';
            document.getElementById('confirmTitle').textContent = form.dataset.title || 'Are you sure?';
            document.getElementById('confirmText').textContent = form.dataset.confirm;
            const icon = document.getElementById('confirmIcon');
            icon.className = 'modal-icon tone-' + tone;
            icon.innerHTML = '<i class="bi ' + (form.dataset.icon || 'bi-exclamation-triangle') + '"></i>';
            const ok = document.getElementById('confirmOk');
            ok.textContent = form.dataset.ok || 'Confirm';
            ok.className = 'btn ' + (tone === 'danger' ? 'btn-danger' : 'btn-primary');
            modal.show();
            return;
        }
        if (e.defaultPrevented) return;
        if (form.method.toLowerCase() === 'post') {
            busyButton(e.submitter || form.querySelector('button[type=submit], button:not([type])'));
        }
        startProgress();
    });

    document.getElementById('confirmOk') && document.getElementById('confirmOk').addEventListener('click', function () {
        if (!pending) return;
        pending.dataset.confirmed = '1';
        modal.hide();
        pending.requestSubmit ? pending.requestSubmit() : pending.submit();
        pending = null;
    });

    /* ---------- Auto-submit filters: <select data-autosubmit> ---------- */
    document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.requestSubmit ? el.form.requestSubmit() : el.form.submit(); });
    });

    /* ---------- Keyboard shortcuts ---------- */
    document.addEventListener('keydown', function (e) {
        const t = e.target;
        const typing = t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName);
        if (typing || e.ctrlKey || e.metaKey || e.altKey) return;
        if (e.key === '/') {
            const search = document.querySelector('[data-search], .filter-bar input[type=text]:not([type=hidden]), .filter-bar input:not([type]), .filter-bar input[type=search]');
            if (search) { e.preventDefault(); search.focus(); search.select && search.select(); }
        } else if (e.key === 'n' || e.key === 'N') {
            const add = document.querySelector('[data-shortcut="n"]');
            if (add) { e.preventDefault(); add.click(); }
        }
    });

    /* ---------- Tooltips ---------- */
    document.querySelectorAll('[data-bs-toggle="tooltip"], .act[title]').forEach(el => new bootstrap.Tooltip(el, { container: 'body', trigger: 'hover' }));
})();
