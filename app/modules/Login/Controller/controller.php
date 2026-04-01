<?php

namespace Modules\Login\Controller;

use App\Core\BaseController;
use Modules\Login\Model\Model;

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
    public function login()
    {
        $usuario = $this->input('username');
        $password = $this->input('password');

        $data = array(
            "usuario" => $usuario,
            "password" => md5($password)
        );
        $getUserLogin = $this->MODEL->getUserLogin($usuario, md5($password));
        if (!empty($getUserLogin)) {
            $resp = array(
                "getUserLogin" => $getUserLogin,
                "ip" => $this->GetIp()
            );
            $this->responseOk($resp, "Login exitoso");
        } else {
            $this->responseError("Usuario o contraseña incorrectos");
        }
    }
    private function GetIp()
    {
        $endpoint = "http://ip-api.com/json/";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        return $data;
    }
}

// Crear instancia del controlador
$controller = new Controller();

// Manejo de peticiones AJAX
if (isset($_POST['funcion'])) {
    call_user_func(array($controller, $_POST['funcion']));
}
