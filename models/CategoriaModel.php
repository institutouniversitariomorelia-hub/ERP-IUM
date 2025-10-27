<?php
// models/CategoriaModel.php

class CategoriaModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene todas las categorías o filtra por tipo.
     * @param string|null $tipo 'Ingreso', 'Egreso' o null para obtener todas.
     * @return array Lista de categorías.
     */
    public function getCategoriasByTipo($tipo = null) {
        $query = "SELECT * FROM categorias";
        $params = [];
        $types = '';

        if ($tipo !== null) {
            $query .= " WHERE tipo = ?";
            $params[] = $tipo;
            $types .= 's';
        }
        $query .= " ORDER BY nombre ASC";

        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            error_log("Error al preparar getCategoriasByTipo: " . $this->db->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $stmt->close();
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Error al ejecutar getCategoriasByTipo: " . $stmt->error);
            $stmt->close();
            return [];
        }
    }

    // --- Funciones CRUD para Categorías (añadidas para el módulo Categorías) ---

    public function getAllCategorias() {
        return $this->getCategoriasByTipo(null); // Reutiliza la función anterior
    }

    public function getCategoriaById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result->fetch_assoc();
        }
        error_log("Error al preparar getCategoriaById: " . $this->db->error);
        return null;
    }

    public function createCategoria($data) {
        $stmt = $this->db->prepare("INSERT INTO categorias (nombre, tipo, descripcion) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $data['nombre'], $data['tipo'], $data['descripcion']);
            $success = $stmt->execute();
            if (!$success) error_log("Error al ejecutar createCategoria: " . $stmt->error);
            $stmt->close();
            return $success;
        }
        error_log("Error al preparar createCategoria: " . $this->db->error);
        return false;
    }

    public function updateCategoria($id, $data) {
        $stmt = $this->db->prepare("UPDATE categorias SET nombre=?, tipo=?, descripcion=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("sssi", $data['nombre'], $data['tipo'], $data['descripcion'], $id);
            $success = $stmt->execute();
            if (!$success) error_log("Error al ejecutar updateCategoria: " . $stmt->error);
            $stmt->close();
            return $success;
        }
        error_log("Error al preparar updateCategoria: " . $this->db->error);
        return false;
    }

    public function deleteCategoria($id) {
        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            if (!$success) error_log("Error al ejecutar deleteCategoria: " . $stmt->error);
            $stmt->close();
            return $success;
        }
        error_log("Error al preparar deleteCategoria: " . $this->db->error);
        return false;
    }
}
?>