<?php
// models/AuditoriaModel.php (CORREGIDO)

class AuditoriaModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene los registros de auditoría, opcionalmente filtrados,
     * uniendo con las tablas de usuario para obtener el nombre.
     * @param array $filtros Array asociativo con los filtros.
     * @return array Lista de registros de auditoría.
     */
    public function getAuditoriaLogs($filtros = []) {
        // Nueva implementación: soporte de filtros más flexible y paginación.
        $page = isset($filtros['page']) ? max(1, (int)$filtros['page']) : 1;
        $pageSize = isset($filtros['pageSize']) ? (int)$filtros['pageSize'] : 50;
        if ($pageSize <= 0) $pageSize = 50;
        if ($pageSize > 500) $pageSize = 500; // límite razonable
        $offset = ($page - 1) * $pageSize;

        $baseFrom = " FROM auditoria a
                      LEFT JOIN usuario_historial uh ON a.id_auditoria = uh.id_ha
                      LEFT JOIN usuarios u ON uh.id_user = u.id_user";

        $where = " WHERE 1=1";
        $params = [];
        $types = '';

        // Filtrar por sección exacta (case-insensitive)
        if (!empty($filtros['seccion'])) {
            $where .= " AND LOWER(a.seccion) = LOWER(?)";
            $params[] = $filtros['seccion']; $types .= 's';
        }

        // Filtrar por usuario: aceptar id (numérico) o username (string)
        if (!empty($filtros['usuario'])) {
            if (is_numeric($filtros['usuario'])) {
                $where .= " AND u.id_user = ?";
                $params[] = (int)$filtros['usuario']; $types .= 'i';
            } else {
                $where .= " AND u.username = ?";
                $params[] = $filtros['usuario']; $types .= 's';
            }
        }

        // Filtrar por acción (parcial, case-insensitive)
        if (!empty($filtros['accion'])) {
            $where .= " AND LOWER(a.accion) LIKE LOWER(?)";
            $params[] = '%' . $filtros['accion'] . '%'; $types .= 's';
        }

        // Rango de fechas (fecha_inicio y fecha_fin esperadas en YYYY-MM-DD)
        if (!empty($filtros['fecha_inicio'])) {
            $where .= " AND DATE(a.fecha_hora) >= ?";
            $params[] = $filtros['fecha_inicio']; $types .= 's';
        }
        if (!empty($filtros['fecha_fin'])) {
            $where .= " AND DATE(a.fecha_hora) <= ?";
            $params[] = $filtros['fecha_fin']; $types .= 's';
        }

        // Búsqueda de texto en old_valor/new_valor (case-insensitive)
        if (!empty($filtros['q'])) {
            $q = '%' . $filtros['q'] . '%';
            // La tabla no contiene 'detalles' en la BD original; usamos old_valor/new_valor
            $where .= " AND (LOWER(a.old_valor) LIKE LOWER(?) OR LOWER(a.new_valor) LIKE LOWER(?) OR LOWER(CONCAT(IFNULL(a.old_valor,''),' ',IFNULL(a.new_valor,''))) LIKE LOWER(?))";
            $params[] = $q; $params[] = $q; $params[] = $q; $types .= 'sss';
        }

        // Primero obtener el total para paginación
        $countSql = "SELECT COUNT(*) as cnt" . $baseFrom . $where;
        $countStmt = $this->db->prepare($countSql);
        if (!$countStmt) {
            error_log('Error preparar count getAuditoriaLogs: ' . $this->db->error);
            return ['data' => [], 'total' => 0, 'page' => $page, 'pageSize' => $pageSize];
        }
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        if (!$countStmt->execute()) {
            error_log('Error ejecutar count getAuditoriaLogs: ' . $countStmt->error);
            $countStmt->close();
            return ['data' => [], 'total' => 0, 'page' => $page, 'pageSize' => $pageSize];
        }
        $res = $countStmt->get_result();
        $total = ($row = $res->fetch_assoc()) ? (int)$row['cnt'] : 0;
        $countStmt->close();

        // Si no hay resultados, registrar consulta y parámetros para depuración
        if ($total === 0) {
            // Preparar una representación segura de parámetros
            $paramDump = [];
            foreach ($params as $p) {
                $paramDump[] = is_scalar($p) ? (string)$p : json_encode($p);
            }
            error_log('[AuditoriaModel::getAuditoriaLogs] total=0 countSql=' . $countSql . ' params=' . implode('|', $paramDump));
        }

        // Consulta de datos con orden y paginación
       $select = "SELECT 
                    a.id_auditoria,
                    a.fecha_hora AS fecha,
                    a.seccion,
                    a.accion,
                    CONCAT(IFNULL(a.old_valor, ''), ' => ', IFNULL(a.new_valor, '')) AS detalles,
                    COALESCE(u.username, 'Sistema') AS usuario
                " . $baseFrom . $where . " ORDER BY a.fecha_hora DESC LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($select);
        if (!$stmt) {
            error_log('Error preparar select getAuditoriaLogs: ' . $this->db->error);
            return ['data' => [], 'total' => $total, 'page' => $page, 'pageSize' => $pageSize];
        }

        // Bind de parámetros + limit/offset
        $bindParams = $params;
        $bindTypes = $types . 'ii';
        $bindParams[] = $pageSize;
        $bindParams[] = $offset;

        if (!empty($bindParams)) {
            $stmt->bind_param($bindTypes, ...$bindParams);
        }

        if (!$stmt->execute()) {
            error_log('Error ejecutar select getAuditoriaLogs: ' . $stmt->error);
            $stmt->close();
            return ['data' => [], 'total' => $total, 'page' => $page, 'pageSize' => $pageSize];
        }

        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return ['data' => $rows, 'total' => $total, 'page' => $page, 'pageSize' => $pageSize];
    }

    /**
     * Devuelve la estructura (DESCRIBE) de la tabla 'auditoria'.
     * @return array
     */
    public function getTableStructure() {
        $rows = [];
        $res = $this->db->query("DESCRIBE auditoria");
        if ($res) {
            while ($r = $res->fetch_assoc()) $rows[] = $r;
        }
        return $rows;
    }

    /**
     * Obtiene un registro de auditoría por su id_auditoria.
     * @param int $id
     * @return array|null
     */
    public function getAuditoriaById($id) {
        $query = "SELECT 
                        a.id_auditoria,
                        a.fecha_hora AS fecha,
                        a.seccion,
                        a.accion,
                        CONCAT(IFNULL(a.old_valor, ''), ' => ', IFNULL(a.new_valor, '')) AS detalles,
                        a.old_valor,
                        a.new_valor,
                        COALESCE(u.username, 'Sistema') AS usuario
                    FROM auditoria a
                    LEFT JOIN usuario_historial uh ON a.id_auditoria = uh.id_ha
                    LEFT JOIN usuarios u ON uh.id_user = u.id_user
                    WHERE a.id_auditoria = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            error_log('Error preparar getAuditoriaById: ' . $this->db->error);
            return null;
        }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) { $stmt->close(); return null; }
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Obtiene los últimos N registros de auditoría ordenados por fecha desc.
     * @param int $limit
     * @return array
     */
    public function getRecentLogs($limit = 5) {
        $limit = (int)$limit;
        if ($limit <= 0) $limit = 5;
        $query = "SELECT 
                        a.id_auditoria,
                        a.fecha_hora AS fecha,
                        a.seccion,
                        a.accion,
                        CONCAT(IFNULL(a.old_valor, ''), ' => ', IFNULL(a.new_valor, '')) AS detalles,
                        COALESCE(u.username, 'Sistema') AS usuario
                    FROM auditoria a
                    LEFT JOIN usuario_historial uh ON a.id_auditoria = uh.id_ha
                    LEFT JOIN usuarios u ON uh.id_user = u.id_user
                    ORDER BY a.fecha_hora DESC
                    LIMIT ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) { error_log('Error preparar getRecentLogs: ' . $this->db->error); return []; }
        $stmt->bind_param('i', $limit);
        if (!$stmt->execute()) { $stmt->close(); return []; }
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Devuelve los triggers que afectan la tabla 'auditoria' o que existen en la BD.
     * @return array
     */
    public function getTriggers() {
        $rows = [];
        $res = $this->db->query("SHOW TRIGGERS");
        if ($res) {
            while ($r = $res->fetch_assoc()) $rows[] = $r;
        }
        return $rows;
    }

    /**
     * Devuelve los procedimientos y funciones almacenadas en la BD.
     * @return array
     */
    public function getRoutines() {
        $rows = [];
        $res = $this->db->query("SELECT ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER, ROUTINE_DEFINITION FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");
        if ($res) {
            while ($r = $res->fetch_assoc()) $rows[] = $r;
        }
        return $rows;
    }

    /**
     * Inserta un registro en la tabla auditoria y, opcionalmente, en usuario_historial.
     * @param string $seccion
     * @param string $accion
     * @param string|null $detalles
     * @param string|null $old_valor
     * @param string|null $new_valor
     * @param int|null $folio_egreso
     * @param int|null $folio_ingreso
     * @param int|null $id_user
     * @return bool True si se insertó correctamente
     */
    public function addLog($seccion, $accion, $detalles = null, $old_valor = null, $new_valor = null, $folio_egreso = null, $folio_ingreso = null, $id_user = null) {
        $query = "INSERT INTO auditoria (seccion, accion, detalles, old_valor, new_valor, folio_egreso, folio_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            error_log('Error preparar addLog: ' . $this->db->error);
            return false;
        }
        $stmt->bind_param('ssssiii', $seccion, $accion, $detalles, $old_valor, $new_valor, $folio_egreso, $folio_ingreso);
        $ok = $stmt->execute();
        if (!$ok) {
            error_log('Error ejecutar addLog: ' . $stmt->error);
            $stmt->close();
            return false;
        }
        $lastId = $this->db->insert_id;
        $stmt->close();

        // Si se proporcionó id_user, insertar en usuario_historial para asignar usuario al log
        if (!empty($id_user) && filter_var($id_user, FILTER_VALIDATE_INT)) {
            $sth = $this->db->prepare("INSERT INTO usuario_historial (id_ha, id_user) VALUES (?, ?)");
            if ($sth) {
                $sth->bind_param('ii', $lastId, $id_user);
                if (!$sth->execute()) {
                    error_log('Error al insertar usuario_historial: ' . $sth->error);
                }
                $sth->close();
            }
        }
        return true;
    }

    /**
     * Comprueba si existe algún trigger asociado a una tabla dada.
     * @param string $table
     * @return bool
     */
    public function hasTriggerForTable($table) {
        $safe = $this->db->real_escape_string($table);
        $res = $this->db->query("SHOW TRIGGERS WHERE `Table` = '{$safe}'");
        if (!$res) return false;
        $has = $res->num_rows > 0;
        $res->close();
        return $has;
    }
}
?>