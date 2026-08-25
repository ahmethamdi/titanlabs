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

  /**
   * Collapse the drawer's sub-menus behind a toggle. A 20+ item menu dumped
   * open is unusable on a phone, so each parent gets a disclosure button and
   * starts closed. Built from the rendered menu so it works for both the WP
   * menu and titan_primary_fallback().
   */
  function buildDrawerAccordion() {
    if (!drawer) return;
    var parents = drawer.querySelectorAll('li > .sub-menu');

    Array.prototype.forEach.call(parents, function (sub, i) {
      var li = sub.parentNode;
      var link = li.querySelector(':scope > a');
      if (!link || li.querySelector(':scope > .tl-drawer__toggle')) return;

      var id = 'tl-submenu-' + i;
      sub.id = id;
      sub.hidden = true;
      li.classList.add('tl-has-sub');

      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'tl-drawer__toggle';
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-controls', id);
      btn.setAttribute('aria-label', link.textContent.trim());
      btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"' +
        ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
        '<path d="m6 9 6 6 6-6"/></svg>';

      btn.addEventListener('click', function () {
        var open = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!open));
        sub.hidden = open;
        li.classList.toggle('is-open', !open);
      });

      // The toggle sits beside the link, so wrap them on one row.
      var row = document.createElement('div');
      row.className = 'tl-drawer__row';
      li.insertBefore(row, link);
      row.appendChild(link);
      row.appendChild(btn);
    });
  }

  buildDrawerAccordion();

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
      return;
    }
    // Navigating away from an in-page anchor leaves the drawer open otherwise.
    if (drawer && drawer.contains(e.target) && e.target.closest('a')) {
      closeDrawer();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
      closeDrawer();
    }
  });

  /* ----------------------------------------------------------------
   * Cart drawer
   *
   * Opens from the cart icon only — adding a product gives inline button
   * feedback and bumps the badge instead, which is less disruptive while
   * someone is still browsing.
   * ------------------------------------------------------------- */
  var cartDrawer = document.querySelector('[data-cart-drawer]');
  var cartLastFocus = null;

  function openCart() {
    if (!cartDrawer) return;
    cartLastFocus = document.activeElement;
    cartDrawer.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    var focusable = cartDrawer.querySelector('.tl-cartdrawer__panel a, .tl-cartdrawer__panel button');
    if (focusable) focusable.focus();
  }

  function closeCart() {
    if (!cartDrawer) return;
    cartDrawer.classList.remove('is-open');
    document.body.style.overflow = '';
    if (cartLastFocus && cartLastFocus.focus) cartLastFocus.focus();
  }

  function cartBusy(state) {
    if (cartDrawer) cartDrawer.classList.toggle('is-busy', !!state);
  }

  document.addEventListener('click', function (e) {
    var opener = e.target.closest('[data-cart-open]');
    if (opener && cartDrawer) {
      // Let the href stand as the no-JS fallback.
      e.preventDefault();
      openCart();
      return;
    }
    if (e.target.closest('[data-cart-close]')) {
      closeCart();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && cartDrawer && cartDrawer.classList.contains('is-open')) {
      closeCart();
    }
  });

  /**
   * Applies the fragment payload WooCommerce (or titan_set_qty) returns.
   */
  function applyFragments(fragments) {
    if (!fragments) return;
    Object.keys(fragments).forEach(function (selector) {
      var target = document.querySelector(selector);
      if (!target) return;
      var tmp = document.createElement('div');
      tmp.innerHTML = fragments[selector];
      var replacement = tmp.firstElementChild;
      if (replacement) target.replaceWith(replacement);
    });
    pulseBadge();
  }

  function pulseBadge() {
    var badge = document.querySelector('.tl-cart__count');
    if (!badge) return;
    badge.classList.remove('is-bumped');
    // Restart the animation on a fragment that was just swapped in.
    void badge.offsetWidth;
    badge.classList.add('is-bumped');
  }

  /**
   * Quantity stepper and remove, both routed through titan_set_qty.
   */
  function setQty(key, qty) {
    if (!window.titanData || !titanData.ajaxUrl) return;
    cartBusy(true);

    var body = new URLSearchParams();
    body.set('action', 'titan_set_qty');
    body.set('nonce', titanData.nonce);
    body.set('key', key);
    body.set('qty', String(qty));

    fetch(titanData.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.success) applyFragments(res.data.fragments);
      })
      .catch(function () { /* leave the drawer as-is on a failed request */ })
      .then(function () { cartBusy(false); });
  }

  document.addEventListener('click', function (e) {
    var remove = e.target.closest('[data-cart-remove]');
    if (remove) {
      e.preventDefault();
      setQty(remove.getAttribute('data-cart-remove'), 0);
      return;
    }

    var step = e.target.closest('[data-qty-up], [data-qty-down]');
    if (!step) return;
    var wrap = step.closest('[data-qty]');
    var input = wrap && wrap.querySelector('[data-cart-qty]');
    if (!input) return;

    var next = (parseInt(input.value, 10) || 0) + (step.hasAttribute('data-qty-up') ? 1 : -1);
    if (next < 0) next = 0;
    input.value = next;
    setQty(input.getAttribute('data-cart-qty'), next);
  });

  document.addEventListener('change', function (e) {
    var input = e.target.closest('[data-cart-qty]');
    if (!input) return;
    var qty = parseInt(input.value, 10);
    if (isNaN(qty) || qty < 0) qty = 0;
    setQty(input.getAttribute('data-cart-qty'), qty);
  });

  /**
   * Inline feedback on the add-to-cart button. WooCommerce fires these on
   * document, so this works for every product loop and the single page.
   */
  if (window.jQuery) {
    jQuery(document.body).on('adding_to_cart', function (_e, button) {
      if (button && button[0]) button[0].classList.add('is-adding');
    });

    jQuery(document.body).on('added_to_cart', function (_e, fragments, _hash, button) {
      pulseBadge();
      if (!button || !button[0]) return;
      var el = button[0];
      el.classList.remove('is-adding');
      el.classList.add('is-added');
      window.setTimeout(function () { el.classList.remove('is-added'); }, 1800);
    });
  }

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
        if (!panel) return;
        panel.hidden = !selected;
        // A hidden panel has no layout, so its scroller keeps a stale offset.
        if (selected) {
          panel.querySelectorAll('.tl-grid--scroll-mobile').forEach(function (strip) {
            strip.scrollLeft = 0;
          });
        }
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
