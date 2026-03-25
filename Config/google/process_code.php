<?php
/**
 * Script para procesar el código de autorización y generar token
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$clientSecretFile = __DIR__ . '/secret2.json';
$tokenPath = __DIR__ . '/token.json';

// El código que recibiste en la URL
$authCode = '4/0ASc3gC3kZf1c1BkLGnt09Pw8VvswOp-_3BoNyrj0_e_vf2Wbwwc0wOJ5o_87FLM6_W0-ZQ';

try {
    $client = new Google_Client();
    $client->setAuthConfig($clientSecretFile);
    $client->addScope(Google_Service_Drive::DRIVE);
    $client->setAccessType('offline');
    
    // Intercambiar código por token
    $token = $client->fetchAccessTokenWithAuthCode($authCode);
    
    if (isset($token['error'])) {
        throw new Exception('Error: ' . $token['error_description']);
    }
    
    // Guardar token
    file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT));
    
    echo '<h2 style="color:green;">✅ Token generado exitosamente!</h2>';
    echo '<p><strong>Ubicación:</strong> ' . $tokenPath . '</p>';
    echo '<h3>Contenido del token:</h3>';
    echo '<pre style="background:#f5f5f5;padding:15px;border-radius:5px;">';
    echo json_encode($token, JSON_PRETTY_PRINT);
    echo '</pre>';
    echo '<p style="color:#28a745;font-weight:bold;">✅ Ahora puedes usar service(\'GoogleDrive\')->UpFile()</p>';
    
} catch (Exception $e) {
    echo '<h2 style="color:red;">❌ Error generando token</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
}
