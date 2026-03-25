<?php

/**
 * Panel de Desarrolladores - PisciWeb
 * 
 * Visualización dinámica de Services y Helpers disponibles
 * Para uso exclusivo de desarrolladores
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\ServiceExplorer;

$explorer = new ServiceExplorer();
$services = $explorer->getAllServices();
$helpers = $explorer->getAllHelpers();
$modules = $explorer->getAllModules();
$dependencies = $explorer->getComposerDependencies();
$environment = $explorer->getEnvironmentInfo();
$stats = $explorer->getStats();

// Ordenar módulos por tamaño (más pesados primero)
usort($modules, function ($a, $b) {
    return $b['size'] - $a['size'];
});
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Desarrolladores - PisciWeb</title>
    <link rel="icon" type="image/png" href="https://discolmets.com.co/assets/img/logo_ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dev-tools.css">
</head>

<body>
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo">
                    <img src="https://discolmets.com.co/assets/img/logo_ico.png" alt="Discolmets" style="max-height: -webkit-fill-available">
                </div>
            </div>
            <div class="header-title">
                <h1>🛠️ PANEL DE DESARROLLADORES</h1>
                <p>Explorador Dinámico de Services y Helpers - PisciWeb</p>
            </div>
        </div>
    </header>

    <div class="welcome-section">
        <div class="welcome-card">
            <h2>Bienvenido al Panel de Desarrolladores</h2>
            <p>
                Esta herramienta proporciona documentación dinámica y completa sobre todos los <strong>Services</strong> y <strong>Helpers</strong>
                disponibles en PisciWeb. Aquí encontrarás información actualizada automáticamente sobre métodos, parámetros y ejemplos de uso.
            </p>
            <p>
                El panel escanea el código fuente en tiempo real para mostrarte siempre la información más actualizada,
                facilitando el desarrollo y la integración de nuevas funcionalidades.
            </p>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['services'] ?></div>
                    <div class="stat-label">Services</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['helpers'] ?></div>
                    <div class="stat-label">Helpers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['modules'] ?></div>
                    <div class="stat-label">Módulos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_methods'] ?></div>
                    <div class="stat-label">Métodos</div>
                </div>
            </div>
            <div class="stats" style="margin-top: 1.5rem;">
                <div class="stat-card">
                    <div class="stat-number"><?= count($dependencies) ?></div>
                    <div class="stat-label">Dependencias</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">PHP <?= $stats['php_version'] ?></div>
                    <div class="stat-label">Versión PHP</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $environment['memory_limit'] ?></div>
                    <div class="stat-label">Memoria PHP</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= count($environment['extensions']) ?></div>
                    <div class="stat-label">Extensiones PHP</div>
                </div>
            </div>
        </div>
    </div>

    <div class="api-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Buscar Services, Helpers o métodos...">
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('services')">📦 Services (<?= count($services) ?>)</button>
            <button class="tab" onclick="switchTab('helpers')">🔧 Helpers (<?= count($helpers) ?>)</button>
            <button class="tab" onclick="switchTab('modules')">📁 Módulos (<?= count($modules) ?>)</button>
            <button class="tab" onclick="switchTab('dependencies')">📚 Dependencias (<?= count($dependencies) ?>)</button>
            <button class="tab" onclick="switchTab('environment')">⚙️ Entorno</button>
        </div>

        <div class="content">
            <!-- SERVICES TAB -->
            <div id="services" class="tab-content active">
                <?php foreach ($services as $service): ?>
                    <div class="service-card" data-search="<?= strtolower($service['name'] . ' ' . $service['doc']) ?>">
                        <div class="service-header">
                            <div class="service-name"><?= $service['name'] ?></div>
                            <div class="service-badge"><?= $service['methodCount'] ?> métodos</div>
                        </div>

                        <?php if ($service['doc']): ?>
                            <div class="service-doc"><?= htmlspecialchars($service['doc']) ?></div>
                        <?php endif; ?>

                        <div class="service-usage">
                            <strong>Uso:</strong> $service = service('<?= $service['shortName'] ?>');
                        </div>

                        <div class="accordion" id="accordion<?= $service['shortName'] ?>">
                            <div class="accordion-item" style="border: none; background: transparent;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $service['shortName'] ?>" aria-expanded="false" style="background: linear-gradient(135deg, #f9f9f9 0%, #ffffff 100%); border-left: 4px solid #d4af37; border-radius: 8px; color: #1a1a1a; font-weight: 600; margin-top: 15px;">
                                        <span style="color: #d4af37; margin-right: 8px;">▶</span> Métodos disponibles (<?= count($service['methods']) ?>)
                                    </button>
                                </h2>
                                <div id="collapse<?= $service['shortName'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordion<?= $service['shortName'] ?>">
                                    <div class="accordion-body" style="padding: 15px 0;">
                                        <div class="methods">
                                            <?php foreach ($service['methods'] as $method): ?>
                                                <div class="method-item">
                                                    <div class="method-signature">
                                                        <span class="method-return"><?= $method['return'] ?></span>
                                                        <span class="method-name"><?= $method['name'] ?></span>(<span class="method-params"><?= htmlspecialchars($method['parameters']) ?></span>)
                                                    </div>
                                                    <?php if ($method['doc']): ?>
                                                        <div class="method-doc"><?= htmlspecialchars($method['doc']) ?></div>
                                                    <?php endif; ?>
                                                    <div class="example-box">
                                                        <div class="example-label">Ejemplo:</div>
                                                        <div class="example-code">service('<?= $service['shortName'] ?>')-><?= $method['name'] ?>()</div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="no-results" style="display: none;">
                    No se encontraron Services que coincidan con tu búsqueda.
                </div>
            </div>

            <!-- HELPERS TAB -->
            <div id="helpers" class="tab-content">
                <?php foreach ($helpers as $helper): ?>
                    <div class="helper-card" data-search="<?= strtolower($helper['name'] . ' ' . $helper['doc']) ?>">
                        <div class="helper-name"><?= $helper['name'] ?>()</div>

                        <?php if ($helper['doc']): ?>
                            <div class="service-doc"><?= htmlspecialchars($helper['doc']) ?></div>
                        <?php endif; ?>

                        <div class="helper-signature">
                            <strong>Firma:</strong> <?= $helper['name'] ?>(<?= htmlspecialchars($helper['parameters']) ?>)
                        </div>

                        <div class="example-box">
                            <div class="example-label">Ejemplo de uso:</div>
                            <div class="example-code"><?= htmlspecialchars($helper['usage']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="no-results" style="display: none;">
                    No se encontraron Helpers que coincidan con tu búsqueda.
                </div>
            </div>

            <!-- MÓDULOS TAB -->
            <div id="modules" class="tab-content">
                <?php foreach ($modules as $module): ?>
                    <div class="service-card" data-search="<?= strtolower($module['name'] . ' ' . $module['type']) ?>">
                        <div class="service-header">
                            <div class="service-name"><?= $module['name'] ?></div>
                            <div class="service-badge"><?= $module['type'] ?></div>
                        </div>

                        <div class="service-doc">
                            <strong>Ruta:</strong> <?= $module['path'] ?>
                            <span style="margin-left: 2rem; color: #d4af37; font-weight: bold;">
                                📦 Tamaño: <?= $module['sizeFormatted'] ?>
                            </span>
                        </div>

                        <div class="stats" style="margin-top: 1rem; gap: 1rem;">
                            <div class="stat-card" style="padding: 1rem;">
                                <div class="stat-number" style="font-size: 1.8rem;"><?= $module['totalFiles'] ?></div>
                                <div class="stat-label" style="font-size: 0.75rem;">Archivos PHP</div>
                            </div>
                            <div class="stat-card" style="padding: 1rem;">
                                <div class="stat-number" style="font-size: 1.8rem;"><?= $module['controllers'] ?></div>
                                <div class="stat-label" style="font-size: 0.75rem;">Controllers</div>
                            </div>
                            <div class="stat-card" style="padding: 1rem;">
                                <div class="stat-number" style="font-size: 1.8rem;"><?= $module['models'] ?></div>
                                <div class="stat-label" style="font-size: 0.75rem;">Models</div>
                            </div>
                            <div class="stat-card" style="padding: 1rem;">
                                <div class="stat-number" style="font-size: 1.8rem;"><?= $module['views'] ?></div>
                                <div class="stat-label" style="font-size: 0.75rem;">Views/Scripts</div>
                            </div>
                            <div class="stat-card" style="padding: 1rem; border-left-color: <?= $module['size'] > 500000 ? '#dc3545' : ($module['size'] > 200000 ? '#ffc107' : '#28a745') ?>;">
                                <div class="stat-number" style="font-size: 1.8rem; color: <?= $module['size'] > 500000 ? '#dc3545' : ($module['size'] > 200000 ? '#d4af37' : '#28a745') ?>;">
                                    <?= $module['sizeFormatted'] ?>
                                </div>
                                <div class="stat-label" style="font-size: 0.75rem;">Tamaño Total</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="no-results" style="display: none;">
                    No se encontraron Módulos que coincidan con tu búsqueda.
                </div>
            </div>

            <!-- DEPENDENCIAS TAB -->
            <div id="dependencies" class="tab-content">
                <?php if (!empty($dependencies)): ?>
                    <div class="welcome-card" style="margin-bottom: 2rem;">
                        <h2>📦 Dependencias de Composer</h2>
                        <p>Paquetes y librerías instaladas mediante Composer en el proyecto.</p>
                    </div>

                    <?php foreach ($dependencies as $dep): ?>
                        <div class="helper-card" data-search="<?= strtolower($dep['package'] . ' ' . $dep['version']) ?>">
                            <div class="service-header">
                                <div class="helper-name" style="margin: 0;"><?= $dep['package'] ?></div>
                                <div class="service-badge" style="background: <?= $dep['installed'] ? '#28a745' : '#dc3545' ?>">
                                    <?= $dep['installed'] ? '✓ Instalado' : '✗ No instalado' ?>
                                </div>
                            </div>

                            <div class="helper-signature" style="margin-top: 1rem;">
                                <strong>Versión:</strong> <?= $dep['version'] ?>
                                <span style="margin-left: 2rem;"><strong>Tipo:</strong> <?= $dep['type'] ?></span>
                            </div>

                            <?php if ($dep['package'] !== 'composer/autoload'): ?>
                                <div class="example-box">
                                    <div class="example-label">Instalación:</div>
                                    <div class="example-code">composer require <?= $dep['package'] ?><?= $dep['version'] !== '*' ? ':' . $dep['version'] : '' ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="welcome-card">
                        <h2>Sin dependencias externas</h2>
                        <p>El proyecto actualmente no tiene dependencias externas definidas en composer.json.</p>
                        <p>Solo está configurado el autoloader PSR-4 para cargar las clases del namespace App\.</p>
                    </div>
                <?php endif; ?>

                <div class="no-results" style="display: none;">
                    No se encontraron Dependencias que coincidan con tu búsqueda.
                </div>
            </div>

            <!-- ENTORNO TAB -->
            <div id="environment" class="tab-content">
                <div class="welcome-card">
                    <h2>⚙️ Información del Entorno PHP</h2>
                    <p>Configuración del servidor y entorno de ejecución de PHP.</p>
                </div>

                <div class="service-card">
                    <div class="service-name">🖥️ Información del Sistema</div>
                    <div class="methods" style="margin-top: 1rem;">
                        <div class="method-item">
                            <div class="method-signature">
                                <span class="method-name">Versión PHP:</span> <?= $environment['php_version'] ?>
                            </div>
                        </div>
                        <div class="method-item">
                            <div class="method-signature">
                                <span class="method-name">SAPI:</span> <?= $environment['php_sapi'] ?>
                            </div>
                        </div>
                        <div class="method-item">
                            <div class="method-signature">
                                <span class="method-name">Sistema Operativo:</span> <?= $environment['os'] ?>
                            </div>
                        </div>
                        <div class="method-item">
                            <div class="method-signature">
                                <span class="method-name">Límite de Memoria:</span> <?= $environment['memory_limit'] ?>
                            </div>
                        </div>
                        <div class="method-item">
                            <div class="method-signature">
                                <span class="method-name">Tiempo Máximo Ejecución:</span> <?= $environment['max_execution_time'] ?>s
                            </div>
                        </div>
                        <div class="method-item">
                            <div class="method-signature">
                                <span class="method-name">Tamaño Máximo Upload:</span> <?= $environment['upload_max_filesize'] ?>
                            </div>
                        </div>
                        <div class="method-item">
                            <div class="method-signature">
                                <span class="method-name">Tamaño Máximo POST:</span> <?= $environment['post_max_size'] ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-header">
                        <div class="service-name">🔌 Extensiones PHP Cargadas</div>
                        <div class="service-badge"><?= count($environment['extensions']) ?> extensiones</div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.5rem; margin-top: 1rem;">
                        <?php foreach ($environment['extensions'] as $ext): ?>
                            <div class="method-item" style="padding: 0.5rem 1rem; margin: 0;">
                                <span style="font-size: 0.9rem;">✓ <?= $ext ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p><strong>DISCOLMETS</strong> - Panel de Desarrolladores v2.0</p>
        <p>Calidad y Servicio Pensando en su Salud</p>
        <p>© 2025 Discolmets. Todos los derechos reservados.</p>
        <p style="margin-top: 1rem; font-size: 0.9rem;">Actualizado automáticamente • <?= date('Y-m-d H:i:s') ?></p>
    </footer>

    <script>
        // Cambiar tabs
        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');

            // Resetear búsqueda
            document.getElementById('searchInput').value = '';
            filterItems('');
        }

        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function(e) {
            filterItems(e.target.value.toLowerCase());
        });

        function filterItems(searchTerm) {
            const activeTab = document.querySelector('.tab-content.active');
            const cards = activeTab.querySelectorAll('.service-card, .helper-card');
            const noResults = activeTab.querySelector('.no-results');
            let visibleCount = 0;

            cards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>