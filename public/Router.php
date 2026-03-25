<?php
/**
 * ROUTER - Sistema de rutas simple y seguro
 * Uso: SoliciTic/index
 */

require_once __DIR__ . '/../app/Core/bootstrap.php';

// 1. Obtener y sanitizar ruta
$route = isset($_GET['p']) ? trim($_GET['p']) : 'Home/index';
$route = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $route);
$route = str_replace(['..', './'], '', $route);
$route = trim($route, '/');

// 2. Parsear módulo y ruta completa
$parts = explode('/', $route);
$module = $parts[0] ?? 'Home';

// ============================================================
// VALIDACIÓN DE LOGIN - Protege todas las rutas EXCEPTO Login
// ============================================================
// Permitir peticiones a controller (POST con funcion) sin validar sesión
$isControllerOnlyRequest = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['funcion']));

// Obtener ruta para verificar si es Login
$route_temp = $route;
$parts_temp = explode('/', $route_temp);
$module_temp = $parts_temp[0] ?? '';

// Permitir Login sin validar sesión (para que se pueda hacer POST del login)
$modulosPublicos = ['Login'];

if (!$isControllerOnlyRequest && !in_array($module_temp, $modulosPublicos)) {
    // Para módulos protegidos, validar sesión
    if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
        header("Location: /");
        exit;
    }
}

// Si tiene más de 2 partes: Modulo/SubProceso/Pagina
// Si tiene 2 partes: Modulo/Pagina
// Si tiene 1 parte: Modulo (default index)
if (count($parts) > 2) {
    // Caso: GestCompetencia/evaluaciones/index
    $subPath = implode('/', array_slice($parts, 1)); // evaluaciones/index
    $page = array_pop($parts); // index
    $controllerSubPath = implode('/', array_slice($parts, 1)); // evaluaciones
} elseif (count($parts) == 2) {
    // Caso: GestionProyectos/index
    $subPath = $parts[1]; // index
    $page = $parts[1];
    $controllerSubPath = $parts[1];
} else {
    // Caso: Home (solo módulo)
    $subPath = 'index';
    $page = 'index';
    $controllerSubPath = '';
}

// 3. Construir rutas (soporta subcarpetas)
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$viewPath = $docRoot . "/app/modules/{$module}/{$subPath}.php";
$controllerPath = $docRoot . "/app/modules/{$module}/controller/controller.php";
$controllerPathSub = $controllerSubPath !== ''
    ? $docRoot . "/app/modules/{$module}/{$controllerSubPath}/controller/controller.php"
    : '';

// 4. Título de página
$title = 'Discolnet';
$titlesFile = $docRoot . '/Config/titles.php';
if (file_exists($titlesFile)) {
    $titles = array_change_key_case(include $titlesFile, CASE_LOWER);
    $key = strtolower($module . '/' . $page);
    $title = $titles[$key] ?? $titles[strtolower($module)] ?? $title;
}

// ============================================================
// MODO CONTROLLER: Si POST con funcion, solo ejecutar controller
// ============================================================
// Si viene con petición POST y hay una función específica (como login)
// entonces ejecutar SOLO el controller sin cargar vista

if ($isControllerOnlyRequest) {
    // Cargar solo el controller, sin vista ni header/footer
    $controllerToLoad = $controllerPath;
    if ($controllerPathSub && file_exists($controllerPathSub)) {
        $controllerToLoad = $controllerPathSub;
    }

    if (file_exists($controllerToLoad)) {
        require_once $controllerToLoad;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => "Controller no encontrado: $controllerToLoad"
        ]);
    }
    exit;
}

// 5. Cargar vista o error 404
if (file_exists($viewPath)) {
    require_once $docRoot . '/includes/header.php';
    
    // Cargar controller si existe (prioriza submódulo)
    if ($controllerPathSub && file_exists($controllerPathSub)) {
        require_once $controllerPathSub;
    } elseif (file_exists($controllerPath)) {
        require_once $controllerPath;
    }
    
    require_once $viewPath;
    require_once $docRoot . '/includes/footer.php';
    
} else {
    http_response_code(404);
    
    $error404 = $docRoot . '/app/modules/errors/404.php';
    if (file_exists($error404)) {
        require_once $error404;
    } else {
        require_once $docRoot . '/includes/header.php';
        echo '<div class="container mt-5">';
        echo '<div class="alert alert-danger">';
        echo '<h4>404 - Página No Encontrada</h4>';
        echo '<p>El módulo solicitado no existe: <code>' . htmlspecialchars($route) . '</code></p>';
        echo '<hr>';
        echo '<h5>Debug Info:</h5>';
        echo '<p><strong>GET p:</strong> ' . htmlspecialchars($_GET['p'] ?? 'N/A') . '</p>';
        echo '<p><strong>Módulo:</strong> ' . htmlspecialchars($module) . '</p>';
        echo '<p><strong>SubPath:</strong> ' . htmlspecialchars($subPath) . '</p>';
        echo '<p><strong>ViewPath buscada:</strong> <code>' . htmlspecialchars($viewPath) . '</code></p>';
        echo '<p><strong>¿Existe ViewPath?</strong> ' . (file_exists($viewPath) ? 'SÍ' : 'NO') . '</p>';
        echo '<p><strong>ControllerPath buscada:</strong> <code>' . htmlspecialchars($controllerPath) . '</code></p>';
        echo '<p><strong>¿Existe ControllerPath?</strong> ' . (file_exists($controllerPath) ? 'SÍ' : 'NO') . '</p>';
        echo '<p><strong>DOCUMENT_ROOT:</strong> <code>' . htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . '</code></p>';
        echo '<p><strong>REQUEST_URI:</strong> <code>' . htmlspecialchars($_SERVER['REQUEST_URI']) . '</code></p>';
        echo '</div></div>';
        require_once $docRoot . '/includes/footer.php';
    }
}
