'use strict';

(function () {
    const toggle = document.getElementById('nav-toggle');
    const nav    = document.getElementById('site-nav');

    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
        const expanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', String(!expanded));
        nav.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
        if (!toggle.contains(e.target) && !nav.contains(e.target)) {
            toggle.setAttribute('aria-expanded', 'false');
            nav.classList.remove('open');
        }
    });
})();
