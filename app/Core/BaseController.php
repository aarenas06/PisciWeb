<?php

namespace App\Core;

/**
 * BaseController - Clase base para todos los Controllers
 * 
 * Proporciona métodos estandarizados para:
 * - Respuestas JSON (éxito/error)
 * - Validación de parámetros
 * - Manejo de peticiones AJAX
 * - Headers de seguridad
 * 
 * @package App\Core
 * @author Sistemas PisciWeb
 * @version 1.0
 */
abstract class BaseController
{
    /**
     * Códigos HTTP comunes
     */
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_ERROR = 500;

    /**
     * Envía una respuesta JSON de éxito
     * 
     * @param mixed $data Datos a enviar
     * @param string $message Mensaje de éxito
     * @param int $httpCode Código HTTP (default: 200)
     * @return void
     * 
     * @example
     * ```php
     * $this->responseOk(['usuario' => $user], 'Usuario encontrado');
     * $this->responseOk($lista, 'Listado obtenido', 200);
     * ```
     */
    protected function responseOk($data = null, string $message = 'Operación exitosa', int $httpCode = self::HTTP_OK): void
    {
        $this->sendJson([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $httpCode);
    }

    /**
     * Envía una respuesta JSON de error
     * 
     * @param string $message Mensaje de error
     * @param int $httpCode Código HTTP (default: 400)
     * @param array|null $errors Detalles adicionales de errores
     * @return void
     * 
     * @example
     * ```php
     * $this->responseError('Usuario no encontrado', 404);
     * $this->responseError('Validación fallida', 400, ['campo' => 'requerido']);
     * ```
     */
    protected function responseError(string $message, int $httpCode = self::HTTP_BAD_REQUEST, ?array $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $this->sendJson($response, $httpCode);
    }

    /**
     * Envía una respuesta JSON de error del servidor
     * 
     * @param string $message Mensaje de error
     * @param \Throwable|null $exception Excepción para logging
     * @return void
     */
    protected function responseServerError(string $message = 'Error interno del servidor', ?\Throwable $exception = null): void
    {
        if ($exception && defined('APP_DEBUG') && APP_DEBUG) {
            $this->sendJson([
                'success' => false,
                'message' => $message,
                'debug' => [
                    'exception' => get_class($exception),
                    'error' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ]
            ], self::HTTP_INTERNAL_ERROR);
        } else {
            $this->responseError($message, self::HTTP_INTERNAL_ERROR);
        }

        // Log del error
        if ($exception) {
            error_log("[Controller Error] {$message}: " . $exception->getMessage());
        }
    }

    /**
     * Envía una respuesta JSON de recurso creado
     * 
     * @param mixed $data Datos del recurso creado
     * @param string $message Mensaje
     * @return void
     */
    protected function responseCreated($data = null, string $message = 'Recurso creado exitosamente'): void
    {
        $this->responseOk($data, $message, self::HTTP_CREATED);
    }

    /**
     * Envía una respuesta JSON de no autorizado
     * 
     * @param string $message Mensaje
     * @return void
     */
    protected function responseUnauthorized(string $message = 'No autorizado'): void
    {
        $this->responseError($message, self::HTTP_UNAUTHORIZED);
    }

    /**
     * Envía una respuesta JSON de recurso no encontrado
     * 
     * @param string $message Mensaje
     * @return void
     */
    protected function responseNotFound(string $message = 'Recurso no encontrado'): void
    {
        $this->responseError($message, self::HTTP_NOT_FOUND);
    }

    /**
     * Envía la respuesta JSON con headers apropiados
     * 
     * @param array $data Datos a enviar
     * @param int $httpCode Código HTTP
     * @return void
     */
    private function sendJson(array $data, int $httpCode): void
    {
        // Evitar output previo
        if (ob_get_length()) {
            ob_clean();
        }

        // Headers de respuesta
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        
        // Headers de seguridad opcionales
        header('X-Content-Type-Options: nosniff');
        
        // Codificar y enviar
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // =========================================================================
    // MÉTODOS DE VALIDACIÓN
    // =========================================================================

    /**
     * Valida que existan los parámetros requeridos en POST
     * 
     * @param array $required Lista de campos requeridos
     * @return array|false Array con los valores si todos existen, false si falta alguno
     * 
     * @example
     * ```php
     * $data = $this->validateRequired(['nombre', 'email']);
     * if ($data === false) return; // Ya envió responseError
     * ```
     */
    protected function validateRequired(array $required): array|false
    {
        $missing = [];
        $values = [];

        foreach ($required as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $missing[] = $field;
            } else {
                $values[$field] = trim($_POST[$field]);
            }
        }

        if (!empty($missing)) {
            $this->responseError(
                'Faltan campos requeridos: ' . implode(', ', $missing),
                self::HTTP_BAD_REQUEST,
                ['missing_fields' => $missing]
            );
            return false;
        }

        return $values;
    }

    /**
     * Obtiene un valor de POST con valor por defecto
     * 
     * @param string $key Nombre del campo
     * @param mixed $default Valor por defecto
     * @param bool $trim Aplicar trim al valor
     * @return mixed
     */
    protected function input(string $key, $default = null, bool $trim = true)
    {
        if (!isset($_POST[$key])) {
            return $default;
        }

        $value = $_POST[$key];
        return $trim && is_string($value) ? trim($value) : $value;
    }

    /**
     * Obtiene todos los valores de POST como array
     * 
     * @param array|null $only Solo estos campos (null = todos)
     * @return array
     */
    protected function allInput(?array $only = null): array
    {
        if ($only === null) {
            return $_POST;
        }

        return array_intersect_key($_POST, array_flip($only));
    }

    /**
     * Verifica si la petición es AJAX
     * 
     * @return bool
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Verifica si la petición es POST
     * 
     * @return bool
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Verifica si existe un archivo subido
     * 
     * @param string $name Nombre del campo del archivo
     * @return bool
     */
    protected function hasFile(string $name): bool
    {
        return isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Obtiene información del archivo subido
     * 
     * @param string $name Nombre del campo
     * @return array|null
     */
    protected function getFile(string $name): ?array
    {
        return $this->hasFile($name) ? $_FILES[$name] : null;
    }
}
