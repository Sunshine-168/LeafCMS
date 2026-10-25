(function () {
  document.documentElement.classList.add('js');
  var top = document.querySelector('.top');
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.querySelector('.nav');
  if (toggle && nav) {
    function setOpen(open, returnFocus) {
      nav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? toggle.dataset.closeLabel : toggle.dataset.openLabel);
      if (returnFocus) toggle.focus();
    }
    toggle.addEventListener('click', function () {
      setOpen(!nav.classList.contains('is-open'));
    });
    nav.addEventListener('click', function (event) {
      if (event.target.closest('a')) setOpen(false);
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && nav.classList.contains('is-open')) setOpen(false, true);
    });
    document.addEventListener('click', function (event) {
      if (!nav.contains(event.target) && !toggle.contains(event.target)) setOpen(false);
    });
    window.matchMedia('(min-width: 901px)').addEventListener('change', function () { setOpen(false); });
  }
  function onScroll() {
    if (!top) return;
    top.classList.toggle('is-stuck', window.scrollY > 4);
  }
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var page = btn.closest('[data-copy-label]');
      var copyLabel = (page && page.getAttribute('data-copy-label')) || '复制';
      var copiedLabel = (page && page.getAttribute('data-copied-label')) || '已复制';
      var code = btn.parentElement && btn.parentElement.querySelector('code');
      if (!code) return;
      if (!navigator.clipboard) { code.focus(); return; }
      navigator.clipboard.writeText(code.innerText).then(function () {
        btn.textContent = copiedLabel;
        setTimeout(function () { btn.textContent = copyLabel; }, 1500);
      }).catch(function () { code.focus(); });
    });
  });
  var catalogGroups = {enterprise:'business', blog:'publishing', manga:'publishing', sports:'publishing', download:'resources', nav:'resources', source:'resources', mobile:'utility', default:'utility'};
  var filterbar = document.querySelector('.theme-filterbar');
  if (filterbar) filterbar.hidden = false;
  document.querySelectorAll('[data-theme-filter]').forEach(function (button) {
    button.addEventListener('click', function () {
      var filter = button.dataset.themeFilter;
      document.querySelectorAll('[data-theme-filter]').forEach(function (item) {
        item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
      });
      var count = 0;
      document.querySelectorAll('.themes .theme').forEach(function (item) {
        item.hidden = filter !== 'all' && catalogGroups[item.id] !== filter;
        if (!item.hidden) count++;
      });
      var status = document.getElementById('theme-results');
      if (status) status.textContent = status.dataset.label.replace(':count', count);
    });
  });
  function revealThemeAnchor() {
    var id = window.location.hash.slice(1);
    var target = id && document.getElementById(id);
    if (target && target.classList.contains('theme') && target.hidden) {
      document.querySelector('[data-theme-filter="all"]')?.click();
      target.scrollIntoView();
    }
  }
  window.addEventListener('hashchange', revealThemeAnchor);
  revealThemeAnchor();

  var viewer = document.getElementById('image-viewer');
  if (viewer && typeof viewer.showModal === 'function') {
    var viewerImage = document.getElementById('image-viewer-image');
    var viewerTitle = document.getElementById('image-viewer-title');
    var viewerOriginal = document.getElementById('image-viewer-original');
    var viewerContent = viewer.querySelector('.image-viewer-content');
    var viewerZoom = document.getElementById('image-viewer-zoom');
    var viewerTrigger;
    function setZoom(original) {
      viewerContent.classList.toggle('is-original', original);
      viewerZoom.setAttribute('aria-pressed', String(original));
      viewerZoom.textContent = original ? viewerZoom.dataset.fitLabel : viewerZoom.dataset.expandLabel;
    }
    document.querySelectorAll('a[data-image-viewer]').forEach(function (link) {
      link.addEventListener('click', function (event) {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        event.preventDefault();
        viewerTrigger = link;
        var title = link.dataset.imageTitle || link.querySelector('img')?.alt || '';
        viewerTitle.textContent = title;
        viewerImage.alt = title;
        viewerImage.src = link.href;
        viewerOriginal.href = link.href;
        setZoom(false);
        viewer.showModal();
        viewerContent.scrollTop = 0;
        viewerContent.scrollLeft = 0;
      });
    });
    viewerZoom.addEventListener('click', function () {
      setZoom(!viewerContent.classList.contains('is-original'));
    });
    viewer.addEventListener('click', function (event) {
      if (event.target === viewer) viewer.close();
    });
    viewer.addEventListener('close', function () {
      viewerImage.removeAttribute('src');
      viewerOriginal.removeAttribute('href');
      if (viewerTrigger && viewerTrigger.isConnected) viewerTrigger.focus();
    });
  }
})();
