/* ==========================================================
   Bharat SEO - Main JS
   Progressive enhancement: site works without JS.
   ========================================================== */
(function () {
  'use strict';

  var doc = document;
  var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Preloader ---------- */
  window.addEventListener('load', function () {
    var pre = doc.getElementById('preloader');
    if (pre) {
      setTimeout(function () { pre.classList.add('hide'); }, 350);
      setTimeout(function () { if (pre.parentNode) pre.parentNode.removeChild(pre); }, 950);
    }
  });
  // Safety: hide preloader even if load never fires
  setTimeout(function () {
    var pre = doc.getElementById('preloader');
    if (pre) pre.classList.add('hide');
  }, 4000);

  /* ---------- Theme toggle (persisted) ---------- */
  var THEME_KEY = 'bharatseo-theme';
  function applyTheme(theme) {
    doc.documentElement.setAttribute('data-theme', theme);
    try { localStorage.setItem(THEME_KEY, theme); } catch (e) {}
    var toggles = doc.querySelectorAll('[data-theme-toggle]');
    toggles.forEach(function (t) {
      t.innerHTML = theme === 'light' ? '\u263E' : '\u2600';
      t.setAttribute('aria-label', theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
    });
  }
  (function initTheme() {
    var saved;
    try { saved = localStorage.getItem(THEME_KEY); } catch (e) {}
    applyTheme(saved || doc.documentElement.getAttribute('data-theme') || 'dark');
  })();
  doc.addEventListener('click', function (e) {
    var t = e.target.closest('[data-theme-toggle]');
    if (!t) return;
    var current = doc.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    applyTheme(current === 'light' ? 'dark' : 'light');
  });

  /* ---------- Mobile drawer ---------- */
  var drawer = doc.getElementById('mobileDrawer');
  var backdrop = doc.getElementById('drawerBackdrop');
  function openDrawer() { if (drawer) { drawer.classList.add('open'); backdrop && backdrop.classList.add('open'); doc.body.style.overflow = 'hidden'; } }
  function closeDrawer() { if (drawer) { drawer.classList.remove('open'); backdrop && backdrop.classList.remove('open'); doc.body.style.overflow = ''; } }
  doc.addEventListener('click', function (e) {
    if (e.target.closest('[data-drawer-open]')) { openDrawer(); }
    if (e.target.closest('[data-drawer-close]') || e.target === backdrop) { closeDrawer(); }
  });
  doc.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });

  /* ---------- Sticky header shadow ---------- */
  var header = doc.querySelector('.site-header');
  if (header) {
    var onScroll = function () {
      if (window.scrollY > 20) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
      var top = doc.getElementById('backToTop');
      if (top) { if (window.scrollY > 500) top.classList.add('show'); else top.classList.remove('show'); }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- Back to top ---------- */
  doc.addEventListener('click', function (e) {
    if (e.target.closest('#backToTop')) { window.scrollTo({ top: 0, behavior: prefersReduced ? 'auto' : 'smooth' }); }
  });

  /* ---------- Scroll reveal ---------- */
  var reveals = doc.querySelectorAll('.reveal');
  if (reveals.length && 'IntersectionObserver' in window && !prefersReduced) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { threshold: 0.12 });
    reveals.forEach(function (el) { io.observe(el); });
  } else {
    reveals.forEach(function (el) { el.classList.add('in'); });
  }

  /* ---------- Animated counters ---------- */
  var counters = doc.querySelectorAll('[data-count]');
  if (counters.length && 'IntersectionObserver' in window && !prefersReduced) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target, target = parseFloat(el.getAttribute('data-count')) || 0;
        var dec = (target % 1 !== 0) ? 1 : 0, start = 0, dur = 1400, t0 = null;
        function step(ts) {
          if (!t0) t0 = ts;
          var p = Math.min((ts - t0) / dur, 1);
          var val = (start + (target - start) * (1 - Math.pow(1 - p, 3)));
          el.textContent = dec ? val.toFixed(1) : Math.round(val).toString();
          if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
        cio.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(function (el) { cio.observe(el); });
  }

  /* ---------- FAQ accordion ---------- */
  doc.addEventListener('click', function (e) {
    var q = e.target.closest('.faq-q');
    if (!q) return;
    var item = q.closest('.faq-item');
    var ans = item.querySelector('.faq-a');
    var open = item.classList.toggle('open');
    q.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (ans) ans.style.maxHeight = open ? (ans.scrollHeight + 'px') : '0px';
  });

  /* ---------- Portfolio filter ---------- */
  doc.addEventListener('click', function (e) {
    var btn = e.target.closest('.filter-btn');
    if (!btn) return;
    var group = btn.closest('[data-filter-group]');
    if (!group) return;
    var cat = btn.getAttribute('data-filter');
    group.querySelectorAll('.filter-btn').forEach(function (b) { b.classList.remove('active'); });
    btn.classList.add('active');
    var targetSel = group.getAttribute('data-filter-target');
    var items = doc.querySelectorAll(targetSel + ' [data-cat]');
    items.forEach(function (it) {
      var show = cat === 'all' || it.getAttribute('data-cat') === cat;
      it.style.display = show ? '' : 'none';
    });
  });

  /* ---------- AJAX forms (graceful fallback to normal submit if JS off) ---------- */
  doc.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-ajax]');
    if (!form) return;
    e.preventDefault();
    var btn = form.querySelector('[type="submit"]');
    var msgBox = form.querySelector('.form-message');
    // clear prior errors
    form.querySelectorAll('.field.has-error').forEach(function (f) { f.classList.remove('has-error'); });
    form.querySelectorAll('.error-msg').forEach(function (el) { el.textContent = ''; });
    if (msgBox) { msgBox.innerHTML = ''; }
    if (btn) { btn.classList.add('is-loading'); btn.disabled = true; }

    var data = new FormData(form);
    fetch(form.getAttribute('action'), {
      method: 'POST',
      body: data,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
        if (res.success) {
          if (res.redirect) { window.location.href = res.redirect; return; }
          if (msgBox) msgBox.innerHTML = '<div class="alert alert--success">' + escapeHtml(res.message || 'Submitted successfully.') + '</div>';
          form.reset();
        } else {
          if (res.errors) {
            Object.keys(res.errors).forEach(function (name) {
              var field = form.querySelector('[name="' + name + '"]');
              if (field) {
                var wrap = field.closest('.field');
                if (wrap) {
                  wrap.classList.add('has-error');
                  var em = wrap.querySelector('.error-msg');
                  if (em) em.textContent = res.errors[name];
                }
              }
            });
          }
          if (msgBox) msgBox.innerHTML = '<div class="alert alert--error">' + escapeHtml(res.message || 'Please fix the errors and try again.') + '</div>';
        }
      })
      .catch(function () {
        if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
        if (msgBox) msgBox.innerHTML = '<div class="alert alert--error">Network error. Please try again.</div>';
      });
  });

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* ---------- Lazy-load Three.js hero only on capable devices ---------- */
  (function lazyHero() {
    var canvas = doc.getElementById('hero-three-canvas');
    if (!canvas) return;
    var lowEnd = (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4) ||
      (navigator.deviceMemory && navigator.deviceMemory <= 4) ||
      window.matchMedia('(max-width: 768px)').matches ||
      prefersReduced;
    if (lowEnd) {
      // Do not load Three.js. Hide the empty canvas (it reserves height and
      // would leave a blank gap at the top) and show the static fallback.
      canvas.style.display = 'none';
      var fb = doc.getElementById('heroFallback');
      if (fb) fb.style.display = 'grid';
      return;
    }
    var loaded = false;
    function load() {
      if (loaded) return; loaded = true;
      var base = canvas.getAttribute('data-base') || '';
      var s = doc.createElement('script');
      s.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
      s.onload = function () {
        var hs = doc.createElement('script');
        hs.src = base + 'assets/js/three-hero.js';
        hs.onerror = showFallback;
        doc.body.appendChild(hs);
      };
      s.onerror = showFallback;
      doc.body.appendChild(s);
    }
    function showFallback() {
      canvas.style.display = 'none';
      var fb = doc.getElementById('heroFallback');
      if (fb) fb.style.display = 'grid';
    }
    if ('requestIdleCallback' in window) { requestIdleCallback(load, { timeout: 2000 }); }
    else { setTimeout(load, 800); }
  })();

})();
