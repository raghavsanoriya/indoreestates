document.addEventListener('DOMContentLoaded',()=>{
    const toggle=document.querySelector('.reference-menu-toggle'),backdrop=document.querySelector('.reference-backdrop');
    if(!toggle||!backdrop)return;
    const desktop=window.matchMedia('(min-width:1025px)');
    const setOpen=open=>{document.body.classList.toggle('property-sidebar-open',open);toggle.setAttribute('aria-expanded',String(desktop.matches?!open:open));backdrop.hidden=!open||desktop.matches;};
    setOpen(false);
    desktop.addEventListener('change',()=>setOpen(false));
    toggle.addEventListener('click',()=>setOpen(!document.body.classList.contains('property-sidebar-open')));
    document.querySelectorAll('[data-show-dashboard]').forEach(button=>button.addEventListener('click',()=>setOpen(true)));
    backdrop.addEventListener('click',()=>setOpen(false));
    document.addEventListener('keydown',event=>{if(event.key==='Escape'&&document.body.classList.contains('property-sidebar-open')){setOpen(false);toggle.focus();}});
    document.querySelectorAll('[data-dashboard-auto-submit]').forEach(input=>input.addEventListener('change',()=>input.form.requestSubmit()));
});
