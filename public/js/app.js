(() => {
    'use strict';
    document.querySelectorAll('[data-auto-submit],[data-dashboard-auto-submit]').forEach(input => {
        input.addEventListener('change', () => input.form?.requestSubmit());
    });
    const unreadTargets = Array.from(document.querySelectorAll('#unread-count,[data-unread-count]'));
    const unread = unreadTargets[0];
    let unreadBusy = false;
    async function refreshUnread() {
        if (!unread || unreadBusy || document.hidden) return;
        unreadBusy = true;
        try {
            const response = await fetch(unread.dataset.url, {headers:{Accept:'application/json'},credentials:'same-origin'});
            if (!response.ok) return;
            const data = await response.json();
            unreadTargets.forEach(target => {
                target.textContent = target.hasAttribute('data-unread-count') ? String(data.unread) : (data.unread > 0 ? String(data.unread) : '');
                if (!target.hasAttribute('data-unread-count')) {
                    target.classList.toggle('badge', data.unread > 0);
                    target.classList.toggle('bg-primary', data.unread > 0);
                }
            });
        } catch {} finally { unreadBusy = false; }
    }
    if (unread) { refreshUnread(); setInterval(refreshUnread, 30000); }
    const dialog = document.getElementById('gallery-dialog');
    document.querySelectorAll('[data-gallery-src]').forEach(button => {
        button.addEventListener('click', () => {
            if (!dialog) return;
            dialog.querySelector('img').src = button.dataset.gallerySrc;
            dialog.showModal();
        });
    });
    dialog?.querySelector('.gallery-close')?.addEventListener('click', () => dialog.close());
    dialog?.addEventListener('click', event => { if(event.target === dialog) dialog.close(); });
    const mobile = document.getElementById('mobile_btn');
    mobile?.addEventListener('click', () => mobile.setAttribute('aria-expanded', 'true'));
    document.getElementById('menu_close')?.addEventListener('click', () => mobile?.setAttribute('aria-expanded', 'false'));
    document.addEventListener('click', event => {
        if (event.target.closest('.sidebar-overlay')) mobile?.setAttribute('aria-expanded', 'false');
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || !document.documentElement.classList.contains('menu-opened')) return;
        document.documentElement.classList.remove('menu-opened');
        document.querySelector('.main-wrapper')?.classList.remove('slide-nav');
        document.querySelectorAll('.sidebar-overlay').forEach(overlay => overlay.classList.remove('opened'));
        mobile?.setAttribute('aria-expanded', 'false');
        mobile?.focus();
    });
    const photos = document.getElementById('photo-uploads');
    const preview = document.getElementById('upload-preview');
    const feedback = document.getElementById('photo-feedback');
    let previewUrls = [];
    function showPhotos() {
        if (!photos || !preview) return;
        previewUrls.forEach(url => URL.revokeObjectURL(url));
        previewUrls = [];
        preview.replaceChildren();
        const files = Array.from(photos.files || []);
        const remaining = document.querySelectorAll('#existing-property-photos .photo-tile').length -
            document.querySelectorAll('#existing-property-photos input[name="remove_photos[]"]:checked').length;
        const errors = [];
        if (remaining + files.length > 20) errors.push('A property may contain up to 20 photos.');
        files.forEach(file => {
            if (!['image/jpeg','image/png','image/webp'].includes(file.type)) errors.push(file.name + ': choose JPEG, PNG or WebP.');
            if (file.size > 5 * 1024 * 1024) errors.push(file.name + ': maximum size is 5 MB.');
            if (file.type.startsWith('image/')) {
                const column = document.createElement('div');
                column.className = 'col-sm-6 mb-3';
                const image = document.createElement('img');
                const url = URL.createObjectURL(file);
                previewUrls.push(url);
                image.src = url; image.alt = file.name; image.className = 'img-fluid rounded';
                column.append(image); preview.append(column);
            }
        });
        photos.setCustomValidity(errors.join(' '));
        if (feedback) { feedback.textContent = errors.join(' ') || files.length + ' new photos selected.'; feedback.className = errors.length ? 'text-danger' : 'text-muted'; }
    }
    photos?.addEventListener('change',showPhotos);
    document.querySelectorAll('input[name="remove_photos[]"]').forEach(input => input.addEventListener('change',showPhotos));
    const zone = document.getElementById('property-photo-dropzone');
    zone?.addEventListener('dragover', event => event.preventDefault());
    zone?.addEventListener('drop', event => {
        event.preventDefault();
        if (photos && event.dataTransfer?.files) { photos.files = event.dataTransfer.files; showPhotos(); }
    });
    document.getElementById('existing-property-photos')?.addEventListener('click', event => {
        const button = event.target.closest('[data-move]');
        if (!button) return;
        const tile = button.closest('.photo-tile');
        if (button.dataset.move === 'up' && tile.previousElementSibling) tile.parentNode.insertBefore(tile, tile.previousElementSibling);
        if (button.dataset.move === 'down' && tile.nextElementSibling) tile.parentNode.insertBefore(tile.nextElementSibling, tile);
    });
    const floorList = document.getElementById('floor-list');
    let floorIndex = floorList ? floorList.querySelectorAll('.floor-block').length : 0;
    document.getElementById('add-floor')?.addEventListener('click', () => {
        if (!floorList || floorList.querySelectorAll('.floor-block').length >= 10) return;
        const template = document.getElementById('floor-template');
        const markup = template.innerHTML.replaceAll('__INDEX__',String(floorIndex++)).replaceAll('__UUID__',crypto.randomUUID());
        floorList.insertAdjacentHTML('beforeend',markup);
    });
    floorList?.addEventListener('click', event => event.target.closest('[data-remove-floor]')?.closest('.floor-block')?.remove());
})();
