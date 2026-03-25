<?php

namespace App\Core;

/**
 * Cargador optimizado de variables de entorno desde archivo .env
 * Implementa singleton y caché para máximo rendimiento
 */
class Env
{
    private static $loaded = false;
    private static $vars = [];
    private static $cacheFile = null;

    /**
     * Carga variables de entorno desde .env
     * Usa caché de archivos para evitar parseo en cada request
     * 
     * @param string|null $path Ruta al archivo .env
     */
    public static function load($path = null)
    {
        if (self::$loaded) {
            return;
        }

        $path = $path ?? __DIR__ . '/../../.env';
        self::$cacheFile = __DIR__ . '/../../temp/env_cache.php';
        
        if (!file_exists($path)) {
            self::$loaded = true;
            return; // No es obligatorio, usar valores por defecto
        }

        // Intentar cargar desde caché si está actualizado
        if (self::loadFromCache($path)) {
            self::$loaded = true;
            return;
        }

        // Parsear .env y crear caché
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas si existen
                $value = trim($value, '"\'');
                
                self::$vars[$key] = $value;
                
                // También ponerlo en $_ENV para compatibilidad
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                }
            }
        }

        // Guardar caché
        self::saveCache();
        
        self::$loaded = true;
    }

    /**
     * Obtiene el valor de una variable de entorno
     * 
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$vars[$key] ?? $_ENV[$key] ?? $default;
    }

    /**
     * Carga variables desde caché si está actualizado
     * 
     * @param string $envPath Ruta al archivo .env
     * @return bool True si se cargó desde caché
     */
    private static function loadFromCache($envPath)
    {
        if (!file_exists(self::$cacheFile)) {
            return false;
        }

        // Verificar si el caché está desactualizado
        if (filemtime(self::$cacheFile) < filemtime($envPath)) {
            return false;
        }

        // Cargar desde caché
        $cached = @include self::$cacheFile;
        if (!is_array($cached)) {
            return false;
        }

        self::$vars = $cached;
        
        // Copiar a $_ENV
        foreach ($cached as $key => $value) {
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }

        return true;
    }

    /**
     * Guarda el caché de variables de entorno
     */
    private static function saveCache()
    {
        $dir = dirname(self::$cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $content = '<?php' . PHP_EOL . '// Cache generado automáticamente - No editar' . PHP_EOL;
        $content .= 'return ' . var_export(self::$vars, true) . ';';
        
        @file_put_contents(self::$cacheFile, $content);
    }

    /**
     * Limpia el caché (útil para desarrollo)
     */
    public static function clearCache()
    {
        if (self::$cacheFile && file_exists(self::$cacheFile)) {
            @unlink(self::$cacheFile);
        }
        self::$loaded = false;
        self::$vars = [];
    }
}
