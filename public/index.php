<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PisciWEB – Iniciar Sesión</title>

    <link rel="icon" type="image/png" href="/app/modules/Login/assets/img/logo_PisciWeb_bg.png" />
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link rel="stylesheet" type="text/css" href="/app/modules/Login/assets/css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
        rel="stylesheet" />


</head>

<body>

    <!-- ═══ BACKGROUND SCENE ═══ -->
    <div class="bg-scene">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <!-- Wave SVG -->
        <svg class="waves" viewBox="0 0 1440 180" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path fill="rgba(0,194,224,.06)" d="M0,80 C360,160 1080,0 1440,80 L1440,180 L0,180 Z" />
            <path fill="rgba(26,127,196,.06)" d="M0,110 C480,40 960,170 1440,100 L1440,180 L0,180 Z" />
            <path fill="rgba(10,42,74,.4)" d="M0,145 C360,105 1080,165 1440,135 L1440,180 L0,180 Z" />
        </svg>
    </div>

    <!-- Floating particles (JS-generated) -->
    <div id="particles" style="position:fixed;inset:0;pointer-events:none;z-index:1;"></div>

    <!-- ═══ LOGIN CARD ═══ -->
    <main class="login-wrapper" role="main" aria-label="Formulario de inicio de sesión">
        <div class="login-card">

            <!-- Logo -->
            <div class="logo-area">
                <div class="logo-circle">
                    <img src="/app/modules/Login/assets/img/logo_PisciWeb.png" alt="PisciWEB Logo"
                        onerror="this.style.display='none';this.parentElement.innerHTML='<i class=\'bi bi-fish\' style=\'font-size:2.2rem;color:var(--cyan-vivid)\'></i>'">
                </div>
                <div class="brand-name">Pisci<span>WEB</span></div>
                <div class="brand-tagline">Gestión inteligente de piscicultura</div>
            </div>

            <!-- Alert banner (hidden by default) -->
            <div class="alert-banner" id="alertBanner" role="alert" aria-live="polite">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="alertText">Usuario o contraseña incorrectos.</span>
            </div>

            <!-- Section heading -->
            <div class="form-title">
                <i class="bi bi-box-arrow-in-right" style="color:var(--cyan-vivid)"></i>
                Iniciar sesión
            </div>

            <!-- Form -->
            <form id="loginForm" novalidate autocomplete="on">

                <!-- Usuario -->
                <div class="field-group" style="--i:2" id="fg-user">
                    <label class="field-label" for="username">
                        <i class="bi bi-person-fill" style="margin-right:.3rem"></i>Usuario
                    </label>
                    <div class="input-wrap">
                        <input class="form-input" type="text" id="username" name="username" placeholder="Ej: admin@pisciweb.co"
                            autocomplete="username" aria-required="true" aria-describedby="username-error" spellcheck="false" />
                        <i class="bi bi-person input-icon"></i>
                    </div>
                    <p class="error-msg" id="username-error">Por favor ingresa tu usuario o correo.</p>
                </div>

                <!-- Contraseña -->
                <div class="field-group" style="--i:3" id="fg-pass">
                    <label class="field-label" for="password">
                        <i class="bi bi-lock-fill" style="margin-right:.3rem"></i>Contraseña
                    </label>
                    <div class="input-wrap">
                        <input class="form-input" type="password" id="password" name="password" placeholder="••••••••"
                            autocomplete="current-password" aria-required="true" aria-describedby="password-error" />
                        <i class="bi bi-lock input-icon"></i>
                        <button type="button" class="pass-toggle" id="passToggle" aria-label="Mostrar contraseña">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <p class="error-msg" id="password-error">La contraseña es obligatoria.</p>
                </div>

                <!-- Recuérdame / Olvidé -->
                <div class="extras-row">
                    <label class="check-wrap">
                        <input type="checkbox" id="remember" name="remember" />
                        <span class="check-box"><i class="bi bi-check2"></i></span>
                        Recordarme
                    </label>
                    <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login" id="loginBtn" aria-label="Iniciar sesión en PisciWEB">
                    <div class="btn-inner">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Ingresar al sistema
                    </div>
                    <div class="spinner"></div>
                </button>
            </form>
            <!-- Footer -->
            <div class="card-footer-area mt-3">
                ¿Necesitas acceso? <a href="https://wa.me/573148446473" target="_blank">Contacta a tu administrador</a>
            </div>

        </div>
    </main>

    <!-- Version badge -->
    <div class="version-badge">PisciWEB v1.0</div>

    <!-- Toast container -->
    <div class="toast-wrap" id="toastWrap"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/app/modules/Login/assets/js/script.js"></script>
</body>

</html>