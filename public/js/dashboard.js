(() => {
    'use strict';
    const sidebar = document.getElementById('workspace-sidebar');
    const toggles = Array.from(document.querySelectorAll('[data-workspace-toggle]'));
    if (sidebar) {
        const mobile = window.matchMedia('(max-width: 991px)');
        const syncSidebar = () => {
            const open = mobile.matches ? document.body.classList.contains('workspace-sidebar-open') : !document.body.classList.contains('workspace-sidebar-collapsed');
            sidebar.inert = !open;
            toggles.forEach(button => button.setAttribute('aria-expanded', String(open)));
        };
        const closeSidebar = () => {
            document.body.classList.remove('workspace-sidebar-open');
            syncSidebar();
            toggles[0]?.focus();
        };
        toggles.forEach(button => button.addEventListener('click', () => {
            if (mobile.matches) {
                document.body.classList.toggle('workspace-sidebar-open');
            } else {
                document.body.classList.toggle('workspace-sidebar-collapsed');
            }
            syncSidebar();
            if (mobile.matches && document.body.classList.contains('workspace-sidebar-open')) sidebar.querySelector('button')?.focus();
            else if (mobile.matches) toggles[0]?.focus();
        }));
        document.querySelector('[data-workspace-close]')?.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && mobile.matches && document.body.classList.contains('workspace-sidebar-open')) closeSidebar(); });
        mobile.addEventListener('change', () => { document.body.classList.remove('workspace-sidebar-open'); syncSidebar(); });
        syncSidebar();
    }
    const dialog = document.getElementById('workspace-confirm');
    if (!dialog) return;
    let pendingForm = null;
    let trigger = null;
    const approved = new WeakSet();
    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-confirm-trash]') || approved.has(form)) return;
        event.preventDefault();
        pendingForm = form;
        trigger = document.activeElement;
        dialog.querySelector('[data-confirm-copy]').textContent = 'This hides the property from public search. You can restore it later from your properties.';
        dialog.showModal();
    });
    dialog.querySelector('[data-confirm-submit]').addEventListener('click', () => {
        const form = pendingForm;
        if (!form) return;
        approved.add(form);
        dialog.close();
        form.requestSubmit();
    });
    dialog.addEventListener('close', () => {
        pendingForm = null;
        trigger?.focus();
    });
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
})();
