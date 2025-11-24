<?php
// src/Presupuestos/Models/PresupuestoModel.php (CORREGIDO PARA COINCIDIR CON LA BD)

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
        // Nuevo diseño: incluir parent_presupuesto, nombre y permitir id_categoria NULL para presupuestos generales
        $query = "SELECT p.*, p.id_presupuesto AS id,
                         c.id_categoria, c.nombre AS cat_nombre,
                         p.parent_presupuesto, p.nombre
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
     * Obtiene solo los sub-presupuestos (presupuestos con parent_presupuesto NOT NULL)
     * Para usar en el dropdown de egresos
     * @return array Lista de sub-presupuestos con nombre, fecha y categoría
     */
    public function getSubPresupuestos() {
        $query = "SELECT p.id_presupuesto, p.nombre, p.fecha, p.monto_limite,
                         c.nombre AS cat_nombre, p.id_categoria
                  FROM presupuestos p
                  LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
                  WHERE p.parent_presupuesto IS NOT NULL
                  ORDER BY p.fecha DESC, p.id_presupuesto DESC";
        $result = $this->db->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        error_log('Error al obtener sub-presupuestos: ' . $this->db->error);
        return [];
    }

    /**
     * Obtiene presupuestos en alerta (>=90% consumidos)
     * @return array Lista de presupuestos con alerta
     */
    public function getPresupuestosEnAlerta() {
        $query = "SELECT p.id_presupuesto, p.nombre, p.monto_limite,
                         COALESCE(SUM(e.monto), 0) AS gastado,
                         ROUND((COALESCE(SUM(e.monto), 0) / p.monto_limite) * 100, 2) AS porcentaje
                  FROM presupuestos p
                  LEFT JOIN egresos e ON e.id_presupuesto = p.id_presupuesto
                  WHERE p.parent_presupuesto IS NOT NULL
                  GROUP BY p.id_presupuesto, p.nombre, p.monto_limite
                  HAVING porcentaje >= 90
                  ORDER BY porcentaje DESC";
        $result = $this->db->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        error_log('Error al obtener presupuestos en alerta: ' . $this->db->error);
        return [];
    }

    /**
     * Obtiene un presupuesto específico por su ID.
     * @param int $id ID del presupuesto (id_presupuesto).
     * @return array|null Datos del presupuesto o null.
     */
    public function getPresupuestoById($id) {
     $query = "SELECT p.*, p.id_presupuesto AS id,
                c.id_categoria, c.nombre AS cat_nombre,
                p.parent_presupuesto
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
     * Retorna la suma de los montos límite de los presupuestos hijos de un presupuesto general
     */
    public function getSumaPresupuestosHijos($parentId) {
        $query = "SELECT COALESCE(SUM(monto_limite), 0) as total FROM presupuestos WHERE parent_presupuesto = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) { error_log('Error preparar getSumaPresupuestosHijos: ' . $this->db->error); return 0; }
        $stmt->bind_param('i', $parentId);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        return floatval($total);
    }

    /**
     * Retorna la suma de egresos asociados a un presupuesto (por id_presupuesto)
     */
    public function getGastadoEnPresupuesto($presupuestoId) {
        $query = "SELECT COALESCE(SUM(monto), 0) as total FROM egresos WHERE id_presupuesto = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) { error_log('Error preparar getGastadoEnPresupuesto: ' . $this->db->error); return 0; }
        $stmt->bind_param('i', $presupuestoId);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        return floatval($total);
    }

    /**
     * Crea o actualiza un presupuesto.
     * @param array $data Datos del presupuesto (monto_limite, fecha, id_user).
     * @param int|null $id ID del presupuesto si es actualización.
     * @return bool True si la operación fue exitosa, false en caso contrario.
     */
    public function savePresupuesto($data, $id = null) {
        
        // CORREGIDO: Lógica adaptada a la nueva tabla
        // id_categoria puede ser NULL para presupuestos generales
        // Manejar parent_presupuesto y nombre opcional
        $parent = isset($data['parent_presupuesto']) && is_numeric($data['parent_presupuesto']) ? (int)$data['parent_presupuesto'] : null;
        $cat = isset($data['id_categoria']) && is_numeric($data['id_categoria']) && $data['id_categoria'] > 0 ? (int)$data['id_categoria'] : null;
        $nombre = isset($data['nombre']) ? trim($data['nombre']) : null;

        if ($id) { // Actualizar
            $query = "UPDATE presupuestos SET monto_limite=?, fecha=?, id_categoria=?, id_user=?, parent_presupuesto=?, nombre=? WHERE id_presupuesto=?";
            $stmt = $this->db->prepare($query);
            if (!$stmt) { error_log('Error al preparar updatePresupuesto: ' . $this->db->error); return false; }
            $stmt->bind_param('dsiissi', $data['monto_limite'], $data['fecha'], $cat, $data['id_user'], $parent, $nombre, $id);
        } else { // Crear
            $query = "INSERT INTO presupuestos (monto_limite, fecha, id_categoria, id_user, parent_presupuesto, nombre) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            if (!$stmt) { error_log('Error al preparar createPresupuesto: ' . $this->db->error); return false; }
            $stmt->bind_param('dsiiss', $data['monto_limite'], $data['fecha'], $cat, $data['id_user'], $parent, $nombre);
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