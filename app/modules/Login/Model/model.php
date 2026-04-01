<?php

namespace Modules\Login\Model;

use App\Core\BaseModel;

class Model extends BaseModel
{
    /**
     * Constructor: Configura la conexión a la base de datos
     */
    public function __construct()
    {
        parent::__construct('mysql'); // Conexión por defecto
    }

    /**
     * Método de ejemplo para obtener datos
     * 
     * @return array
     */
    public function getUserLogin($usuario, $password)
    {
        try {
            $sql = "SELECT usuSec,UsuCod,UsuNom,UsuEst,tb2.PerNom
            FROM usuario tb1 
            INNER JOIN perfil tb2 on tb2.PerSec=tb1.PerSec
            WHERE tb1.UsuEst='A' AND tb2.PerEst='A' AND tb1.UsuCod = :usuario AND tb1.UsuPass = :password";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario', $usuario, self::PARAM_STR);
            $stmt->bindParam(':password', $password, self::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(self::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Método de ejemplo para insertar datos
     * 
     * @param array $data
     * @return bool
     */
    public function insertData($data)
    {
        try {
            $columnas = implode(", ", array_keys($data));
            $valores = array_values($data);
            $placeholders = implode(", ", array_fill(0, count($valores), "?"));

            $sql = "INSERT INTO empresa ($columnas) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($valores);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'empresa creada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear la empresa'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error al insertar empresa: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear la empresa: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Método de ejemplo para actualizar datos
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateData($id, $data)
    {
        try {
            $sql = "UPDATE tabla_ejemplo SET campo1 = :campo1, campo2 = :campo2 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':campo1', $data['campo1'], self::PARAM_STR);
            $stmt->bindParam(':campo2', $data['campo2'], self::PARAM_STR);
            $stmt->bindParam(':id', $id, self::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Método de ejemplo para eliminar datos
     * 
     * @param int $id
     * @return bool
     */
    public function deleteData($id)
    {
        try {
            $sql = "DELETE FROM tabla_ejemplo WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, self::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }
}
