<?php
/**
 * @deprecated Este archivo se mantiene solo por compatibilidad con código legacy
 * Usar App\Core\BaseModel en su lugar
 * 
 * Conexiones de base de datos usando variables de entorno
 * Legacy compatibility: clase estática con métodos que retornan PDO
 */

use App\Core\Env;

class conexion
{
    private static $instances = [];

    /**
     * @deprecated Usar App\Core\BaseModel con connectionName='mysql'
     */
    public static function mysql()
    {
        if (!isset(self::$instances['mysql'])) {
            try {
                $host = Env::get('DB_MYSQL_HOST');
                $db = Env::get('DB_MYSQL_DATABASE');
                $user = Env::get('DB_MYSQL_USERNAME');
                $pass = Env::get('DB_MYSQL_PASSWORD');
                
                if (!$host || !$db || !$user) {
                    throw new \Exception('Variables DB_MYSQL_* no configuradas en .env');
                }
                
                $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
                $pdo = new \PDO($dsn, $user, $pass, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]);
                self::$instances['mysql'] = $pdo;
            } catch (\PDOException $e) {
                error_log("Error MySQL Discol: " . $e->getMessage());
                throw new \RuntimeException("Error en la conexión a MySQL Discol");
            }
        }
        return self::$instances['mysql'];
    }

    /**
     * @deprecated Usar App\Core\BaseModel con connectionName='webdiscol'
     */
    public static function webdiscol()
    {
        if (!isset(self::$instances['webdiscol'])) {
            try {
                $host = Env::get('DB_WEBDISCOL_HOST');
                $db = Env::get('DB_WEBDISCOL_DATABASE');
                $user = Env::get('DB_WEBDISCOL_USERNAME');
                $pass = Env::get('DB_WEBDISCOL_PASSWORD');
                
                if (!$host || !$db || !$user) {
                    throw new \Exception('Variables DB_WEBDISCOL_* no configuradas en .env');
                }
                
                $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
                $pdo = new \PDO($dsn, $user, $pass, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]);
                self::$instances['webdiscol'] = $pdo;
            } catch (\PDOException $e) {
                error_log("Error MySQL webdiscolmedica: " . $e->getMessage());
                throw new \RuntimeException("Error en la conexión a webdiscolmedica");
            }
        }
        return self::$instances['webdiscol'];
    }

    /**
     * @deprecated Usar App\Core\BaseModel con connectionName='sql'
     */
    public static function sql()
    {
        if (!isset(self::$instances['sql'])) {
            try {
                $server = Env::get('DB_SQLSRV_HOST');
                $db = Env::get('DB_SQLSRV_DATABASE');
                $user = Env::get('DB_SQLSRV_USERNAME');
                $pass = Env::get('DB_SQLSRV_PASSWORD');
                
                if (!$server || !$db || !$user) {
                    throw new \Exception('Variables DB_SQLSRV_* no configuradas en .env');
                }
                
                $loginTimeout = (int) Env::get('DB_SQLSRV_LOGIN_TIMEOUT', 5);
                if ($loginTimeout < 1) {
                    $loginTimeout = 5;
                }

                $queryTimeout = (int) Env::get('DB_SQLSRV_QUERY_TIMEOUT', 30);
                if ($queryTimeout < 1) {
                    $queryTimeout = 30;
                }

                $encrypt = filter_var(Env::get('DB_SQLSRV_ENCRYPT', 'false'), FILTER_VALIDATE_BOOLEAN);
                $trustServerCertificate = filter_var(Env::get('DB_SQLSRV_TRUST_SERVER_CERTIFICATE', 'true'), FILTER_VALIDATE_BOOLEAN);
                $connectionPooling = filter_var(Env::get('DB_SQLSRV_CONNECTION_POOLING', 'true'), FILTER_VALIDATE_BOOLEAN);
                $mars = filter_var(Env::get('DB_SQLSRV_MARS', 'false'), FILTER_VALIDATE_BOOLEAN);

                $dsn = sprintf(
                    'sqlsrv:Server=%s;Database=%s;LoginTimeout=%d;Encrypt=%d;TrustServerCertificate=%d;ConnectionPooling=%d;MultipleActiveResultSets=%d;',
                    $server,
                    $db,
                    $loginTimeout,
                    $encrypt ? 1 : 0,
                    $trustServerCertificate ? 1 : 0,
                    $connectionPooling ? 1 : 0,
                    $mars ? 1 : 0
                );

                $options = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ];

                if (defined('PDO::SQLSRV_ATTR_QUERY_TIMEOUT')) {
                    $options[\PDO::SQLSRV_ATTR_QUERY_TIMEOUT] = $queryTimeout;
                }

                $pdo = new \PDO($dsn, $user, $pass, $options);
                self::$instances['sql'] = $pdo;
            } catch (\PDOException $e) {
                error_log("Error SQL Server: " . $e->getMessage());
                throw new \RuntimeException("Error en la conexión a SQL Server");
            }
        }
        return self::$instances['sql'];
    }
}
