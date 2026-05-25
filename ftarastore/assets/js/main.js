/* ftarastore main.js — fixed + modal system */
'use strict';

// ── Banner Slider ──────────────────────────────────────────────
(function(){
  const slides = document.querySelectorAll('.banner-slide');
  const dots   = document.querySelectorAll('.banner-dot');
  if(!slides.length) return;
  let cur=0, timer;

  const go = i => {
    slides[cur].classList.remove('active');
    dots[cur]?.classList.remove('on');
    cur = (i+slides.length)%slides.length;
    slides[cur].classList.add('active');
    dots[cur]?.classList.add('on');
  };
  const next=()=>go(cur+1), prev=()=>go(cur-1);
  const auto=()=>{clearInterval(timer);timer=setInterval(next,4800)};

  document.querySelector('.banner-arrow--next')?.addEventListener('click',()=>{next();auto()});
  document.querySelector('.banner-arrow--prev')?.addEventListener('click',()=>{prev();auto()});
  dots.forEach((d,i)=>d.addEventListener('click',()=>{go(i);auto()}));

  const slider = document.querySelector('.banner-slider');
  if(slider){
    let tx=0;
    slider.addEventListener('touchstart',e=>{ tx=e.touches[0].clientX; },{passive:true});
    slider.addEventListener('touchend',e=>{
      const diff = tx - e.changedTouches[0].clientX;
      if(Math.abs(diff)>40){ diff>0?next():prev(); auto(); }
    },{passive:true});
  }

  auto();
})();

// ── Product Select ─────────────────────────────────────────────
(function(){
  const cards = document.querySelectorAll('.p-card');
  if(!cards.length) return;
  const hidId        = document.getElementById('sel_product_id');
  const previewName  = document.getElementById('sp-name');
  const previewPrice = document.getElementById('sp-price');
  const preview      = document.getElementById('sel-preview');
  const btnOrder     = document.getElementById('btn-order');

  cards.forEach(c=>{
    c.addEventListener('click',()=>{
      cards.forEach(x=>x.classList.remove('sel'));
      c.classList.add('sel');
      if(hidId)        hidId.value             = c.dataset.id;
      if(previewName)  previewName.textContent  = c.dataset.name;
      if(previewPrice) previewPrice.textContent = 'Rp '+parseInt(c.dataset.price).toLocaleString('id-ID');
      if(preview)      preview.classList.add('active');
      if(btnOrder){
        btnOrder.disabled    = false;
        btnOrder.textContent = '🛒 Beli — Rp '+parseInt(c.dataset.price).toLocaleString('id-ID');
      }
    });
  });
})();

// ── Category Filter ────────────────────────────────────────────
(function(){
  var tabs = document.querySelectorAll('.cat-tab');
  if(!tabs.length) return;

  tabs.forEach(function(tab){
    tab.addEventListener('click', function(){
      tabs.forEach(function(t){ t.classList.remove('on'); });
      tab.classList.add('on');

      var cat = tab.getAttribute('data-cat');
      var cards = document.querySelectorAll('.game-card');
      var visibleCount = 0;
      cards.forEach(function(card){
        var cardCat = card.getAttribute('data-cat') || '0';
        var show = (cat === '0' || cardCat === cat);
        card.style.display = show ? '' : 'none';
        if(show) visibleCount++;
      });

      var empty = document.getElementById('cat-empty');
      if(empty) empty.style.display = visibleCount === 0 ? 'block' : 'none';

      var gw = document.getElementById('gw');
      if(gw && cat !== '0'){
        gw.classList.remove('clamped');
        var lmb = document.querySelector('.load-more-box');
        if(lmb) lmb.style.display = 'none';
      }
    });
  });
})();

// ── Avatar Dropdown ────────────────────────────────────────────
(function(){
  var btn = document.getElementById('nav-avatar-btn');
  var dd  = document.getElementById('nav-avatar-dropdown');
  if(!btn || !dd) return;

  btn.addEventListener('click', function(e){
    e.stopPropagation();
    var isOpen = dd.classList.toggle('open');
    btn.classList.toggle('open', isOpen);
  });
  document.addEventListener('click', function(){ dd.classList.remove('open'); btn.classList.remove('open'); });
  dd.addEventListener('click', function(e){ e.stopPropagation(); });
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){ dd.classList.remove('open'); btn.classList.remove('open'); }
  });
})();

// ── MODAL SYSTEM ───────────────────────────────────────────────
// Handles: data-modal-open, data-modal-close, backdrop click, ESC
(function(){
  function openModal(id){
    var m = document.getElementById(id);
    if(m){ m.classList.add('show'); document.body.style.overflow='hidden'; }
  }
  function closeModal(id){
    var m = document.getElementById(id);
    if(m) m.classList.remove('show');
    if(!document.querySelector('.modal.show')) document.body.style.overflow='';
  }
  function closeAll(){
    document.querySelectorAll('.modal.show').forEach(function(m){ m.classList.remove('show'); });
    document.body.style.overflow='';
  }

  document.addEventListener('click', function(e){
    // Buka modal
    var ob = e.target.closest('[data-modal-open]');
    if(ob){ openModal(ob.getAttribute('data-modal-open')); return; }

    // Tutup modal via tombol close
    var cb = e.target.closest('[data-modal-close]');
    if(cb){ closeModal(cb.getAttribute('data-modal-close')); return; }

    // Tutup via klik backdrop
    if(e.target.classList.contains('modal')){
      e.target.classList.remove('show');
      if(!document.querySelector('.modal.show')) document.body.style.overflow='';
    }
  });

  // ESC tutup semua
  document.addEventListener('keydown', function(e){
    if(e.key==='Escape') closeAll();
  });

  // Global expose
  window.openModal  = openModal;
  window.closeModal = closeModal;
})();

// ── FLASH AUTO DISMISS ─────────────────────────────────────────
(function(){
  var flash = document.getElementById('flash-msg');
  if(!flash) return;
  setTimeout(function(){
    flash.style.transition='opacity .4s,transform .4s';
    flash.style.opacity='0';
    flash.style.transform='translateX(20px)';
    setTimeout(function(){ flash.remove(); }, 400);
  }, 4000);
})();

// ── NOTIFICATION SYSTEM ────────────────────────────────────────
(function(){
  var notifOpen = false;

  // Load notifikasi dari API
  function loadNotifs() {
    fetch('/api/notif.php?action=get')
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (!d.ok) return;
        renderNotifs(d.items, d.unread);
      })
      .catch(function(){});
  }

  // Render items ke dropdown
  function renderNotifs(items, unread) {
    var list  = document.getElementById('notif-list');
    var empty = document.getElementById('notif-empty');
    var badge = document.getElementById('notif-badge');

    // Update badge
    if (badge) {
      if (unread > 0) {
        badge.textContent = unread > 9 ? '9+' : unread;
        badge.style.display = 'flex';
      } else {
        badge.style.display = 'none';
      }
    }

    if (!list) return;

    if (!items || items.length === 0) {
      if (empty) empty.style.display = 'block';
      return;
    }
    if (empty) empty.style.display = 'none';

    // Build HTML
    var html = '';
    items.forEach(function(n) {
      html += '<a href="' + (n.url || '#') + '" onclick="readNotif(' + n.id + ')" style="' +
        'display:flex;gap:10px;padding:11px 16px;border-bottom:1px solid rgba(255,255,255,.04);' +
        'text-decoration:none;transition:background .12s;' +
        (n.is_read ? '' : 'background:rgba(227,24,55,.04);') +
        '" onmouseover="this.style.background=\'rgba(255,255,255,.03)\'" onmouseout="this.style.background=\'' + (n.is_read ? '' : 'rgba(227,24,55,.04)') + '\'">' +
        '<div style="font-size:1.2rem;flex-shrink:0;margin-top:1px;">' + n.icon + '</div>' +
        '<div style="flex:1;min-width:0;">' +
          '<div style="font-size:.8rem;font-weight:' + (n.is_read ? '500' : '700') + ';color:#e8eaf0;line-height:1.3;">' + escHtml(n.title) + '</div>' +
          (n.message ? '<div style="font-size:.71rem;color:#5a6478;margin-top:2px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">' + escHtml(n.message) + '</div>' : '') +
          '<div style="font-size:.65rem;color:#374155;margin-top:3px;">' + n.time_ago + '</div>' +
        '</div>' +
        (n.is_read ? '' : '<div style="width:7px;height:7px;border-radius:50%;background:#e31837;flex-shrink:0;margin-top:5px;"></div>') +
        '</a>';
    });
    list.innerHTML = html;
  }

  function escHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
  }

  // Toggle dropdown
  window.toggleNotif = function(e) {
    e.stopPropagation();
    var dd = document.getElementById('notif-dropdown');
    if (!dd) return;
    notifOpen = !notifOpen;
    dd.style.display = notifOpen ? 'flex' : 'none';
    if (notifOpen) loadNotifs();
  };

  // Read one
  window.readNotif = function(id) {
    fetch('/api/notif.php?action=read&id=' + id, { method: 'POST' });
  };

  // Mark all read
  window.markAllRead = function() {
    fetch('/api/notif.php?action=read_all', { method: 'POST' })
      .then(function(){ loadNotifs(); })
      .catch(function(){});
  };

  // Close on outside click
  document.addEventListener('click', function() {
    var dd = document.getElementById('notif-dropdown');
    if (dd && notifOpen) { dd.style.display = 'none'; notifOpen = false; }
  });

  // Auto-check unread count every 60s
  function checkCount() {
    fetch('/api/notif.php?action=count')
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (!d.ok) return;
        var badge = document.getElementById('notif-badge');
        if (!badge) return;
        if (d.unread > 0) {
          badge.textContent = d.unread > 9 ? '9+' : d.unread;
          badge.style.display = 'flex';
        } else {
          badge.style.display = 'none';
        }
      })
      .catch(function(){});
  }

  // Only run if user is logged in (bell exists)
  if (document.getElementById('notif-btn')) {
    checkCount();
    setInterval(checkCount, 60000);
  }
})();
