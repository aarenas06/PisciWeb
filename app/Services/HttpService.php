<?php

namespace App\Services;

/**
 * HttpService
 * 
 * Service para gestión centralizada de peticiones HTTP.
 * Envuelve funcionalidad cURL para APIs externas.
 * Elimina duplicación de código curl en controllers.
 
 * ```
 */
class HttpService extends BaseService
{
    /**
     * Timeout por defecto para peticiones HTTP (segundos)
     */
    private const DEFAULT_TIMEOUT = 30;

    public function __construct()
    {
        // No necesita conexión BD (solo HTTP requests)
    }

    /**
     * Realizar petición POST
     * 
     * @param string $url URL destino
     * @param array|string $data Datos a enviar (array se convierte a JSON)
     * @param array $headers Headers HTTP adicionales
     * @param int $timeout Timeout en segundos
     * @return array ['response' => '', 'http_code' => 200, 'error' => null]
     */
    public function post($url, $data, $headers = [], $timeout = self::DEFAULT_TIMEOUT)
    {
        $ch = curl_init($url);

        // Convertir array a JSON si es necesario
        $postData = is_array($data) ? json_encode($data) : $data;

        // Headers por defecto si enviamos JSON
        if (is_array($data) && !$this->hasContentTypeHeader($headers)) {
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'response' => $response,
            'http_code' => $httpCode,
            'error' => $error ?: null
        ];
    }

    /**
     * Realizar petición GET
     * 
     * @param string $url URL destino
     * @param array $params Parámetros query string
     * @param array $headers Headers HTTP adicionales
     * @param int $timeout Timeout en segundos
     * @return array ['response' => '', 'http_code' => 200, 'error' => null]
     */
    public function get($url, $params = [], $headers = [], $timeout = self::DEFAULT_TIMEOUT)
    {
        // Agregar query string si hay parámetros
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'response' => $response,
            'http_code' => $httpCode,
            'error' => $error ?: null
        ];
    }

    /**
     * Método específico para API de mejorar redacción
     * 
     * @param string $texto Texto a mejorar
     * @return array Respuesta de la API
     */
    public function mejorarRedaccion($texto)
    {
        $result = $this->post(
            'http://104.225.140.83:5000/mejorar_redaccion',
            ['texto' => $texto],
            ['Content-Type: application/json'],
            30
        );

        if ($result['http_code'] === 200 && $result['response']) {
            return json_decode($result['response'], true);
        }

        return null;
    }

    /**
     * Verificar si los headers ya incluyen Content-Type
     * 
     * @param array $headers Lista de headers
     * @return bool
     */
    private function hasContentTypeHeader($headers)
    {
        foreach ($headers as $header) {
            if (stripos($header, 'Content-Type:') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Descargar archivo de una URL
     * 
     * @param string $url URL del archivo
     * @param string $destino Ruta destino local
     * @return bool True si se descargó correctamente
     */
    public function downloadFile($url, $destino)
    {
        $ch = curl_init($url);
        $fp = fopen($destino, 'wb');

        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 300, // 5 minutos para archivos grandes
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        fclose($fp);

        return $httpCode === 200;
    }
}
