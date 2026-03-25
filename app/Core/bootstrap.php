<?php

if (defined('PISCIWEB_BOOTSTRAPPED')) {
    return;
}

define('PISCIWEB_BOOTSTRAPPED', true);

/**
 * Bootstrap del Framework
 * Carga el autoloader de Composer UNA SOLA VEZ
 * Similar a CodeIgniter 4, Laravel, Symfony
 */

// ============================================================
// CONFIGURACIÓN DE SESIÓN
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    // Aumentar el tiempo de vida de la sesión a 4 horas
    ini_set('session.gc_maxlifetime', 4 * 60 * 60); // 4 horas = 14400 segundos
    ini_set('session.cookie_lifetime', 0); // 0 = cookie de sesión (se elimina al cerrar navegador)
    ini_set('session.use_strict_mode', 1); // Evitar session fixation attacks
    ini_set('session.use_only_cookies', 1); // Solo cookies, no URL

    // Configurar parámetros de sesión antes de iniciarla
    session_set_cookie_params(
        0,              // lifetime (0 = cookie de sesión)
        '/',            // path
        '',             // domain
        false,          // secure (HTTP y HTTPS)
        true            // httponly (sin acceso desde JavaScript)
    );

    session_start();
}

// Registrar timestamp de creación de sesión si no existe
if (!isset($_SESSION['created_at'])) {
    $_SESSION['created_at'] = time();
    $_SESSION['last_activity'] = time();
}

// ============================================================
// AUTOLOADER CUSTOM CASE-INSENSITIVE (antes de Composer)
// ============================================================
// Permite buscar archivos en minúsculas o mayúsculas
spl_autoload_register(function ($class) {
    // Mapeo de namespaces a directorios
    $namespaces = [
        'App\\Modules\\' => __DIR__ . '/../../app/modules/',
        'App\\Core\\' => __DIR__ . '/../Core/',
        'App\\Services\\' => __DIR__ . '/../../app/Services/',
    ];

    foreach ($namespaces as $namespace => $basePath) {
        if (strpos($class, $namespace) === 0) {
            // Extraer la clase sin el namespace
            $relativePath = substr($class, strlen($namespace));
            
            // Convertir \ a /
            $relativePath = str_replace('\\', '/', $relativePath);
            
            // Intentar archivos: normal, minúsculas y mayúsculas iniciales
            $paths = [
                $basePath . $relativePath . '.php',
                $basePath . strtolower($relativePath) . '.php',
                $basePath . ucfirst($relativePath) . '.php',
            ];
            
            // Para rutas de múltiples niveles, probar variaciones
            if (strpos($relativePath, '/') !== false) {
                $parts = explode('/', $relativePath);
                $lastPart = array_pop($parts);
                $prefix = implode('/', $parts) . '/';
                
                $paths[] = $basePath . $prefix . strtolower($lastPart) . '.php';
                $paths[] = $basePath . $prefix . ucfirst(strtolower($lastPart)) . '.php';
            }
            
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    return true;
                }
            }
        }
    }
    
    // Dejar que composer lo maneje
    return false;
}, true, true); // prepend = true, throw = true

// Cargar autoloader de Composer (PSR-4)
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($autoload)) {
    die('Error: Composer autoload no encontrado. Ejecuta: composer dump-autoload');
}

if (!class_exists('Composer\\Autoload\\ClassLoader', false)) {
    require_once $autoload;
}

// Cargar variables de entorno
if (!class_exists('App\\Core\\Env', false)) {
    spl_autoload_call('App\\Core\\Env');
}

\App\Core\Env::load();

// Cargar helpers solo si no fueron cargados por otro bootstrap
if (!function_exists('module_script')) {
    require_once __DIR__ . '/helpers.php';
}

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// ============================================================
// CONFIGURACIÓN DE LOGS EN CARPETA DEL PROYECTO
// ============================================================
$logsDir = __DIR__ . '/../../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}
$logFile = $logsDir . '/app-' . date('Y-m-d') . '.log';

// ============================================================
// CONFIGURACIÓN DE ERRORES SEGÚN ENTORNO
// ============================================================
$env = \App\Core\Env::get('APP_ENV', 'development');
$debug = filter_var(\App\Core\Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

// Definir constante global para acceder en cualquier parte
define('APP_DEBUG', $debug);
define('APP_ENV', $env);
define('LOG_FILE', $logFile);

// Configurar PHP para usar nuestro archivo de log
ini_set('log_errors', '1');
ini_set('error_log', $logFile);

if ($debug) {
    // MODO DEBUG: Mostrar TODOS los errores en pantalla + guardar en log
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} elseif ($env === 'production') {
    // PRODUCCIÓN: Ocultar errores al usuario, solo loguear
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
} else {
    // DESARROLLO: Mostrar todos los errores
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// ============================================================
// MANEJADOR DE ERRORES PERSONALIZADO
// ============================================================
set_exception_handler(function($exception) {
    // Loguear en archivo logs/app-YYYY-MM-DD.log
    $logMsg = sprintf(
        "[%s] ERROR: %s en %s:%d\nStack: %s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    error_log($logMsg);
    
    if (APP_DEBUG) {
        // Modo debug: Mostrar error completo en pantalla
        echo '<div style="background:#f8d7da;border:2px solid #f5c6cb;padding:20px;margin:20px;font-family:monospace;">';
        echo '<h2 style="color:#721c24;">⚠️ Error Fatal</h2>';
        echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<p><strong>Archivo:</strong> ' . htmlspecialchars($exception->getFile()) . '</p>';
        echo '<p><strong>Línea:</strong> ' . $exception->getLine() . '</p>';
        echo '<details style="margin-top:15px;"><summary style="cursor:pointer;"><strong>Stack Trace</strong></summary>';
        echo '<pre style="background:#fff;padding:10px;overflow:auto;">' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
        echo '</details></div>';
    } else {
        // Modo producción: Mensaje genérico
        http_response_code(500);
        echo '<div style="background:#f8d7da;padding:20px;margin:20px;">';
        echo '<h2>Error del Servidor</h2>';
        echo '<p>Ha ocurrido un error. Contacta al administrador.</p>';
        echo '</div>';
    }
    exit(1);
});

// Definir BASE_URL (normalizar slashes para evitar '\' inesperados)
if (!defined('BASE_URL')) {
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if (basename($scriptDir) === 'public') {
        $baseUrl = dirname($scriptDir);
    } else {
        $baseUrl = $scriptDir;
    }

    // Normalizar separadores a '/' y limpiar espacios
    $baseUrl = str_replace('\\', '/', trim((string)$baseUrl));

    // Eliminar barra final si existe
    $baseUrl = rtrim($baseUrl, '/');

    // Valores especiales
    if ($baseUrl === '' || $baseUrl === '.' || $baseUrl === '/') {
        $baseUrl = '';
    } else {
        // Asegurar prefijo '/'
        if ($baseUrl[0] !== '/') {
            $baseUrl = '/' . ltrim($baseUrl, '/');
        }
    }

    define('BASE_URL', $baseUrl);
    $GLOBALS['baseUrl'] = $baseUrl;
}

// Función helper para verificar login
if (!function_exists('isLogged')) {
    function isLogged() {
        return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
    }
}
