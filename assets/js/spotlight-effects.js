// spotlight-effects.js — Spotlight Reveal ambient layer
// Injects the cursor-following glow (all pages) and the drifting
// light-dust canvas (storefront pages only). No HTML markup needed —
// just include this script once per page, after the theme CSS.
(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var spot = document.createElement('div');
  spot.id = 'gx-spotlight';
  document.body.appendChild(spot);

  if (reduce) {
    spot.style.display = 'none';
  } else {
    document.addEventListener('mousemove', function (e) {
      spot.style.left = e.clientX + 'px';
      spot.style.top  = e.clientY + 'px';
    });
  }

  var isAdmin = document.body.getAttribute('data-gx') === 'admin';

  if (!reduce && !isAdmin) {
    var canvas = document.createElement('canvas');
    canvas.id = 'gx-dust';
    document.body.insertBefore(canvas, document.body.firstChild);
    var ctx = canvas.getContext('2d');

    function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
    resize();
    window.addEventListener('resize', resize);

    var particles = [];
    for (var i = 0; i < 30; i++) {
      particles.push({
        x: Math.random() * window.innerWidth,
        y: Math.random() * window.innerHeight,
        r: Math.random() * 1.5 + .4,
        s: Math.random() * .35 + .1,
        o: Math.random() * .45 + .12
      });
    }

    (function tick() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      for (var j = 0; j < particles.length; j++) {
        var p = particles[j];
        p.y -= p.s;
        if (p.y < -10) { p.y = window.innerHeight + 10; p.x = Math.random() * window.innerWidth; }
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(242,182,91,' + p.o + ')';
        ctx.fill();
      }
      requestAnimationFrame(tick);
    })();
  }
})();