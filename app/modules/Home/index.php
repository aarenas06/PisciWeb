 
<!-- ═══════════════════════════════════════════════════════════
     DASHBOARD HOME — PisciWEB
     Gestión Inteligente de Activos Fijos
     ═══════════════════════════════════════════════════════════ -->

<!-- Leaflet CSS for Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Dashboard-specific styles -->
<link rel="stylesheet" href="../public/assets/css/dashboard-home.css">

<!-- ── Page Header / Breadcrumb ── -->
<div class="pw-page-header pw-animate-in">
    <div>
        <h1 class="pw-page-title">Dashboard</h1>
        <div class="pw-header-breadcrumb">
            <a href="#!"><i class="ti ti-home" style="font-size:14px"></i></a>
            <span class="separator">/</span>
            <span>Inicio</span>
        </div>
    </div>
    <div class="pw-page-header-actions">
        <div class="pw-date-badge">
            <i class="ti ti-calendar"></i>
            <span id="currentDate"></span>
        </div>
        <button class="pw-btn pw-btn-secondary pw-btn-sm" title="Exportar datos">
            <i class="ti ti-download"></i>
            <span class="d-none d-md-inline">Exportar</span>
        </button>
        <button class="pw-btn pw-btn-primary pw-btn-sm" title="Nuevo activo">
            <i class="ti ti-plus"></i>
            <span class="d-none d-md-inline">Nuevo Activo</span>
        </button>
    </div>
</div>

<!-- ═══════════════════════════════
     ROW 1 — KPI CARDS (5 métricas)
     ═══════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- KPI: Total Activos -->
    <div class="col-xl col-md-4 col-sm-6">
        <div class="pw-kpi-card pw-animate-in pw-animate-delay-1">
            <div class="pw-kpi-icon cyan">
                <i class="ti ti-box"></i>
            </div>
            <div class="pw-kpi-body">
                <span class="pw-kpi-value" data-target="1248">0</span>
                <span class="pw-kpi-label">Total Activos</span>
            </div>
            <div class="pw-kpi-trend up">
                <i class="ti ti-trending-up"></i> +12.5%
            </div>
        </div>
    </div>

    <!-- KPI: Activos Disponibles -->
    <div class="col-xl col-md-4 col-sm-6">
        <div class="pw-kpi-card pw-animate-in pw-animate-delay-2">
            <div class="pw-kpi-icon success">
                <i class="ti ti-circle-check"></i>
            </div>
            <div class="pw-kpi-body">
                <span class="pw-kpi-value" data-target="1076">0</span>
                <span class="pw-kpi-label">Disponibles</span>
            </div>
            <div class="pw-kpi-trend up">
                <i class="ti ti-trending-up"></i> +3.2%
            </div>
        </div>
    </div>

    <!-- KPI: En Mantenimiento -->
    <div class="col-xl col-md-4 col-sm-6">
        <div class="pw-kpi-card pw-animate-in pw-animate-delay-3">
            <div class="pw-kpi-icon warning">
                <i class="ti ti-tool"></i>
            </div>
            <div class="pw-kpi-body">
                <span class="pw-kpi-value" data-target="84">0</span>
                <span class="pw-kpi-label">En Mantenimiento</span>
            </div>
            <div class="pw-kpi-trend down">
                <i class="ti ti-trending-down"></i> -5.1%
            </div>
        </div>
    </div>

    <!-- KPI: Centros Productivos -->
    <div class="col-xl col-md-6 col-sm-6">
        <div class="pw-kpi-card pw-animate-in pw-animate-delay-4">
            <div class="pw-kpi-icon ocean">
                <i class="ti ti-building"></i>
            </div>
            <div class="pw-kpi-body">
                <span class="pw-kpi-value" data-target="18">0</span>
                <span class="pw-kpi-label">Centros Productivos</span>
            </div>
            <div class="pw-kpi-trend neutral">
                <i class="ti ti-minus"></i> 0%
            </div>
        </div>
    </div>

    <!-- KPI: Alertas Activas -->
    <div class="col-xl col-md-6 col-sm-6">
        <div class="pw-kpi-card pw-animate-in pw-animate-delay-5" data-alert="true">
            <div class="pw-kpi-icon error">
                <i class="ti ti-alert-triangle"></i>
            </div>
            <div class="pw-kpi-body">
                <span class="pw-kpi-value" data-target="7">0</span>
                <span class="pw-kpi-label">Alertas Activas</span>
            </div>
            <div class="pw-kpi-trend down" style="color: var(--pw-error);">
                <i class="ti ti-trending-up"></i> +2
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════
     ROW 2 — MAPA + Distribución
     ═══════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Mapa Interactivo -->
    <div class="col-xl-8 col-lg-7">
        <div class="card pw-animate-in" style="overflow:hidden;">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0" style="font-family:var(--pw-font-display);font-weight:600;">
                        <i class="ti ti-map-pin me-2" style="color:var(--pw-cyan-vivid)"></i>
                        Distribución Geográfica
                    </h6>
                    <span style="font-size:12px;color:var(--pw-text-muted);">Ubicaciones, fincas y centros productivos</span>
                </div>
                <div class="d-flex gap-2">
                    <button class="pw-btn pw-btn-ghost pw-btn-sm pw-map-filter active" data-filter="all">Todos</button>
                    <button class="pw-btn pw-btn-ghost pw-btn-sm pw-map-filter" data-filter="farms">Fincas</button>
                    <button class="pw-btn pw-btn-ghost pw-btn-sm pw-map-filter" data-filter="centers">Centros</button>
                </div>
            </div>
            <div class="card-body p-0" style="position:relative;">
                <div id="dashboardMap" style="height: 420px; width: 100%; border-radius: 0 0 12px 12px;"></div>
                <!-- Map legend overlay -->
                <div class="pw-map-legend">
                    <div class="pw-legend-item">
                        <span class="pw-legend-dot" style="background:var(--pw-cyan-vivid)"></span> Fincas
                    </div>
                    <div class="pw-legend-item">
                        <span class="pw-legend-dot" style="background:var(--pw-ocean-bright)"></span> Centros Logísticos
                    </div>
                    <div class="pw-legend-item">
                        <span class="pw-legend-dot" style="background:var(--pw-success)"></span> Sedes
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución de Activos por Tipo -->
    <div class="col-xl-4 col-lg-5">
        <div class="card pw-animate-in h-100">
            <div class="card-header">
                <h6 class="mb-0" style="font-family:var(--pw-font-display);font-weight:600;">
                    <i class="ti ti-chart-pie me-2" style="color:var(--pw-ocean-bright)"></i>
                    Activos por Categoría
                </h6>
            </div>
            <div class="card-body d-flex flex-column">
                <div id="chartAssetsByType" style="flex:1; min-height:240px;"></div>
                <!-- Category mini-list -->
                <div class="pw-category-list mt-3">
                    <div class="pw-category-item">
                        <span class="pw-cat-dot" style="background:#00c2e0"></span>
                        <span class="pw-cat-name">Equipos</span>
                        <span class="pw-cat-value">412</span>
                        <span class="pw-cat-pct">33%</span>
                    </div>
                    <div class="pw-category-item">
                        <span class="pw-cat-dot" style="background:#1a7fc4"></span>
                        <span class="pw-cat-name">Maquinaria</span>
                        <span class="pw-cat-value">328</span>
                        <span class="pw-cat-pct">26%</span>
                    </div>
                    <div class="pw-category-item">
                        <span class="pw-cat-dot" style="background:#3ac6c6"></span>
                        <span class="pw-cat-name">Vehículos</span>
                        <span class="pw-cat-value">236</span>
                        <span class="pw-cat-pct">19%</span>
                    </div>
                    <div class="pw-category-item">
                        <span class="pw-cat-dot" style="background:#10b981"></span>
                        <span class="pw-cat-name">Infraestructura</span>
                        <span class="pw-cat-value">172</span>
                        <span class="pw-cat-pct">14%</span>
                    </div>
                    <div class="pw-category-item">
                        <span class="pw-cat-dot" style="background:#94a3b8"></span>
                        <span class="pw-cat-name">Otros</span>
                        <span class="pw-cat-value">100</span>
                        <span class="pw-cat-pct">8%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════
     ROW 3 — Charts (Tendencia + Mantenimiento)
     ═══════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Tendencia de Activos (12 meses) -->
    <div class="col-xl-8 col-lg-7">
        <div class="card pw-animate-in">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0" style="font-family:var(--pw-font-display);font-weight:600;">
                        <i class="ti ti-chart-line me-2" style="color:var(--pw-cyan-vivid)"></i>
                        Tendencia de Activos
                    </h6>
                    <span style="font-size:12px;color:var(--pw-text-muted);">Adquisiciones vs Bajas — Últimos 12 meses</span>
                </div>
                <div class="pw-chart-period">
                    <button class="pw-btn pw-btn-ghost pw-btn-sm pw-period-btn" data-period="6m">6M</button>
                    <button class="pw-btn pw-btn-ghost pw-btn-sm pw-period-btn active" data-period="12m">12M</button>
                    <button class="pw-btn pw-btn-ghost pw-btn-sm pw-period-btn" data-period="ytd">YTD</button>
                </div>
            </div>
            <div class="card-body">
                <div id="chartAssetsTrend" style="height: 320px;"></div>
            </div>
        </div>
    </div>

    <!-- Estado de Mantenimientos -->
    <div class="col-xl-4 col-lg-5">
        <div class="card pw-animate-in h-100">
            <div class="card-header">
                <h6 class="mb-0" style="font-family:var(--pw-font-display);font-weight:600;">
                    <i class="ti ti-settings me-2" style="color:var(--pw-warning)"></i>
                    Mantenimientos
                </h6>
            </div>
            <div class="card-body">
                <div id="chartMaintenance" style="height: 200px;"></div>

                <!-- Maintenance summary -->
                <div class="pw-maint-summary mt-3">
                    <div class="pw-maint-item">
                        <div class="pw-maint-left">
                            <span class="pw-maint-dot" style="background:var(--pw-success)"></span>
                            <span>Completados</span>
                        </div>
                        <span class="pw-maint-val">42</span>
                    </div>
                    <div class="pw-maint-item">
                        <div class="pw-maint-left">
                            <span class="pw-maint-dot" style="background:var(--pw-warning)"></span>
                            <span>En Progreso</span>
                        </div>
                        <span class="pw-maint-val">18</span>
                    </div>
                    <div class="pw-maint-item">
                        <div class="pw-maint-left">
                            <span class="pw-maint-dot" style="background:var(--pw-error)"></span>
                            <span>Pendientes</span>
                        </div>
                        <span class="pw-maint-val">7</span>
                    </div>
                    <div class="pw-maint-item">
                        <div class="pw-maint-left">
                            <span class="pw-maint-dot" style="background:var(--pw-gray-300)"></span>
                            <span>Programados</span>
                        </div>
                        <span class="pw-maint-val">24</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════
     ROW 4 — Por Centro + Actividad Reciente
     ═══════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Activos por Centro Productivo (Barras Horizontales) -->
    <div class="col-xl-5 col-lg-6">
        <div class="card pw-animate-in h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0" style="font-family:var(--pw-font-display);font-weight:600;">
                    <i class="ti ti-building me-2" style="color:var(--pw-teal-soft)"></i>
                    Activos por Centro
                </h6>
                <a href="#!" class="pw-btn pw-btn-ghost pw-btn-sm">Ver todos</a>
            </div>
            <div class="card-body">
                <div id="chartByCenter" style="height: 320px;"></div>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="col-xl-7 col-lg-6">
        <div class="card pw-animate-in h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0" style="font-family:var(--pw-font-display);font-weight:600;">
                    <i class="ti ti-activity me-2" style="color:var(--pw-ocean-bright)"></i>
                    Actividad Reciente
                </h6>
                <a href="#!" class="pw-btn pw-btn-ghost pw-btn-sm">Ver historial</a>
            </div>
            <div class="card-body p-0">
                <div class="pw-activity-list">

                    <div class="pw-activity-item">
                        <div class="pw-activity-icon" style="background:var(--pw-success-light);color:var(--pw-success);">
                            <i class="ti ti-check"></i>
                        </div>
                        <div class="pw-activity-content">
                            <p class="pw-activity-text">
                                <strong>Mantenimiento completado</strong> — Bomba centrífuga #BM-042
                            </p>
                            <span class="pw-activity-meta">
                                <i class="ti ti-map-pin"></i> Finca La Esperanza
                                <span class="pw-activity-sep">·</span>
                                Hace 15 min
                            </span>
                        </div>
                        <span class="pw-badge pw-badge-success">Completado</span>
                    </div>

                    <div class="pw-activity-item">
                        <div class="pw-activity-icon" style="background:rgba(0,194,224,0.1);color:var(--pw-cyan-vivid);">
                            <i class="ti ti-transfer"></i>
                        </div>
                        <div class="pw-activity-content">
                            <p class="pw-activity-text">
                                <strong>Traslado de activo</strong> — Generador eléctrico #GE-018
                            </p>
                            <span class="pw-activity-meta">
                                <i class="ti ti-arrow-right"></i> Centro Norte → Finca San José
                                <span class="pw-activity-sep">·</span>
                                Hace 1 hora
                            </span>
                        </div>
                        <span class="pw-badge pw-badge-info">Traslado</span>
                    </div>

                    <div class="pw-activity-item">
                        <div class="pw-activity-icon" style="background:var(--pw-warning-light);color:var(--pw-warning);">
                            <i class="ti ti-alert-triangle"></i>
                        </div>
                        <div class="pw-activity-content">
                            <p class="pw-activity-text">
                                <strong>Alerta de mantenimiento</strong> — Motor fuera de borda #MF-007
                            </p>
                            <span class="pw-activity-meta">
                                <i class="ti ti-calendar"></i> Vence en 3 días
                                <span class="pw-activity-sep">·</span>
                                Hace 2 horas
                            </span>
                        </div>
                        <span class="pw-badge pw-badge-warning">Alerta</span>
                    </div>

                    <div class="pw-activity-item">
                        <div class="pw-activity-icon" style="background:rgba(26,127,196,0.1);color:var(--pw-ocean-bright);">
                            <i class="ti ti-plus"></i>
                        </div>
                        <div class="pw-activity-content">
                            <p class="pw-activity-text">
                                <strong>Nuevo activo registrado</strong> — Aireador mecánico #AM-156
                            </p>
                            <span class="pw-activity-meta">
                                <i class="ti ti-user"></i> Diego Arenas
                                <span class="pw-activity-sep">·</span>
                                Hace 4 horas
                            </span>
                        </div>
                        <span class="pw-badge pw-badge-info">Nuevo</span>
                    </div>

                    <div class="pw-activity-item">
                        <div class="pw-activity-icon" style="background:var(--pw-error-light);color:var(--pw-error);">
                            <i class="ti ti-alert-circle"></i>
                        </div>
                        <div class="pw-activity-content">
                            <p class="pw-activity-text">
                                <strong>Activo dado de baja</strong> — Tanque reservorio #TR-003
                            </p>
                            <span class="pw-activity-meta">
                                <i class="ti ti-building"></i> Centro Sur
                                <span class="pw-activity-sep">·</span>
                                Ayer, 16:30
                            </span>
                        </div>
                        <span class="pw-badge pw-badge-error">Baja</span>
                    </div>

                    <div class="pw-activity-item">
                        <div class="pw-activity-icon" style="background:var(--pw-success-light);color:var(--pw-success);">
                            <i class="ti ti-clipboard-check"></i>
                        </div>
                        <div class="pw-activity-content">
                            <p class="pw-activity-text">
                                <strong>Auditoría finalizada</strong> — Centro Productivo Norte
                            </p>
                            <span class="pw-activity-meta">
                                <i class="ti ti-user"></i> Carlos Mendoza
                                <span class="pw-activity-sep">·</span>
                                Ayer, 10:15
                            </span>
                        </div>
                        <span class="pw-badge pw-badge-success">Auditoría</span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════
     ROW 5 — Quick Actions + Top Activos
     ═══════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Acciones Rápidas -->
    <div class="col-xl-4 col-lg-5">
        <div class="card pw-animate-in h-100">
            <div class="card-header">
                <h6 class="mb-0" style="font-family:var(--pw-font-display);font-weight:600;">
                    <i class="ti ti-bolt me-2" style="color:var(--pw-warning)"></i>
                    Acciones Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="pw-quick-actions">
                    <a href="#!" class="pw-quick-action">
                        <div class="pw-qa-icon" style="background:rgba(0,194,224,0.1);color:var(--pw-cyan-vivid);">
                            <i class="ti ti-plus"></i>
                        </div>
                        <div>
                            <strong>Registrar Activo</strong>
                            <span>Agregar nuevo activo fijo</span>
                        </div>
                        <i class="ti ti-chevron-right pw-qa-arrow"></i>
                    </a>
                    <a href="#!" class="pw-quick-action">
                        <div class="pw-qa-icon" style="background:var(--pw-warning-light);color:var(--pw-warning);">
                            <i class="ti ti-tool"></i>
                        </div>
                        <div>
                            <strong>Programar Mantenimiento</strong>
                            <span>Crear orden de trabajo</span>
                        </div>
                        <i class="ti ti-chevron-right pw-qa-arrow"></i>
                    </a>
                    <a href="#!" class="pw-quick-action">
                        <div class="pw-qa-icon" style="background:rgba(26,127,196,0.1);color:var(--pw-ocean-bright);">
                            <i class="ti ti-transfer"></i>
                        </div>
                        <div>
                            <strong>Trasladar Activo</strong>
                            <span>Mover entre ubicaciones</span>
                        </div>
                        <i class="ti ti-chevron-right pw-qa-arrow"></i>
                    </a>
                    <a href="#!" class="pw-quick-action">
                        <div class="pw-qa-icon" style="background:var(--pw-success-light);color:var(--pw-success);">
                            <i class="ti ti-file-analytics"></i>
                        </div>
                        <div>
                            <strong>Generar Reporte</strong>
                            <span>Exportar datos y análisis</span>
                        </div>
                        <i class="ti ti-chevron-right pw-qa-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Activos que requieren atención -->
    <div class="col-xl-8 col-lg-7">
        <div class="card pw-animate-in h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0" style="font-family:var(--pw-font-display);font-weight:600;">
                    <i class="ti ti-urgent me-2" style="color:var(--pw-error)"></i>
                    Activos que Requieren Atención
                </h6>
                <a href="#!" class="pw-btn pw-btn-ghost pw-btn-sm">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="pw-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Activo</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code style="color:var(--pw-ocean-bright);background:rgba(0,194,224,0.06);padding:2px 8px;border-radius:4px;font-size:12px;">BM-042</code></td>
                                <td>Bomba centrífuga</td>
                                <td>Finca La Esperanza</td>
                                <td><span class="pw-badge pw-badge-error">Crítico</span></td>
                                <td>
                                    <div class="pw-priority-bar high">
                                        <div class="pw-priority-fill" style="width:90%"></div>
                                    </div>
                                </td>
                                <td><button class="pw-btn pw-btn-primary pw-btn-sm">Atender</button></td>
                            </tr>
                            <tr>
                                <td><code style="color:var(--pw-ocean-bright);background:rgba(0,194,224,0.06);padding:2px 8px;border-radius:4px;font-size:12px;">MF-007</code></td>
                                <td>Motor fuera de borda</td>
                                <td>Centro Norte</td>
                                <td><span class="pw-badge pw-badge-warning">Alerta</span></td>
                                <td>
                                    <div class="pw-priority-bar medium">
                                        <div class="pw-priority-fill" style="width:65%"></div>
                                    </div>
                                </td>
                                <td><button class="pw-btn pw-btn-secondary pw-btn-sm">Revisar</button></td>
                            </tr>
                            <tr>
                                <td><code style="color:var(--pw-ocean-bright);background:rgba(0,194,224,0.06);padding:2px 8px;border-radius:4px;font-size:12px;">AE-023</code></td>
                                <td>Aireador eléctrico</td>
                                <td>Finca San José</td>
                                <td><span class="pw-badge pw-badge-warning">Alerta</span></td>
                                <td>
                                    <div class="pw-priority-bar medium">
                                        <div class="pw-priority-fill" style="width:55%"></div>
                                    </div>
                                </td>
                                <td><button class="pw-btn pw-btn-secondary pw-btn-sm">Revisar</button></td>
                            </tr>
                            <tr>
                                <td><code style="color:var(--pw-ocean-bright);background:rgba(0,194,224,0.06);padding:2px 8px;border-radius:4px;font-size:12px;">TR-011</code></td>
                                <td>Tanque reservorio</td>
                                <td>Centro Sur</td>
                                <td><span class="pw-badge pw-badge-info">Revisión</span></td>
                                <td>
                                    <div class="pw-priority-bar low">
                                        <div class="pw-priority-fill" style="width:30%"></div>
                                    </div>
                                </td>
                                <td><button class="pw-btn pw-btn-ghost pw-btn-sm">Programar</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ External Libraries ═══ -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- ═══ Dashboard Home JS ═══ -->
<script src="../public/assets/js/dashboard-home.js"></script>

