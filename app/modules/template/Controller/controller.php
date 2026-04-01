<?php

namespace Modules\Template\Controller;

use App\Core\BaseController;
use Modules\Template\Model\Model;

class Controller extends BaseController
{
    public $MODEL;

    /**
     * Constructor: Instancia el Model
     */
    public function __construct()
    {
        $this->MODEL = new Model();
    }

    /**
     * Método de ejemplo
     * Puedes agregar tus propios métodos aquí
     */
    public function index()
    {
        // Tu código aquí
        $this->responseOk(null, 'Módulo Template funcionando');
    }
}

// Crear instancia del controlador
$controller = new Controller();

// Manejo de peticiones AJAX
if (isset($_POST['funcion'])) {
    call_user_func(array($controller, $_POST['funcion']));
}
