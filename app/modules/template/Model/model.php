<?php

namespace Modules\Template\Model;

use App\Core\BaseModel;

class Model extends BaseModel
{
    /**
     * Constructor: Configura la conexión a la base de datos
     */
    public function __construct()
    {
        parent::__construct('sql'); // Conexión por defecto
    }

    /**
     * Método de ejemplo para obtener datos
     * 
     * @return array
     */
    public function getData()
    {
        try {
            $sql = "SELECT * FROM tabla_ejemplo LIMIT 10";
            $stmt = $this->db->prepare($sql);
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
            $sql = "INSERT INTO tabla_ejemplo (campo1, campo2) VALUES (:campo1, :campo2)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':campo1', $data['campo1'], self::PARAM_STR);
            $stmt->bindParam(':campo2', $data['campo2'], self::PARAM_STR);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
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
