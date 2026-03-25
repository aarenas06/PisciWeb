<?php
/**
 * Keep-Alive de Sesión
 * Mantiene la sesión activa respondiendo a pings del cliente
 * Previene que la sesión se cierre por inactividad
 */

require_once __DIR__ . '/../app/Core/bootstrap.php';

// Headers para evitar caché
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Type: application/json; charset=utf-8');

// Validar que hay sesión activa
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'Sesión no válida o expirada'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Actualizar el timestamp de última actividad
    // Esto es suficiente para mantener la sesión viva sin regenerar ID
    $_SESSION['last_activity'] = time();
    
    // Responder con éxito
    echo json_encode([
        'success' => true,
        'message' => 'Sesión renovada',
        'usuario' => $_SESSION['usuario'],
        'timestamp' => date('Y-m-d H:i:s'),
        'session_id' => session_id(),
        'session_maxlifetime' => ini_get('session.gc_maxlifetime')
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al renovar sesión: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
