document.addEventListener('DOMContentLoaded',()=>{
    document.querySelectorAll('[data-home-purpose]').forEach(button=>button.addEventListener('click',()=>{
        const purpose=document.querySelector('#home-purpose');
        if(purpose)purpose.value=button.dataset.homePurpose;
        document.querySelectorAll('[data-home-purpose]').forEach(tab=>{const active=tab===button;tab.classList.toggle('active',active);tab.setAttribute('aria-selected',String(active));});
    }));
    document.querySelector('[data-home-advanced]')?.addEventListener('click',event=>{
        const button=event.currentTarget,panel=document.querySelector('#home-advanced');
        panel.hidden=!panel.hidden;button.setAttribute('aria-expanded',String(!panel.hidden));
    });
    const mobileToggle=document.querySelector('.mobile-nav-toggler');
    const closeMobileMenu=()=>{document.body.classList.remove('mobile-menu-visible');mobileToggle?.setAttribute('aria-expanded','false');};
    mobileToggle?.addEventListener('click',()=>{document.body.classList.add('mobile-menu-visible');mobileToggle.setAttribute('aria-expanded','true');});
    document.querySelectorAll('.mobile-menu .menu-backdrop,.main-header .close-btn').forEach(button=>button.addEventListener('click',closeMobileMenu));
    document.addEventListener('keydown',event=>{if(event.key==='Escape'&&document.body.classList.contains('mobile-menu-visible')){closeMobileMenu();mobileToggle?.focus();}});
    document.querySelectorAll('.mobile-menu .dropdown2-btn').forEach(button=>button.addEventListener('click',()=>{
        const menu=button.parentElement.querySelector('ul');if(menu){const open=button.getAttribute('aria-expanded')!=='true';button.setAttribute('aria-expanded',String(open));menu.hidden=!open;menu.style.display=open?'block':'none';menu.style.height=open?'auto':'0';}
    }));
    const toggle=document.querySelector('.menu-toggle'),nav=document.querySelector('#site-nav');
    toggle?.addEventListener('click',()=>{const open=nav.classList.toggle('open');toggle.setAttribute('aria-expanded',String(open));});
    const dialog=document.querySelector('#gallery-dialog');
    document.querySelectorAll('[data-gallery-src]').forEach(button=>button.addEventListener('click',()=>{dialog.querySelector('img').src=button.dataset.gallerySrc;dialog.showModal();}));
    dialog?.querySelector('.gallery-close')?.addEventListener('click',()=>dialog.close());
    dialog?.addEventListener('click',e=>{if(e.target===dialog)dialog.close();});
    document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',e=>{if(!confirm(form.dataset.confirm))e.preventDefault();}));
    const editor=document.querySelector('.photo-editor');
    editor?.addEventListener('click',e=>{
        const button=e.target.closest('[data-move]');
        if(!button)return;
        const tile=button.closest('.photo-tile');
        if(button.dataset.move==='up'&&tile.previousElementSibling)editor.insertBefore(tile,tile.previousElementSibling);
        if(button.dataset.move==='down'&&tile.nextElementSibling)editor.insertBefore(tile.nextElementSibling,tile);
    });
    const uploads=document.querySelector('#photo-uploads'),preview=document.querySelector('#upload-preview');
    uploads?.addEventListener('change',()=>{
        preview.replaceChildren();
        Array.from(uploads.files).forEach(file=>{const image=document.createElement('img');image.src=URL.createObjectURL(file);image.alt=file.name;image.onload=()=>URL.revokeObjectURL(image.src);const tile=document.createElement('div');tile.className='photo-tile';tile.append(image,document.createTextNode(file.name));preview.append(tile);});
    });
    const unread=document.querySelector('#unread-count');
    if(unread){
        const refresh=async()=>{if(document.hidden)return;try{const response=await fetch(unread.dataset.url,{headers:{Accept:'application/json'}});if(!response.ok)return;const data=await response.json();unread.textContent=data.unread?String(data.unread):'';}catch{}};
        refresh();setInterval(refresh,30000);
    }
});
