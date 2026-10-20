(() => {
    'use strict';
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
