<?php
// models/PresupuestoModel.php

class PresupuestoModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene todos los presupuestos asignados.
     * @return array Lista de presupuestos.
     */
    public function getAllPresupuestos() {
        $query = "SELECT * FROM presupuestos ORDER BY categoria ASC"; // Ordenamos por categoría
        $result = $this->db->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Error al obtener presupuestos: " . $this->db->error);
            return [];
        }
    }

    /**
     * Obtiene un presupuesto específico por su ID.
     * @param int $id ID del presupuesto.
     * @return array|null Datos del presupuesto o null.
     */
    public function getPresupuestoById($id) {
        $query = "SELECT * FROM presupuestos WHERE id = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result->fetch_assoc();
        } else {
            error_log("Error al preparar getPresupuestoById: " . $this->db->error);
            return null;
        }
    }

     /**
     * Obtiene un presupuesto por el nombre de la categoría.
     * @param string $categoria Nombre de la categoría.
     * @return array|null Datos del presupuesto o null.
     */
    public function getPresupuestoByCategoria($categoria) {
        $query = "SELECT * FROM presupuestos WHERE categoria = ?";
        $stmt = $this->db->prepare($query);
         if ($stmt) {
            $stmt->bind_param("s", $categoria);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result->fetch_assoc();
        } else {
            error_log("Error al preparar getPresupuestoByCategoria: " . $this->db->error);
            return null;
        }
    }

    /**
     * Crea o actualiza un presupuesto para una categoría.
     * Solo debe haber un presupuesto por categoría.
     * @param array $data Datos del presupuesto (categoria, monto, fecha).
     * @param int|null $id ID del presupuesto si se está actualizando explícitamente.
     * @return bool True si la operación fue exitosa, false en caso contrario.
     */
    public function savePresupuesto($data, $id = null) {
        // Verificar si ya existe un presupuesto para esta categoría (excluyendo el ID actual si es update)
        $existing = $this->getPresupuestoByCategoria($data['categoria']);

        if ($existing && (empty($id) || $existing['id'] != $id)) {
            // Ya existe para esta categoría y no es el que estamos editando, actualizamos el existente.
            $id = $existing['id'];
             error_log("Actualizando presupuesto existente para categoría: " . $data['categoria']);
        } elseif (!empty($id)) {
            // Es una actualización por ID directo
             error_log("Actualizando presupuesto por ID: " . $id);
        } else {
             // Es una creación nueva
             error_log("Creando nuevo presupuesto para categoría: " . $data['categoria']);
             $id = null; // Asegura que $id es null para la inserción
        }

        if ($id) { // Actualizar
            $query = "UPDATE presupuestos SET categoria=?, monto=?, fecha=? WHERE id=?";
            $stmt = $this->db->prepare($query);
            if (!$stmt) { error_log("Error al preparar updatePresupuesto: " . $this->db->error); return false; }
            $stmt->bind_param("sdsi", $data['categoria'], $data['monto'], $data['fecha'], $id);
        } else { // Crear
            $query = "INSERT INTO presupuestos (categoria, monto, fecha) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
             if (!$stmt) { error_log("Error al preparar createPresupuesto: " . $this->db->error); return false; }
            $stmt->bind_param("sds", $data['categoria'], $data['monto'], $data['fecha']);
        }

        $success = $stmt->execute();
        if (!$success) error_log("Error al ejecutar savePresupuesto: " . $stmt->error);
        $stmt->close();
        return $success;
    }


    /**
     * Elimina un presupuesto de la base de datos.
     * @param int $id ID del presupuesto a eliminar.
     * @return bool True si se eliminó con éxito, false en caso contrario.
     */
    public function deletePresupuesto($id) {
        $query = "DELETE FROM presupuestos WHERE id = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            if (!$success) error_log("Error al ejecutar deletePresupuesto: " . $stmt->error);
            $stmt->close();
            return $success;
        } else {
            error_log("Error al preparar deletePresupuesto: " . $this->db->error);
            return false;
        }
    }
}
?>