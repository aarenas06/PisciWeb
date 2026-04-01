<!DOCTYPE html>
<html lang="es">
<!-- [Head] start -->

<head>
    <title>Home | PisciWEB</title>
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="PisciWEB — Gestión inteligente de piscicultura">

    <!-- [Favicon] icon -->
    <link rel="icon" href="../public/assets/plantilla/images/Pisciweb/Icon.png" type="image/x-icon">

    <!-- [Google Fonts] — Mismas familias que el Login para coherencia -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600;700&family=Public+Sans:wght@300;400;500;600;700&display=swap"
        id="main-font-link">

    <!-- [Icon Libraries] -->
    <link rel="stylesheet" href="../public/assets/plantilla/fonts/tabler-icons.min.css">
    <link rel="stylesheet" href="../public/assets/plantilla/fonts/feather.css">
    <link rel="stylesheet" href="../public/assets/plantilla/fonts/fontawesome.css">
    <link rel="stylesheet" href="../public/assets/plantilla/fonts/material.css">

    <!-- [Template CSS] -->
    <link rel="stylesheet" href="../public/assets/plantilla/css/style.css" id="main-style-link">
    <link rel="stylesheet" href="../public/assets/plantilla/css/style-preset.css">

    <!-- [PisciWEB Design System] — Sistema unificado de diseño -->
    <link rel="stylesheet" href="../public/assets/css/pisciweb-design-system.css">

    <style>
        .sidebar-logo {
            margin-top: 15px;
            max-width: 240px;
            max-height: 56px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }
    </style>
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill" style="background: linear-gradient(135deg, var(--pw-ocean-bright), var(--pw-cyan-vivid));"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- ═══════════════════════════════════════════════
         SIDEBAR — Ocean dark theme (coherente con Login)
         ═══════════════════════════════════════════════ -->
    <nav class="pc-sidebar">
        <div class="navbar-wrapper">
            <div class="m-header">
                <a href="Home" class="b-brand text-primary">
                    <img src="../public/assets/plantilla/images/Pisciweb/Logo_Lateral_banner.png"
                         class="img-fluid logo-lg sidebar-logo" alt="PisciWEB">
                </a>
            </div>
            <div class="navbar-content">
                <ul class="pc-navbar">
                    <!-- ── Principal ── -->
                    <li class="pc-item">
                        <a href="Home" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                            <span class="pc-mtext">Dashboard</span>
                        </a>
                    </li>
                    <li class="pc-item">
                        <a href="Reportes" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-report-analytics"></i></span>
                            <span class="pc-mtext">Reportes</span>
                        </a>
                    </li>

                    <!-- ── Parámetros ── -->
                    <li class="pc-item pc-caption">
                        <label>Parámetros</label>
                        <i class="ti ti-dashboard"></i>
                    </li>
                    <li class="pc-item">
                        <a href="Usuarios" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-users"></i></span>
                            <span class="pc-mtext">Usuarios</span>
                        </a>
                    </li>
                    <li class="pc-item">
                        <a href="../elements/bc_color.html" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-box"></i></span>
                            <span class="pc-mtext">Activos</span>
                        </a>
                    </li>

                    <!-- ── Ubicaciones ── -->
                    <li class="pc-item pc-caption">
                        <label>Ubicaciones</label>
                        <i class="ti ti-news"></i>
                    </li>
                    <li class="pc-item">
                        <a href="../pages/login.html" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-map-pin"></i></span>
                            <span class="pc-mtext">Ubicaciones</span>
                        </a>
                    </li>
                    <li class="pc-item">
                        <a href="../pages/login.html" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-arrow-right"></i></span>
                            <span class="pc-mtext">Traslados</span>
                        </a>
                    </li>

                    <!-- ── Operaciones ── -->
                    <li class="pc-item pc-caption">
                        <label>Operaciones</label>
                        <i class="ti ti-news"></i>
                    </li>
                    <li class="pc-item pc-hasmenu">
                        <a href="#!" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-package"></i></span>
                            <span class="pc-mtext">Inventario</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item"><a class="pc-link" href="#!">Kardex</a></li>
                            <li class="pc-item"><a class="pc-link" href="#!">Activos Fijos</a></li>
                            <li class="pc-item"><a class="pc-link" href="#!">Insumos gastos</a></li>
                        </ul>
                    </li>
                    <li class="pc-item pc-hasmenu">
                        <a href="#!" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-tool"></i></span>
                            <span class="pc-mtext">Mantenimientos</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item"><a class="pc-link" href="#!">Creación Mantenimientos</a></li>
                            <li class="pc-item"><a class="pc-link" href="#!">Auditoría Mantenimientos</a></li>
                            <li class="pc-item"><a class="pc-link" href="#!">Procedimiento operativo</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- [ Sidebar Menu ] end -->

    <!-- ═══════════════════════════════════════════════
         HEADER — Glassmorphism + acciones clave
         ═══════════════════════════════════════════════ -->
    <header class="pc-header">
        <div class="header-wrapper">
            <!-- [Left: Menu toggle + Search] -->
            <div class="me-auto pc-mob-drp">
                <ul class="list-unstyled">
                    <li class="pc-h-item pc-sidebar-collapse">
                        <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <li class="pc-h-item pc-sidebar-popup">
                        <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <!-- Search bar inline -->
                    <li class="pc-h-item d-none d-lg-inline-flex" style="margin-left: 8px;">
                        <div class="pw-header-search">
                            <i class="ti ti-search search-icon"></i>
                            <input type="text" placeholder="Buscar módulos, reportes..." aria-label="Buscar">
                            <kbd>⌘K</kbd>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- [Right: Notifications + User profile] -->
            <div class="ms-auto">
                <ul class="list-unstyled">
                    <!-- Notifications -->
                    <li class="dropdown pc-h-item">
                        <a class="pc-head-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-bell" style="font-size: 20px;"></i>
                            <span class="badge" style="background:var(--pw-error);position:absolute;top:6px;right:6px;min-width:18px;height:18px;font-size:10px;border:2px solid white;border-radius:50%;display:flex;align-items:center;justify-content:center;">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pc-h-dropdown" style="min-width: 320px;">
                            <div class="dropdown-header d-flex align-items-center justify-content-between" style="padding: 12px 16px;">
                                <h6 class="m-0" style="font-family: var(--pw-font-display); font-weight: 600;">Notificaciones</h6>
                                <a href="#!" style="font-size: 12px; color: var(--pw-ocean-bright); text-decoration: none;">Marcar todas leídas</a>
                            </div>
                            <div style="max-height: 280px; overflow-y: auto;">
                                <a href="#!" class="dropdown-item" style="white-space: normal; padding: 12px 16px;">
                                    <div class="d-flex align-items-start gap-3">
                                        <div style="width:36px;height:36px;border-radius:8px;background:rgba(0,194,224,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="ti ti-alert-circle" style="color:var(--pw-cyan-vivid);"></i>
                                        </div>
                                        <div>
                                            <p class="mb-1" style="font-size:13px;font-weight:500;">Nivel de oxígeno bajo en Estanque #3</p>
                                            <span style="font-size:11px;color:var(--pw-text-muted);">Hace 5 minutos</span>
                                        </div>
                                    </div>
                                </a>
                                <a href="#!" class="dropdown-item" style="white-space: normal; padding: 12px 16px;">
                                    <div class="d-flex align-items-start gap-3">
                                        <div style="width:36px;height:36px;border-radius:8px;background:var(--pw-success-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="ti ti-check" style="color:var(--pw-success);"></i>
                                        </div>
                                        <div>
                                            <p class="mb-1" style="font-size:13px;font-weight:500;">Reporte mensual generado</p>
                                            <span style="font-size:11px;color:var(--pw-text-muted);">Hace 1 hora</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="text-center" style="padding:10px;border-top:1px solid var(--pw-border-light);">
                                <a href="#!" style="font-size:13px;color:var(--pw-ocean-bright);text-decoration:none;font-weight:500;">Ver todas las notificaciones</a>
                            </div>
                        </div>
                    </li>

                    <!-- Help -->
                    <li class="pc-h-item d-none d-md-inline-flex">
                        <a href="#!" class="pc-head-link" title="Centro de ayuda">
                            <i class="ti ti-help-circle" style="font-size: 20px;"></i>
                        </a>
                    </li>

                    <!-- User Profile -->
                    <li class="dropdown pc-h-item header-user-profile">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                            <img src="../public/assets/plantilla/images/user/avatar-2.jpg" alt="user-image" class="user-avtar">
                            <span>Diego Arenas</span>
                        </a>
                        <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown" style="min-width: 280px;">
                            <div class="dropdown-header" style="padding: 16px; background: linear-gradient(135deg, var(--pw-ocean-deep), var(--pw-ocean-mid)); border-radius: 8px 8px 0 0;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <img src="../public/assets/plantilla/images/user/avatar-2.jpg" alt="user-image"
                                            class="user-avtar wid-35" style="border: 2px solid rgba(255,255,255,0.3); border-radius: 50%;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0" style="color: white; font-family: var(--pw-font-display); font-weight: 600;">Diego Arenas</h6>
                                        <span style="color: var(--pw-cyan-vivid); font-size: 12px;">Administrador</span>
                                    </div>
                                </div>
                            </div>
                            <div style="padding: 8px;">
                                <a href="#!" class="dropdown-item" style="border-radius: 6px; padding: 10px 12px;">
                                    <i class="ti ti-user" style="margin-right: 10px; color: var(--pw-gray-500);"></i>
                                    <span>Mi Perfil</span>
                                </a>
                                <a href="#!" class="dropdown-item" style="border-radius: 6px; padding: 10px 12px;">
                                    <i class="ti ti-settings" style="margin-right: 10px; color: var(--pw-gray-500);"></i>
                                    <span>Configuración</span>
                                </a>
                                <a href="#!" class="dropdown-item" style="border-radius: 6px; padding: 10px 12px;">
                                    <i class="ti ti-help" style="margin-right: 10px; color: var(--pw-gray-500);"></i>
                                    <span>Soporte</span>
                                </a>
                                <hr style="margin: 8px 0; border-color: var(--pw-border-light);">
                                <a href="#!" class="dropdown-item" style="border-radius: 6px; padding: 10px 12px; color: var(--pw-error);">
                                    <i class="ti ti-power" style="margin-right: 10px;"></i>
                                    <span>Cerrar Sesión</span>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">