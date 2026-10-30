(() => {
    'use strict';
    const phone = window.matchMedia('(max-width: 767px)');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const rails = new Set();
    document.querySelectorAll('.o3-property-card').forEach(card => {
        const slide = card.parentElement;
        const rail = slide.parentElement;
        if (rail.classList.contains('row') && !rail.closest('.dashboard-page')) {
            slide.classList.add('mobile-property-slide');
            rails.add(rail);
        }
    });
    rails.forEach((rail, railIndex) => {
        const slides = Array.from(rail.children).filter(child => child.classList.contains('mobile-property-slide'));
        if (slides.length < 2) return;
        rail.classList.add('mobile-property-rail');
        rail.id ||= `mobile-properties-${railIndex}`;
        const controls = document.createElement('div');
        controls.className = 'mobile-rail-controls';
        controls.setAttribute('role', 'group');
        controls.setAttribute('aria-label', 'Property carousel controls');
        const previous = document.createElement('button');
        const next = document.createElement('button');
        [previous, next].forEach((button, index) => {
            button.type = 'button';
            button.className = 'mobile-rail-arrow';
            button.textContent = index ? '→' : '←';
            button.setAttribute('aria-label', index ? 'Next property' : 'Previous property');
            button.setAttribute('aria-controls', rail.id);
        });
        const dots = document.createElement('div');
        dots.className = 'mobile-rail-dots';
        let activeIndex = 0;
        const goTo = index => {
            const slide = slides[index];
            if (!slide) return;
            rail.scrollTo({left: slide.offsetLeft - rail.offsetLeft - (rail.clientWidth - slide.clientWidth) / 2,
                behavior: reducedMotion.matches ? 'instant' : 'smooth'});
        };
        const dotButtons = slides.map((slide, index) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'mobile-rail-dot';
            dot.setAttribute('aria-label', `Show property ${index + 1} of ${slides.length}`);
            dot.setAttribute('aria-controls', rail.id);
            dot.addEventListener('click', () => goTo(index));
            dots.append(dot);
            return dot;
        });
        const update = () => {
            if (!phone.matches || !rail.clientWidth) return;
            const centre = rail.getBoundingClientRect().left + rail.clientWidth / 2;
            let distance = Infinity;
            slides.forEach((slide, index) => {
                const box = slide.getBoundingClientRect();
                const delta = Math.abs(box.left + box.width / 2 - centre);
                if (delta < distance) { distance = delta; activeIndex = index; }
            });
            slides.forEach((slide, index) => slide.classList.toggle('is-centred', index === activeIndex));
            dotButtons.forEach((dot, index) => dot.setAttribute('aria-current', String(index === activeIndex)));
            previous.disabled = activeIndex === 0;
            next.disabled = activeIndex === slides.length - 1;
        };
        previous.addEventListener('click', () => goTo(activeIndex - 1));
        next.addEventListener('click', () => goTo(activeIndex + 1));
        rail.addEventListener('keydown', event => {
            if (!phone.matches || !['ArrowRight', 'ArrowLeft'].includes(event.key) || event.target !== rail) return;
            event.preventDefault();
            goTo(Math.max(0, Math.min(slides.length - 1, activeIndex + (event.key === 'ArrowRight' ? 1 : -1))));
        });
        const sync = () => {
            if (phone.matches) {
                rail.tabIndex = 0;
                rail.setAttribute('role', 'region');
                rail.setAttribute('aria-roledescription', 'carousel');
                rail.setAttribute('aria-label', 'Browse properties. Swipe for more.');
            } else {
                ['tabindex', 'role', 'aria-roledescription', 'aria-label'].forEach(attribute => rail.removeAttribute(attribute));
            }
            update();
        };
        let queued = false;
        rail.addEventListener('scroll', () => {
            if (!queued) { queued = true; requestAnimationFrame(() => { update(); queued = false; }); }
        }, {passive: true});
        controls.append(previous, dots, next);
        rail.after(controls);
        new ResizeObserver(update).observe(rail);
        phone.addEventListener('change', sync);
        sync();
    });
    const filters = document.querySelector('.filter-sidebar');
    if (filters) {
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'mobile-filter-toggle';
        toggle.textContent = 'Search & filters  +';
        filters.id ||= 'mobile-property-filters';
        toggle.setAttribute('aria-controls', filters.id);
        toggle.setAttribute('aria-haspopup', 'dialog');
        toggle.setAttribute('aria-expanded', 'false');
        const anchor = document.createComment('Desktop filter sidebar');
        const sheet = document.createElement('dialog');
        sheet.className = 'mobile-filter-dialog';
        sheet.id = 'mobile-filter-sheet';
        toggle.setAttribute('aria-controls', sheet.id);
        const heading = filters.querySelector('.filter-head h5');
        heading.id ||= 'mobile-filter-title';
        sheet.setAttribute('aria-labelledby', heading.id);
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'mobile-filter-close';
        closeButton.setAttribute('aria-label', 'Close filters');
        closeButton.textContent = '×';
        heading.parentElement.append(closeButton);
        const setOpenState = open => {
            toggle.setAttribute('aria-expanded', String(open));
            document.body.classList.toggle('mobile-filters-open', open);
        };
        toggle.addEventListener('click', () => {
            if (!phone.matches) return;
            sheet.showModal();
            setOpenState(true);
            closeButton.focus({preventScroll: true});
        });
        closeButton.addEventListener('click', () => sheet.close());
        sheet.addEventListener('close', () => {
            setOpenState(false);
            if (phone.matches) toggle.focus({preventScroll: true});
        });
        sheet.addEventListener('click', event => {
            if (event.target !== sheet) return;
            const box = sheet.getBoundingClientRect();
            if (event.clientY < box.top || event.clientY > box.bottom || event.clientX < box.left || event.clientX > box.right) sheet.close();
        });
        const syncFilters = () => {
            if (phone.matches) {
                sheet.append(filters);
            } else {
                if (sheet.open) sheet.close();
                setOpenState(false);
                anchor.after(filters);
            }
        };
        filters.before(toggle, anchor);
        document.body.append(sheet);
        phone.addEventListener('change', syncFilters);
        syncFilters();
    }
    const propertyForm = document.getElementById('add-property-form');
    if (propertyForm) {
        const sections = Array.from(propertyForm.querySelectorAll('.property-form-section'));
        const setSectionOpen = (section, open) => {
            section.classList.toggle('is-open', open);
            section.querySelector('.mobile-section-toggle')?.setAttribute('aria-expanded', String(open));
        };
        const openSection = section => {
            if (!phone.matches) return;
            sections.forEach(item => setSectionOpen(item, item === section));
        };
        sections.forEach((section, index) => {
            const row = section.querySelector(':scope > .row');
            const heading = row?.querySelector('h6');
            if (!row || !heading) return;
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'mobile-section-toggle';
            button.textContent = `${index + 1}. ${heading.textContent}`;
            button.setAttribute('aria-label', button.textContent);
            row.id ||= `${section.id}-fields`;
            button.setAttribute('aria-controls', row.id);
            button.addEventListener('click', () => {
                if (section.classList.contains('is-open')) setSectionOpen(section, false);
                else openSection(section);
            });
            section.prepend(button);
            section.classList.add('mobile-property-section');
            setSectionOpen(section, index === 0 || Boolean(section.querySelector('.text-danger.mt-1')));
        });
        document.querySelectorAll('.add-tab-list a').forEach(link => link.addEventListener('click', () => {
            const section = sections.find(item => `#${item.id}` === link.hash);
            if (section) openSection(section);
        }));
        propertyForm.addEventListener('invalid', event => {
            if (!phone.matches) return;
            const section = event.target.closest('.property-form-section');
            if (section) setSectionOpen(section, true);
        }, true);
        const syncForm = () => {
            if (phone.matches) {
                const hashSection = sections.find(section => `#${section.id}` === window.location.hash);
                if (hashSection) openSection(hashSection);
            }
        };
        phone.addEventListener('change', syncForm);
        syncForm();
    }
    const navigation = document.getElementById('site-navigation');
    const trigger = document.getElementById('mobile_btn');
    navigation?.addEventListener('keydown', event => {
        if (event.key !== 'Tab' || !document.body.classList.contains('o3-menu-open')) return;
        const focusable = Array.from(navigation.querySelectorAll('a[href],button:not([disabled])')).filter(element => element.getClientRects().length);
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    document.getElementById('menu_close')?.addEventListener('click', () => trigger?.focus());
    document.querySelector('[data-close-navigation]')?.addEventListener('click', () => trigger?.focus());
})();
