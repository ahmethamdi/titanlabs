/**
 * Titan Labs — front-end behaviour.
 * Theme toggle, mobile drawer, product tabs, COA filtering, age gate.
 */
(function () {
  'use strict';

  var root = document.documentElement;

  /* ----------------------------------------------------------------
   * Sticky header
   *
   * Condenses once the page scrolls: the logo shrinks and the bar loses
   * height, so the nav keeps its place without taking a band off every
   * screenful.
   * ------------------------------------------------------------- */
  var header = document.querySelector('.tl-header');

  if (header) {
    var condensed = false;

    var onScroll = function () {
      var should = window.scrollY > 40;
      if (should === condensed) return;
      condensed = should;
      header.classList.toggle('is-condensed', should);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

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

  /* ----------------------------------------------------------------
   * Shop filters
   *
   * Every option is a real link, so this works without JS and the URLs
   * stay shareable and indexable. With JS the same URL is fetched and
   * swapped in, and pushState keeps the back button honest.
   * ------------------------------------------------------------- */
  var shopMain = document.querySelector('[data-shop-main]');

  if (shopMain && window.titanData && titanData.ajaxUrl) {
    var sheet = document.querySelector('[data-filtersheet]');
    var sheetBody = document.querySelector('[data-filtersheet-body]');
    var pending = null;

    function syncSheet() {
      // The sheet shows the same controls as the sidebar; clone rather than
      // render twice so they can never disagree.
      var sidebar = document.querySelector('.tl-shoplayout__sidebar .tl-filters');
      if (sheetBody && sidebar) {
        sheetBody.innerHTML = '';
        sheetBody.appendChild(sidebar.cloneNode(true));
      }
    }

    function setBadge(n) {
      var badge = document.querySelector('[data-filter-badge]');
      if (!badge) return;
      badge.textContent = String(n);
      badge.hidden = !n;
    }

    function loadFilters(url, push) {
      if (pending) pending.abort();
      pending = new AbortController();

      shopMain.classList.add('is-loading');

      var body = new URLSearchParams();
      body.set('action', 'titan_filter');
      body.set('nonce', titanData.nonce);
      body.set('url', url);

      fetch(titanData.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        signal: pending.signal,
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res || !res.success) return;

          shopMain.innerHTML = res.data.results;

          var sidebar = document.querySelector('.tl-shoplayout__sidebar');
          if (sidebar) sidebar.innerHTML = res.data.filters;

          setBadge(res.data.count);
          syncSheet();

          if (push) window.history.pushState({ titanFilter: url }, '', url);

          var top = document.querySelector('.tl-shoplayout');
          if (top && top.getBoundingClientRect().top < 0) {
            top.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        })
        .catch(function (err) {
          // An aborted request is a newer click, not a failure.
          if (err && err.name === 'AbortError') return;
          window.location.href = url;
        })
        .then(function () {
          shopMain.classList.remove('is-loading');
          pending = null;
        });
    }

    document.addEventListener('click', function (e) {
      var opt = e.target.closest('[data-filter-opt]');
      if (opt && opt.getAttribute('href')) {
        if (opt.getAttribute('aria-disabled') === 'true') {
          e.preventDefault();
          return;
        }
        e.preventDefault();
        loadFilters(opt.getAttribute('href'), true);
        return;
      }

      // Pagination inside the filtered grid.
      var page = e.target.closest('.tl-shoplayout__main .woocommerce-pagination a');
      if (page && page.getAttribute('href')) {
        e.preventDefault();
        loadFilters(page.getAttribute('href'), true);
        return;
      }

      if (e.target.closest('[data-filters-open]')) {
        syncSheet();
        if (sheet) {
          sheet.classList.add('is-open');
          document.body.style.overflow = 'hidden';
        }
        return;
      }

      if (e.target.closest('[data-filters-close]')) {
        if (sheet) {
          sheet.classList.remove('is-open');
          document.body.style.overflow = '';
        }
      }
    });

    // Sorting posts through the same path.
    document.addEventListener('change', function (e) {
      var select = e.target.closest('.tl-shoplayout__main .woocommerce-ordering select');
      if (!select) return;
      e.preventDefault();
      var url = new URL(window.location.href);
      url.searchParams.set('orderby', select.value);
      url.searchParams.delete('paged');
      loadFilters(url.toString(), true);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sheet && sheet.classList.contains('is-open')) {
        sheet.classList.remove('is-open');
        document.body.style.overflow = '';
      }
    });

    window.addEventListener('popstate', function (e) {
      if (e.state && e.state.titanFilter) {
        loadFilters(e.state.titanFilter, false);
      } else {
        loadFilters(window.location.href, false);
      }
    });

    syncSheet();
  }

  /* ----------------------------------------------------------------
   * Scroll reveal
   *
   * Sections rise into place as they enter the viewport, and counters in
   * the hero and quality stats count up to their value. Both run once.
   *
   * The hiding styles live behind html.has-reveal, which is only set here —
   * so if this script never runs, nothing is left invisible.
   * ------------------------------------------------------------- */
  (function () {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (reduced.matches || !('IntersectionObserver' in window)) {
      return;
    }

    // Sections worth announcing, plus the grids inside them.
    var targets = document.querySelectorAll(
      '.tl-section .tl-sectionhead, .tl-spotlight__inner, .tl-stack-promo, .tl-qa__stats'
    );
    var grids = document.querySelectorAll(
      '.tl-grid, .tl-cat-grid, .tl-product-grid, .tl-reviews__list'
    );

    if (!targets.length && !grids.length) {
      return;
    }

    root.classList.add('has-reveal');

    Array.prototype.forEach.call(targets, function (el) {
      el.setAttribute('data-reveal', '');
    });

    Array.prototype.forEach.call(grids, function (grid) {
      grid.setAttribute('data-reveal-stagger', '');
      Array.prototype.forEach.call(grid.children, function (child, i) {
        // Cap the stagger so a long grid does not trail far behind the fold.
        child.style.setProperty('--i', Math.min(i, 8));
      });
    });

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) {
          return;
        }
        entry.target.classList.add('is-visible');
        io.unobserve(entry.target);
      });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });

    Array.prototype.forEach.call(targets, function (el) { io.observe(el); });
    Array.prototype.forEach.call(grids, function (el) { io.observe(el); });

    /* Count the measured figures up rather than just printing them. Only
       numeric values animate; "EU" and the like are left alone. */
    var counters = document.querySelectorAll('.tl-hero__trust dt, .tl-qa__stats dt');

    var countUp = function (el) {
      var raw = el.textContent.trim();
      var match = raw.match(/^([^\d-]*)(-?[\d.,]+)(.*)$/);
      if (!match) {
        return;
      }
      var prefix = match[1];
      var suffix = match[3];
      var numText = match[2];
      var decimals = (numText.split(/[.,]/)[1] || '').length;
      var usesComma = numText.indexOf(',') > -1 && decimals > 0;
      var target = parseFloat(numText.replace(/,/g, usesComma ? '.' : ''));

      if (isNaN(target)) {
        return;
      }

      var start = null;
      var dur = 900;

      var step = function (now) {
        if (start === null) {
          start = now;
        }
        var t = Math.min((now - start) / dur, 1);
        // Ease out so it decelerates onto the final figure.
        var eased = 1 - Math.pow(1 - t, 3);
        var value = (target * eased).toFixed(decimals);
        if (usesComma) {
          value = value.replace('.', ',');
        }
        el.textContent = prefix + value + suffix;
        if (t < 1) {
          requestAnimationFrame(step);
        }
      };

      requestAnimationFrame(step);
    };

    if (counters.length) {
      var counterIo = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }
          countUp(entry.target);
          counterIo.unobserve(entry.target);
        });
        // The hero figures straddle the fold on a laptop screen, so any
        // meaningful threshold or negative margin would keep them from ever
        // counting. A sliver in view is enough.
      }, { threshold: 0.01 });

      Array.prototype.forEach.call(counters, function (el) { counterIo.observe(el); });
    }
  }());

})();
