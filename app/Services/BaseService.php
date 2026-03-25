<?php
namespace App\Services;

use App\Core\BaseModel;

/**
 * BaseService - Clase base para todos los Services
 * 
 * Proporciona funcionalidad común a todos los servicios del sistema:
 * - Gestión de conexiones a bases de datos
 * - Validación de parámetros
 * - Herencia de métodos de BaseModel
 * 
 * IMPORTANTE: Todos los Services deben extender de esta clase
 * 
 * @example Crear un nuevo Service
 * ```php
 * class MiNuevoService extends BaseService
 * {
 *     public function __construct()
 * {
 *         parent::__construct('sql'); // O 'mysql', 'webdiscol'
 *     }
 * }
 * ```
 * 
 * @package App\Services
 * @author Sistemas Discolmets
 * @version 1.0
 */
abstract class BaseService extends BaseModel
{
    /**
     * Constructor base del servicio
     * 
     * Inicializa la conexión a la base de datos según el tipo especificado.
     * 
     * @param string $connection Tipo de conexión a la base de datos:
     *                          - 'ficc' (defecto): SQL Server principal
     *                          - 'mysql': MySQL local
     *                          - 'webdiscol': Base de datos externa
     * 
     * @example
     * ```php
     * // En tu Service personalizado
     * public function __construct()
     * {
     *     parent::__construct('sql'); // Usar SQL Server
     * }
     * ```
     */
    public function __construct($connection = 'ficc')
    {
        parent::__construct($connection);
    }

    /**
     * Cambiar conexión dinámicamente durante la ejecución
     * 
     * Útil cuando necesitas consultar múltiples bases de datos
     * en un mismo método.
     * 
     * @param string $connection Tipo de conexión: 'mysql', 'sql', 'webdiscol'
     * 
     * @example Cambiar entre bases de datos
     * ```php
     * public function obtenerDatosMultiples()
     * {
     *     // Consultar SQL Server
     *     $this->switchConnection('sql');
     *     $datosSQL = $this->query("SELECT * FROM Usuarios");
     *     
     *     // Cambiar a MySQL
     *     $this->switchConnection('mysql');
     *     $datosMySQL = $this->query("SELECT * FROM logs");
     *     
     *     return ['sql' => $datosSQL, 'mysql' => $datosMySQL];
     * }
     * ```
     */
    protected function switchConnection($connection)
    {
        parent::__construct($connection);
    }

    /**
     * Validar que existan los parámetros requeridos
     * 
     * Lanza una excepción si falta algún parámetro obligatorio.
     * Útil para validar datos de entrada en métodos públicos.
     * 
     * @param array $params Parámetros recibidos (ej: $_POST, array de config)
     * @param array $required Lista de campos requeridos
     * 
     * @throws \InvalidArgumentException Si falta algún parámetro
     * 
     * @example Validar parámetros POST
     * ```php
     * public function crearUsuario($datos)
     * {
     *     // Validar campos obligatorios
     *     $this->validateRequired($datos, ['nombre', 'email', 'cargo']);
     *     
     *     // Si llega aquí, todos los campos existen
     *     return $this->insert('Usuarios', $datos);
     * }
     * ```
     * 
     * @example Uso en controller
     * ```php
     * // En un controller
     * public function registrarUsuario()
     * {
     *     try {
     *         service('User')->crearUsuario($_POST);
     *         echo json_encode(['success' => true]);
     *     } catch (\InvalidArgumentException $e) {
     *         echo json_encode(['error' => $e->getMessage()]);
     *     }
     * }
     * ```
     */
    protected function validateRequired($params, $required)
    {
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                throw new \InvalidArgumentException("El parámetro '$field' es requerido");
            }
        }
    }
}
