<?php
// models/PresupuestoModel.php (CORREGIDO PARA COINCIDIR CON LA BD)

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
        // Nuevo diseño: presupuesto -> id_categoria; un presupuesto pertenece a una categoría
        $query = "SELECT p.*, p.id_presupuesto AS id,
                         c.id_categoria, c.nombre AS cat_nombre
                  FROM presupuestos p
                  LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
                  ORDER BY p.id_presupuesto DESC";
        $result = $this->db->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        error_log('Error al obtener presupuestos: ' . $this->db->error);
        return [];
    }

    /**
     * Obtiene un presupuesto específico por su ID.
     * @param int $id ID del presupuesto (id_presupuesto).
     * @return array|null Datos del presupuesto o null.
     */
    public function getPresupuestoById($id) {
        $query = "SELECT p.*, p.id_presupuesto AS id,
                         c.id_categoria, c.nombre AS cat_nombre
                  FROM presupuestos p
                  LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
                  WHERE p.id_presupuesto = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            error_log('Error al preparar getPresupuestoById: ' . $this->db->error);
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pres = $result->fetch_assoc();
        $stmt->close();
        return $pres;
    }

    /**
     * Crea o actualiza un presupuesto.
     * @param array $data Datos del presupuesto (monto_limite, fecha, id_user).
     * @param int|null $id ID del presupuesto si es actualización.
     * @return bool True si la operación fue exitosa, false en caso contrario.
     */
    public function savePresupuesto($data, $id = null) {
        
        // CORREGIDO: Lógica adaptada a la nueva tabla
        // Validar que venga id_categoria
        if (empty($data['id_categoria']) || !is_numeric($data['id_categoria'])) {
            error_log('savePresupuesto: id_categoria faltante o inválido');
            return false;
        }

        if ($id) { // Actualizar
            $query = "UPDATE presupuestos SET monto_limite=?, fecha=?, id_categoria=?, id_user=? WHERE id_presupuesto=?";
            $stmt = $this->db->prepare($query);
            if (!$stmt) { error_log('Error al preparar updatePresupuesto: ' . $this->db->error); return false; }
            $stmt->bind_param('dsiii', $data['monto_limite'], $data['fecha'], $data['id_categoria'], $data['id_user'], $id);
        } else { // Crear
            $query = "INSERT INTO presupuestos (monto_limite, fecha, id_categoria, id_user) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            if (!$stmt) { error_log('Error al preparar createPresupuesto: ' . $this->db->error); return false; }
            $stmt->bind_param('dsii', $data['monto_limite'], $data['fecha'], $data['id_categoria'], $data['id_user']);
        }

        $success = $stmt->execute();
        if (!$success) error_log("Error al ejecutar savePresupuesto: " . $stmt->error);
        $stmt->close();
        return $success;
    }


    /**
     * Elimina un presupuesto de la base de datos.
     * @param int $id ID (id_presupuesto) del presupuesto a eliminar.
     * @return bool True si se eliminó con éxito, false en caso contrario.
     */
    public function deletePresupuesto($id) {
        // CORREGIDO: Busca por 'id_presupuesto'
        $query = "DELETE FROM presupuestos WHERE id_presupuesto = ?";
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