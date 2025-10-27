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
        
        // --- ¡CONSULTA TOTALMENTE REESCRITA CON JOINS! ---
        $query = "SELECT 
                    a.id_auditoria,
                    a.fecha_hora,
                    a.seccion,
                    a.accion,
                    a.old_valor,
                    a.new_valor,
                    a.folio_egreso,
                    a.folio_ingreso,
                    u.username  -- Obtenemos el username del usuario
                  FROM auditoria a
                  -- Unimos con la tabla de relación
                  LEFT JOIN usuario_historial uh ON a.id_auditoria = uh.id_ha
                  -- Unimos con la tabla de usuarios para obtener el nombre
                  LEFT JOIN usuarios u ON uh.id_user = u.id_user
                  WHERE 1=1"; // Empieza la consulta base
        
        $params = []; // Array para los parámetros de bind_param
        $types = ''; // String para los tipos de bind_param

        // Añadir filtros si existen
        if (!empty($filtros['seccion'])) {
            $query .= " AND a.seccion = ?"; // Usamos alias 'a'
            $params[] = $filtros['seccion'];
            $types .= 's';
        }
        
        // CORREGIDO: El filtro de usuario ahora es por 'id_user'
        if (!empty($filtros['usuario'])) { // Asumimos que $filtros['usuario'] es el id_user
            $query .= " AND u.id_user = ?"; // Usamos alias 'u'
            $params[] = $filtros['usuario'];
            $types .= 'i'; // Es un ID numérico
        }
        
        // CORREGIDO: La columna de fecha es 'fecha_hora'
        if (!empty($filtros['fecha_inicio'])) {
            $query .= " AND DATE(a.fecha_hora) >= ?"; // Usamos alias 'a'
            $params[] = $filtros['fecha_inicio'];
            $types .= 's';
        }
        if (!empty($filtros['fecha_fin'])) {
            $query .= " AND DATE(a.fecha_hora) <= ?"; // Usamos alias 'a'
            $params[] = $filtros['fecha_fin'];
            $types .= 's';
        }

        // CORREGIDO: Ordenar por 'fecha_hora'
        $query .= " ORDER BY a.fecha_hora DESC LIMIT 500"; 

        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            error_log("Error al preparar getAuditoriaLogs: " . $this->db->error);
            return [];
        }

        // Vincular parámetros si hay filtros
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        // Ejecutar y obtener resultados
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $stmt->close();
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Error al ejecutar getAuditoriaLogs: " . $stmt->error);
            $stmt->close();
            return [];
        }
    }
}
?>