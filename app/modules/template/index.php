<?php

use Modules\Template\Controller\Controller;

$controller = new Controller();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo Template</title>
    <?php module_style('template/css'); ?>

<body>
    <div class="module-container">
        <div class="module-icon">📦</div>
        <h1 class="module-title">Módulo Generado</h1>
        <p class="module-message">
            Este módulo fue generado automáticamente por el sistema de plantillas de DiscolnetV2.
        </p>
        <div class="module-badge">Template Module</div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php module_script('template'); ?>
</body>

</html>