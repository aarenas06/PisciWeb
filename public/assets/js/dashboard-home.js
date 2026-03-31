/**
 * PisciWEB — Dashboard Home
 * Charts (ApexCharts) + Map (Leaflet) + Animations
 *
 * Datos de ejemplo (mock) — Reemplazar con datos reales desde BD.
 * Cada sección incluye un comentario '// @DB:' explicando qué consulta alimentaría esa métrica.
 */

document.addEventListener('DOMContentLoaded', function () {
    initCurrentDate();
    initCountUp();
    initAssetsTrendChart();
    initAssetsByTypeChart();
    initMaintenanceChart();
    initByCenterChart();
    initDashboardMap();
    initMapFilters();
    initPeriodButtons();
});

/* ================================================================
   CURRENT DATE
   ================================================================ */
function initCurrentDate() {
    var el = document.getElementById('currentDate');
    if (!el) return;
    var now = new Date();
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    el.textContent = now.toLocaleDateString('es-CO', options);
}

/* ================================================================
   COUNT-UP ANIMATION for KPI values
   ================================================================ */
function initCountUp() {
    var els = document.querySelectorAll('.pw-kpi-value[data-target]');
    els.forEach(function (el) {
        var target = parseInt(el.getAttribute('data-target'), 10);
        animateValue(el, 0, target, 1200);
    });
}

function animateValue(el, start, end, duration) {
    var range = end - start;
    var startTime = null;
    function step(timestamp) {
        if (!startTime) startTime = timestamp;
        var progress = Math.min((timestamp - startTime) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
        el.textContent = Math.floor(start + range * eased).toLocaleString('es-CO');
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    }
    requestAnimationFrame(step);
}

/* ================================================================
   CHART 1 — Tendencia de Activos (Área + Línea)
   // @DB: SELECT MONTH(fecha), COUNT(*) FROM activos GROUP BY MONTH(fecha) — últimos 12 meses
   ================================================================ */
function initAssetsTrendChart() {
    var el = document.getElementById('chartAssetsTrend');
    if (!el || typeof ApexCharts === 'undefined') return;

    var options = {
        series: [
            {
                name: 'Adquisiciones',
                type: 'area',
                data: [28, 35, 42, 38, 55, 62, 48, 72, 60, 85, 78, 92]
            },
            {
                name: 'Bajas',
                type: 'line',
                data: [5, 8, 3, 6, 4, 10, 7, 5, 9, 6, 8, 4]
            }
        ],
        chart: {
            height: 320,
            type: 'area',
            fontFamily: "'DM Sans', 'Public Sans', sans-serif",
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        colors: ['#00c2e0', '#ef4444'],
        fill: {
            type: ['gradient', 'solid'],
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.3,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        stroke: {
            width: [2.5, 2],
            curve: 'smooth'
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: ['Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic', 'Ene', 'Feb', 'Mar'],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: {
                    colors: '#94a3b8',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#94a3b8',
                    fontSize: '12px'
                }
            }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4,
            padding: { left: 8, right: 8 }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            fontSize: '12px',
            fontWeight: 500,
            markers: { radius: 3 },
            itemMargin: { horizontal: 12 }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (val) {
                    return val + ' activos';
                }
            }
        }
    };

    new ApexCharts(el, options).render();
}

/* ================================================================
   CHART 2 — Activos por Categoría (Donut)
   // @DB: SELECT categoria, COUNT(*) FROM activos GROUP BY categoria
   ================================================================ */
function initAssetsByTypeChart() {
    var el = document.getElementById('chartAssetsByType');
    if (!el || typeof ApexCharts === 'undefined') return;

    var options = {
        series: [412, 328, 236, 172, 100],
        chart: {
            type: 'donut',
            height: 260,
            fontFamily: "'DM Sans', 'Public Sans', sans-serif"
        },
        labels: ['Equipos', 'Maquinaria', 'Vehículos', 'Infraestructura', 'Otros'],
        colors: ['#00c2e0', '#1a7fc4', '#3ac6c6', '#10b981', '#94a3b8'],
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '13px',
                            fontWeight: 500,
                            color: '#64748b'
                        },
                        value: {
                            show: true,
                            fontSize: '22px',
                            fontWeight: 700,
                            fontFamily: "'Outfit', sans-serif",
                            color: '#0f172a',
                            formatter: function (val) {
                                return parseInt(val).toLocaleString('es-CO');
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '13px',
                            fontWeight: 500,
                            color: '#94a3b8',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0).toLocaleString('es-CO');
                            }
                        }
                    }
                }
            }
        },
        stroke: { width: 2, colors: ['#fff'] },
        dataLabels: { enabled: false },
        legend: { show: false },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + ' activos';
                }
            }
        }
    };

    new ApexCharts(el, options).render();
}

/* ================================================================
   CHART 3 — Estado de Mantenimientos (Radial Bar)
   // @DB: SELECT estado, COUNT(*) FROM mantenimientos WHERE fecha >= CURDATE() - INTERVAL 30 DAY GROUP BY estado
   ================================================================ */
function initMaintenanceChart() {
    var el = document.getElementById('chartMaintenance');
    if (!el || typeof ApexCharts === 'undefined') return;

    var completed = 42, inProgress = 18, pending = 7, scheduled = 24;
    var total = completed + inProgress + pending + scheduled;

    var options = {
        series: [
            Math.round(completed / total * 100),
            Math.round(inProgress / total * 100),
            Math.round(pending / total * 100),
            Math.round(scheduled / total * 100)
        ],
        chart: {
            type: 'radialBar',
            height: 200,
            fontFamily: "'DM Sans', 'Public Sans', sans-serif"
        },
        plotOptions: {
            radialBar: {
                hollow: { size: '35%' },
                track: {
                    background: '#f1f5f9',
                    strokeWidth: '100%'
                },
                dataLabels: {
                    name: { fontSize: '12px', color: '#94a3b8' },
                    value: {
                        fontSize: '18px',
                        fontFamily: "'Outfit', sans-serif",
                        fontWeight: 700,
                        color: '#0f172a',
                        formatter: function (val) { return val + '%'; }
                    },
                    total: {
                        show: true,
                        label: 'Cumplimiento',
                        fontSize: '11px',
                        color: '#94a3b8',
                        formatter: function () {
                            return Math.round(completed / total * 100) + '%';
                        }
                    }
                }
            }
        },
        labels: ['Completados', 'En Progreso', 'Pendientes', 'Programados'],
        colors: ['#10b981', '#f59e0b', '#ef4444', '#cbd5e1'],
        stroke: { lineCap: 'round' }
    };

    new ApexCharts(el, options).render();
}

/* ================================================================
   CHART 4 — Activos por Centro Productivo (Barras Horizontales)
   // @DB: SELECT c.nombre, COUNT(a.id) FROM centros c JOIN activos a ON a.centro_id = c.id GROUP BY c.id ORDER BY COUNT(a.id) DESC LIMIT 8
   ================================================================ */
function initByCenterChart() {
    var el = document.getElementById('chartByCenter');
    if (!el || typeof ApexCharts === 'undefined') return;

    var options = {
        series: [{
            name: 'Activos',
            data: [187, 156, 142, 128, 112, 98, 84, 72]
        }],
        chart: {
            type: 'bar',
            height: 320,
            fontFamily: "'DM Sans', 'Public Sans', sans-serif",
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 6,
                barHeight: '55%',
                distributed: false,
                dataLabels: { position: 'top' }
            }
        },
        colors: ['#00c2e0'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'horizontal',
                gradientToColors: ['#1a7fc4'],
                stops: [0, 100]
            }
        },
        dataLabels: {
            enabled: true,
            offsetX: 30,
            style: {
                fontSize: '12px',
                fontWeight: 600,
                colors: ['#0f172a']
            },
            formatter: function (val) {
                return val.toLocaleString('es-CO');
            }
        },
        xaxis: {
            categories: [
                'Centro Norte',
                'Finca La Esperanza',
                'Centro Sur',
                'Finca San José',
                'Centro Oriente',
                'Finca El Paraíso',
                'Sede Principal',
                'Centro Occidente'
            ],
            labels: {
                style: {
                    colors: '#94a3b8',
                    fontSize: '12px'
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                },
                maxWidth: 140
            }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4,
            xaxis: { lines: { show: true } },
            yaxis: { lines: { show: false } }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + ' activos';
                }
            }
        }
    };

    new ApexCharts(el, options).render();
}

/* ================================================================
   LEAFLET MAP — Distribución geográfica
   // @DB: SELECT nombre, tipo, latitud, longitud, total_activos FROM ubicaciones WHERE activo = 1
   ================================================================ */

// Mock location data — replace with real coordinates from DB
var mapLocations = [
    { name: 'Centro Norte',         type: 'center', lat: 7.12,  lng: -73.12, assets: 187, status: 'Operativo' },
    { name: 'Finca La Esperanza',   type: 'farm',   lat: 6.85,  lng: -73.35, assets: 156, status: 'Operativo' },
    { name: 'Centro Sur',           type: 'center', lat: 6.25,  lng: -75.56, assets: 142, status: 'Operativo' },
    { name: 'Finca San José',       type: 'farm',   lat: 5.07,  lng: -75.52, assets: 128, status: 'Operativo' },
    { name: 'Centro Oriente',       type: 'center', lat: 4.63,  lng: -74.07, assets: 112, status: 'Alerta' },
    { name: 'Finca El Paraíso',     type: 'farm',   lat: 3.45,  lng: -76.53, assets: 98,  status: 'Operativo' },
    { name: 'Sede Principal',       type: 'hq',     lat: 4.71,  lng: -74.07, assets: 84,  status: 'Operativo' },
    { name: 'Centro Occidente',     type: 'center', lat: 5.07,  lng: -75.50, assets: 72,  status: 'Mantenimiento' },
    { name: 'Finca Los Andes',      type: 'farm',   lat: 2.93,  lng: -75.28, assets: 65,  status: 'Operativo' },
    { name: 'Finca Río Claro',      type: 'farm',   lat: 5.89,  lng: -74.76, assets: 54,  status: 'Operativo' }
];

var dashboardMap = null;
var mapMarkers = [];

function initDashboardMap() {
    var container = document.getElementById('dashboardMap');
    if (!container || typeof L === 'undefined') return;

    dashboardMap = L.map('dashboardMap', {
        center: [5.0, -74.5],
        zoom: 6,
        zoomControl: true,
        scrollWheelZoom: false
    });

    // Use a clean, modern tile layer
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://carto.com/">CARTO</a>',
        maxZoom: 18
    }).addTo(dashboardMap);

    // Add markers
    mapLocations.forEach(function (loc) {
        var marker = createMarker(loc);
        marker.locationData = loc;
        mapMarkers.push(marker);
    });

    // Fix map rendering when container is resized
    setTimeout(function () {
        if (dashboardMap) dashboardMap.invalidateSize();
    }, 300);
}

function createMarker(loc) {
    var colorMap = {
        farm: '#00c2e0',
        center: '#1a7fc4',
        hq: '#10b981'
    };
    var typeLabels = {
        farm: 'Finca',
        center: 'Centro Logístico',
        hq: 'Sede'
    };
    var statusColors = {
        'Operativo': '#10b981',
        'Alerta': '#f59e0b',
        'Mantenimiento': '#ef4444'
    };

    var color = colorMap[loc.type] || '#94a3b8';
    var icon = L.divIcon({
        className: 'pw-map-marker',
        html: '<div style="' +
            'width:14px;height:14px;border-radius:50%;' +
            'background:' + color + ';' +
            'border:3px solid white;' +
            'box-shadow:0 2px 8px rgba(0,0,0,0.25);' +
            'transition:transform 0.2s;' +
            '"></div>',
        iconSize: [14, 14],
        iconAnchor: [7, 7]
    });

    var statusCol = statusColors[loc.status] || '#94a3b8';

    var popupContent =
        '<div class="pw-popup">' +
            '<div class="pw-popup-title">' + escapeHtml(loc.name) + '</div>' +
            '<div class="pw-popup-meta">' +
                '<span><strong>Tipo:</strong> ' + escapeHtml(typeLabels[loc.type] || loc.type) + '</span>' +
                '<span><strong>Activos:</strong> ' + loc.assets.toLocaleString('es-CO') + '</span>' +
            '</div>' +
            '<span class="pw-popup-badge" style="background:' + statusCol + '22;color:' + statusCol + ';">' +
                '<i class="ti ti-circle-filled" style="font-size:8px;"></i> ' + escapeHtml(loc.status) +
            '</span>' +
        '</div>';

    var marker = L.marker([loc.lat, loc.lng], { icon: icon })
        .addTo(dashboardMap)
        .bindPopup(popupContent, { closeButton: false, offset: [0, -4] });

    marker.on('mouseover', function () { this.openPopup(); });

    return marker;
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

/* ================================================================
   MAP FILTER BUTTONS
   ================================================================ */
function initMapFilters() {
    var buttons = document.querySelectorAll('.pw-map-filter');
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            buttons.forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            var filter = this.getAttribute('data-filter');
            filterMapMarkers(filter);
        });
    });
}

function filterMapMarkers(filter) {
    if (!dashboardMap) return;

    mapMarkers.forEach(function (marker) {
        dashboardMap.removeLayer(marker);
    });
    mapMarkers = [];

    var filtered = mapLocations;
    if (filter === 'farms') {
        filtered = mapLocations.filter(function (l) { return l.type === 'farm'; });
    } else if (filter === 'centers') {
        filtered = mapLocations.filter(function (l) { return l.type === 'center' || l.type === 'hq'; });
    }

    filtered.forEach(function (loc) {
        var marker = createMarker(loc);
        marker.locationData = loc;
        mapMarkers.push(marker);
    });

    // Fit bounds
    if (mapMarkers.length > 0) {
        var group = L.featureGroup(mapMarkers);
        dashboardMap.fitBounds(group.getBounds().pad(0.15));
    }
}

/* ================================================================
   PERIOD TOGGLE (Tendencia chart)
   ================================================================ */
function initPeriodButtons() {
    var buttons = document.querySelectorAll('.pw-period-btn');
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            buttons.forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            // In production, re-fetch data based on data-period and re-render chart
        });
    });
}
