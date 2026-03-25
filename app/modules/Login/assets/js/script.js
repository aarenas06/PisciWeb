/* ── FLOATING PARTICLES ── */
(function () {
    const c = document.getElementById('particles');
    for (let i = 0; i < 22; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        p.style.cssText = `
          left:${Math.random() * 100}%;
          bottom:${Math.random() * 20}%;
          animation-delay:${Math.random() * 10}s;
          animation-duration:${8 + Math.random() * 8}s;
          width:${3 + Math.random() * 5}px;
          height:${3 + Math.random() * 5}px;
          opacity:0;
        `;
        c.appendChild(p);
    }
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
    fill.style.width = pct + '%';
    fill.style.background = colors[Math.max(0, score - 1)];
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

    /* Simulate API call */
    await new Promise(r => setTimeout(r, 1800));

    btn.classList.remove('loading');
    btn.disabled = false;

    /* DEMO: show error for wrong creds, success for admin/admin */
    if (user.value.toLowerCase() === 'admin' && pass.value === 'admin') {
        showToast('¡Bienvenido a PisciWEB! Redirigiendo…');
    } else {
        alertT.textContent = 'Usuario o contraseña incorrectos. Intenta de nuevo.';
        alert.classList.add('show');
        pass.value = '';
        document.getElementById('strengthFill').style.width = '0%';
        pass.focus();
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
