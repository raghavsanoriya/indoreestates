// Website Content (CMS) editor: formatting buttons, FAQ rows, image preview, reset confirmation.
(() => {
    const wrap = (textarea, before, after, placeholder) => {
        const {selectionStart: start, selectionEnd: end, value} = textarea;
        const selected = value.slice(start, end) || placeholder;
        textarea.setRangeText(before + selected + after, start, end, 'end');
        textarea.focus();
        textarea.setSelectionRange(start + before.length, start + before.length + selected.length);
    };
    const prefixLines = (textarea, prefix) => {
        const {selectionStart: start, selectionEnd: end, value} = textarea;
        const lineStart = value.lastIndexOf('\n', start - 1) + 1;
        const block = value.slice(lineStart, end) || 'List item';
        const lines = block.split('\n').map((line, index) => (typeof prefix === 'function' ? prefix(index) : prefix) + line);
        textarea.setRangeText(lines.join('\n'), lineStart, end, 'end');
        textarea.focus();
    };
    const actions = {
        bold: (t) => wrap(t, '**', '**', 'bold text'),
        italic: (t) => wrap(t, '*', '*', 'italic text'),
        h2: (t) => prefixLines(t, '## '),
        h3: (t) => prefixLines(t, '### '),
        ul: (t) => prefixLines(t, '- '),
        ol: (t) => prefixLines(t, (index) => `${index + 1}. `),
        link: (t) => {
            const url = window.prompt('Link address (starting with https://)', 'https://');
            if (url && /^(https?:\/\/|mailto:|tel:|\/)/i.test(url.trim())) {
                wrap(t, '[', `](${url.trim()})`, 'link text');
            }
        },
    };

    document.addEventListener('click', (event) => {
        const formatButton = event.target.closest('[data-md]');
        if (formatButton) {
            const textarea = document.getElementById(formatButton.closest('[data-md-target]').dataset.mdTarget);
            actions[formatButton.dataset.md]?.(textarea);
            return;
        }
        const addButton = event.target.closest('[data-faq-add]');
        if (addButton) {
            const faq = addButton.closest('[data-cms-faq]');
            const list = faq.querySelector('.cms-faq-list');
            if (list.children.length >= Number(faq.dataset.max || 20)) {
                window.alert(`You can add up to ${faq.dataset.max} questions.`);
                return;
            }
            const index = `n${Date.now()}`;
            list.insertAdjacentHTML('beforeend', faq.querySelector('[data-faq-template]').innerHTML.replaceAll('__INDEX__', index));
            list.lastElementChild.querySelector('input')?.focus();
            return;
        }
        const removeButton = event.target.closest('[data-faq-remove]');
        if (removeButton) {
            removeButton.closest('.cms-faq-item').remove();
            return;
        }
        const confirmButton = event.target.closest('[data-confirm]');
        if (confirmButton && !window.confirm(confirmButton.dataset.confirm)) {
            event.preventDefault();
        }
    });

    document.addEventListener('change', (event) => {
        const input = event.target;
        if (input.type !== 'file' || !input.files?.[0]) {
            return;
        }
        const preview = document.querySelector(`[data-preview-for="${input.id}"]`);
        if (preview && input.files[0].type.startsWith('image/')) {
            preview.src = URL.createObjectURL(input.files[0]);
        }
    });
})();
