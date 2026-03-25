<?php
/**
 * Script para generar token.json de Google Drive
 * Ejecutar una sola vez desde el navegador
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$clientSecretFile = __DIR__ . '/secret2.json';
$tokenPath = __DIR__ . '/token.json';

$client = new Google_Client();
$client->setAuthConfig($clientSecretFile);
$client->addScope(Google_Service_Drive::DRIVE);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Manejar callback de OAuth2
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        die('Error obteniendo token: ' . $token['error_description']);
    }
    
    // Guardar token
    file_put_contents($tokenPath, json_encode($token));
    
    echo '<h2>✅ Token generado correctamente!</h2>';
    echo '<p>Archivo guardado en: ' . $tokenPath . '</p>';
    echo '<pre>' . json_encode($token, JSON_PRETTY_PRINT) . '</pre>';
    echo '<p><a href="/PisciWeb/">Volver al sistema</a></p>';
    exit;
}

// Si no hay código, mostrar enlace de autorización
$authUrl = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generar Token de Google Drive</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .btn { 
            display: inline-block; 
            background: #4285f4; 
            color: white; 
            padding: 15px 30px; 
            text-decoration: none; 
            border-radius: 5px; 
            font-size: 18px;
        }
        .btn:hover { background: #357ae8; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>🔐 Generador de Token para Google Drive</h1>
    
    <div class="warning">
        <strong>⚠️ Importante:</strong> Este proceso solo se debe ejecutar UNA vez para generar el token inicial.
    </div>
    
    <h2>Instrucciones:</h2>
    <ol>
        <li>Haz clic en el botón de abajo</li>
        <li>Inicia sesión con tu cuenta de Google</li>
        <li>Autoriza el acceso a Google Drive</li>
        <li>Serás redirigido automáticamente y el token se generará</li>
    </ol>
    
    <p>
        <a href="<?php echo $authUrl; ?>" class="btn">
            🔓 Autorizar Acceso a Google Drive
        </a>
    </p>
    
    <hr>
    <p><small>Ubicación del token: <?php echo $tokenPath; ?></small></p>
</body>
</html>
