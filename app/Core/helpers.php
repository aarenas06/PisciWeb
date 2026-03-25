<?php

/**
 * Helpers para Módulos
 * Funciones auxiliares para cargar assets y rutas de módulos
 */

/**
 * Carga el script de un módulo
 * Uso: module_script('SoliciTic')
 */
function module_script($moduleName, $scriptName = 'script', $Directory = '')
{
    if ($Directory != '') {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $scriptPath = "{$baseUrl}/app/modules/{$moduleName}/{$Directory}/{$scriptName}.js";
        $relativePhysicalPath = "/app/modules/{$moduleName}/{$Directory}/{$scriptName}.js";
    } else {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $scriptPath = "{$baseUrl}/app/modules/{$moduleName}/{$scriptName}.js";
        $relativePhysicalPath = "/app/modules/{$moduleName}/{$scriptName}.js";
    }

    // Verificar si el archivo existe usando ruta absoluta desde el proyecto
    $projectRoot = realpath(__DIR__ . '/../../');
    $fullPath = $projectRoot . str_replace('/', DIRECTORY_SEPARATOR, $relativePhysicalPath);
    
    // DEBUG: Imprimir información de depuración
    echo "<!-- module_script DEBUG:\n";
    echo "     Module: {$moduleName}\n";
    echo "     Script Name: {$scriptName}\n";
    echo "     Directory: {$Directory}\n";
    echo "     BASE_URL: {$baseUrl}\n";
    echo "     Script Path: {$scriptPath}\n";
    echo "     Project Root: {$projectRoot}\n";
    echo "     Full Path: {$fullPath}\n";
    echo "     File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
    echo "-->\n";
    
    if (file_exists($fullPath)) {
        echo "<script type=\"text/javascript\" src=\"{$scriptPath}\"></script>\n";
    } else {
        error_log("Advertencia: Script no encontrado - {$fullPath}");
        echo "<!-- Script no encontrado: {$scriptPath} -->\n";
    }
}

/**
 * Expone variables de sesión PHP en JavaScript
 * Crea un objeto global 'App.session' accesible desde cualquier script
 * Uso: En header.php llamar session_to_js()
 * En JS: App.session.UsuCod, App.session.usuario, etc.
 */
function session_to_js()
{
    $sessionData = [];

    // Lista de variables de sesión a exponer
    $allowedKeys = [
        'UsuCod',
        'usuario',
        'nombre_usuario',
        'NitIde',
        'PerUsuCod',
        'ProcesoSec',
        'ProEmpNom',
        'Cargo_Sec',
        'Cargo',
        'SucCod',
        'SucNom',
        'clave',
    ];

    foreach ($allowedKeys as $key) {
        if (isset($_SESSION[$key])) {
            $sessionData[$key] = $_SESSION[$key];
        }
    }

    $jsonData = json_encode($sessionData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    // Escapar para prevenir XSS
    $baseUrlEscaped = htmlspecialchars(defined('BASE_URL') ? BASE_URL : '', ENT_QUOTES, 'UTF-8');

    echo "<script>
        // Variables de sesión PHP disponibles en JavaScript
        window.App = window.App || {};
        App.session = {$jsonData};
        App.baseUrl = '{$baseUrlEscaped}';
    </script>\n";
}

/**
 * Carga el CSS de un módulo
 * Uso: module_style('SoliciTic')
 */
function module_style($moduleName, $styleName = 'style')
{
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    $stylePath = "/app/modules/{$moduleName}/{$styleName}.css";
    
    // Verificar si el archivo existe usando ruta absoluta desde el proyecto
    $projectRoot = realpath(__DIR__ . '/../../');
    $relativePhysicalPath = "/app/modules/{$moduleName}/{$styleName}.css";
    $fullPath = $projectRoot . str_replace('/', DIRECTORY_SEPARATOR, $relativePhysicalPath);
    
    // DEBUG: información útil para diagnosticar rutas (se muestra como comentario HTML)
    echo "<!-- module_style DEBUG: module={$moduleName} style={$styleName} BASE_URL={$baseUrl} stylePath={$stylePath} fullPath={$fullPath} exists=" . (file_exists($fullPath) ? 'YES' : 'NO') . " -->\n";
    
    if (file_exists($fullPath)) {
        echo "<link rel=\"stylesheet\" href=\"{$stylePath}\">\n";
    } else {
        error_log("Advertencia: Estilo no encontrado - {$fullPath}");
        echo "<!-- Estilo no encontrado: {$stylePath} -->\n";
    }
}

/**
 * Obtiene la URL de un asset de un módulo
 * Uso: module_asset('SoliciTic', 'logo.png')
 * Retorna: '/PisciWeb/app/modules/SoliciTic/assets/logo.png'
 * 
 * @param string $moduleName Nombre del módulo
 * @param string $fileName Nombre del archivo (puede incluir subcarpetas: 'images/logo.png')
 * @param bool $absolute Si es true, retorna URL absoluta con protocolo y dominio
 * @return string URL del asset
 */
function module_asset($moduleName, $fileName, $absolute = false)
{

    if (empty($moduleName)) {
        $assetPath = "/public/assets/{$fileName}";
    } else {
        $assetPath = "/app/modules/{$moduleName}/assets/{$fileName}";
    }

    // Verificar si el archivo existe físicamente
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $assetPath;

    if (!file_exists($fullPath)) {
        error_log("⚠️ Asset no encontrado: {$fullPath}");
        error_log("📂 DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT']);
        error_log("🔗 Path solicitado: {$assetPath}");
    }

    // Retornar URL absoluta si se solicita
    if ($absolute) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return "{$protocol}://{$host}{$assetPath}";
    }

    return $assetPath;
}

/**
 * Genera la URL del controlador de un módulo para JavaScript
 * Uso: module_api('SoliciTic') → 'app/modules/SoliciTic/controller/controller.php'
 */
function module_api($moduleName, $Namecontroller = 'controller')
{
    return "app/modules/{$moduleName}/controller/{$Namecontroller}.php";
}

/**
 * Inicializa el objeto global de rutas en JavaScript
 * Ahora valida existencia del controller base antes de definir window.api
 */
function init_module_routes($currentModule = null, $controllerName = 'controller')
{
    $controllerPath = __DIR__ . "/../../app/modules/{$currentModule}/controller/{$controllerName}.php";
    $routes = [
        'current' => $currentModule ? module_api($currentModule, $controllerName) : null,
        'base' => defined('BASE_URL') ? BASE_URL : '',
    ];

    echo "<script>\n";
    echo "window.MODULE_ROUTES = " . json_encode($routes) . ";\n";
    echo "window.api = function(module, controller = '{$controllerName}') { return (window.MODULE_ROUTES.base || '') + '/app/modules/' + module + '/controller/' + controller + '.php'; };\n";
    echo "</script>\n";
}

/**
 * Incluye un archivo layout/vista de un módulo
 * Uso: module_layout('SoliciTic', 'tbSoli', compact('datos', 'area'))
 */
function module_layout($moduleName, $layoutName, $variables = [], $isInterDiretory = false, $NameInterDirectory = '')
{
    if ($isInterDiretory != false) {
        $layoutPath = __DIR__ . "/../../app/modules/{$moduleName}/layout/{$NameInterDirectory}/{$layoutName}.php";
    } else {
        $layoutPath = __DIR__ . "/../../app/modules/{$moduleName}/layout/{$layoutName}.php";
    }


    if (file_exists($layoutPath)) {
        // Extraer variables para que estén disponibles en el layout
        extract($variables);
        include $layoutPath;
    } else {
        echo "<!-- Error: Layout {$layoutName} no encontrado en módulo {$moduleName} -->";
    }
}

/**
 * Carga una vista de un módulo
 * Uso: module_view('CertifiCom', 'viewAdmin', ['data' => $datos])
 * 
 * @param string $moduleName Nombre del módulo
 * @param string $viewName Nombre de la vista (sin extensión .php)
 * @param array $variables Variables a pasar a la vista
 * @param bool $isInterDiretory Si la vista está en un subdirectorio
 * @param string $NameInterDirectory Nombre del subdirectorio
 */
function module_view($moduleName, $viewName, $variables = [], $isInterDiretory = false, $NameInterDirectory = '')
{
    if ($isInterDiretory != false) {
        $viewPath = __DIR__ . "/../../app/modules/{$moduleName}/view/{$NameInterDirectory}/{$viewName}.php";
    } else {
        $viewPath = __DIR__ . "/../../app/modules/{$moduleName}/view/{$viewName}.php";
    }

    if (file_exists($viewPath)) {
        // Extraer variables para que estén disponibles en la vista
        extract($variables);
        include $viewPath;
    } else {
        echo "<!-- Error: Vista {$viewName} no encontrada en módulo {$moduleName} -->";
    }
}

/**
 * Carga una instancia de un Service (Patrón CodeIgniter)
 * Crea instancia singleton de Services para evitar múltiples conexiones
 * 
 * Uso:
 * $userService = service('User');
 * $processService = service('Process');
 * $notif = service('Notification');
 * $http = service('Http');
 * 
 * @param string $serviceName Nombre del Service (sin 'Service')
 * @return object Instancia del Service
 */
function service($serviceName)
{
    static $instances = [];

    $serviceClass = "App\\Services\\{$serviceName}Service";

    // Singleton: crear solo una instancia por Service
    if (!isset($instances[$serviceName])) {
        if (class_exists($serviceClass)) {
            $instances[$serviceName] = new $serviceClass();
        } else {
            throw new \Exception("Service '{$serviceName}' no encontrado en App\\Services\\{$serviceName}Service");
        }
    }

    return $instances[$serviceName];
}

/**
 * Obtiene la ruta del directorio temporal de un módulo
 * Crea el directorio si no existe y opcionalmente limpia archivos antiguos
 * 
 * Uso:
 * $tmpDir = temp_dir('PendienteDis'); // Ruta absoluta
 * $tmpUrl = temp_dir('PendienteDis', 'url'); // Ruta relativa para URLs
 * $tmpDir = temp_dir('PendienteDis', 'path', 1200); // Con limpieza de archivos > 20 min
 * 
 * @param string $moduleName Nombre del módulo
 * @param string $type 'path' para ruta absoluta, 'url' para ruta relativa
 * @param int|null $cleanOlderThan Segundos para limpiar archivos antiguos (null = no limpiar)
 * @param string $pattern Patrón de archivos a limpiar (ej: 'Informe_*.csv')
 * @return string Ruta del directorio temporal
 */
function temp_dir($moduleName, $type = 'path', $cleanOlderThan = null, $pattern = '*')
{
    // Obtener la raíz del proyecto (2 niveles arriba desde app/Core/)
    $projectRoot = dirname(__DIR__, 2);
    $relativePath = "app/modules/{$moduleName}/tmp";
    $absolutePath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

    // Crear directorio si no existe
    if (!is_dir($absolutePath)) {
        if (!mkdir($absolutePath, 0755, true) && !is_dir($absolutePath)) {
            throw new \RuntimeException("No se pudo crear el directorio: {$absolutePath}");
        }
    }

    // Limpieza automática de archivos antiguos
    if ($cleanOlderThan !== null) {
        $files = glob($absolutePath . DIRECTORY_SEPARATOR . $pattern);
        $now = time();
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file) && $now - filemtime($file) > $cleanOlderThan) {
                    try {
                        @unlink($file); // Suprimir warning de permisos
                    } catch (\Exception $e) {
                        error_log("No se pudo eliminar {$file}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    // Retornar según el tipo solicitado
    if ($type === 'url') {
        return $relativePath;
    }

    return $absolutePath . DIRECTORY_SEPARATOR;
}

/**
 * Genera un nombre de archivo temporal único
 * 
 * Uso:
 * $filename = temp_filename('Informe', 'csv'); // Informe_abc123.csv
 * $filename = temp_filename('export', 'xlsx', 'user_123'); // export_user_123_abc123.xlsx
 * 
 * @param string $prefix Prefijo del archivo
 * @param string $extension Extensión del archivo
 * @param string|null $suffix Sufijo adicional antes del ID único
 * @return string Nombre del archivo
 */
function temp_filename($prefix = 'file', $extension = 'tmp', $suffix = null)
{
    $parts = [$prefix];

    if ($suffix !== null) {
        $parts[] = $suffix;
    }

    $parts[] = uniqid();

    return implode('_', $parts) . '.' . $extension;
}

/**
 * Helper: convierte cualquier “imagen” de entrada a un Data URI JPEG:
 *   data:image/jpeg;base64,....
 *
 * Acepta:
 *  - Data URI (png/jpg/webp/gif/etc)  -> lo normaliza a JPEG
 *  - Base64 “puro” (sin cabecera)     -> detecta y normaliza a JPEG
 *  - Binario crudo (contenido bytes)  -> detecta y normaliza a JPEG
 *  - Texto “rarito” tipo: "PNG IHDR ..." (bytes ya decodificados a texto)
 *
 * Requisitos: extensión GD habilitada.
 */

function ConvertImgToBase64($input, int $jpegQuality = 85): string
{
    if ($input === null) {
        throw new InvalidArgumentException('Input vacío (null).');
    }

    $raw = normalize_image_input_to_bytes($input);

    // Intentar cargar imagen con GD (sirve para PNG/JPG/GIF/WEBP, etc. según build)
    $im = @imagecreatefromstring($raw);
    if ($im === false) {
        // Intento extra: cuando llega como texto con caracteres raros, probar a recuperar bytes
        $raw2 = try_recover_bytes_from_text($input);
        if ($raw2 !== null) {
            $im = @imagecreatefromstring($raw2);
        }
    }

    if ($im === false) {
        throw new RuntimeException('No se pudo interpretar la imagen (formato no soportado o datos corruptos).');
    }

    // Convertir a JPEG en memoria
    ob_start();
    imagejpeg($im, null, max(0, min(100, $jpegQuality)));
    $jpegBin = ob_get_clean();
    imagedestroy($im);

    if ($jpegBin === false || $jpegBin === '') {
        throw new RuntimeException('No se pudo generar el JPEG.');
    }

    return 'data:image/jpeg;base64,' . base64_encode($jpegBin);
}

/**
 * Normaliza distintas entradas a bytes crudos de imagen.
 */
function normalize_image_input_to_bytes($input): string
{
    // Si llega como recurso/stream, leerlo
    if (is_resource($input)) {
        $data = stream_get_contents($input);
        if ($data === false || $data === '') {
            throw new RuntimeException('No se pudo leer el recurso/stream.');
        }
        return $data;
    }

    // Si llega como string
    if (!is_string($input)) {
        throw new InvalidArgumentException('El input debe ser string o recurso/stream.');
    }

    $s = trim($input);

    // 1) Data URI
    if (preg_match('~^data:\s*image\/[a-zA-Z0-9.+-]+;\s*base64\s*,~i', $s)) {
        [$header, $b64] = explode(',', $s, 2);
        $b64 = preg_replace('~\s+~', '', $b64);
        $bin = base64_decode($b64, true);
        if ($bin === false) {
            throw new RuntimeException('Data URI inválido (base64 no decodifica).');
        }
        return $bin;
    }

    // 2) Base64 “puro” (sin cabecera)
    //    - Lo consideramos base64 si:
    //       a) solo contiene chars base64 + espacios
    //       b) longitud razonable y decodifica a bytes que parecen imagen
    $maybeB64 = preg_replace('~\s+~', '', $s);
    if ($maybeB64 !== '' && preg_match('~^[A-Za-z0-9+\/=]+$~', $maybeB64)) {
        $bin = base64_decode($maybeB64, true);
        if ($bin !== false && $bin !== '') {
            // Si parece imagen, lo aceptamos como bytes
            if (looks_like_image_bytes($bin)) {
                return $bin;
            }
            // A veces puede no empezar con firma conocida pero igual ser imagen decodificable
            // lo intentaremos igual con GD (imagecreatefromstring)
            return $bin;
        }
    }

    // 3) Si ya son bytes (por ejemplo contenido binario guardado en string),
    //    detectamos por firmas
    if (looks_like_image_bytes($s)) {
        return $s;
    }

    // 4) Último intento: si el string contiene “PNG” “IHDR” etc como texto,
    //    intentar recuperar bytes (latin1) y devolverlos.
    $recovered = try_recover_bytes_from_text($s);
    if ($recovered !== null) {
        return $recovered;
    }

    // Si nada funcionó, se devuelve tal cual para que GD intente (y falle con mensaje claro)
    return $s;
}

/**
 * Heurística rápida para reconocer firmas comunes de imágenes.
 */
function looks_like_image_bytes(string $bin): bool
{
    if (strlen($bin) < 12) return false;

    // PNG: 89 50 4E 47 0D 0A 1A 0A
    if (substr($bin, 0, 8) === "\x89PNG\x0D\x0A\x1A\x0A") return true;

    // JPEG: FF D8 FF
    if (substr($bin, 0, 3) === "\xFF\xD8\xFF") return true;

    // GIF: "GIF87a" o "GIF89a"
    if (substr($bin, 0, 6) === "GIF87a" || substr($bin, 0, 6) === "GIF89a") return true;

    // WEBP: "RIFF" .... "WEBP"
    if (substr($bin, 0, 4) === "RIFF" && substr($bin, 8, 4) === "WEBP") return true;

    // BMP: "BM"
    if (substr($bin, 0, 2) === "BM") return true;

    // ICO: 00 00 01 00
    if (substr($bin, 0, 4) === "\x00\x00\x01\x00") return true;

    return false;
}

/**
 * Cuando llega “tipo texto” (como tu ejemplo "PNG IHDR ...") a veces es porque
 * bytes binarios se interpretaron como ISO-8859-1/Windows-1252.
 * Este método intenta recuperar los bytes originales.
 *
 * Devuelve null si no tiene sentido intentarlo.
 */
function try_recover_bytes_from_text($input): ?string
{
    if (!is_string($input)) return null;
    $s = $input;

    // Si contiene pistas típicas de PNG en “texto”
    if (stripos($s, 'PNG') === false && stripos($s, 'IHDR') === false && stripos($s, 'IDAT') === false) {
        return null;
    }

    // Intento 1: tratar el texto como latin1 -> bytes 1:1
    // (utf8_decode convierte UTF-8 a ISO-8859-1, útil cuando se “rompió” así)
    $latin1 = @utf8_decode($s);
    if (is_string($latin1) && $latin1 !== '' && looks_like_image_bytes($latin1)) {
        return $latin1;
    }

    // Intento 2: interpretar como Windows-1252 a ISO-8859-1 (aprox)
    if (function_exists('iconv')) {
        $cvt = @iconv('Windows-1252', 'ISO-8859-1//IGNORE', $s);
        if (is_string($cvt) && $cvt !== '' && looks_like_image_bytes($cvt)) {
            return $cvt;
        }
    }

    // Intento 3: si el texto ya estaba en latin1 “tal cual”
    if (looks_like_image_bytes($s)) {
        return $s;
    }

    return null;
}

/**
 * Genera una URL completa de la aplicación
 * Detecta automáticamente el protocolo (http/https) y el host
 * 
 * @param string $path Ruta relativa desde la raíz del proyecto (ej: 'Public/SistPrueCono/views/prueba')
 * @param array $params Parámetros query string opcionales (ej: ['id' => 123, 'name' => 'test'])
 * @return string URL completa
 * 
 * Ejemplos:
 * app_url('Public/index.php') → 'http://localhost/PisciWeb/Public/index.php'
 * app_url('Public/SistPrueCono/views/prueba', ['idPrue' => 5, 'cc' => '123'])
 *   → 'http://localhost/PisciWeb/Public/SistPrueCono/views/prueba?idPrue=5&cc=123'
 * En producción: 'https://discolnet.discolmets.com.co/Public/SistPrueCono/views/prueba?idPrue=5&cc=123'
 */
function app_url($path = '', $params = [])
{
    // Detectar protocolo (http o https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

    // Detectar host (localhost, dominio, ip:puerto, etc)
    // Usar HTTP_HOST si existe, si no usar SERVER_NAME:PORT
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] . (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80' ? ':' . $_SERVER['SERVER_PORT'] : ''));

    // Obtener el directorio base del proyecto de forma confiable
    $documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $currentDir = str_replace('\\', '/', __DIR__); // C:\xampp\htdocs\PisciWeb\app\Core

    // Calcular el basePath relativo al DOCUMENT_ROOT
    $basePath = str_replace($documentRoot, '', dirname(dirname($currentDir)));

    // Normalizar: asegurar que empiece con / (o esté vacío si es raíz)
    $basePath = rtrim($basePath, '/');

    if ($basePath === '' || $basePath === '/' || $basePath === '.') {
        $basePath = '';
    } else {
        // Asegurar que empiece con /
        $basePath = '/' . ltrim($basePath, '/');
    }

    // Limpiar path de entrada
    $path = ltrim($path, '/');

    // Construir URL base: protocolo://host/basePath
    $url = "{$protocol}://{$host}{$basePath}";

    // Agregar path si existe
    if ($path !== '') {
        $url .= '/' . $path;
    }

    // Agregar parámetros query string
    if (!empty($params)) {
        $queryString = http_build_query($params);
        $url .= '?' . $queryString;
    }

    return $url;
}
