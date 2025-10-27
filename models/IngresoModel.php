<?php
// models/IngresoModel.php (VERSIÓN CORREGIDA bind_param v2)

class IngresoModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene todos los ingresos. SELECT * recuperará todos los campos.
     * Ordenado por fecha descendente, luego por ID (folio_ingreso).
     * @return array Lista de ingresos o array vacío.
     */
    public function getAllIngresos() {
        // Añadir alias 'id' para consistencia con JS (data-id)
        $query = "SELECT *, folio_ingreso as id FROM ingresos ORDER BY fecha DESC, folio_ingreso DESC";
        $result = $this->db->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Error al obtener ingresos: " . $this->db->error);
            return [];
        }
    }

    /**
     * Obtiene un ingreso específico por su ID (folio_ingreso).
     * @param int $folio_ingreso ID (PK) del ingreso.
     * @return array|null Datos del ingreso o null si no se encuentra o hay error.
     */
    public function getIngresoById($folio_ingreso) {
        $query = "SELECT * FROM ingresos WHERE folio_ingreso = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $folio_ingreso);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            // Opcional: añadir alias 'id' si JS lo espera
            // if ($data) $data['id'] = $data['folio_ingreso'];
            return $data;
        } else {
            error_log("Error al preparar getIngresoById: " . $this->db->error);
            return null;
        }
    }

    /**
     * Crea un nuevo ingreso en la base de datos (CON BIND_PARAM REESCRITO).
     * @param array $data Datos del ingreso del formulario/controlador.
     * @return bool|int Retorna el ID del nuevo ingreso si tiene éxito, false/Exception en caso contrario.
     */
    public function createIngreso($data) {
        // Asegurar valores para campos opcionales o con default
        $mes_correspondiente = !empty($data['mes_correspondiente']) ? trim($data['mes_correspondiente']) : null;
        $observaciones = !empty($data['observaciones']) ? trim($data['observaciones']) : null;
        $dia_pago = !empty($data['dia_pago']) ? (int)$data['dia_pago'] : null;
        $modalidad = !empty($data['modalidad']) ? $data['modalidad'] : null;
        $grado = !empty($data['grado']) ? (int)$data['grado'] : null;
        $grupo = !empty($data['grupo']) ? trim($data['grupo']) : null;

        // Validar y convertir tipos obligatorios
        if (empty($data['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha']) ||
            empty($data['alumno']) || empty($data['matricula']) || empty($data['nivel']) ||
            !isset($data['monto']) || !is_numeric($data['monto']) || $data['monto'] <= 0 ||
            empty($data['metodo_de_pago']) || empty($data['concepto']) ||
            empty($data['año']) || !filter_var($data['año'], FILTER_VALIDATE_INT) ||
            empty($data['programa']) ||
            empty($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT))
        {
             throw new Exception("Datos inválidos o faltantes para crear ingreso.");
        }
         $id_categoria = (int)$data['id_categoria'];
         $año = (int)$data['año'];
         $monto = (float)$data['monto'];
         $fecha = $data['fecha'];
         $alumno = trim($data['alumno']);
         $matricula = trim($data['matricula']);
         $nivel = $data['nivel'];
         $metodo_de_pago = $data['metodo_de_pago'];
         $concepto = $data['concepto'];
         $programa = trim($data['programa']);

        $query = "INSERT INTO ingresos
                    (fecha, alumno, matricula, nivel, monto, metodo_de_pago, concepto, mes_correspondiente, año, observaciones, dia_pago, modalidad, grado, programa, grupo, id_categoria)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 16 placeholders

        $stmt = $this->db->prepare($query);
        if (!$stmt) { throw new Exception("Error al preparar consulta INSERT Ingreso: " . $this->db->error); }

        // --- bind_param REESCRITO CUIDADOSAMENTE ---
        // Tipos: s s s s d s s s i s i s i s s i (16 tipos)
        $types = "ssssdssssisisssi"; // Verificado: 16 caracteres
        $bindResult = $stmt->bind_param(
            $types,
            $fecha,                 // s
            $alumno,                // s
            $matricula,             // s
            $nivel,                 // s
            $monto,                 // d
            $metodo_de_pago,        // s
            $concepto,              // s
            $mes_correspondiente,   // s
            $año,                   // i
            $observaciones,         // s
            $dia_pago,              // i
            $modalidad,             // s
            $grado,                 // i
            $programa,              // s
            $grupo,                 // s
            $id_categoria           // i
        ); // 16 variables

        if ($bindResult === false) { $error = $stmt->error; $stmt->close(); throw new Exception("Error en bind_param (create Ingreso): " . $error); }

        // --- Ejecución ---
        $success = $stmt->execute();
        if (!$success) {
            $error_no = $stmt->errno; $error_msg = $stmt->error; $stmt->close();
            error_log("Error al ejecutar createIngreso: ({$error_no}) {$error_msg}");
            if ($error_no === 1062) { throw new Exception("La matrícula '{$matricula}' ya está registrada."); }
            if ($error_no === 1452) { throw new Exception("Error de referencia: Verifique que la categoría exista."); }
            throw new Exception("Error al guardar Ingreso en BD: {$error_msg}");
        }

        $newId = $this->db->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Actualiza un ingreso existente (REVISADO bind_param CUIDADOSAMENTE).
     * @param int $folio_ingreso ID (PK) del ingreso a actualizar.
     * @param array $data Nuevos datos del ingreso.
     * @return bool True si se actualizó con éxito, false/Exception en caso contrario.
     */
    public function updateIngreso($folio_ingreso, $data) {
        $mes_correspondiente = !empty($data['mes_correspondiente']) ? trim($data['mes_correspondiente']) : null;
        $observaciones = !empty($data['observaciones']) ? trim($data['observaciones']) : null;
        $dia_pago = !empty($data['dia_pago']) ? (int)$data['dia_pago'] : null;
        $modalidad = !empty($data['modalidad']) ? $data['modalidad'] : null;
        $grado = !empty($data['grado']) ? (int)$data['grado'] : null;
        $grupo = !empty($data['grupo']) ? trim($data['grupo']) : null;

         if (empty($data['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha']) ||
             empty($data['alumno']) || empty($data['matricula']) || empty($data['nivel']) ||
             !isset($data['monto']) || !is_numeric($data['monto']) || $data['monto'] <= 0 ||
             empty($data['metodo_de_pago']) || empty($data['concepto']) ||
             empty($data['año']) || !filter_var($data['año'], FILTER_VALIDATE_INT) ||
             empty($data['programa']) ||
             empty($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT))
         { throw new Exception("Datos inválidos o faltantes para actualizar ingreso."); }
         $id_categoria = (int)$data['id_categoria'];
         $año = (int)$data['año'];
         $monto = (float)$data['monto'];
         $fecha = $data['fecha'];
         $alumno = trim($data['alumno']);
         $matricula = trim($data['matricula']);
         $nivel = $data['nivel'];
         $metodo_de_pago = $data['metodo_de_pago'];
         $concepto = $data['concepto'];
         $programa = trim($data['programa']);


        $query = "UPDATE ingresos SET
                    fecha=?, alumno=?, matricula=?, nivel=?, monto=?, metodo_de_pago=?,
                    concepto=?, mes_correspondiente=?, año=?, observaciones=?, dia_pago=?,
                    modalidad=?, grado=?, programa=?, grupo=?, id_categoria=?
                  WHERE folio_ingreso=?"; // 16 SET + 1 WHERE

        $stmt = $this->db->prepare($query);
        if (!$stmt) { throw new Exception("Error al preparar consulta UPDATE Ingreso: " . $this->db->error); }

        // --- bind_param REESCRITO CUIDADOSAMENTE ---
        // Tipos: s s s s d s s s i s i s i s s i i (17 tipos)
        $types = "ssssdssssisisssii"; // Verificado: 17 caracteres
        $bindResult = $stmt->bind_param(
            $types,
            $fecha,                 // s
            $alumno,                // s
            $matricula,             // s
            $nivel,                 // s
            $monto,                 // d
            $metodo_de_pago,        // s
            $concepto,              // s
            $mes_correspondiente,   // s
            $año,                   // i
            $observaciones,         // s
            $dia_pago,              // i
            $modalidad,             // s
            $grado,                 // i
            $programa,              // s
            $grupo,                 // s
            $id_categoria,          // i
            $folio_ingreso          // i (WHERE)
        ); // 17 variables

         if ($bindResult === false) { $error = $stmt->error; $stmt->close(); throw new Exception("Error en bind_param (update Ingreso): " . $error); }

        $success = $stmt->execute();
         if (!$success) {
             $error_no = $stmt->errno; $error_msg = $stmt->error; $stmt->close();
             error_log("Error al ejecutar updateIngreso: ({$error_no}) {$error_msg}");
             if ($error_no === 1062) { throw new Exception("La matrícula '{$matricula}' ya pertenece a otro registro."); }
             if ($error_no === 1452) { throw new Exception("Error de referencia: Verifique que la categoría exista."); }
             throw new Exception("Error al actualizar Ingreso en BD: {$error_msg}");
         }
        $stmt->close();
        return true;
    }

    /**
     * Elimina un ingreso (Sin cambios necesarios).
     * @param int $folio_ingreso ID (PK) del ingreso a eliminar.
     * @return bool True si se eliminó con éxito, false en caso contrario.
     */
    public function deleteIngreso($folio_ingreso) {
        $query = "DELETE FROM ingresos WHERE folio_ingreso = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $folio_ingreso);
            $success = $stmt->execute();
             if (!$success) error_log("Error al ejecutar deleteIngreso: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return $success;
        } else {
            error_log("Error al preparar deleteIngreso: (" . $this->db->errno . ") " . $this->db->error);
            return false;
        }
    }
} // Fin clase IngresoModel
?>