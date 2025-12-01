<?php
// src/Ingresos/Models/IngresoModel.php (CORREGIDO Y ACTUALIZADO v2)

class IngresoModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene todos los ingresos con información de pagos parciales.
     */
    public function getAllIngresos() {
        // Usar LEFT JOIN para obtener información de pagos parciales agrupada
        $query = "SELECT 
                    i.*, 
                    i.folio_ingreso as id, 
                    c.nombre AS nombre_categoria,
                    GROUP_CONCAT(
                        CONCAT(pp.metodo_pago, ': $', FORMAT(pp.monto, 2)) 
                        ORDER BY pp.orden 
                        SEPARATOR ' | '
                    ) AS metodos_pago_detalle,
                    COUNT(pp.id_pago_parcial) AS num_pagos
                  FROM 
                    ingresos i
                  LEFT JOIN 
                    categorias c ON i.id_categoria = c.id_categoria
                  LEFT JOIN
                    pagos_parciales pp ON i.folio_ingreso = pp.folio_ingreso
                  GROUP BY
                    i.folio_ingreso
                  ORDER BY 
                    i.folio_ingreso DESC";
        
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
            return $data;
        } else {
            error_log("Error al preparar getIngresoById: " . $this->db->error);
            return null;
        }
    }

    /**
     * Crea un nuevo ingreso en la base de datos (CON BIND_PARAM CORREGIDO).
     * @param array $data Datos del ingreso.
     * @return bool|int Retorna el ID del nuevo ingreso si tiene éxito, false/Exception en caso contrario.
     */
    public function createIngreso($data) {
        // Validar campos obligatorios primero
        if (empty($data['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha']) ||
            empty($data['alumno']) || empty($data['matricula']) || empty($data['nivel']) ||
            !isset($data['monto']) || !is_numeric($data['monto']) || $data['monto'] <= 0 ||
            empty($data['metodo_de_pago']) ||
            empty($data['anio']) || !filter_var($data['anio'], FILTER_VALIDATE_INT) ||
            empty($data['programa']) ||
            empty($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT))
        {
            throw new Exception("Datos inválidos o faltantes para actualizar ingreso.");
        }
         
        // Procesar y normalizar TODOS los campos (obligatorios y opcionales)
        $fecha = $data['fecha'];
        $alumno = trim($data['alumno']);
        $matricula = trim($data['matricula']);
        $nivel = $data['nivel'];
        $monto = (float)$data['monto'];
        $metodo_de_pago = $data['metodo_de_pago'];
        $año = (int)$data['anio'];
        $programa = trim($data['programa']);
        $id_categoria = (int)$data['id_categoria'];
        
        // Campos opcionales: normalizar strings vacíos a NULL
        $mes_correspondiente = isset($data['mes_correspondiente']) && trim($data['mes_correspondiente']) !== '' ? trim($data['mes_correspondiente']) : null;
        $observaciones = isset($data['observaciones']) && trim($data['observaciones']) !== '' ? trim($data['observaciones']) : null;
        $dia_pago = isset($data['dia_pago']) && $data['dia_pago'] !== '' ? (int)$data['dia_pago'] : null;
        $modalidad = isset($data['modalidad']) && trim($data['modalidad']) !== '' ? trim($data['modalidad']) : null;
        $grado = isset($data['grado']) && $data['grado'] !== '' ? (int)$data['grado'] : null;
        $grupo = isset($data['grupo']) && trim($data['grupo']) !== '' ? trim($data['grupo']) : null;

        $query = "INSERT INTO ingresos
                    (fecha, alumno, matricula, nivel, monto, metodo_de_pago, mes_correspondiente, anio, observaciones, dia_pago, modalidad, grado, programa, grupo, id_categoria)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 15 campos

        $stmt = $this->db->prepare($query);
        if (!$stmt) { throw new Exception("Error al preparar consulta INSERT Ingreso: " . $this->db->error); }

    // Cadena de tipos EXACTA para los 15 parámetros en el orden del INSERT
    // fecha(s), alumno(s), matricula(s), nivel(s), monto(d), metodo(s),
    // mes_correspondiente(s), anio(i), observaciones(s), dia_pago(i), modalidad(s),
    // grado(i), programa(s), grupo(s), id_categoria(i)
    $types = "ssssdssisisssii";
        // ===============================================

        $bindResult = $stmt->bind_param(
            $types,
            $fecha,                 // s
            $alumno,                // s
            $matricula,             // s
            $nivel,                 // s
            $monto,                 // d
            $metodo_de_pago,        // s
            $mes_correspondiente,   // s
            $año,                   // i
            $observaciones,         // s
            $dia_pago,              // i
            $modalidad,             // s
            $grado,                 // i
            $programa,              // s
            $grupo,                 // s
            $id_categoria           // i
        ); // 15 variables

        if ($bindResult === false) { $error = $stmt->error; $stmt->close(); throw new Exception("Error en bind_param (create Ingreso): " . $error); }

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
     * Actualiza un ingreso existente (CON BIND_PARAM CORREGIDO).
     * @param int $folio_ingreso ID (PK) del ingreso a actualizar.
     * @param array $data Nuevos datos del ingreso.
     * @return bool True si se actualizó con éxito, false/Exception en caso contrario.
     */
    public function updateIngreso($folio_ingreso, $data) {
        // Validar campos obligatorios
        if (empty($data['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha']) ||
            empty($data['alumno']) || empty($data['matricula']) || empty($data['nivel']) ||
            !isset($data['monto']) || !is_numeric($data['monto']) || $data['monto'] <= 0 ||
            empty($data['metodo_de_pago']) ||
            empty($data['anio']) || !filter_var($data['anio'], FILTER_VALIDATE_INT) ||
            empty($data['programa']) ||
            empty($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT))
        { throw new Exception("Datos inválidos o faltantes para actualizar ingreso."); }
         
        // Procesar y normalizar TODOS los campos
        $fecha = $data['fecha'];
        $alumno = trim($data['alumno']);
        $matricula = trim($data['matricula']);
        $nivel = $data['nivel'];
        $monto = (float)$data['monto'];
        $metodo_de_pago = $data['metodo_de_pago'];
        $año = (int)$data['anio'];
        $programa = trim($data['programa']);
        $id_categoria = (int)$data['id_categoria'];
        
        // Campos opcionales: normalizar strings vacíos a NULL
        $mes_correspondiente = isset($data['mes_correspondiente']) && trim($data['mes_correspondiente']) !== '' ? trim($data['mes_correspondiente']) : null;
        $observaciones = isset($data['observaciones']) && trim($data['observaciones']) !== '' ? trim($data['observaciones']) : null;
        $dia_pago = isset($data['dia_pago']) && $data['dia_pago'] !== '' ? (int)$data['dia_pago'] : null;
        $modalidad = isset($data['modalidad']) && trim($data['modalidad']) !== '' ? trim($data['modalidad']) : null;
        $grado = isset($data['grado']) && $data['grado'] !== '' ? (int)$data['grado'] : null;
        $grupo = isset($data['grupo']) && trim($data['grupo']) !== '' ? trim($data['grupo']) : null;


        $query = "UPDATE ingresos SET
                    fecha=?, alumno=?, matricula=?, nivel=?, monto=?, metodo_de_pago=?,
                    mes_correspondiente=?, anio=?, observaciones=?, dia_pago=?,
                    modalidad=?, grado=?, programa=?, grupo=?, id_categoria=?
                  WHERE folio_ingreso=?"; // 15 SET + 1 WHERE

        $stmt = $this->db->prepare($query);
        if (!$stmt) { throw new Exception("Error al preparar consulta UPDATE Ingreso: " . $this->db->error); }

    // Cadena de tipos EXACTA para los 16 parámetros en el orden del UPDATE (+ WHERE al final)
    // (mismos 15 que en INSERT) + folio_ingreso(i)
    $types = "ssssdssisissssii";
        // ==========================================================

        $types = "ssssdssisisssiii";
        $bindResult = $stmt->bind_param(
            $types,
            $fecha,                 // s
            $alumno,                // s
            $matricula,             // s
            $nivel,                 // s
            $monto,                 // d
            $metodo_de_pago,        // s
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
        ); // 16 variables

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

    /**
     * Guarda los pagos parciales de un ingreso. Elimina los existentes y crea nuevos.
     * @param int $folio_ingreso ID del ingreso
     * @param array $pagos Array de pagos con estructura: [['metodo' => 'Efectivo', 'monto' => 500.00], ...]
     * @return bool True si se guardó correctamente
     */
    public function savePagosParciales($folio_ingreso, $pagos) {
        try {
            // Primero eliminar los pagos parciales existentes
            $deleteQuery = "DELETE FROM pagos_parciales WHERE folio_ingreso = ?";
            $stmt = $this->db->prepare($deleteQuery);
            if (!$stmt) {
                error_log("Error al preparar DELETE pagos_parciales: " . $this->db->error);
                return false;
            }
            $stmt->bind_param("i", $folio_ingreso);
            $stmt->execute();
            $stmt->close();

            // Ahora insertar los nuevos pagos parciales
            if (empty($pagos)) {
                return true; // Si no hay pagos, ya está
            }

            $insertQuery = "INSERT INTO pagos_parciales (folio_ingreso, metodo_pago, monto, orden) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($insertQuery);
            if (!$stmt) {
                error_log("Error al preparar INSERT pagos_parciales: " . $this->db->error);
                return false;
            }

            $orden = 1;
            foreach ($pagos as $pago) {
                $metodo = $pago['metodo'];
                $monto = (float)$pago['monto'];
                
                $stmt->bind_param("isdi", $folio_ingreso, $metodo, $monto, $orden);
                if (!$stmt->execute()) {
                    error_log("Error al insertar pago parcial: " . $stmt->error);
                    $stmt->close();
                    return false;
                }
                $orden++;
            }

            $stmt->close();
            return true;

        } catch (Exception $e) {
            error_log("Excepción en savePagosParciales: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los pagos parciales de un ingreso.
     * @param int $folio_ingreso ID del ingreso
     * @return array Array de pagos parciales
     */
    public function getPagosParciales($folio_ingreso) {
        $query = "SELECT * FROM pagos_parciales WHERE folio_ingreso = ? ORDER BY orden ASC";
        $stmt = $this->db->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $folio_ingreso);
            $stmt->execute();
            $result = $stmt->get_result();
            $pagos = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $pagos;
        } else {
            error_log("Error al preparar getPagosParciales: " . $this->db->error);
            return [];
        }
    }
} // Fin clase IngresoModel
?>