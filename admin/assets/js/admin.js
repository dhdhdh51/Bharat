/* Bharat SEO - Admin JS */
(function(){
  'use strict';
  var d=document;
  // Sidebar toggle (mobile)
  d.addEventListener('click',function(e){
    if(e.target.closest('#adMenuToggle')){d.querySelector('.ad-sidebar').classList.toggle('open');d.getElementById('adOverlay').classList.toggle('show');}
    if(e.target.closest('#adOverlay')){d.querySelector('.ad-sidebar').classList.remove('open');d.getElementById('adOverlay').classList.remove('show');}
  });
  // Confirm delete
  d.addEventListener('submit',function(e){
    var f=e.target.closest('form[data-confirm]');
    if(f && !window.confirm(f.getAttribute('data-confirm')||'Are you sure?')){e.preventDefault();}
  });
  d.addEventListener('click',function(e){
    var a=e.target.closest('a[data-confirm]');
    if(a && !window.confirm(a.getAttribute('data-confirm')||'Are you sure?')){e.preventDefault();}
  });
  // Auto-slug from title
  d.addEventListener('input',function(e){
    var src=e.target.closest('[data-slug-source]');
    if(!src)return;
    var targetSel=src.getAttribute('data-slug-source');
    var t=d.querySelector(targetSel);
    if(t && !t.dataset.touched){
      t.value=src.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
    }
  });
  d.addEventListener('input',function(e){
    if(e.target.matches('[data-slug-target]')){e.target.dataset.touched='1';}
  });
  // Image preview on file input
  d.addEventListener('change',function(e){
    if(e.target.type==='file' && e.target.dataset.preview){
      var prev=d.querySelector(e.target.dataset.preview);
      if(prev && e.target.files[0]){prev.src=URL.createObjectURL(e.target.files[0]);prev.style.display='block';}
    }
  });
})();
