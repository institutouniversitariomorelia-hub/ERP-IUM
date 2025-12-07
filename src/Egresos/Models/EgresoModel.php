<?php
// src/Egresos/Models/EgresoModel.php (LIMPIO, SIN id_presupuesto/activo_fijo)

class EgresoModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene todos los egresos CON el nombre de la categoría.
     * @return array Lista de egresos o array vacío.
     */
    public function getAllEgresos() {
        $query = "SELECT
                    e.*,
                    c.nombre AS nombre_categoria
                  FROM egresos e
                  LEFT JOIN categorias c ON e.id_categoria = c.id_categoria
                  ORDER BY e.fecha DESC, e.folio_egreso DESC";

        $result = $this->db->query($query);
        if ($result) {
            $egresos = [];
            while ($row = $result->fetch_assoc()) {
                $row['id'] = $row['folio_egreso'];
                $egresos[] = $row;
            }
            return $egresos;
        } else {
            error_log("Error al obtener egresos con JOIN: " . $this->db->error);
            return [];
        }
    }

    /**
     * Obtiene un egreso específico por su ID (folio_egreso).
     */
    public function getEgresoById($folio_egreso) {
        $query = "SELECT * FROM egresos WHERE folio_egreso = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $folio_egreso);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data;
        } else {
            error_log("Error al preparar getEgresoById: " . $this->db->error);
            return null;
        }
    }

    /**
     * Crea un nuevo egreso (sin id_presupuesto, alineado al changelog).
     */
    public function createEgreso($data) {
        $proveedor   = isset($data['proveedor']) && trim($data['proveedor']) !== '' ? trim($data['proveedor']) : null;
        $descripcion = isset($data['descripcion']) && trim($data['descripcion']) !== '' ? trim($data['descripcion']) : null;
        $doc_amparo  = isset($data['documento_de_amparo']) && trim($data['documento_de_amparo']) !== '' ? trim($data['documento_de_amparo']) : null;

        if (!isset($data['monto']) || $data['monto'] === '' || !is_numeric($data['monto']) || $data['monto'] < 0 ||
            empty($data['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha']) ||
            empty($data['destinatario']) || empty($data['forma_pago']) ||
            empty($data['id_user']) || !filter_var($data['id_user'], FILTER_VALIDATE_INT) ||
            empty($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT)) {
            throw new Exception("Datos inválidos o faltantes para crear egreso. Verifique fecha (YYYY-MM-DD) y campos obligatorios.");
        }

        $id_categoria = (int)$data['id_categoria'];
        $id_user      = (int)$data['id_user'];
        $monto        = (float)$data['monto'];
        $fecha        = $data['fecha'];
        $destinatario = trim($data['destinatario']);
        $forma_pago   = $data['forma_pago'];

        $query = "INSERT INTO egresos
                    (proveedor, descripcion, monto, fecha, destinatario, forma_pago, documento_de_amparo, id_user, id_categoria)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta INSERT: " . $this->db->error);
        }

        // s s d s s s s i i  => 9 parámetros
        $bindResult = $stmt->bind_param(
            "ssds" . "ss" . "sii",
            $proveedor,
            $descripcion,
            $monto,
            $fecha,
            $destinatario,
            $forma_pago,
            $doc_amparo,
            $id_user,
            $id_categoria
        );

        if ($bindResult === false) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception("Error en bind_param (createEgreso): " . $error);
        }

        $success = $stmt->execute();
        if (!$success) {
            $error_no  = $stmt->errno;
            $error_msg = $stmt->error;
            $stmt->close();
            error_log("Error al ejecutar createEgreso: ({$error_no}) {$error_msg}");
            if ($error_no === 1452) {
                throw new Exception("Error de referencia: Verifique que el usuario y la categoría existan.");
            }
            throw new Exception("Error al guardar en BD: {$error_msg}");
        }

        $newId = $this->db->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Actualiza un egreso existente (sin id_presupuesto).
     */
    public function updateEgreso($folio_egreso, $data) {
        $proveedor   = isset($data['proveedor']) && trim($data['proveedor']) !== '' ? trim($data['proveedor']) : null;
        $descripcion = isset($data['descripcion']) && trim($data['descripcion']) !== '' ? trim($data['descripcion']) : null;
        $doc_amparo  = isset($data['documento_de_amparo']) && trim($data['documento_de_amparo']) !== '' ? trim($data['documento_de_amparo']) : null;

        if (!isset($data['monto']) || $data['monto'] === '' || !is_numeric($data['monto']) || $data['monto'] < 0 ||
            empty($data['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha']) ||
            empty($data['destinatario']) || empty($data['forma_pago']) ||
            empty($data['id_user']) || !filter_var($data['id_user'], FILTER_VALIDATE_INT) ||
            empty($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT)) {
            throw new Exception("Datos inválidos o faltantes para actualizar egreso.");
        }

        $id_categoria = (int)$data['id_categoria'];
        $id_user      = (int)$data['id_user'];
        $monto        = (float)$data['monto'];
        $fecha        = $data['fecha'];
        $destinatario = trim($data['destinatario']);
        $forma_pago   = $data['forma_pago'];

        $query = "UPDATE egresos SET
                    proveedor = ?, descripcion = ?, monto = ?, fecha = ?,
                    destinatario = ?, forma_pago = ?, documento_de_amparo = ?,
                    id_user = ?, id_categoria = ?
                  WHERE folio_egreso = ?";

        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta UPDATE: " . $this->db->error);
        }

        // s s d s s s s i i i => 10 parámetros
        $bindResult = $stmt->bind_param(
            "ssds" . "ss" . "siii",
            $proveedor,
            $descripcion,
            $monto,
            $fecha,
            $destinatario,
            $forma_pago,
            $doc_amparo,
            $id_user,
            $id_categoria,
            $folio_egreso
        );

        if ($bindResult === false) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception("Error en bind_param (updateEgreso): " . $error);
        }

        $success = $stmt->execute();
        if (!$success) {
            $error_no  = $stmt->errno;
            $error_msg = $stmt->error;
            $stmt->close();
            error_log("Error al ejecutar updateEgreso: ({$error_no}) {$error_msg}");
            if ($error_no === 1452) {
                throw new Exception("Error de referencia: Verifique usuario/categoría.");
            }
            throw new Exception("Error al actualizar en BD: {$error_msg}");
        }

        $stmt->close();
        return true;
    }

    /**
     * Elimina un egreso.
     */
    public function deleteEgreso($folio_egreso) {
        $query = "DELETE FROM egresos WHERE folio_egreso = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $folio_egreso);
            $success = $stmt->execute();
            if (!$success) {
                error_log("Error al ejecutar deleteEgreso: (" . $stmt->errno . ") " . $stmt->error);
            }
            $stmt->close();
            return $success;
        } else {
            error_log("Error al preparar deleteEgreso: (" . $this->db->errno . ") " . $this->db->error);
            return false;
        }
    }
}
?>