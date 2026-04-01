/* ── FLOATING BUBBLES ── */
(function () {
    const c = document.getElementById('particles');
    if (!c) return;
    const n = window.innerWidth < 480 ? 10 : 18;
    for (let i = 0; i < n; i++) {
        const sz = 4 + Math.random() * 13;
        const p = document.createElement('div');
        p.className = 'particle';
        p.style.cssText = `
          left:${5 + Math.random() * 90}%;
          bottom:${-6 + Math.random() * 14}%;
          animation-delay:${Math.random() * 14}s;
          animation-duration:${11 + Math.random() * 13}s;
          width:${sz}px;
          height:${sz}px;
        `;
        c.appendChild(p);
    }
})();

/* ── AQUATIC FISH SCENE (Canvas) ── */
(function () {
    const cvs = document.getElementById('fishScene');
    if (!cvs || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const ctx = cvs.getContext('2d');
    let W, H;

    function resize() { W = cvs.width = window.innerWidth; H = cvs.height = window.innerHeight; }
    resize();
    let rT;
    window.addEventListener('resize', () => { clearTimeout(rT); rT = setTimeout(resize, 250); });

    /* Palette from design tokens (r, g, b) */
    const C = [[0,194,224],[26,127,196],[58,198,198],[94,224,245]];

    /* Draw a single fish silhouette */
    function drawFish(s, tw) {
        ctx.beginPath();
        ctx.moveTo(s, 0);
        ctx.bezierCurveTo(s*.78, -s*.38, s*.12, -s*.42, -s*.28, -s*.14);
        ctx.quadraticCurveTo(-s*.65, -s*(.38+tw), -s, -s*(.36+tw));
        ctx.lineTo(-s*.72, 0);
        ctx.lineTo(-s, s*(.36-tw));
        ctx.quadraticCurveTo(-s*.65, s*(.38-tw), -s*.28, s*.14);
        ctx.bezierCurveTo(s*.12, s*.42, s*.78, s*.38, s, 0);
        ctx.closePath();
        ctx.fill();
        /* Dorsal fin */
        ctx.beginPath();
        ctx.moveTo(s*.12, -s*.38);
        ctx.quadraticCurveTo(-s*.02, -s*.62, -s*.2, -s*.36);
        ctx.fill();
        /* Pectoral fin */
        ctx.beginPath();
        ctx.moveTo(s*.35, s*.1);
        ctx.quadraticCurveTo(s*.18, s*.36, s*.02, s*.22);
        ctx.fill();
    }

    /* Spawn a fish */
    const N = window.innerWidth < 600 ? 6 : 10;
    const pool = [];

    function spawn(offScreen) {
        const size = 14 + Math.random() * 30;
        const goL  = Math.random() > .35;
        const c    = C[Math.floor(Math.random() * C.length)];
        return {
            x: offScreen
                ? (goL ? W + size*2 + Math.random()*300 : -size*2 - Math.random()*300)
                : Math.random() * W,
            y:  50 + Math.random() * (H - 100),
            size,
            vx: (.25 + Math.random()*.6) * (goL ? -1 : 1),
            dir: goL ? 1 : -1,
            c,
            a:  .04 + Math.random() * .08,
            tp: Math.random() * Math.PI * 2,
            ts: 2.5 + Math.random() * 2,
            wp: Math.random() * Math.PI * 2,
            wa: .15 + Math.random() * .45,
        };
    }

    for (let i = 0; i < N; i++) pool.push(spawn(false));

    /* Render loop throttled to ~24 fps */
    let last = 0;
    const INTERVAL = 1000 / 24;

    function tick(now) {
        requestAnimationFrame(tick);
        if (document.hidden || now - last < INTERVAL) return;
        last = now - ((now - last) % INTERVAL);

        ctx.clearRect(0, 0, W, H);

        for (let i = 0; i < pool.length; i++) {
            const f = pool[i];
            f.x  += f.vx;
            f.tp += f.ts * .05;
            f.wp += .018;

            if ((f.vx < 0 && f.x < -f.size*3) || (f.vx > 0 && f.x > W + f.size*3)) {
                pool[i] = spawn(true);
                continue;
            }

            const wy = Math.sin(f.wp) * f.wa * f.size;
            const tw = Math.sin(f.tp) * .25;

            ctx.save();
            ctx.translate(f.x, f.y + wy);
            ctx.scale(f.dir, 1);
            ctx.fillStyle = `rgba(${f.c[0]},${f.c[1]},${f.c[2]},${f.a})`;
            drawFish(f.size, tw);
            ctx.restore();
        }
    }

    requestAnimationFrame(tick);
})();

/* ── PASSWORD TOGGLE ── */
const passField = document.getElementById('password');
const eyeIcon = document.getElementById('eyeIcon');
document.getElementById('passToggle').addEventListener('click', () => {
    const visible = passField.type === 'text';
    passField.type = visible ? 'password' : 'text';
    eyeIcon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
});

/* ── STRENGTH METER ── */
passField.addEventListener('input', () => {
    const v = passField.value;
    let score = 0;
    if (v.length >= 6) score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const pct = (score / 5) * 100;
    const colors = ['#e03a5a', '#f4a422', '#f4d422', '#5ee0f5', '#00c2a0'];
    const fill = document.getElementById('strengthFill');
    if (fill) {
        fill.style.width = pct + '%';
        fill.style.background = colors[Math.max(0, score - 1)];
    }
});

/* ── VALIDATION ── */
function setError(fg, inp, show) {
    fg.classList.toggle('has-error', show);
    inp.classList.toggle('is-invalid', show);
}

/* ── FORM SUBMIT ── */
const form = document.getElementById('loginForm');
const btn = document.getElementById('loginBtn');
const alert = document.getElementById('alertBanner');
const alertT = document.getElementById('alertText');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const user = document.getElementById('username');
    const pass = document.getElementById('password');
    const fgU = document.getElementById('fg-user');
    const fgP = document.getElementById('fg-pass');

    let valid = true;
    if (!user.value.trim()) { setError(fgU, user, true); valid = false; } else setError(fgU, user, false);
    if (!pass.value.trim()) { setError(fgP, pass, true); valid = false; } else setError(fgP, pass, false);

    if (!valid) return;

    /* Loading state */
    btn.classList.add('loading');
    btn.disabled = true;
    alert.classList.remove('show');

    const formData = new FormData();
    formData.append('funcion', 'login');
    formData.append('username', user.value.trim());
    formData.append('password', pass.value);

    try {
        const response = await fetch('/api/Login', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(`¡Bienvenido, ${result.data[0].UsuNom}! Redirigiendo…`);
            setTimeout(() => { window.location.href = '/Home/index'; }, 1200);
        } else {
            alertT.textContent = result.message || 'Usuario o contraseña incorrectos.';
            alert.classList.add('show');
            pass.value = '';
            const strengthBar = document.getElementById('strengthFill');
            if (strengthBar) strengthBar.style.width = '0%';
            pass.focus();
        }
    } catch (error) {
        console.error('Error:', error);
        alertT.textContent = 'Error de conexión con el servidor.';
        alert.classList.add('show');
    } finally {
        btn.classList.remove('loading');
        btn.disabled = false;
    }
});

/* Clear errors on input */
document.getElementById('username').addEventListener('input', () => {
    setError(document.getElementById('fg-user'), document.getElementById('username'), false);
});
passField.addEventListener('input', () => {
    setError(document.getElementById('fg-pass'), passField, false);
});

/* ── TOAST ── */
function showToast(msg, type = 'success') {
    const wrap = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.className = 'toast-item';
    t.innerHTML = `<i class="bi bi-check2-circle"></i> ${msg}`;
    wrap.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}
