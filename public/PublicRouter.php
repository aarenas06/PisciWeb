<?php
/**
 * ROUTER PÚBLICO - Sin validación de login
 * Uso: /public/SoliciTic/testeo
 */

require_once __DIR__ . '/../app/Core/bootstrap.php';

// 1. Obtener y sanitizar ruta
$route = isset($_GET['p']) ? trim($_GET['p']) : '';
$route = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $route);
$route = str_replace(['..', './'], '', $route);
$route = trim($route, '/');

if (empty($route)) {
    http_response_code(404);
    die('Acceso denegado');
}

// 2. Parsear módulo y ruta completa (soporta subcarpetas)
$parts = explode('/', $route);
$module = $parts[0] ?? '';

// Construir la ruta completa después del módulo
if (count($parts) > 1) {
    // Caso: GestCompetencia/evaluaciones/index o GestionProyectos/print
    $subPath = implode('/', array_slice($parts, 1)); // evaluaciones/index o print
} else {
    // Caso: solo módulo
    $subPath = 'index';
}

// 3. Construir ruta al archivo (soporta subcarpetas)
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$viewPath = $docRoot . "/app/modules/{$module}/{$subPath}.php";

// 4. Cargar vista pública (sin header/footer)
if (file_exists($viewPath)) {
    // Cargar controller si existe
    $controllerPath = $docRoot . "/app/modules/{$module}/controller/controller.php";
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
    }
    
    require_once $viewPath;
} else {
    http_response_code(404);
    echo '<h1>404 - No Encontrado</h1>';
    echo '<p>Ruta pública no existe: ' . htmlspecialchars($route) . '</p>';
}
