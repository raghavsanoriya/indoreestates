(() => {
    'use strict';
    document.querySelectorAll('[data-account-dropdown]').forEach(dropdown => {
        const trigger = dropdown.querySelector('.o3-account-trigger');
        const panel = dropdown.querySelector('.o3-account-panel');
        const hoverPointer = window.matchMedia('(min-width: 992px) and (hover: hover) and (pointer: fine)');
        let closeTimer;
        const closeAccount = () => {
            if (trigger.getAttribute('aria-expanded') === 'true') trigger.click();
        };
        dropdown.addEventListener('pointerenter', () => {
            clearTimeout(closeTimer);
            if (hoverPointer.matches && trigger.getAttribute('aria-expanded') !== 'true') trigger.click();
        });
        dropdown.addEventListener('pointerleave', () => {
            if (hoverPointer.matches) closeTimer = setTimeout(() => {
                if (!panel.contains(document.activeElement)) closeAccount();
            }, 180);
        });
        dropdown.addEventListener('focusout', event => {
            if (!dropdown.contains(event.relatedTarget)) closeAccount();
        });
    });
    const mobile = document.getElementById('mobile_btn');
    const navigation = document.getElementById('site-navigation');
    const syncNavigation = () => { if(navigation) navigation.inert = window.innerWidth <= 991 && !document.body.classList.contains('o3-menu-open'); };
    const close = () => { document.body.classList.remove('o3-menu-open'); mobile?.setAttribute('aria-expanded','false'); syncNavigation(); };
    syncNavigation();
    window.addEventListener('resize', syncNavigation);
    mobile?.addEventListener('click', () => { document.body.classList.add('o3-menu-open'); mobile.setAttribute('aria-expanded','true'); syncNavigation(); document.getElementById('menu_close')?.focus(); });
    document.getElementById('menu_close')?.addEventListener('click', close);
    document.querySelector('[data-close-navigation]')?.addEventListener('click', close);
    document.addEventListener('keydown', event => { if(event.key === 'Escape' && document.body.classList.contains('o3-menu-open')) { close(); mobile?.focus(); } });
    document.querySelectorAll('.toggle-passwords').forEach(button => button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        const show = input.type === 'password'; input.type = show ? 'text' : 'password'; button.setAttribute('aria-label',show ? 'Hide password' : 'Show password');
        button.querySelector('.material-icons-outlined').textContent = show ? 'visibility_off' : 'visibility';
    }));
    document.querySelectorAll('.service-slider-card').forEach(card => {
        const slides = Array.from(card.querySelectorAll('.service-slider .swiper-slide'));
        const thumbs = Array.from(card.querySelectorAll('[data-homez-photo]'));
        if(!slides.length) return;
        let current = 0;
        const show = index => {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide,i) => { slide.hidden = i !== current; });
            thumbs.forEach((button,i) => button.setAttribute('aria-pressed',String(i === current)));
        };
        thumbs.forEach((button,index) => button.addEventListener('click',() => show(index)));
        if(slides.length > 1) {
            const controls = document.createElement('div'); controls.className = 'o3-gallery-controls';
            for(const [label,step] of [['Previous photo',-1],['Next photo',1]]) {
                const button = document.createElement('button'); button.type='button'; button.className='btn btn-sm btn-light'; button.textContent=label; button.addEventListener('click',()=>show(current+step)); controls.append(button);
            }
            card.append(controls);
        }
        show(0);
    });
})();
