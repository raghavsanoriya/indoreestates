document.addEventListener('DOMContentLoaded',()=>{
    const form=document.querySelector('#add-property-form');
    if(!form)return;
    const tourMode=form.querySelector('#tour-mode');
    const updateTour=()=>form.querySelectorAll('[data-tour-mode]').forEach(section=>{
        const value=tourMode.querySelector('input:checked')?.value||'none';
        const active=section.dataset.tourMode===value;
        section.hidden=!active && !(value==='none' && section.dataset.tourMode==='embed');
        section.querySelectorAll('input,textarea').forEach(input=>input.disabled=!active);
    });
    tourMode.addEventListener('change',updateTour);updateTour();
    tourMode.querySelector('.clear-tour')?.addEventListener('click',()=>{tourMode.querySelectorAll('input[type=radio]').forEach(input=>input.checked=false);updateTour();});
    const contactMode=form.querySelector('#contact-mode'),otherContact=form.querySelector('#other-contact');
    const updateContact=()=>{const active=contactMode.querySelector('input:checked')?.value==='other';otherContact.hidden=!active;otherContact.querySelectorAll('input').forEach(input=>input.disabled=!active);};
    contactMode.addEventListener('change',updateContact);updateContact();
    const floorsEnabled=form.querySelector('#floors-enabled'),floorEditor=form.querySelector('#floor-editor'),floorList=form.querySelector('#floor-list'),addFloor=form.querySelector('#add-floor');
    let nextFloorIndex=Math.max(-1,...Array.from(floorList.children).map(node=>Number(node.dataset.floorIndex)))+1;
    const updateFloors=()=>{floorEditor.hidden=!floorsEnabled.checked;floorEditor.querySelectorAll('input,textarea,button').forEach(input=>input.disabled=!floorsEnabled.checked);addFloor.disabled=!floorsEnabled.checked||floorList.children.length>=10;};
    const insertFloor=()=>{
        if(floorList.children.length>=10)return;
        const template=form.querySelector('#floor-template');
        floorList.insertAdjacentHTML('beforeend',template.innerHTML.replaceAll('__INDEX__',String(nextFloorIndex++)));
        updateFloors();
    };
    addFloor.addEventListener('click',insertFloor);
    floorsEnabled.addEventListener('change',()=>{if(floorsEnabled.checked&&!floorList.children.length)insertFloor();updateFloors();});
    form.querySelector('#floors-disabled').addEventListener('change',updateFloors);
    floorList.addEventListener('click',event=>{const button=event.target.closest('.remove-floor');if(button){button.closest('.floor-card').remove();updateFloors();}});
    updateFloors();
    const uploads=form.querySelector('#photo-uploads'),feedback=form.querySelector('#photo-feedback'),dropzone=form.querySelector('#property-photo-dropzone');
    uploads.addEventListener('change',()=>{
        const files=Array.from(uploads.files),removed=form.querySelectorAll('[name="remove_photos[]"]:checked').length,existing=form.querySelectorAll('[name="photo_order[]"]').length;
        const invalid=files.find(file=>file.size>5*1024*1024||!['image/jpeg','image/png','image/webp'].includes(file.type));
        const message=invalid?'Choose only JPEG, PNG or WebP photos, no larger than 5 MB each.':existing-removed+files.length>20?'A property can contain up to 20 photos.':'';
        uploads.setCustomValidity(message);feedback.textContent=message||(files.length?files.length+' new photo(s) selected.':'');
        feedback.classList.toggle('field-error',Boolean(message));
    });
    ['dragenter','dragover'].forEach(type=>dropzone.addEventListener(type,event=>{event.preventDefault();dropzone.classList.add('dragover');}));
    ['dragleave','drop'].forEach(type=>dropzone.addEventListener(type,event=>{event.preventDefault();dropzone.classList.remove('dragover');}));
    dropzone.addEventListener('drop',event=>{if(event.dataTransfer?.files.length){uploads.files=event.dataTransfer.files;uploads.dispatchEvent(new Event('change',{bubbles:true}));}});
    const purpose=form.querySelector('[name="purpose"]'),rentalPeriod=form.querySelector('.rental-period');
    const updateRental=()=>{rentalPeriod.hidden=purpose.value!=='rent';};
    purpose.addEventListener('change',updateRental);updateRental();
    const map=form.querySelector('#property-location-map'),updateMap=()=>{
        const latitude=form.querySelector('[name="latitude"]').value,longitude=form.querySelector('[name="longitude"]').value;
        const query=latitude&&longitude?latitude+','+longitude:form.querySelector('[name="location"]').value+' '+form.querySelector('[name="country"]').value;
        map.src='https://maps.google.com/maps?q='+encodeURIComponent(query)+'&z=12&output=embed';
    };
    form.querySelector('#update-location-map').addEventListener('click',updateMap);
    form.querySelector('[name="location"]').addEventListener('change',updateMap);
});
