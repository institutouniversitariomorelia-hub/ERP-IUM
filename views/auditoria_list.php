<?php
// views/auditoria_list.php
// Llamada por AuditoriaController->index()
// Variables disponibles: $pageTitle, $activeModule, $auditoriaLogs, $usuarios, $filtrosActuales, $currentUser
?>

<h3 class="text-danger mb-4"><?php echo htmlspecialchars($pageTitle); ?></h3>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form id="formFiltroAuditoria" method="GET" action="<?php echo BASE_URL; ?>index.php">
            <input type="hidden" name="controller" value="auditoria">
            <input type="hidden" name="action" value="index">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="filtro_seccion" class="form-label">Sección</label>
                    <select id="filtro_seccion" name="seccion" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="Usuario" <?php echo ($filtrosActuales['seccion'] ?? '') === 'Usuario' ? 'selected' : ''; ?>>Usuario</option>
                        <option value="Egreso" <?php echo ($filtrosActuales['seccion'] ?? '') === 'Egreso' ? 'selected' : ''; ?>>Egreso</option>
                        <option value="Ingreso" <?php echo ($filtrosActuales['seccion'] ?? '') === 'Ingreso' ? 'selected' : ''; ?>>Ingreso</option>
                        <option value="Categoria" <?php echo ($filtrosActuales['seccion'] ?? '') === 'Categoria' ? 'selected' : ''; ?>>Categoría</option>
                        <option value="Presupuesto" <?php echo ($filtrosActuales['seccion'] ?? '') === 'Presupuesto' ? 'selected' : ''; ?>>Presupuesto</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtro_usuario" class="form-label">Usuario</label>
                    <select id="filtro_usuario" name="usuario" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach($usuarios as $user): ?>
                                <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo (isset($filtrosActuales['usuario']) && $filtrosActuales['usuario'] == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['nombre']); ?>
                                </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rango de Fechas</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($filtrosActuales['fecha_inicio'] ?? ''); ?>">
                        <span class="input-group-text">a</span>
                        <input type="date" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($filtrosActuales['fecha_fin'] ?? ''); ?>">
                    </div>
                </div>

                    <style>
                        /* Estilos específicos para la vista de auditoría */
                        .aud-action-badge { font-weight:600; font-size:0.85em; }
                        .aud-row-insert { background: rgba(198, 239, 206, 0.35); }
                        .aud-row-update { background: rgba(255, 243, 205, 0.35); }
                        .aud-row-delete { background: rgba(248, 215, 218, 0.35); }
                        #aud_raw_consulta { font-family: monospace; font-size: 0.9em; }
                    </style>

                <div class="col-md-2">
                    <label for="filtro_accion_tipo" class="form-label">Tipo Acción</label>
                    <select id="filtro_accion_tipo" name="accion_tipo" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="Insercion" <?php echo ($filtrosActuales['accion_tipo'] ?? '') === 'Insercion' ? 'selected' : ''; ?>>Registro</option>
                        <option value="Actualizacion" <?php echo ($filtrosActuales['accion_tipo'] ?? '') === 'Actualizacion' ? 'selected' : ''; ?>>Actualización</option>
                        <option value="Eliminacion" <?php echo ($filtrosActuales['accion_tipo'] ?? '') === 'Eliminacion' ? 'selected' : ''; ?>>Eliminación</option>
                    </select>
                </div>

                    <?php
                    // Simple controles de paginación: mostrar total y prev/next si se recibieron variables
                    $totalLogs = isset($totalLogs) ? (int)$totalLogs : null;
                    $page = isset($page) ? (int)$page : 1;
                    $pageSize = isset($pageSize) ? (int)$pageSize : 50;
                    if ($totalLogs !== null):
                        $totalPages = max(1, (int)ceil($totalLogs / $pageSize));
                        // Construir base de query con filtros actuales para mantenerlos en los enlaces
                        $baseParams = [];
                        if (!empty($filtrosActuales['seccion'])) $baseParams['seccion'] = $filtrosActuales['seccion'];
                        if (!empty($filtrosActuales['usuario'])) $baseParams['usuario'] = $filtrosActuales['usuario'];
                        if (!empty($filtrosActuales['fecha_inicio'])) $baseParams['fecha_inicio'] = $filtrosActuales['fecha_inicio'];
                        if (!empty($filtrosActuales['fecha_fin'])) $baseParams['fecha_fin'] = $filtrosActuales['fecha_fin'];
                        if (!empty($filtrosActuales['accion'])) $baseParams['accion'] = $filtrosActuales['accion'];
                        if (!empty($filtrosActuales['q'])) $baseParams['q'] = $filtrosActuales['q'];
                        // Helper para construir url
                        function aud_query($params) {
                            return htmlspecialchars(BASE_URL . 'index.php?' . http_build_query($params));
                        }
                    ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Mostrando página <?php echo $page; ?> de <?php echo $totalPages; ?> — <?php echo $totalLogs; ?> registros</div>
                        <div>
                            <div class="btn-group btn-group-sm" role="group">
                                <?php if ($page > 1):
                                    $p = $page - 1; $params = array_merge($baseParams, ['controller' => 'auditoria', 'action' => 'index', 'page' => $p, 'pageSize' => $pageSize]); ?>
                                    <a class="btn btn-outline-secondary" href="<?php echo aud_query($params); ?>">&laquo; Anterior</a>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary disabled">&laquo; Anterior</button>
                                <?php endif; ?>

                                <?php if ($page < $totalPages):
                                    $p = $page + 1; $params = array_merge($baseParams, ['controller' => 'auditoria', 'action' => 'index', 'page' => $p, 'pageSize' => $pageSize]); ?>
                                    <a class="btn btn-outline-secondary" href="<?php echo aud_query($params); ?>">Siguiente &raquo;</a>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary disabled">Siguiente &raquo;</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                         <ion-icon name="filter-outline"></ion-icon> Filtrar
                    </button>
                </div>
            </div>
            <!-- Segunda fila de filtros: acción y búsqueda libre -->
            <div class="row g-3 mt-2 align-items-end">
                <div class="col-md-4">
                    <label for="filtro_accion" class="form-label">Acción (texto)</label>
                    <input id="filtro_accion" name="accion" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filtrosActuales['accion'] ?? ''); ?>" placeholder="p.ej. Insercion, Eliminacion, Actualizacion">
                </div>
                <div class="col-md-6">
                    <label for="filtro_q" class="form-label">Buscar (detalles / old / new)</label>
                    <input id="filtro_q" name="q" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filtrosActuales['q'] ?? ''); ?>" placeholder="Texto libre para buscar en detalles">
                </div>
                <div class="col-md-2"></div>
            </div>
            <!-- Paginación y tamaño de página (oculto, se puede ajustar) -->
            <input type="hidden" name="page" value="<?php echo (int)($filtrosActuales['page'] ?? 1); ?>">
            <input type="hidden" name="pageSize" value="<?php echo (int)($filtrosActuales['pageSize'] ?? 50); ?>">
        </form>
    </div>
</div>

<!-- Pequeña tabla con los 5 movimientos más recientes -->
<div class="card shadow-sm mb-4">
    <div class="card-header">Movimientos Recientes</div>
            <div class="card-body p-2">
        <?php if (!empty($recentLogs)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr class="table-light"><th>Fecha</th><th>Usuario</th><th>Sección</th><th>Acción</th><th>Resumen</th></tr></thead>
                    <tbody>
                        <?php foreach($recentLogs as $r):
                            $dt = '-';
                            if (!empty($r['fecha'])) {
                                try { $d = new DateTime($r['fecha']); $dt = $d->format('d/m/Y H:i'); } catch(Exception $e) { $dt = htmlspecialchars($r['fecha']); }
                            }
                            // Determinar tipo de acción para badge y clase de fila
                            $actRaw = $r['accion'] ?? '';
                            $actLower = mb_strtolower($actRaw);
                            $actionLabel = htmlspecialchars($actRaw ?: '-');
                            $rowClass = '';
                            $badgeClass = 'bg-secondary';
                            if (mb_stripos($actLower, 'inser') !== false || mb_stripos($actLower, 'registro') !== false) { $rowClass = 'aud-row-insert'; $badgeClass = 'bg-success'; $actionLabel = 'Registro'; }
                            elseif (mb_stripos($actLower, 'actual') !== false || mb_stripos($actLower, 'update') !== false) { $rowClass = 'aud-row-update'; $badgeClass = 'bg-warning text-dark'; $actionLabel = 'Actualización'; }
                            elseif (mb_stripos($actLower, 'elim') !== false || mb_stripos($actLower, 'delete') !== false) { $rowClass = 'aud-row-delete'; $badgeClass = 'bg-danger'; $actionLabel = 'Eliminación'; }
                            $summary = htmlspecialchars(mb_strimwidth($r['detalles'] ?? '', 0, 60, '...'));
                            $rowId = htmlspecialchars($r['id_auditoria'] ?? $r['id_auditoria']);
                        ?>
                            <tr class="aud-row <?php echo $rowClass; ?> clickable-row" data-id="<?php echo $rowId; ?>">
                                <td><?php echo $dt; ?></td>
                                <td><?php echo htmlspecialchars($r['usuario'] ?? 'Sistema'); ?></td>
                                <td><?php echo htmlspecialchars($r['seccion'] ?? '-'); ?></td>
                                <td><span class="badge aud-action-badge <?php echo $badgeClass; ?>"><?php echo $actionLabel; ?></span></td>
                                <td><?php echo $summary; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-2"><small class="text-muted">Haz clic en un registro para ver detalles.</small></div>
        <?php else: ?>
            <div class="text-muted p-2">No hay movimientos recientes.</div>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4">
     <div class="card-header">Historial Global</div>
    <div class="card-body p-0">
                <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead><tr class="table-light"><th>Fecha</th><th>Usuario</th><th>Sección</th><th>Acción</th><th>Detalles</th></tr></thead>
                <tbody id="tablaAuditoria">
                    <?php if (empty($auditoriaLogs)): ?>
                        <tr><td colspan="5" class="text-center p-4 text-muted">No se encontraron registros con los filtros aplicados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($auditoriaLogs as $log): 
                            // Preparar campos de forma segura para evitar notices
                            $fechaDisplay = '-';
                            if (!empty($log['fecha'])) {
                                try {
                                    $dt = new DateTime($log['fecha']);
                                    $fechaDisplay = $dt->format('d/m/Y, g:i:s a');
                                } catch (Exception $e) {
                                    $fechaDisplay = htmlspecialchars($log['fecha']);
                                }
                            }
                            $usuario = htmlspecialchars($log['usuario'] ?? 'Sistema');
                            $seccion = htmlspecialchars($log['seccion'] ?? '-');
                            $accion = htmlspecialchars($log['accion'] ?? '-');
                            // Crear un resumen limpio para la tabla (preferir new_valor, luego detalles, luego old=>new)
                            $rawDetails = '';
                            if (!empty($log['new_valor'])) $rawDetails = $log['new_valor'];
                            elseif (!empty($log['detalles'])) $rawDetails = $log['detalles'];
                            elseif (!empty($log['old_valor']) || !empty($log['new_valor'])) $rawDetails = ($log['old_valor'] ?? '') . ' => ' . ($log['new_valor'] ?? '');
                            $cleanSummary = trim(preg_replace('/[{}\[\]"]+/', '', $rawDetails));
                            $summary = htmlspecialchars(mb_strimwidth($cleanSummary, 0, 80, '...'));
                            $rowId = htmlspecialchars($log['id_auditoria'] ?? $log['id'] ?? '');
                        ?>
                            <?php
                                // Normalizar tipo de acción para badge/row class
                                $actLower = mb_strtolower($accion);
                                $rowClass = '';
                                $badgeClass = 'bg-secondary';
                                $actionLabel = $accion;
                                if (mb_stripos($actLower, 'inser') !== false || mb_stripos($actLower, 'registro') !== false) { $rowClass = 'aud-row-insert'; $badgeClass = 'bg-success'; $actionLabel = 'Registro'; }
                                elseif (mb_stripos($actLower, 'actual') !== false || mb_stripos($actLower, 'update') !== false) { $rowClass = 'aud-row-update'; $badgeClass = 'bg-warning text-dark'; $actionLabel = 'Actualización'; }
                                elseif (mb_stripos($actLower, 'elim') !== false || mb_stripos($actLower, 'delete') !== false) { $rowClass = 'aud-row-delete'; $badgeClass = 'bg-danger'; $actionLabel = 'Eliminación'; }
                            ?>
                            <tr class="aud-row <?php echo $rowClass; ?> clickable-row" data-id="<?php echo $rowId; ?>">
                                <td><?php echo $fechaDisplay; ?></td>
                                <td><?php echo $usuario; ?></td>
                                <td><?php echo $seccion; ?></td>
                                <td><span class="badge aud-action-badge <?php echo $badgeClass; ?>"><?php echo $actionLabel; ?></span></td>
                                <td><?php echo $summary; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para ver detalles completos de un registro de auditoría -->
<div class="modal fade" id="modalAuditoriaDetalle" tabindex="-1" aria-labelledby="modalAuditoriaDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header modal-header-danger">
                <h5 class="modal-title" id="modalAuditoriaDetalleLabel">Detalle de Movimiento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="aud_detalle_body">
                    <!-- Contenido rellenado vía AJAX -->
                    <dl class="row">
                        <dt class="col-sm-3">Fecha</dt><dd class="col-sm-9" id="aud_det_fecha">-</dd>
                        <dt class="col-sm-3">Usuario</dt><dd class="col-sm-9" id="aud_det_usuario">-</dd>
                        <dt class="col-sm-3">Sección</dt><dd class="col-sm-9" id="aud_det_seccion">-</dd>
                        <dt class="col-sm-3">Acción</dt><dd class="col-sm-9" id="aud_det_accion">-</dd>
                        <dt class="col-sm-3">Detalles</dt><dd class="col-sm-9" id="aud_det_detalles">-</dd>
                        <dt class="col-sm-3">Old Value</dt><dd class="col-sm-9" id="aud_det_old">-</dd>
                        <dt class="col-sm-3">New Value</dt><dd class="col-sm-9" id="aud_det_new">-</dd>
                    </dl>
                    <div class="mb-2">
                        <button id="aud_toggle_raw" type="button" class="btn btn-outline-secondary btn-sm">Mostrar consulta completa</button>
                    </div>
                    <pre id="aud_raw_consulta">-</pre>
                    <div id="aud_compare" class="mt-3"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>



<div class="card shadow-sm">
    <div class="card-header">Historial de Cambios (solo actualizaciones)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead><tr class="table-light"><th>Fecha</th><th>Usuario</th><th>Sección</th><th>Detalles</th></tr></thead>
                <tbody id="tablaCambios">
                        <?php 
                        $cambiosEncontrados = false;
                        if (!empty($auditoriaLogs)) {
                            foreach ($auditoriaLogs as $log) {
                                $accionRaw = $log['accion'] ?? '';
                                if (stripos($accionRaw, 'actualización') !== false || stripos($accionRaw, 'update') !== false) {
                                    $fechaDisplay = '-';
                                    if (!empty($log['fecha'])) {
                                        try { $dt = new DateTime($log['fecha']); $fechaDisplay = $dt->format('d/m/Y, g:i:s a'); } catch (Exception $e) { $fechaDisplay = htmlspecialchars($log['fecha']); }
                                    }
                                    echo "<tr>";
                                    echo "<td>" . $fechaDisplay . "</td>";
                                    echo "<td>" . htmlspecialchars($log['usuario'] ?? 'Sistema') . "</td>";
                                    echo "<td>" . htmlspecialchars($log['seccion'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($log['detalles'] ?? '') . "</td>";
                                    echo "</tr>";
                                    $cambiosEncontrados = true;
                                }
                            }
                        }
                        if (!$cambiosEncontrados): 
                    ?>
                        <tr><td colspan="4" class="text-center p-4 text-muted">No se encontraron registros de actualización con los filtros aplicados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
     <button class="btn btn-secondary disabled">
         <ion-icon name="download-outline"></ion-icon> Generar Reporte (CSV)
    </button>
</div>