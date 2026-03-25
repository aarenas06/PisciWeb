<?php
namespace App\Core;

/**
 * ServiceExplorer
 * 
 * Escanea y documenta dinámicamente todos los Services y Helpers
 * disponibles en el aplicativo para que los desarrolladores
 * conozcan qué tienen disponible.
 */
class ServiceExplorer
{
    private $servicesPath;
    private $helpersPath;

    public function __construct()
    {
        $this->servicesPath = __DIR__ . '/../Services';
        $this->helpersPath = __DIR__ . '/helpers.php';
    }

    /**
     * Obtener todos los Services con sus métodos y documentación
     */
    public function getAllServices()
    {
        $services = [];
        
        if (!is_dir($this->servicesPath)) {
            return $services;
        }

        $files = glob($this->servicesPath . '/*.php');
        
        foreach ($files as $file) {
            $fileName = basename($file, '.php');
            
            // Saltar BaseService
            if ($fileName === 'BaseService') {
                continue;
            }

            $className = "App\\Services\\{$fileName}";
            
            if (class_exists($className)) {
                $reflection = new \ReflectionClass($className);
                
                // Obtener métodos públicos (excepto constructor)
                $methods = [];
                foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                    if ($method->name === '__construct' || $method->class !== $className) {
                        continue;
                    }
                    
                    $methods[] = [
                        'name' => $method->name,
                        'parameters' => $this->getMethodParameters($method),
                        'doc' => $this->getMethodDoc($method),
                        'return' => $this->getReturnType($method)
                    ];
                }
                
                $services[] = [
                    'name' => $fileName,
                    'shortName' => str_replace('Service', '', $fileName),
                    'class' => $className,
                    'file' => basename($file),
                    'doc' => $this->getClassDoc($reflection),
                    'methods' => $methods,
                    'methodCount' => count($methods)
                ];
            }
        }
        
        return $services;
    }

    /**
     * Obtener todos los Helpers con sus parámetros
     */
    public function getAllHelpers()
    {
        $helpers = [];
        
        if (!file_exists($this->helpersPath)) {
            return $helpers;
        }
        
        $content = file_get_contents($this->helpersPath);
        
        // Buscar todas las funciones
        preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\((.*?)\)/s', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $functionName = $match[1];
            $parameters = trim($match[2]);
            
            // Extraer documentación
            $doc = $this->extractHelperDoc($content, $functionName);
            
            $helpers[] = [
                'name' => $functionName,
                'parameters' => $this->parseParameters($parameters),
                'doc' => $doc,
                'usage' => $this->generateHelperUsage($functionName, $parameters)
            ];
        }
        
        return $helpers;
    }

    /**
     * Obtener parámetros de un método
     */
    private function getMethodParameters($method)
    {
        $params = [];
        foreach ($method->getParameters() as $param) {
            $paramInfo = '$' . $param->name;
            
            // Tipo
            if ($param->hasType()) {
                $type = $param->getType();
                $paramInfo = ($type ? $type->getName() . ' ' : '') . $paramInfo;
            }
            
            // Valor por defecto
            if ($param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
                if (is_array($default)) {
                    $paramInfo .= ' = []';
                } elseif (is_null($default)) {
                    $paramInfo .= ' = null';
                } elseif (is_string($default)) {
                    $paramInfo .= ' = "' . $default . '"';
                } else {
                    $paramInfo .= ' = ' . var_export($default, true);
                }
            }
            
            $params[] = $paramInfo;
        }
        
        return implode(', ', $params);
    }

    /**
     * Obtener documentación de un método
     */
    private function getMethodDoc($method)
    {
        $doc = $method->getDocComment();
        if (!$doc) {
            return '';
        }
        
        // Extraer descripción corta
        preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\n/s', $doc, $matches);
        return isset($matches[1]) ? trim($matches[1]) : '';
    }

    /**
     * Obtener tipo de retorno
     */
    private function getReturnType($method)
    {
        if ($method->hasReturnType()) {
            return $method->getReturnType()->getName();
        }
        
        // Intentar extraer de la documentación
        $doc = $method->getDocComment();
        if (preg_match('/@return\s+([^\s]+)/', $doc, $matches)) {
            return $matches[1];
        }
        
        return 'mixed';
    }

    /**
     * Obtener documentación de una clase
     */
    private function getClassDoc($reflection)
    {
        $doc = $reflection->getDocComment();
        if (!$doc) {
            return '';
        }
        
        // Extraer líneas de descripción
        preg_match_all('/\*\s*([^@\*\/].+?)\n/s', $doc, $matches);
        $description = isset($matches[1]) ? implode(' ', array_map('trim', $matches[1])) : '';
        
        return trim($description);
    }

    /**
     * Extraer documentación de un helper
     */
    private function extractHelperDoc($content, $functionName)
    {
        $pattern = '/\/\*\*.*?\*\/\s*function\s+' . preg_quote($functionName) . '/s';
        if (preg_match($pattern, $content, $match)) {
            preg_match_all('/\*\s*([^@\*\/].+?)\n/', $match[0], $docMatches);
            if (!empty($docMatches[1])) {
                return trim(implode(' ', array_map('trim', $docMatches[1])));
            }
        }
        return '';
    }

    /**
     * Parsear parámetros de string
     */
    private function parseParameters($params)
    {
        if (empty(trim($params))) {
            return '';
        }
        
        return $params;
    }

    /**
     * Generar ejemplo de uso de helper
     */
    private function generateHelperUsage($name, $params)
    {
        if (empty(trim($params))) {
            return "{$name}()";
        }
        
        // Extraer nombres de parámetros
        preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', $params, $matches);
        $paramNames = $matches[1];
        
        $examples = array_map(function($p) {
            if (strpos($p, 'Name') !== false) return "'ModuleName'";
            if (strpos($p, 'module') !== false) return "'SoliciTic'";
            if (strpos($p, 'layout') !== false) return "'tbSoli'";
            if (strpos($p, 'service') !== false) return "'User'";
            if (strpos($p, 'variables') !== false) return "compact('data')";
            return "'...'";
        }, $paramNames);
        
        return "{$name}(" . implode(', ', $examples) . ")";
    }

    /**
     * Obtener estadísticas generales
     */
    public function getStats()
    {
        $services = $this->getAllServices();
        $helpers = $this->getAllHelpers();
        $modules = $this->getAllModules();
        $dependencies = $this->getComposerDependencies();
        
        $totalMethods = array_sum(array_column($services, 'methodCount'));
        
        return [
            'services' => count($services),
            'helpers' => count($helpers),
            'total_methods' => $totalMethods,
            'total_functions' => count($services) + count($helpers) + $totalMethods,
            'modules' => count($modules),
            'dependencies' => count($dependencies),
            'php_version' => phpversion()
        ];
    }

    /**
     * Obtener todos los módulos disponibles
     * Solo escanea app/modules (excluye API)
     */
    public function getAllModules()
    {
        $modules = [];
        
        // Módulos de app/modules únicamente
        $appModulesPath = __DIR__ . '/../modules';
        if (is_dir($appModulesPath)) {
            $dirs = array_filter(glob($appModulesPath . '/*'), 'is_dir');
            foreach ($dirs as $dir) {
                $moduleName = basename($dir);
                if ($moduleName === 'errors') continue; // Excluir módulo de errores
                
                $modules[] = $this->scanModule($dir, $moduleName, 'APP');
            }
        }
        
        return $modules;
    }

    /**
     * Escanear un módulo específico
     */
    private function scanModule($path, $name, $type)
    {
        $files = [];
        $controllers = 0;
        $models = 0;
        $routes = 0;
        $views = 0;
        $totalSize = 0; // Tamaño total en bytes
        
        // Buscar archivos PHP
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                $extension = $file->getExtension();
                
                // Sumar tamaño de todos los archivos
                $totalSize += $file->getSize();
                
                if ($extension === 'php') {
                    $files[] = $filename;
                    
                    if (stripos($filename, 'controller') !== false) {
                        $controllers++;
                    } elseif (stripos($filename, 'model') !== false) {
                        $models++;
                    } elseif (stripos($filename, 'routes') !== false) {
                        $routes++;
                    } else {
                        $views++;
                    }
                }
            }
        }
        
        return [
            'name' => $name,
            'type' => $type,
            'path' => str_replace('\\', '/', $path),
            'files' => $files,
            'totalFiles' => count($files),
            'controllers' => $controllers,
            'models' => $models,
            'routes' => $routes,
            'views' => $views,
            'size' => $totalSize,
            'sizeFormatted' => $this->formatBytes($totalSize)
        ];
    }

    /**
     * Formatear bytes a formato legible (KB, MB, GB)
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Obtener dependencias de Composer
     */
    public function getComposerDependencies()
    {
        $dependencies = [];
        
        // Leer composer.json
        $composerPath = dirname(__DIR__, 2) . '/composer.json';
        if (file_exists($composerPath)) {
            $composerData = json_decode(file_get_contents($composerPath), true);
            
            if (isset($composerData['require'])) {
                foreach ($composerData['require'] as $package => $version) {
                    $dependencies[] = [
                        'package' => $package,
                        'version' => $version,
                        'type' => 'require',
                        'installed' => $this->isPackageInstalled($package)
                    ];
                }
            }
            
            if (isset($composerData['require-dev'])) {
                foreach ($composerData['require-dev'] as $package => $version) {
                    $dependencies[] = [
                        'package' => $package,
                        'version' => $version,
                        'type' => 'require-dev',
                        'installed' => $this->isPackageInstalled($package)
                    ];
                }
            }
        }
        
        // Si no hay dependencias en composer.json, mostrar autoloader de composer
        if (empty($dependencies)) {
            $dependencies[] = [
                'package' => 'composer/autoload',
                'version' => 'PSR-4',
                'type' => 'core',
                'installed' => true
            ];
        }
        
        return $dependencies;
    }

    /**
     * Verificar si un paquete está instalado
     */
    private function isPackageInstalled($package)
    {
        $vendorPath = dirname(__DIR__, 2) . '/vendor/' . $package;
        return is_dir($vendorPath);
    }

    /**
     * Obtener información del entorno PHP
     */
    public function getEnvironmentInfo()
    {
        return [
            'php_version' => phpversion(),
            'php_sapi' => php_sapi_name(),
            'os' => PHP_OS,
            'extensions' => get_loaded_extensions(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        ];
    }
}
