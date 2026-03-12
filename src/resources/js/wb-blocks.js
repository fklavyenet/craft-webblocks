/* =============================================================================
   WebBlocks — wb-blocks.js
   All WebBlocks-specific frontend JavaScript.
   Loaded via WebBlocksAsset (Craft Asset Bundle).
   Bootstrap is loaded separately via CDN and is available
   as a global when this script runs (position: end of <body>).
   ============================================================================= */

/* ── WB Fullscreen Image — vanilla JS fade slider ───────────────────────── */
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.wb-fullscreen-image[data-wb-fs-slider]').forEach(function (block) {
        var slides      = Array.from(block.querySelectorAll('.wb-fs-slide'));
        var pagination  = block.querySelector('.wb-fs-pagination');
        var btnPrev     = block.querySelector('.wb-fs-btn-prev');
        var btnNext     = block.querySelector('.wb-fs-btn-next');
        var autoplayMs  = parseInt(block.getAttribute('data-wb-fs-autoplay'), 10) || 0;
        var total       = slides.length;
        var current     = 0;
        var timer       = null;

        if (total < 2) return;

        /* --- initial slide styles --- */
        slides.forEach(function (slide, i) {
            slide.style.position   = i === 0 ? 'relative' : 'absolute';
            slide.style.inset      = '0';
            slide.style.opacity    = i === 0 ? '1' : '0';
            slide.style.transition = 'opacity 0.8s ease';
            slide.style.width      = '100%';
        });
        block.querySelector('.wb-fs-slides').style.position = 'relative';

        /* --- dot pagination --- */
        if (pagination) {
            pagination.innerHTML = '';
            slides.forEach(function (_, i) {
                var dot = document.createElement('span');
                dot.className = 'wb-fs-dot' + (i === 0 ? ' is-active' : '');
                dot.setAttribute('role', 'button');
                dot.setAttribute('aria-label', 'Slide ' + (i + 1));
                dot.addEventListener('click', function () { goTo(i); });
                pagination.appendChild(dot);
            });
        }

        function goTo(index) {
            slides[current].style.opacity  = '0';
            slides[current].style.position = 'absolute';
            current = (index + total) % total;
            slides[current].style.position = 'relative';
            slides[current].style.opacity  = '1';
            if (pagination) {
                pagination.querySelectorAll('.wb-fs-dot').forEach(function (d, i) {
                    d.classList.toggle('is-active', i === current);
                });
            }
        }

        function next() { goTo(current + 1); }
        function prev() { goTo(current - 1); }

        if (btnPrev) btnPrev.addEventListener('click', function () { stopAuto(); prev(); });
        if (btnNext) btnNext.addEventListener('click', function () { stopAuto(); next(); });

        /* --- keyboard nav --- */
        block.setAttribute('tabindex', '0');
        block.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft')  { stopAuto(); prev(); }
            if (e.key === 'ArrowRight') { stopAuto(); next(); }
        });

        /* --- touch / swipe --- */
        var touchStartX = null;
        block.addEventListener('touchstart', function (e) {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });
        block.addEventListener('touchend', function (e) {
            if (touchStartX === null) return;
            var dx = e.changedTouches[0].clientX - touchStartX;
            touchStartX = null;
            if (Math.abs(dx) < 40) return;
            stopAuto();
            if (dx < 0) next(); else prev();
        });

        /* --- autoplay --- */
        function startAuto() {
            if (autoplayMs > 0) timer = setInterval(next, autoplayMs);
        }
        function stopAuto() {
            clearInterval(timer);
            timer = null;
        }

        startAuto();
    });

    /* ── WB Masonry ──────────────────────────────────────────────────────── */
    // CSS-grid row-span masonry. No absolute positioning — overflow impossible.
    // Each item gets grid-row: span N so it fills its natural height.
    // grid-auto-rows is set to rowSize px so spans map 1:1 to pixels.
    (function () {
        var ROW_SIZE = 4; // px — smaller = more precise, but more rows

        function layout(grid) {
            var cs     = getComputedStyle(grid);
            var gapY   = parseFloat(cs.rowGap) || 0;

            // Set a fine row grid so spans can approximate any height
            grid.style.gridAutoRows = ROW_SIZE + 'px';
            grid.style.alignItems   = 'start';

            Array.from(grid.children).forEach(function (item) {
                // Reset span so item shrinks to natural height
                item.style.gridRow = '';

                var h    = item.getBoundingClientRect().height;
                var span = Math.ceil((h + gapY) / (ROW_SIZE + gapY));
                item.style.gridRow = 'span ' + span;
            });
        }

        document.querySelectorAll('[data-wb-masonry]').forEach(function (grid) {
            layout(grid);

            // Re-run after each image loads
            grid.querySelectorAll('img').forEach(function (img) {
                if (!img.complete) {
                    img.addEventListener('load',  function () { layout(grid); });
                    img.addEventListener('error', function () { layout(grid); });
                }
            });

            // Re-run on resize with debounce
            var resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () { layout(grid); }, 100);
            });
        });
    })();

    /* ── WB Lightbox ─────────────────────────────────────────────────────── */
    // Reads data-fancybox (group name) and data-caption from <a> elements.
    // Pure vanilla JS, zero dependencies, MIT-compatible.
    (function () {
        // Build overlay DOM once
        var overlay = document.createElement('div');
        overlay.id = 'wb-lightbox';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-label', 'Image viewer');
        overlay.innerHTML =
            '<button class="wb-lb-close" aria-label="Close">&times;</button>' +
            '<button class="wb-lb-prev" aria-label="Previous">&#8249;</button>' +
            '<button class="wb-lb-next" aria-label="Next">&#8250;</button>' +
            '<div class="wb-lb-content">' +
                '<img class="wb-lb-img" src="" alt="">' +
                '<p class="wb-lb-caption"></p>' +
            '</div>';
        document.body.appendChild(overlay);

        var groups = {};   // groupName → [{ href, caption }]
        var current = { group: null, index: 0 };

        // Index all data-fancybox links
        function indexLinks() {
            groups = {};
            document.querySelectorAll('a[data-fancybox]').forEach(function (a) {
                var g = a.getAttribute('data-fancybox');
                if (!groups[g]) groups[g] = [];
                groups[g].push({ href: a.href, caption: a.getAttribute('data-caption') || '' });
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    var idx = groups[g].indexOf(groups[g].find(function (item) { return item.href === a.href; }));
                    open(g, idx);
                });
            });
        }

        function open(group, index) {
            current.group = group;
            current.index = index;
            show();
            overlay.classList.add('wb-lb-open');
            document.body.style.overflow = 'hidden';
            overlay.querySelector('.wb-lb-close').focus();
        }

        function close() {
            overlay.classList.remove('wb-lb-open');
            document.body.style.overflow = '';
        }

        function show() {
            var items = groups[current.group];
            var item  = items[current.index];
            var img   = overlay.querySelector('.wb-lb-img');
            img.src = item.href;
            img.alt = item.caption;
            overlay.querySelector('.wb-lb-caption').textContent = item.caption;
            overlay.querySelector('.wb-lb-prev').style.visibility = items.length > 1 ? '' : 'hidden';
            overlay.querySelector('.wb-lb-next').style.visibility = items.length > 1 ? '' : 'hidden';
        }

        function prev() { var n = groups[current.group].length; current.index = (current.index - 1 + n) % n; show(); }
        function next() { var n = groups[current.group].length; current.index = (current.index + 1) % n; show(); }

        overlay.querySelector('.wb-lb-close').addEventListener('click', close);
        overlay.querySelector('.wb-lb-prev').addEventListener('click', prev);
        overlay.querySelector('.wb-lb-next').addEventListener('click', next);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });

        document.addEventListener('keydown', function (e) {
            if (!overlay.classList.contains('wb-lb-open')) return;
            if (e.key === 'Escape')     close();
            if (e.key === 'ArrowLeft')  prev();
            if (e.key === 'ArrowRight') next();
        });

        indexLinks();
    })();

});

/* ── WB Color Mode Toggle ────────────────────────────────────────────────── */
// Runs immediately (not deferred behind DOMContentLoaded) so toggle buttons
// are wired as soon as the script is parsed after </body>.
(function () {
    var CYCLE = ['light', 'dark', 'auto'];
    var mq = window.matchMedia('(prefers-color-scheme: dark)');

    function applyMode(mode) {
        var resolved = (mode === 'auto') ? (mq.matches ? 'dark' : 'light') : mode;
        document.documentElement.setAttribute('data-bs-theme', resolved);
        document.documentElement.setAttribute('data-wb-mode', mode);
        localStorage.setItem('wbColorMode', mode);
    }

    // Initialise from localStorage
    applyMode(localStorage.getItem('wbColorMode') || 'auto');

    // React to OS-level changes when in auto mode
    mq.addEventListener('change', function () {
        if ((localStorage.getItem('wbColorMode') || 'auto') === 'auto') {
            applyMode('auto');
        }
    });

    // Wire up all toggle buttons on the page
    document.querySelectorAll('.wb-color-mode-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var current = localStorage.getItem('wbColorMode') || 'auto';
            applyMode(CYCLE[(CYCLE.indexOf(current) + 1) % CYCLE.length]);
        });
    });
})();

/* ── WB Cookie Consent ───────────────────────────────────────────────────── */
// Storage key: "wb_cookie_consent"  →  { necessary:true, analytics:bool, marketing:bool, preferences:bool }
// window.wbCookieConsent is published so third-party scripts can gate on it.
// window.wbOpenCookieBanner() can be called by external links/buttons to re-open the banner.
(function () {
    var STORAGE_KEY = 'wb_cookie_consent';
    var banner      = document.getElementById('wb-cookie-banner');
    if (!banner) { return; } // banner not rendered (wbCookieBannerEnabled = false)

    var btnAcceptAll = document.getElementById('wb-cookie-accept-all');
    var btnSave      = document.getElementById('wb-cookie-save');
    var chkAnalytics    = document.getElementById('wb_cat_analytics');
    var chkMarketing    = document.getElementById('wb_cat_marketing');
    var chkPreferences  = document.getElementById('wb_cat_preferences');

    /** Read stored consent, or null if not yet decided. */
    function getConsent() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)); }
        catch (e) { return null; }
    }

    /** Persist consent, publish window.wbCookieConsent, hide banner. */
    function saveConsent(analytics, marketing, preferences) {
        var consent = { necessary: true, analytics: !!analytics, marketing: !!marketing, preferences: !!preferences };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
        window.wbCookieConsent = consent;
        banner.hidden = true;
    }

    /** Restore checkbox state from stored consent (so "save" reflects real choices). */
    function restoreCheckboxes(consent) {
        if (!consent) { return; }
        if (chkAnalytics)   { chkAnalytics.checked   = !!consent.analytics; }
        if (chkMarketing)   { chkMarketing.checked   = !!consent.marketing; }
        if (chkPreferences) { chkPreferences.checked = !!consent.preferences; }
    }

    /** Re-open the banner (e.g. triggered from a footer link). */
    function openBanner() {
        restoreCheckboxes(getConsent());
        banner.hidden = false;
        banner.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }

    // Expose globally so footer trigger and any external script can call it
    window.wbOpenCookieBanner = openBanner;

    // Wire footer / any other trigger links
    document.querySelectorAll('[data-wb-cookie-trigger]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            openBanner();
        });
    });

    // Wire action buttons — always, so they work when banner is re-opened later
    btnAcceptAll && btnAcceptAll.addEventListener('click', function () {
        saveConsent(true, true, true);
    });

    btnSave && btnSave.addEventListener('click', function () {
        saveConsent(
            chkAnalytics   ? chkAnalytics.checked   : false,
            chkMarketing   ? chkMarketing.checked   : false,
            chkPreferences ? chkPreferences.checked : false
        );
    });

    var stored = getConsent();

    if (stored) {
        // Already decided — publish and stay hidden
        window.wbCookieConsent = stored;
        restoreCheckboxes(stored);
        return;
    }

    // No decision yet — show banner
    banner.hidden = false;
})();
