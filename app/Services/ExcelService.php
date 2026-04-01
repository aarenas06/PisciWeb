<?php

namespace App\Services;

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Exception;
use PDO;

/**
 * Servicio centralizado para exportación de datos a Excel, CSV y TXT
 * Utiliza Box Spout desde el vendor de Composer
 */
class ExcelService extends BaseService
{
    private $cnxSql;
    private $cnxMysql;
    private $lastExportInfo = null;

    public function __construct($cnxSql = null, $cnxMysql = null)
    {
        $this->cnxSql = $cnxSql;
        $this->cnxMysql = $cnxMysql;
    }

    /**
     * Exportar consulta SQL a archivo Excel (.xlsx)
     * 
     * @param string $dbType Tipo de conexión: 'sql' o 'mysql'
     * @param string $query Consulta SQL a ejecutar
     * @param string $filename Nombre del archivo (default: 'informe.xlsx')
     * @param string $modulePath Ruta del módulo (ej: 'app/modules/compras1') para guardar el archivo
     * @return bool True si se generó correctamente, false en caso de error
     */


    public function exportToExcel($dbType, $query, $filename = 'informe.xlsx', $modulePath = null)
    {
        $connection = $this->getConnection($dbType);
        if (!$connection) {
            return false;
        }

        try {
            $stmt = $connection->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($data)) {
                return false;
            }
            $controllerDir = null;
            $preferredPath = null;
            $publicUrl = null;

            if ($modulePath) {
                $preferredPath = rtrim($_SERVER['DOCUMENT_ROOT'], '\\/')
                    . DIRECTORY_SEPARATOR
                    . trim(str_replace('/', DIRECTORY_SEPARATOR, $modulePath), '\\/')
                    . DIRECTORY_SEPARATOR
                    . $filename;
            } else {
                $controllerDir = $this->detectControllerDirectory();
                if ($controllerDir) {
                    $preferredPath = $controllerDir . DIRECTORY_SEPARATOR . $filename;
                } else {
                    $preferredPath = $filename;
                }
            }

            if ($this->writeExcelFile($data, $preferredPath)) {
                $this->lastExportInfo = [
                    'success' => true,
                    'path' => $preferredPath,
                    'url' => null,
                    'fallback' => false,
                ];
                return true;
            }

            // Fallback global: carpeta pública escribible para descargas
            $fallback = $this->buildFallbackPath($filename, $controllerDir, $modulePath);
            if ($this->writeExcelFile($data, $fallback['path'])) {
                $publicUrl = $fallback['url'];
                $this->lastExportInfo = [
                    'success' => true,
                    'path' => $fallback['path'],
                    'url' => $publicUrl,
                    'fallback' => true,
                ];
                return true;
            }

            $this->lastExportInfo = [
                'success' => false,
                'path' => null,
                'url' => null,
                'fallback' => false,
            ];
            return false;

        } catch (Exception $e) {
            error_log("Error en exportToExcel: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener información del último archivo exportado.
     *
     * @return array|null
     */
    public function getLastExportInfo()
    {
        return $this->lastExportInfo;
    }

    /**
     * Detecta la carpeta del controller que llamó al servicio.
     *
     * @return string|null
     */
    private function detectControllerDirectory()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($backtrace as $trace) {
            if (!isset($trace['file'])) {
                continue;
            }

            if (stripos($trace['file'], 'controller') !== false) {
                return dirname($trace['file']);
            }
        }

        return null;
    }

    /**
     * Escribe un archivo XLSX y crea el directorio destino si es necesario.
     *
     * @param array $data
     * @param string $filePath
     * @return bool
     */
    private function writeExcelFile($data, $filePath)
    {
        try {
            $dir = dirname($filePath);
            if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
                error_log("No se pudo crear directorio para Excel: {$dir}");
                return false;
            }

            clearstatcache(true, $dir);
            if (!is_writable($dir)) {
                error_log("Directorio sin permisos de escritura para Excel: {$dir}");
                return false;
            }

            if (file_exists($filePath)) {
                clearstatcache(true, $filePath);
                if (!is_writable($filePath)) {
                    error_log("Archivo Excel bloqueado o sin permisos de escritura: {$filePath}");
                    return false;
                }
            }

            $writer = WriterFactory::create(Type::XLSX);
            $writer->openToFile($filePath);
            $writer->addRow(array_keys($data[0]));

            foreach ($data as $rowData) {
                foreach ($rowData as &$value) {
                    if ($value === null) {
                        $value = '';
                    }
                }
                $writer->addRow(array_values($rowData));
            }

            $writer->close();
            return true;
        } catch (Exception $e) {
            error_log("Error escribiendo Excel en {$filePath}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Construye ruta/URL fallback pública para descargas cuando falla la ruta original.
     *
     * @param string $filename
     * @param string|null $controllerDir
     * @param string|null $modulePath
     * @return array{path:string,url:string}
     */
    private function buildFallbackPath($filename, $controllerDir = null, $modulePath = null)
    {
        $safeFilename = basename($filename);
        $moduleSlug = 'general';

        if ($modulePath) {
            $moduleSlug = trim(str_replace(['/', '\\'], '_', $modulePath), '_');
        } elseif ($controllerDir) {
            $parts = preg_split('/[\\\\\/]+/', $controllerDir);
            $moduleSlug = strtolower(implode('_', array_slice($parts, -3)));
        }

        $projectRoot = realpath(__DIR__ . '/../../');
        $relativeDir = 'public' . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . $moduleSlug;
        $absoluteDir = $projectRoot . DIRECTORY_SEPARATOR . $relativeDir;
        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $safeFilename;

        $baseUrl = defined('BASE_URL') ? rtrim((string) BASE_URL, '/') : '';

        // Si BASE_URL viene contaminado con una ruta de módulo/controller,
        // usar raíz web para evitar URLs inválidas como /app/modules/.../public/exports/...
        if (
            $baseUrl === '' ||
            stripos($baseUrl, '/app/modules/') !== false ||
            stripos($baseUrl, '/controller') !== false
        ) {
            $baseUrl = '';
        }

        $publicUrl = $baseUrl . '/public/exports/' . rawurlencode($moduleSlug) . '/' . rawurlencode($safeFilename);

        return [
            'path' => $absolutePath,
            'url' => $publicUrl,
        ];
    }

    /**
     * Exportar consulta SQL a archivo CSV con separador pipe (|)
     * 
     * @param string $dbType Tipo de conexión: 'sql' o 'mysql'
     * @param string $query Consulta SQL a ejecutar
     * @param string $filename Nombre del archivo (default: 'informe.csv')
     * @return bool True si se generó correctamente, false en caso de error
     */
    public function exportToCsvPipe($dbType, $query, $filename = 'informe.csv')
    {
        $connection = $this->getConnection($dbType);
        if (!$connection) {
            return false;
        }

        try {
            $stmt = $connection->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($data)) {
                return false;
            }

            $writer = WriterFactory::create(Type::CSV);
            $writer->setFieldDelimiter('|');
            $writer->openToFile($filename);

            // Encabezados y datos
            $writer->addRow(array_keys($data[0]));
            foreach ($data as $rowData) {
                foreach ($rowData as &$value) {
                    if ($value === null) {
                        $value = '';
                    }
                }
                $writer->addRow(array_values($rowData));
            }

            $writer->close();

            // Forzar descarga
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '";');
            readfile($filename);

            return true;
        } catch (Exception $e) {
            error_log("Error en exportToCsvPipe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar archivo TXT con separador pipe (|)
     * 
     * @param string $dbType Tipo de conexión: 'sql' o 'mysql'
     * @param string $query Consulta SQL a ejecutar
     * @param string $filename Nombre del archivo (default: 'informe.txt')
     * @return bool True si se generó correctamente, false en caso de error
     */
    public function generatePipeTxt($dbType, $query, $filename = 'informe.txt')
    {
        $connection = $this->getConnection($dbType);
        if (!$connection) {
            return false;
        }

        try {
            $stmt = $connection->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($data)) {
                echo "No hay datos para descargar.";
                return false;
            }

            // Crear contenido TXT
            $txtContent = '';
            foreach ($data as $rowData) {
                $txtContent .= implode('|', $rowData) . PHP_EOL;
            }

            // Archivo temporal
            $txtFilePath = tempnam(sys_get_temp_dir(), 'informe_');
            file_put_contents($txtFilePath, $txtContent);

            if (!file_exists($txtFilePath)) {
                echo "Error: No se pudo crear el archivo temporal.";
                return false;
            }

            // Forzar descarga
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($txtFilePath));
            readfile($txtFilePath);
            unlink($txtFilePath);

            exit();
        } catch (Exception $e) {
            error_log("Error en generatePipeTxt: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exportar array de datos a Excel
     * 
     * @param array $data Array de datos
     * @param string $filename Nombre del archivo
     * @return bool
     */
    public function exportArrayToExcel($data, $filename = 'informe.xlsx')
    {
        if (empty($data)) {
            return false;
        }

        try {
            $writer = WriterFactory::create(Type::XLSX);
            $writer->openToFile($filename);

            // Encabezados
            $writer->addRow(array_keys($data[0]));

            // Datos
            foreach ($data as $rowData) {
                foreach ($rowData as &$value) {
                    if ($value === null) {
                        $value = '';
                    }
                }
                $writer->addRow(array_values($rowData));
            }

            $writer->close();
            return true;
        } catch (Exception $e) {
            error_log("Error en exportArrayToExcel: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exportar múltiples arrays a Excel (múltiples hojas)
     * 
     * @param array $sheets Array de arrays con formato: [['nombre_hoja', [datos]], ...]
     * @param string $filename Nombre del archivo
     * @return bool
     */
    public function exportMultipleSheetsToExcel($sheets, $filename = 'informe.xlsx')
    {
        if (empty($sheets)) {
            return false;
        }

        try {
            $writer = WriterFactory::create(Type::XLSX);
            $writer->openToFile($filename);

            for ($i = 0; $i < count($sheets); $i++) {
                if ($i > 0) {
                    $writer->addNewSheetAndMakeItCurrent();
                }

                // Box Spout: establecer nombre de la hoja en la primera fila o usar el nombre
                // Por ahora solo agregamos los datos
                if (!empty($sheets[$i][1])) {
                    $writer->addRow(array_keys($sheets[$i][1][0]));

                    foreach ($sheets[$i][1] as $rowData) {
                        foreach ($rowData as &$value) {
                            if ($value === null) {
                                $value = '';
                            }
                        }
                        $writer->addRow(array_values($rowData));
                    }
                }
            }

            $writer->close();
            return true;
        } catch (Exception $e) {
            error_log("Error en exportMultipleSheetsToExcel: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ejecutar consulta y retornar datos como array
     * 
     * @param string $dbType Tipo de conexión: 'sql' o 'mysql'
     * @param string $query Consulta SQL
     * @return array|string Array de datos o "vacio" si no hay resultados
     */
    public function executeQuery($dbType, $query)
    {
        $connection = $this->getConnection($dbType);
        if (!$connection) {
            return ["vacio"];
        }

        try {
            $stmt = $connection->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return empty($data) ? ["vacio"] : $data;
        } catch (Exception $e) {
            error_log("Error en executeQuery: " . $e->getMessage());
            return ["vacio"];
        }
    }

    // =========================================================================
    // MÉTODOS DE IMPORTACIÓN (LECTURA DE EXCEL)
    // =========================================================================

    /**
     * Importar datos desde un archivo Excel (.xlsx, .xls)
     * 
     * @param string $filePath Ruta completa al archivo Excel
     * @param array $options Opciones de importación:
     *   - 'headerRow' => int (fila de encabezados, default: 1)
     *   - 'startRow' => int (fila donde empiezan los datos, default: 2)
     *   - 'sheet' => int|string (índice o nombre de la hoja, default: 0)
     *   - 'columns' => array (columnas específicas a leer, ej: ['A', 'B', 'D'])
     *   - 'maxRows' => int (límite de filas a leer, default: sin límite)
     *   - 'skipEmpty' => bool (omitir filas vacías, default: true)
     *   - 'trimValues' => bool (limpiar espacios en valores, default: true)
     * 
     * @return array ['success' => bool, 'data' => array, 'headers' => array, 'error' => string|null, 'rowCount' => int]
     * 
     * @example Uso básico
     * ```php
     * $service = new ExcelService();
     * $result = $service->importFromExcel('/path/to/file.xlsx');
     * if ($result['success']) {
     *     foreach ($result['data'] as $row) {
     *         // $row es un array asociativo con las columnas del Excel
     *     }
     * }
     * ```
     * 
     * @example Con opciones personalizadas
     * ```php
     * $result = $service->importFromExcel('/path/to/file.xlsx', [
     *     'headerRow' => 3,      // Los encabezados están en fila 3
     *     'startRow' => 4,       // Los datos empiezan en fila 4
     *     'columns' => ['A', 'C', 'E'], // Solo leer columnas A, C y E
     *     'maxRows' => 1000      // Máximo 1000 filas
     * ]);
     * ```
     */
    public function importFromExcel(string $filePath, array $options = []): array
    {
        // Valores por defecto
        $headerRow = $options['headerRow'] ?? 1;
        $startRow = $options['startRow'] ?? 2;
        $sheetIndex = $options['sheet'] ?? 0;
        $specificColumns = $options['columns'] ?? null;
        $maxRows = $options['maxRows'] ?? null;
        $skipEmpty = $options['skipEmpty'] ?? true;
        $trimValues = $options['trimValues'] ?? true;

        // Validar que el archivo existe
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'data' => [],
                'headers' => [],
                'error' => "El archivo no existe: {$filePath}",
                'rowCount' => 0
            ];
        }

        try {
            // Usar PhpSpreadsheet para lectura
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            
            // Obtener la hoja (por índice o nombre)
            if (is_string($sheetIndex)) {
                $sheet = $spreadsheet->getSheetByName($sheetIndex);
                if (!$sheet) {
                    return [
                        'success' => false,
                        'data' => [],
                        'headers' => [],
                        'error' => "No se encontró la hoja: {$sheetIndex}",
                        'rowCount' => 0
                    ];
                }
            } else {
                $sheet = $spreadsheet->getSheet($sheetIndex);
            }

            // Obtener rango de datos
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            
            // Aplicar límite de filas si está configurado
            if ($maxRows !== null) {
                $highestRow = min($highestRow, $startRow + $maxRows - 1);
            }

            // Leer encabezados
            $headers = [];
            $columnLetters = [];
            
            if ($specificColumns) {
                $columnLetters = $specificColumns;
            } else {
                // Obtener todas las columnas desde A hasta la última
                $columnLetters = $this->getColumnRange('A', $highestColumn);
            }

            foreach ($columnLetters as $col) {
                $cellValue = $sheet->getCell($col . $headerRow)->getValue();
                $headers[$col] = $trimValues ? trim((string)$cellValue) : (string)$cellValue;
            }

            // Leer datos
            $data = [];
            $rowCount = 0;

            for ($row = $startRow; $row <= $highestRow; $row++) {
                $rowData = [];
                $isEmptyRow = true;

                foreach ($columnLetters as $col) {
                    $cellValue = $sheet->getCell($col . $row)->getValue();
                    
                    // Manejar valores de fecha/hora
                    if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($sheet->getCell($col . $row))) {
                        try {
                            $cellValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cellValue)->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            // Si falla la conversión, mantener el valor original
                        }
                    }
                    
                    $value = $trimValues ? trim((string)$cellValue) : (string)$cellValue;
                    
                    if ($value !== '' && $value !== null) {
                        $isEmptyRow = false;
                    }

                    // Usar el encabezado como clave si existe, sino usar la letra de columna
                    $key = !empty($headers[$col]) ? $headers[$col] : $col;
                    $rowData[$key] = $value;
                }

                // Saltar filas vacías si está configurado
                if ($skipEmpty && $isEmptyRow) {
                    continue;
                }

                $data[] = $rowData;
                $rowCount++;
            }

            // Liberar memoria
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return [
                'success' => true,
                'data' => $data,
                'headers' => array_values($headers),
                'error' => null,
                'rowCount' => $rowCount
            ];

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'headers' => [],
                'error' => "Error al leer el archivo Excel: " . $e->getMessage(),
                'rowCount' => 0
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'headers' => [],
                'error' => "Error inesperado: " . $e->getMessage(),
                'rowCount' => 0
            ];
        }
    }

    /**
     * Importar Excel desde archivo subido ($_FILES)
     * 
     * @param array $uploadedFile El array de $_FILES['nombre_campo']
     * @param array $options Opciones de importación (ver importFromExcel)
     * @return array Resultado de la importación
     * 
     * @example
     * ```php
     * // En tu controller
     * if (isset($_FILES['excel_file'])) {
     *     $service = new ExcelService();
     *     $result = $service->importFromUpload($_FILES['excel_file']);
     *     if ($result['success']) {
     *         $datos = $result['data'];
     *     }
     * }
     * ```
     */
    public function importFromUpload(array $uploadedFile, array $options = []): array
    {
        // Validar estructura del archivo subido
        if (!isset($uploadedFile['tmp_name']) || !isset($uploadedFile['error'])) {
            return [
                'success' => false,
                'data' => [],
                'headers' => [],
                'error' => 'Estructura de archivo inválida',
                'rowCount' => 0
            ];
        }

        // Verificar errores de subida
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
                UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo',
                UPLOAD_ERR_EXTENSION => 'Extensión de PHP detuvo la subida'
            ];
            
            return [
                'success' => false,
                'data' => [],
                'headers' => [],
                'error' => $errorMessages[$uploadedFile['error']] ?? 'Error desconocido al subir archivo',
                'rowCount' => 0
            ];
        }

        // Validar extensión
        $allowedExtensions = ['xlsx', 'xls', 'csv'];
        $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'data' => [],
                'headers' => [],
                'error' => "Extensión no permitida: {$extension}. Use: " . implode(', ', $allowedExtensions),
                'rowCount' => 0
            ];
        }

        return $this->importFromExcel($uploadedFile['tmp_name'], $options);
    }

    /**
     * Obtener lista de hojas disponibles en un archivo Excel
     * 
     * @param string $filePath Ruta al archivo Excel
     * @return array ['success' => bool, 'sheets' => array, 'error' => string|null]
     */
    public function getSheetNames(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'sheets' => [],
                'error' => "El archivo no existe: {$filePath}"
            ];
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();
            
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return [
                'success' => true,
                'sheets' => $sheetNames,
                'error' => null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'sheets' => [],
                'error' => "Error al leer el archivo: " . $e->getMessage()
            ];
        }
    }

    /**
     * Genera un rango de letras de columna (A, B, C, ..., AA, AB, ...)
     * 
     * @param string $start Columna inicial
     * @param string $end Columna final
     * @return array
     */
    private function getColumnRange(string $start, string $end): array
    {
        $columns = [];
        $current = $start;
        
        while (true) {
            $columns[] = $current;
            if ($current === $end) {
                break;
            }
            $current++;
        }
        
        return $columns;
    }
}
