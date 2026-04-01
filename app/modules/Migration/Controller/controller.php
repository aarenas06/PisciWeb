<?php

namespace Modules\Migration\Controller;

use App\Core\BaseController;
use Modules\Migration\Model\Model;

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
    public function cargueEmpresa()
    {
        try {
            // Validar que exista el archivo
            if (!$this->hasFile('archivo')) {
                return $this->responseError('No se recibió ningún archivo');
            }

            $adjunto = $this->getFile('archivo');
            $Servicio = service('Excel');


            $Sheet = $Servicio->getSheetNames($adjunto['tmp_name']);
            $infoExcel = $Servicio->importFromUpload($adjunto);

            $listEmpresas = $infoExcel['data'] ?? [];

            $insertados = 0;
            $errores = [];
            $total = count($listEmpresas);

            foreach ($listEmpresas as $index => $Le) {
                $datos = [
                    "EmpreNom" => $Le['Nombre_Empresa'] ?? '',
                    "EmpreNit" => $Le['Nit_Empresa'] ?? '',
                    "EmpreNomLeg" => $Le['Nombre_Legal'] ?? '',
                    "EmpreEst" => "A",
                    "EmpreCodSiigo" => $Le['Codigo_Piscitec_empresa'] ?? '',
                    "EmpreCodTech" => $Le['Codigo_Tech_empresa'] ?? null,
                ];

                $insertData = $this->MODEL->insertData($datos);

                if ($insertData) {
                    $insertados++;
                } else {
                    $errores[] = [
                        'fila' => $index + 2, // +2 porque fila 1 es encabezado y arrays empiezan en 0
                        'empresa' => $Le['Nombre_Empresa'] ?? 'Sin nombre',
                        'nit' => $Le['Nit_Empresa'] ?? 'Sin NIT'
                    ];
                }
            }

            // Preparar respuesta según resultados
            $resultado = [
                'total' => $total,
                'insertados' => $insertados,
                'fallidos' => count($errores),
                'errores' => $errores
            ];

            if (count($errores) === 0) {
                $this->responseOk($resultado, "Se insertaron {$insertados} empresas correctamente");
            } elseif ($insertados > 0) {
                $this->responseOk($resultado, "Se insertaron {$insertados} de {$total} empresas. " . count($errores) . " fallaron.");
            } else {
                $this->responseError("No se pudo insertar ninguna empresa", 400, $errores);
            }
        } catch (\Exception $e) {
            $this->responseServerError('Error al procesar el archivo', $e);
        }
    }
    public function cargueSucursal()
    {
        try {
            // Validar que exista el archivo
            if (!$this->hasFile('archivo')) {
                return $this->responseError('No se recibió ningún archivo');
            }

            $adjunto = $this->getFile('archivo');
            $Servicio = service('Excel');
            $Sheet = $Servicio->getSheetNames($adjunto['tmp_name']);
            $infoExcel = $Servicio->importFromUpload($adjunto);
            $this->responseOk($infoExcel, 'Archivo procesado correctamente');
            $ListSucusales = $infoExcel['data'];

            $insertados = 0;
            $errores = [];
            $total = count($ListSucusales);
            foreach ($ListSucusales as $Ls) {
                $Nit_Empresa = $Ls['Nit_Empresa'] ?? '';
                $Nombre_Centro = $Ls['Nombre_Centro'] ?? '';
                $Codigo_Piscitec_CP = $Ls['Codigo_Piscitec_CP'] ?? '';
                $Codigo_Siigo_Cp = $Ls['Codigo_Siigo_Cp'] ?? '';

                $getEmpresaByNit = $this->MODEL->getEmpresaByNit($Nit_Empresa);
                if ($getEmpresaByNit) {
                    $datos = array(
                        "EmpreSec " => $getEmpresaByNit['EmpreSec'],
                        "SucuNom" => $Codigo_Siigo_Cp,
                        "SucuEst" => 'A',
                        "SucCodSiigo" => $Codigo_Siigo_Cp,
                        "SucCodTech" => $Codigo_Piscitec_CP
                    );
                    $insertData = $this->MODEL->insertSucursal($datos);
                    // if ($insertData) {
                    //     $insertados++;
                    // } else {
                    //     $errores[] = [
                    //         'fila' => $Ls + 2, // +2 porque fila 1 es encabezado y arrays empiezan en 0
                    //         'sucursal' => $Nombre_Centro ?? 'Sin nombre',
                    //         'nit' => $Nit_Empresa ?? 'Sin NIT'
                    //     ];
                    // }
                }
            }
        } catch (\Exception $e) {
            $this->responseServerError('Error al procesar el archivo', $e);
        }
    }
}

// Crear instancia del controlador
$controller = new Controller();

// Manejo de peticiones AJAX
if (isset($_POST['funcion'])) {
    call_user_func(array($controller, $_POST['funcion']));
}
