/**
 * Titan Labs — front-end behaviour.
 * Theme toggle, mobile drawer, product tabs, COA filtering, age gate.
 */
(function () {
  'use strict';

  var root = document.documentElement;

  /* ----------------------------------------------------------------
   * Dark / light mode
   * ------------------------------------------------------------- */
  function setTheme(mode) {
    root.classList.toggle('dark', mode === 'dark');
    try { localStorage.setItem('titanTheme', mode); } catch (e) {}
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      btn.setAttribute('aria-pressed', String(mode === 'dark'));
    });
  }

  document.addEventListener('click', function (e) {
    var toggle = e.target.closest('[data-theme-toggle]');
    if (!toggle) return;
    setTheme(root.classList.contains('dark') ? 'light' : 'dark');
  });

  /* ----------------------------------------------------------------
   * Mobile drawer
   * ------------------------------------------------------------- */
  var drawer = document.querySelector('[data-drawer]');

  function closeDrawer() {
    if (!drawer) return;
    drawer.classList.remove('is-open');
    document.body.style.overflow = '';
    var opener = document.querySelector('[data-drawer-open]');
    if (opener) opener.setAttribute('aria-expanded', 'false');
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-drawer-open]')) {
      if (!drawer) return;
      drawer.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      e.target.closest('[data-drawer-open]').setAttribute('aria-expanded', 'true');
      var first = drawer.querySelector('a, button');
      if (first) first.focus();
      return;
    }
    if (e.target.closest('[data-drawer-close]') || e.target.matches('.tl-drawer__scrim')) {
      closeDrawer();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
      closeDrawer();
    }
  });

  /* ----------------------------------------------------------------
   * Tab groups (bestsellers: vials / pens / sprays / orals)
   * ------------------------------------------------------------- */
  document.querySelectorAll('[data-tabs]').forEach(function (group) {
    var tabs = Array.prototype.slice.call(group.querySelectorAll('[role="tab"]'));

    function activate(index) {
      tabs.forEach(function (tab, i) {
        var selected = i === index;
        tab.setAttribute('aria-selected', String(selected));
        tab.setAttribute('tabindex', selected ? '0' : '-1');
        var panel = document.getElementById(tab.getAttribute('aria-controls'));
        if (panel) panel.hidden = !selected;
      });
    }

    tabs.forEach(function (tab, i) {
      tab.addEventListener('click', function () { activate(i); });
      tab.addEventListener('keydown', function (e) {
        var next = null;
        if (e.key === 'ArrowRight') next = (i + 1) % tabs.length;
        if (e.key === 'ArrowLeft') next = (i - 1 + tabs.length) % tabs.length;
        if (next === null) return;
        e.preventDefault();
        activate(next);
        tabs[next].focus();
      });
    });
  });

  /* ----------------------------------------------------------------
   * COA table filtering
   * ------------------------------------------------------------- */
  var coaSearch = document.querySelector('[data-coa-search]');
  var coaSelect = document.querySelector('[data-coa-filter]');
  var coaTable = document.querySelector('[data-coa-table]');

  function filterCoa() {
    if (!coaTable) return;
    var term = (coaSearch && coaSearch.value || '').trim().toLowerCase();
    var product = (coaSelect && coaSelect.value) || '';
    var visible = 0;

    coaTable.querySelectorAll('tbody tr').forEach(function (row) {
      if (row.hasAttribute('data-coa-empty')) return;
      var name = (row.getAttribute('data-name') || '').toLowerCase();
      var batch = (row.getAttribute('data-batch') || '').toLowerCase();
      var matchTerm = !term || name.indexOf(term) > -1 || batch.indexOf(term) > -1;
      var matchProduct = !product || name === product.toLowerCase();
      var show = matchTerm && matchProduct;
      row.hidden = !show;
      if (show) visible++;
    });

    var empty = coaTable.querySelector('[data-coa-empty]');
    if (empty) empty.hidden = visible > 0;
  }

  if (coaSearch) coaSearch.addEventListener('input', filterCoa);
  if (coaSelect) coaSelect.addEventListener('change', filterCoa);

  /* ----------------------------------------------------------------
   * Age gate
   * ------------------------------------------------------------- */
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-age-confirm]')) {
      try { localStorage.setItem('titanAgeVerified', '1'); } catch (err) {}
      root.classList.remove('tl-gated');
      return;
    }
    if (e.target.closest('[data-age-decline]')) {
      window.location.href = 'https://www.google.com/';
    }
  });
})();
