<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * BaseModel con soporte para múltiples conexiones PDO
 * Usa singleton pattern para reutilizar conexiones
 * Todas las conexiones se configuran via variables de entorno (.env)
 */
class BaseModel
{
    protected $db;
    private static $connections = [];

    // Constantes PDO para que los modelos hijos las usen sin imports
    const FETCH_NAMED = PDO::FETCH_NAMED;
    const FETCH_ASSOC = PDO::FETCH_ASSOC;
    const FETCH_NUM = PDO::FETCH_NUM;
    const FETCH_BOTH = PDO::FETCH_BOTH;
    const FETCH_OBJ = PDO::FETCH_OBJ;
    const PARAM_LOB = PDO::PARAM_LOB;

    const PARAM_STR = PDO::PARAM_STR;
    const PARAM_INT = PDO::PARAM_INT;
    const PARAM_BOOL = PDO::PARAM_BOOL;
    const PARAM_NULL = PDO::PARAM_NULL;

    /**
     * Constructor que inicializa la conexión según el nombre
        * @param string $connectionName Nombre de la conexión: 'mysql', 'webdiscol', 'sql', 'ficc', 'ficcdiscolnet', 'incolmetric', 'olast', 'menjalher'
     * @throws \RuntimeException Si falla la conexión
     */
    public function __construct($connectionName = 'sql')
    {
        $this->db = self::getConnection($connectionName);
    }

    /**
     * Obtiene una conexión PDO (singleton pattern)
     * @param string $name Nombre de la conexión
     * @return PDO
     * @throws \RuntimeException
     */
    protected static function getConnection(string $name): PDO
    {
        // Normalizar nombre
        $name = self::normalizeConnectionName($name);

        // Si ya existe la conexión, reutilizarla
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        // Crear nueva conexión
        try {
            self::$connections[$name] = self::createConnection($name);
            return self::$connections[$name];
        } catch (\Throwable $e) {
            $env = \App\Core\Env::get('APP_ENV', 'development');
            $debug = defined('APP_DEBUG') ? APP_DEBUG : false;

            error_log("[{$env}] Error conexión [{$name}]: " . $e->getMessage());

            // Mensaje detallado según modo debug
            if ($debug) {
                throw new \RuntimeException(
                    "Error al conectar a la base de datos [{$name}]\n" .
                        "Entorno: {$env}\n" .
                        "Error original: " . $e->getMessage() . "\n" .
                        "Verifica las credenciales en .env para APP_ENV={$env}"
                );
            } else {
                throw new \RuntimeException("Error al conectar a la base de datos [{$name}]. Verifica tu configuración.");
            }
        }
    }

    /**
     * Normaliza el nombre de la conexión
     * @param string $name
     * @return string
     */
    private static function normalizeConnectionName(string $name): string
    {
        $name = strtolower(trim($name));

        // Aliases
        if (in_array($name, ['mysql_discol', 'discol'])) {
            return 'mysql';
        }
        if (in_array($name, ['web_discol', 'web'])) {
            return 'webdiscol';
        }
        if (in_array($name, ['sqlserver', 'mssql'])) {
            return 'sql';
        }

        // Legacy mappings: algunas partes del sistema aún usan 'sqlDiscolnet' (legacy)
        // Mapearlo a la conexión FICC/Discolnet moderna
        if (in_array($name, ['sqldiscolnet', 'sql_discolnet', 'sqldiscolnetficc'])) {
            return 'ficcdiscolnet';
        }

        if (in_array($name, ['incolmetric', 'imcolmetric', 'incolmetri'])) {
            return 'incolmetric';
        }

        if (in_array($name, ['olast'])) {
            return 'olast';
        }

        if (in_array($name, ['menjalher'])) {
            return 'menjalher';
        }

        if (in_array($name, ['farma'])) {
            return 'farma';
        }

        return $name;
    }

    /**
     * Crea una nueva conexión PDO según el nombre
     * @param string $name
     * @return PDO
     * @throws PDOException
     */
    private static function createConnection(string $name): PDO
    {
        switch ($name) {
            case 'mysql':
                return self::createMySQLConnection('mysql');

            case 'webdiscol':
                return self::createMySQLConnection('webdiscol');

            case 'sql':
                return self::createSQLServerConnection(); //Discolmedica Viejo

            case 'ficc':
                return self::createFICCServerConnection(); //Discolmedica new 


            case 'ficcdiscolnet':
                return self::createFiccDiscolnetServerConnection();//Discolnet new QA

            case 'incolmetric':
                return self::createEmpresaSQLServerConnection('INCOLMETRIC');

            case 'olast':
                return self::createEmpresaSQLServerConnection('OLAST');

            case 'menjalher':
                return self::createEmpresaSQLServerConnection('MENJALHER');

            case 'farma':
                return self::createEmpresaSQLServerConnection('FARMA');

            default:
                throw new \InvalidArgumentException("Conexión desconocida: {$name}. Conexiones disponibles: mysql, webdiscol, sql, ficc, ficcdiscolnet, incolmetric, olast, menjalher, farma");
        }
    }

    /**
     * Crea conexión MySQL/MariaDB
     * @param string $type 'mysql' o 'webdiscol'
     * @return PDO
     * @throws PDOException
     */
    private static function createMySQLConnection(string $type): PDO
    {
        // Determinar el prefijo según entorno y tipo
        $env = Env::get('APP_ENV', 'development');
        $isProduction = ($env === 'production');

        if ($isProduction) {
            // Producción: usar DB_PROD_MYSQL_* o DB_PROD_WEBDISCOL_*
            $prefix = ($type === 'mysql') ? 'DB_PROD_MYSQL_' : 'DB_PROD_WEBDISCOL_';
        } else {
            // Desarrollo/Testing: usar DB_MYSQL_* o DB_WEBDISCOL_*
            $prefix = ($type === 'mysql') ? 'DB_MYSQL_' : 'DB_WEBDISCOL_';
        }

        $host = Env::get($prefix . 'HOST');
        $db = Env::get($prefix . 'DATABASE');
        $user = Env::get($prefix . 'USERNAME');
        $pass = Env::get($prefix . 'PASSWORD');

        if (!$host || !$db || !$user) {
            throw new \RuntimeException("Variables {$prefix}* no configuradas en .env (Entorno: {$env})");
        }

        $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";

        try {
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
        } catch (\PDOException $e) {
            $debug = defined('APP_DEBUG') ? APP_DEBUG : false;
            if ($debug) {
                throw new \RuntimeException(
                    "Error al conectar a MySQL [{$type}]\n" .
                        "Entorno: {$env}\n" .
                        "Servidor: {$host}\n" .
                        "Base de Datos: {$db}\n" .
                        "Usuario: {$user}\n" .
                        "Error: " . $e->getMessage()
                );
            } else {
                throw new \RuntimeException("Error al conectar a MySQL [{$type}]: " . $e->getMessage());
            }
        }
    }

    /**
     * Crea conexión SQL Server
     * @return PDO
     * @throws PDOException
     */
    private static function createSQLServerConnection(): PDO
    {
        // Determinar el prefijo según entorno
        $env = Env::get('APP_ENV', 'development');
        $isProduction = ($env === 'production');

        if ($isProduction) {
            // Producción: usar DB_PROD_SQLSRV_*
            $prefix = 'DB_PROD_SQLSRV_';
        } else {
            // Desarrollo/Testing: usar DB_SQLSRV_*
            $prefix = 'DB_SQLSRV_';
        }

        $server = Env::get($prefix . 'HOST');
        $db = Env::get($prefix . 'DATABASE');
        $user = Env::get($prefix . 'USERNAME');
        $pass = Env::get($prefix . 'PASSWORD');

        if (!$server || !$db || !$user) {
            throw new \RuntimeException("Variables {$prefix}* no configuradas en .env (Entorno: {$env})");
        }

        $dsn = self::buildSqlSrvDsn($server, $db);
        $pdoOptions = self::buildSqlSrvPdoOptions();

        try {
            return new PDO($dsn, $user, $pass, $pdoOptions);
        } catch (\PDOException $e) {
            $debug = defined('APP_DEBUG') ? APP_DEBUG : false;
            if ($debug) {
                throw new \RuntimeException(
                    "Error al conectar a SQL Server\n" .
                        "Entorno: {$env}\n" .
                        "Servidor: {$server}\n" .
                        "Base de Datos: {$db}\n" .
                        "Usuario: {$user}\n" .
                        "Error: " . $e->getMessage() . "\n\n" .
                        "💡 Verifica que:\n" .
                        "  1. El servidor {$server} esté accesible desde tu ubicación\n" .
                        "  2. Las credenciales en .env sean correctas para {$env}\n" .
                        "  3. El driver sqlsrv esté instalado (php -m | findstr sqlsrv)"
                );
            } else {
                throw new \RuntimeException("Error al conectar a SQL Server: " . $e->getMessage());
            }
        }
    }

    private static function createFICCServerConnection(): PDO
    {
        // Determinar el prefijo según entorno
        $env = Env::get('APP_ENV', 'development');
        $isProduction = ($env === 'production');

        if ($isProduction) {
            // Producción: usar DB_PROD_SQLSRV_*
            $prefix = 'DB_PROD_SQLFICC_';
        } else {
            // Desarrollo/Testing: usar DB_SQLSRV_*
            $prefix = 'DB_SQLFICC_';
        }

        $server = Env::get($prefix . 'HOST');
        $db = Env::get($prefix . 'DATABASE');
        $user = Env::get($prefix . 'USERNAME');
        $pass = Env::get($prefix . 'PASSWORD');

        if (!$server || !$db || !$user) {
            throw new \RuntimeException("Variables {$prefix}* no configuradas en .env (Entorno: {$env})");
        }

        $dsn = self::buildSqlSrvDsn($server, $db);
        $pdoOptions = self::buildSqlSrvPdoOptions();

        try {
            return new PDO($dsn, $user, $pass, $pdoOptions);
        } catch (\PDOException $e) {
            $debug = defined('APP_DEBUG') ? APP_DEBUG : false;
            if ($debug) {
                throw new \RuntimeException(
                    "Error al conectar a SQL Server\n" .
                        "Entorno: {$env}\n" .
                        "Servidor: {$server}\n" .
                        "Base de Datos: {$db}\n" .
                        "Usuario: {$user}\n" .
                        "Error: " . $e->getMessage() . "\n\n" .
                        "💡 Verifica que:\n" .
                        "  1. El servidor {$server} esté accesible desde tu ubicación\n" .
                        "  2. Las credenciales en .env sean correctas para {$env}\n" .
                        "  3. El driver sqlsrv esté instalado (php -m | findstr sqlsrv)"
                );
            } else {
                throw new \RuntimeException("Error al conectar a SQL Server: " . $e->getMessage());
            }
        }
    }

    // private static function createDiscolnetSQLServerConnection(): PDO
    // {
    //     // Determinar el prefijo según entorno
    //     $env = Env::get('APP_ENV', 'development');
    //     $isProduction = ($env === 'production');

    //     if ($isProduction) {
    //         // Producción: usar DB_PROD_SQLDiscolnet_*
    //         $prefix = 'DB_PROD_SQLDiscolnet_';
    //     } else {
    //         // Desarrollo/Testing: usar DB_SQLDiscolnet_*
    //         $prefix = 'DB_SQLDiscolnet_';
    //     }

    //     $server = Env::get($prefix . 'HOST');
    //     $db = Env::get($prefix . 'DATABASE');
    //     $user = Env::get($prefix . 'USERNAME');
    //     $pass = Env::get($prefix . 'PASSWORD');

    //     if (!$server || !$db || !$user) {
    //         throw new \RuntimeException("Variables {$prefix}* no configuradas en .env (Entorno: {$env})");
    //     }

    //     $dsn = "sqlsrv:Server={$server};Database={$db};";

    //     try {
    //         return new PDO($dsn, $user, $pass, [
    //             PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    //         ]);
    //     } catch (\PDOException $e) {
    //         $debug = defined('APP_DEBUG') ? APP_DEBUG : false;
    //         if ($debug) {
    //             throw new \RuntimeException(
    //                 "Error al conectar a SQL Server\n" .
    //                     "Entorno: {$env}\n" .
    //                     "Servidor: {$server}\n" .
    //                     "Base de Datos: {$db}\n" .
    //                     "Usuario: {$user}\n" .
    //                     "Error: " . $e->getMessage() . "\n\n" .
    //                     "💡 Verifica que:\n" .
    //                     "  1. El servidor {$server} esté accesible desde tu ubicación\n" .
    //                     "  2. Las credenciales en .env sean correctas para {$env}\n" .
    //                     "  3. El driver sqlsrv esté instalado (php -m | findstr sqlsrv)"
    //             );
    //         } else {
    //             throw new \RuntimeException("Error al conectar a SQL Server: " . $e->getMessage());
    //         }
    //     }
    // }
    private static function createFiccDiscolnetServerConnection(): PDO
    {
        // Determinar el prefijo según entorno
        $env = Env::get('APP_ENV', 'development');
        $isProduction = ($env === 'production');

        if ($isProduction) {
            // Producción: usar DB_PROD_SQLDiscolnetFicc_*
            $prefix = 'DB_PROD_FICCDiscolnet_';
        } else {
            // Desarrollo/Testing: usar DB_SQLDiscolnetFicc_*
            $prefix = 'DB_FICCDiscolnet_';
        }

        $server = Env::get($prefix . 'HOST');
        $db = Env::get($prefix . 'DATABASE');
        $user = Env::get($prefix . 'USERNAME');
        $pass = Env::get($prefix . 'PASSWORD');

        if (!$server || !$db || !$user) {
            throw new \RuntimeException("Variables {$prefix}* no configuradas en .env (Entorno: {$env})");
        }

        $dsn = self::buildSqlSrvDsn($server, $db);
        $pdoOptions = self::buildSqlSrvPdoOptions();

        try {
            return new PDO($dsn, $user, $pass, $pdoOptions);
        } catch (\PDOException $e) {
            $debug = defined('APP_DEBUG') ? APP_DEBUG : false;
            if ($debug) {
                throw new \RuntimeException(
                    "Error al conectar a SQL Server\n" .
                        "Entorno: {$env}\n" .
                        "Servidor: {$server}\n" .
                        "Base de Datos: {$db}\n" .
                        "Usuario: {$user}\n" .
                        "Error: " . $e->getMessage() . "\n\n" .
                        "💡 Verifica que:\n" .
                        "  1. El servidor {$server} esté accesible desde tu ubicación\n" .
                        "  2. Las credenciales en .env sean correctas para {$env}\n" .
                        "  3. El driver sqlsrv esté instalado (php -m | findstr sqlsrv)"
                );
            } else {
                throw new \RuntimeException("Error al conectar a SQL Server: " . $e->getMessage());
            }
        }
    }

    /**
     * Crea conexión SQL Server para empresas externas (INCOLMETRIC, OLAST, MENJALHER)
     * Usa variables de entorno:
     * - Desarrollo: DB_{EMPRESA}_*
     * - Producción: DB_PROD_{EMPRESA}_*
     *
     * @param string $empresaKey
     * @return PDO
     */
    private static function createEmpresaSQLServerConnection(string $empresaKey): PDO
    {
        $env = Env::get('APP_ENV', 'development');
        $prefix = 'DB_' . strtoupper($empresaKey) . '_';

        $server = Env::get($prefix . 'HOST');
        $db = Env::get($prefix . 'DATABASE');
        $user = Env::get($prefix . 'USERNAME');
        $pass = Env::get($prefix . 'PASSWORD');

        if (!$server || !$db || !$user) {
            throw new \RuntimeException("Variables {$prefix}* no configuradas en .env (Entorno: {$env})");
        }

        $dsn = self::buildSqlSrvDsn($server, $db);
        $pdoOptions = self::buildSqlSrvPdoOptions();

        try {
            return new PDO($dsn, $user, $pass, $pdoOptions);
        } catch (\PDOException $e) {
            $debug = defined('APP_DEBUG') ? APP_DEBUG : false;
            if ($debug) {
                throw new \RuntimeException(
                    "Error al conectar a SQL Server [{$empresaKey}]\n" .
                        "Entorno: {$env}\n" .
                        "Servidor: {$server}\n" .
                        "Base de Datos: {$db}\n" .
                        "Usuario: {$user}\n" .
                        "Error: " . $e->getMessage() . "\n\n" .
                        "💡 Verifica que:\n" .
                        "  1. El servidor {$server} esté accesible desde tu ubicación\n" .
                        "  2. Las credenciales en .env sean correctas para {$env}\n" .
                        "  3. El driver sqlsrv esté instalado (php -m | findstr sqlsrv)"
                );
            } else {
                throw new \RuntimeException("Error al conectar a SQL Server [{$empresaKey}]: " . $e->getMessage());
            }
        }
    }

    /**
     * EJEMPLO: Crear conexión PostgreSQL
     * Descomenta y agrega al switch para usarla
     */
    // private static function createPostgreSQLConnection(): PDO
    // {
    //     $env = Env::get('APP_ENV', 'development');
    //     $prefix = ($env === 'production') ? 'DB_PROD_POSTGRES_' : 'DB_POSTGRES_';
    //     
    //     $host = Env::get($prefix . 'HOST');
    //     $db = Env::get($prefix . 'DATABASE');
    //     $user = Env::get($prefix . 'USERNAME');
    //     $pass = Env::get($prefix . 'PASSWORD');
    //     
    //     if (!$host || !$db || !$user) {
    //         throw new \RuntimeException("Variables {$prefix}* no configuradas en .env");
    //     }
    //     
    //     $dsn = "pgsql:host={$host};dbname={$db}";
    //     
    //     return new PDO($dsn, $user, $pass, [
    //         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    //     ]);
    // }

    /**
     * Permite cerrar todas las conexiones abiertas
     * Útil para testing o long-running scripts
     */
    public static function closeAllConnections(): void
    {
        self::$connections = [];
    }

    /**
     * Construye el DSN de SQL Server con opciones optimizadas y configurables por entorno.
     *
     * Variables opcionales:
     * - DB_SQLSRV_LOGIN_TIMEOUT (segundos, default 5)
     * - DB_SQLSRV_ENCRYPT (true/false, default false)
     * - DB_SQLSRV_TRUST_SERVER_CERTIFICATE (true/false, default true)
     * - DB_SQLSRV_CONNECTION_POOLING (true/false, default true)
     * - DB_SQLSRV_MARS (true/false, default false)
     */
    private static function buildSqlSrvDsn(string $server, string $db): string
    {
        $loginTimeout = (int) Env::get('DB_SQLSRV_LOGIN_TIMEOUT', 5);
        if ($loginTimeout < 1) {
            $loginTimeout = 5;
        }

        $encrypt = self::envToBool('DB_SQLSRV_ENCRYPT', false);
        $trustServerCertificate = self::envToBool('DB_SQLSRV_TRUST_SERVER_CERTIFICATE', true);
        $connectionPooling = self::envToBool('DB_SQLSRV_CONNECTION_POOLING', true);
        $mars = self::envToBool('DB_SQLSRV_MARS', false);

        $parts = [
            "Server={$server}",
            "Database={$db}",
            "LoginTimeout={$loginTimeout}",
            'Encrypt=' . ($encrypt ? '1' : '0'),
            'TrustServerCertificate=' . ($trustServerCertificate ? '1' : '0'),
            'ConnectionPooling=' . ($connectionPooling ? '1' : '0'),
            'MultipleActiveResultSets=' . ($mars ? '1' : '0'),
        ];

        return 'sqlsrv:' . implode(';', $parts) . ';';
    }

    /**
     * Opciones PDO para SQL Server con timeout configurable a nivel de consulta.
     *
     * Variable opcional:
     * - DB_SQLSRV_QUERY_TIMEOUT (segundos, default 30)
     */
    private static function buildSqlSrvPdoOptions(): array
    {
        $queryTimeout = (int) Env::get('DB_SQLSRV_QUERY_TIMEOUT', 30);
        if ($queryTimeout < 1) {
            $queryTimeout = 30;
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        if (defined('PDO::SQLSRV_ATTR_QUERY_TIMEOUT')) {
            $options[PDO::SQLSRV_ATTR_QUERY_TIMEOUT] = $queryTimeout;
        }

        return $options;
    }

    /**
     * Convierte una variable de entorno a booleano de forma segura.
     */
    private static function envToBool(string $key, bool $default): bool
    {
        $rawValue = Env::get($key, null);
        if ($rawValue === null || $rawValue === '') {
            return $default;
        }

        $value = filter_var($rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $value ?? $default;
    }

    // ============================================================
    // SISTEMA DE TRAZABILIDAD DE CONSULTAS
    // ============================================================

    /**
     * Sobrescribe el método prepare() de PDO para inyectar comentarios de trazabilidad
     * Agrega automáticamente información de contexto en cada consulta SQL
     * 
     * @param string $query La consulta SQL original
     * @param array $options Opciones de PDO para el statement
     * @return \PDOStatement
     */
    public function prepare($query, $options = [])
    {
        // Verificar si la trazabilidad está habilitada
        $tracingEnabled = Env::get('QUERY_TRACING_ENABLED', 'true') === 'true';

        if ($tracingEnabled) {
            $query = $this->injectQueryComment($query);
        }

        return $this->db->prepare($query, $options);
    }

    /**
     * Inyecta comentario SQL con información de trazabilidad
     * 
     * @param string $query Consulta SQL original
     * @return string Consulta con comentario inyectado
     */
    private function injectQueryComment(string $query): string
    {
        $context = $this->captureExecutionContext();

        // Formato del comentario: /* [USER: xxx] [MODULE: xxx] [METHOD: xxx] [TIME: xxx] */
        $comment = sprintf(
            "/* [USER: %s] [MODULE: %s] [METHOD: %s] [TIME: %s] */\n",
            $context['user'],
            $context['module'],
            $context['method'],
            $context['timestamp']
        );

        // Si hay IP disponible, agregarla también
        if (!empty($context['ip'])) {
            $comment = rtrim($comment, "*/\n") . " [IP: {$context['ip']}] */\n";
        }

        return $comment . $query;
    }

    /**
     * Captura el contexto de ejecución (usuario, módulo, método, etc.)
     * 
     * @return array Contexto con información de trazabilidad
     */
    private function captureExecutionContext(): array
    {
        // 1. Capturar usuario desde sesión
        $user = 'SYSTEM';
        if (session_status() === PHP_SESSION_ACTIVE || isset($_SESSION)) {
            $user = $_SESSION['UsuCod'] ?? $_SESSION['username'] ?? 'GUEST';
        }

        // 2. Capturar módulo desde la clase que llama
        $module = $this->getCallerModule();

        // 3. Capturar método que ejecuta la consulta
        $method = $this->getCallerMethod();

        // 4. Timestamp actual
        $timestamp = date('Y-m-d H:i:s');

        // 5. IP del cliente (opcional)
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        return [
            'user' => $user,
            'module' => $module,
            'method' => $method,
            'timestamp' => $timestamp,
            'ip' => $ip
        ];
    }

    /**
     * Obtiene el nombre del módulo desde el backtrace
     * 
     * @return string Nombre del módulo/clase
     */
    private function getCallerModule(): string
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        // Buscar la primera clase que NO sea BaseModel
        foreach ($backtrace as $trace) {
            if (isset($trace['class']) && $trace['class'] !== self::class && $trace['class'] !== 'PDO') {
                // Extraer solo el nombre de la clase sin namespace
                $parts = explode('\\', $trace['class']);
                return end($parts);
            }
        }

        return 'Unknown';
    }

    /**
     * Obtiene el nombre del método que ejecuta la consulta
     * 
     * @return string Nombre del método
     */
    private function getCallerMethod(): string
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        // Buscar el método que llamó a prepare()
        // Típicamente será el método del modelo (ej: getNit, login, etc.)
        foreach ($backtrace as $index => $trace) {
            if (
                isset($trace['function']) &&
                $trace['function'] !== 'prepare' &&
                $trace['function'] !== 'injectQueryComment' &&
                $trace['function'] !== 'captureExecutionContext' &&
                $trace['function'] !== 'getCallerMethod' &&
                $trace['function'] !== 'getCallerModule' &&
                !in_array($trace['function'], ['call_user_func', 'call_user_func_array'])
            ) {

                return $trace['function'];
            }
        }

        return 'directQuery';
    }

    /**
     * Método para logging opcional de queries lentas
     * 
     * @param string $query Consulta ejecutada
     * @param float $executionTime Tiempo de ejecución en segundos
     * @return void
     */
    protected function logSlowQuery(string $query, float $executionTime): void
    {
        $slowQueryThreshold = (float) Env::get('SLOW_QUERY_THRESHOLD', '1.0');

        if ($executionTime >= $slowQueryThreshold) {
            $logFile = __DIR__ . '/../../logs/slow_queries.log';
            $logDir = dirname($logFile);

            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $context = $this->captureExecutionContext();
            $logEntry = sprintf(
                "[%s] SLOW QUERY (%.2fs) - User: %s, Module: %s, Method: %s\nQuery: %s\n\n",
                date('Y-m-d H:i:s'),
                $executionTime,
                $context['user'],
                $context['module'],
                $context['method'],
                $query
            );

            file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
    }
}
