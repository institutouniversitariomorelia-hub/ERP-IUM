<?php
// models/EgresoModel.php (CON JOIN PARA NOMBRE CATEGORÍA)

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
        // ** CONSULTA MODIFICADA CON LEFT JOIN **
                $query = "SELECT
                                        e.*,  -- Selecciona todos los campos de egresos (alias e)
                                        c.nombre AS nombre_categoria -- Trae el nombre de categorias (alias c)
                                    FROM
                                        egresos e
                                    LEFT JOIN
                                        categorias c ON e.id_categoria = c.id_categoria -- Une por la columna real 'id_categoria'
                                    ORDER BY
                                        e.fecha DESC, e.folio_egreso DESC"; // Ordena usando alias

        $result = $this->db->query($query);
        if ($result) {
            // Añadir alias 'id' para consistencia con JS (data-id)
            $egresos = [];
            while($row = $result->fetch_assoc()) {
                $row['id'] = $row['folio_egreso']; // JS usa data-id esperando la PK
                $egresos[] = $row;
            }
            return $egresos;
            // return $result->fetch_all(MYSQLI_ASSOC); // Alternativa si JS usa folio_egreso directamente
        } else {
            error_log("Error al obtener egresos con JOIN: " . $this->db->error);
            return [];
        }
    }

    /**
     * Obtiene un egreso específico por su ID (folio_egreso).
     * (No necesita JOIN aquí, el formulario trabaja con IDs)
     * @param int $folio_egreso ID (PK) del egreso.
     * @return array|null Datos del egreso o null.
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
            // Opcional: añadir alias 'id' si JS lo espera consistentemente
            // if ($data) $data['id'] = $data['folio_egreso'];
            return $data;
        } else {
            error_log("Error al preparar getEgresoById: " . $this->db->error);
            return null;
        }
    }

    /**
     * Crea un nuevo egreso (Sin cambios funcionales aquí).
     * @param array $data Datos del egreso.
     * @return bool|int Retorna el ID del nuevo egreso si tiene éxito, false/Exception en caso contrario.
     */
    public function createEgreso($data) {
        // --- Preparación y Validación de Datos ---
        $proveedor = isset($data['proveedor']) && trim($data['proveedor']) !== '' ? trim($data['proveedor']) : null;
        $activo_fijo = $data['activo_fijo'] ?? 'NO';
        $descripcion = isset($data['descripcion']) && trim($data['descripcion']) !== '' ? trim($data['descripcion']) : null;
        $doc_amparo = isset($data['documento_de_amparo']) && trim($data['documento_de_amparo']) !== '' ? trim($data['documento_de_amparo']) : null;
        $id_presupuesto = isset($data['id_presupuesto']) && filter_var($data['id_presupuesto'], FILTER_VALIDATE_INT) ? (int)$data['id_presupuesto'] : null;

        // Si no se proporcionó id_presupuesto, intentar obtenerlo desde la categoría (una sola vez)
        if (is_null($id_presupuesto) && !empty($data['id_categoria']) && filter_var($data['id_categoria'], FILTER_VALIDATE_INT)) {
            $catId = (int)$data['id_categoria'];
            $q = "SELECT id_presupuesto FROM categorias WHERE id_categoria = ? LIMIT 1";
            $st = $this->db->prepare($q);
            if ($st) {
                $st->bind_param('i', $catId);
                if ($st->execute()) {
                    $res = $st->get_result();
                    $row = $res->fetch_assoc();
                    if ($row && !empty($row['id_presupuesto'])) {
                        $id_presupuesto = (int)$row['id_presupuesto'];
                    }
                }
                $st->close();
            }
        }

        // Si aún no hay id_presupuesto, abortar con mensaje claro porque la columna en BD es NOT NULL
        if (is_null($id_presupuesto)) {
            throw new Exception("No existe un presupuesto asignado para esta categoría. Asigne un presupuesto o seleccione uno en el formulario antes de guardar el egreso.");
        }

        if (empty($data['monto']) || !is_numeric($data['monto']) || $data['monto'] <= 0 ||
            empty($data['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha']) ||
            empty($data['destinatario']) || empty($data['forma_pago']) ||
            empty($data['id_user']) || !filter_var($data['id_user'], FILTER_VALIDATE_INT) ||
            empty($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT))
        {
             throw new Exception("Datos inválidos o faltantes para crear egreso. Verifique fecha (YYYY-MM-DD) y campos obligatorios.");
        }
        $id_categoria = (int)$data['id_categoria'];
        $id_user = (int)$data['id_user'];
        $monto = (float)$data['monto'];
        $fecha = $data['fecha'];
        $destinatario = trim($data['destinatario']);
        $forma_pago = $data['forma_pago'];

        $query = "INSERT INTO egresos
                    (proveedor, activo_fijo, descripcion, monto, fecha, destinatario, forma_pago, documento_de_amparo, id_user, id_presupuesto, id_categoria)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        if (!$stmt) { throw new Exception("Error al preparar consulta INSERT: " . $this->db->error); }

        // Tipos: proveedor(s), activo_fijo(s), descripcion(s), monto(d), fecha(s),
        // destinatario(s), forma_pago(s), documento_de_amparo(s), id_user(i), id_presupuesto(i), id_categoria(i)
        $bindResult = $stmt->bind_param( "" . "sssds" . "sss" . "iii",
            $proveedor, $activo_fijo, $descripcion, $monto, $fecha,
            $destinatario, $forma_pago, $doc_amparo,
            $id_user, $id_presupuesto, $id_categoria
        );

        if ($bindResult === false) { $error = $stmt->error; $stmt->close(); throw new Exception("Error en bind_param (create): " . $error); }

        $success = $stmt->execute();
        if (!$success) {
            $error_no = $stmt->errno; $error_msg = $stmt->error; $stmt->close();
            error_log("Error al ejecutar createEgreso: ({$error_no}) {$error_msg}");
            if ($error_no === 1452) { throw new Exception("Error de referencia: Verifique que el usuario y la categoría existan."); }
            throw new Exception("Error al guardar en BD: {$error_msg}");
        }

        $newId = $this->db->insert_id;
        $stmt->close();
        return $newId;
    }


    /**
     * Actualiza un egreso existente (Sin cambios funcionales aquí).
     * @param int $folio_egreso ID (PK) del egreso a actualizar.
     * @param array $data Nuevos datos del egreso.
     * @return bool True si se actualizó con éxito, false/Exception en caso contrario.
     */
    public function updateEgreso($folio_egreso, $data) {
        $proveedor = isset($data['proveedor']) && trim($data['proveedor']) !== '' ? trim($data['proveedor']) : null;
        $activo_fijo = $data['activo_fijo'] ?? 'NO';
        $descripcion = isset($data['descripcion']) && trim($data['descripcion']) !== '' ? trim($data['descripcion']) : null;
        $doc_amparo = isset($data['documento_de_amparo']) && trim($data['documento_de_amparo']) !== '' ? trim($data['documento_de_amparo']) : null;
        $id_presupuesto = isset($data['id_presupuesto']) && filter_var($data['id_presupuesto'], FILTER_VALIDATE_INT) ? (int)$data['id_presupuesto'] : null;

         if (empty($data['monto']) || !is_numeric($data['monto']) || $data['monto'] <= 0 ||
             empty($data['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha']) ||
             empty($data['destinatario']) || empty($data['forma_pago']) ||
             empty($data['id_user']) || !filter_var($data['id_user'], FILTER_VALIDATE_INT) ||
             empty($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT))
         { throw new Exception("Datos inválidos o faltantes para actualizar egreso."); }
         $id_categoria = (int)$data['id_categoria'];
         $id_user = (int)$data['id_user'];
         $monto = (float)$data['monto'];
         $fecha = $data['fecha'];
         $destinatario = trim($data['destinatario']);
         $forma_pago = $data['forma_pago'];

        $query = "UPDATE egresos SET
                    proveedor=?, activo_fijo=?, descripcion=?, monto=?, fecha=?,
                    destinatario=?, forma_pago=?, documento_de_amparo=?,
                    id_user=?, id_presupuesto=?, id_categoria=?
                  WHERE folio_egreso=?";

        $stmt = $this->db->prepare($query);
        if (!$stmt) { throw new Exception("Error al preparar consulta UPDATE: " . $this->db->error); }

        $bindResult = $stmt->bind_param( "sssds" . "sssiiii",
            $proveedor, $activo_fijo, $descripcion, $monto, $fecha,
            $destinatario, $forma_pago, $doc_amparo,
            $id_user, $id_presupuesto, $id_categoria,
            $folio_egreso
        );

         if ($bindResult === false) { $error = $stmt->error; $stmt->close(); throw new Exception("Error en bind_param (update): " . $error); }

        $success = $stmt->execute();
         if (!$success) {
             $error_no = $stmt->errno; $error_msg = $stmt->error; $stmt->close();
             error_log("Error al ejecutar updateEgreso: ({$error_no}) {$error_msg}");
             if ($error_no === 1452) { throw new Exception("Error de referencia: Verifique usuario/categoría."); }
             throw new Exception("Error al actualizar en BD: {$error_msg}");
         }
        $stmt->close();
        return true;
    }

    /**
     * Elimina un egreso (Sin cambios necesarios).
     * @param int $folio_egreso ID (PK) del egreso a eliminar.
     * @return bool True si se eliminó con éxito, false en caso contrario.
     */
    public function deleteEgreso($folio_egreso) {
        $query = "DELETE FROM egresos WHERE folio_egreso = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $folio_egreso);
            $success = $stmt->execute();
             if (!$success) error_log("Error al ejecutar deleteEgreso: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return $success;
        } else {
            error_log("Error al preparar deleteEgreso: (" . $this->db->errno . ") " . $this->db->error);
            return false;
        }
    }
} // Fin clase EgresoModel
?>