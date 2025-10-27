<?php
// models/AuditoriaModel.php

class AuditoriaModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene los registros de auditoría, opcionalmente filtrados.
     * @param array $filtros Array asociativo con los filtros (seccion, usuario, fecha_inicio, fecha_fin).
     * @return array Lista de registros de auditoría.
     */
    public function getAuditoriaLogs($filtros = []) {
        $query = "SELECT * FROM auditoria WHERE 1=1"; // Empieza la consulta base
        $params = []; // Array para los parámetros de bind_param
        $types = ''; // String para los tipos de bind_param

        // Añadir filtros si existen
        if (!empty($filtros['seccion'])) {
            $query .= " AND seccion = ?";
            $params[] = $filtros['seccion'];
            $types .= 's';
        }
        if (!empty($filtros['usuario'])) {
            $query .= " AND usuario = ?";
            $params[] = $filtros['usuario'];
            $types .= 's';
        }
        if (!empty($filtros['fecha_inicio'])) {
            // Compara solo la parte de la fecha (ignora hora) >= fecha_inicio
            $query .= " AND DATE(fecha) >= ?"; 
            $params[] = $filtros['fecha_inicio'];
            $types .= 's';
        }
        if (!empty($filtros['fecha_fin'])) {
             // Compara solo la parte de la fecha (ignora hora) <= fecha_fin
            $query .= " AND DATE(fecha) <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= 's';
        }

        // Ordenar por fecha más reciente y limitar resultados (opcional)
        $query .= " ORDER BY fecha DESC LIMIT 500"; // Limita a 500 para evitar sobrecarga

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