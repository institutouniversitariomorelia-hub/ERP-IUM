<?php
// views/auditoria_list.php - Versión Definitiva (Limpia)
// La lógica JS se ha movido a public/js/app.js (AuditoriaModule)
// Llamada por AuditoriaController->index()
?>

<h3 class="text-danger mb-4"><?php echo htmlspecialchars($pageTitle); ?></h3>

<!-- Tarjeta de Filtros -->
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
                    /* Estilos específicos locales para badges de auditoría */
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
                // Controles de paginación
                $totalLogs = isset($totalLogs) ? (int)$totalLogs : null;
                $page = isset($page) ? (int)$page : 1;
                $pageSize = isset($pageSize) ? (int)$pageSize : 50;
                if ($totalLogs !== null):
                    $totalPages = max(1, (int)ceil($totalLogs / $pageSize));
                    $baseParams = [];
                    if (!empty($filtrosActuales['seccion'])) $baseParams['seccion'] = $filtrosActuales['seccion'];
                    if (!empty($filtrosActuales['usuario'])) $baseParams['usuario'] = $filtrosActuales['usuario'];
                    if (!empty($filtrosActuales['fecha_inicio'])) $baseParams['fecha_inicio'] = $filtrosActuales['fecha_inicio'];
                    if (!empty($filtrosActuales['fecha_fin'])) $baseParams['fecha_fin'] = $filtrosActuales['fecha_fin'];
                    if (!empty($filtrosActuales['accion_tipo'])) $baseParams['accion_tipo'] = $filtrosActuales['accion_tipo'];
                    
                    function aud_query($params) {
                        return htmlspecialchars(BASE_URL . 'index.php?' . http_build_query($params));
                    }
                ?>
                <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">Mostrando página <?php echo $page; ?> de <?php echo $totalPages; ?> — <?php echo $totalLogs; ?> registros</div>
                    <div>
                        <div class="btn-group btn-group-sm" role="group">
                            <?php if ($page > 1):
                                $p = $page - 1; $params = array_merge($baseParams, ['controller' => 'auditoria', 'action' => 'index', 'page' => $p, 'pageSize' => $pageSize]); ?>
                                <a class="btn btn-outline-secondary" href="<?php echo aud_query($params); ?>">&laquo; Anterior</a>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary disabled" type="button">&laquo; Anterior</button>
                            <?php endif; ?>

                            <?php if ($page < $totalPages):
                                $p = $page + 1; $params = array_merge($baseParams, ['controller' => 'auditoria', 'action' => 'index', 'page' => $p, 'pageSize' => $pageSize]); ?>
                                <a class="btn btn-outline-secondary" href="<?php echo aud_query($params); ?>">Siguiente &raquo;</a>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary disabled" type="button">Siguiente &raquo;</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                         <ion-icon name="filter-outline" style="vertical-align:middle"></ion-icon> Filtrar
                    </button>
                </div>
            </div>
            <input type="hidden" name="page" value="<?php echo (int)($filtrosActuales['page'] ?? 1); ?>">
            <input type="hidden" name="pageSize" value="<?php echo (int)($filtrosActuales['pageSize'] ?? 50); ?>">
        </form>
    </div>
</div>

<!-- Tabla Historial Global -->
<div class="card shadow-sm mb-4">
     <div class="card-header">Historial Global</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead><tr class="table-light"><th>Fecha</th><th>Usuario</th><th>Sección</th><th>Acción</th></tr></thead>
                <tbody id="tablaAuditoria">
                    <?php if (empty($auditoriaLogs)): ?>
                        <tr><td colspan="4" class="text-center p-4 text-muted">No se encontraron registros con los filtros aplicados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($auditoriaLogs as $log): 
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
                            $rowId = htmlspecialchars($log['id_auditoria'] ?? $log['id'] ?? '');

                            $actLower = mb_strtolower($accion);
                            $rowClass = '';
                            $badgeClass = 'bg-secondary';
                            $actionLabel = $accion;
                            if (mb_stripos($actLower, 'inser') !== false || mb_stripos($actLower, 'registro') !== false) { $rowClass = 'aud-row-insert'; $badgeClass = 'bg-success'; $actionLabel = 'Registro'; }
                            elseif (mb_stripos($actLower, 'actual') !== false || mb_stripos($actLower, 'update') !== false) { $rowClass = 'aud-row-update'; $badgeClass = 'bg-warning text-dark'; $actionLabel = 'Actualización'; }
                            elseif (mb_stripos($actLower, 'elim') !== false || mb_stripos($actLower, 'delete') !== false) { $rowClass = 'aud-row-delete'; $badgeClass = 'bg-danger'; $actionLabel = 'Eliminación'; }
                        ?>
                            <tr class="aud-row <?php echo $rowClass; ?> clickable-row" data-id="<?php echo $rowId; ?>" style="cursor:pointer;">
                                <td><?php echo $fechaDisplay; ?></td>
                                <td><?php echo $usuario; ?></td>
                                <td><?php echo $seccion; ?></td>
                                <td><span class="badge aud-action-badge <?php echo $badgeClass; ?>"><?php echo $actionLabel; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tabla Movimientos Recientes -->
<div class="card shadow-sm mb-4">
    <div class="card-header">Movimientos Recientes</div>
    <div class="card-body p-2">
        <?php if (!empty($recentLogs)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr class="table-light"><th>Fecha</th><th>Usuario</th><th>Sección</th><th>Acción</th></tr></thead>
                    <tbody>
                        <?php foreach($recentLogs as $r):
                            $dt = '-';
                            if (!empty($r['fecha'])) {
                                try { $d = new DateTime($r['fecha']); $dt = $d->format('d/m/Y H:i'); } catch(Exception $e) { $dt = htmlspecialchars($r['fecha']); }
                            }
                            $actRaw = $r['accion'] ?? '';
                            $actLower = mb_strtolower($actRaw);
                            $actionLabel = htmlspecialchars($actRaw ?: '-');
                            $rowClass = '';
                            $badgeClass = 'bg-secondary';
                            if (mb_stripos($actLower, 'inser') !== false || mb_stripos($actLower, 'registro') !== false) { $rowClass = 'aud-row-insert'; $badgeClass = 'bg-success'; $actionLabel = 'Registro'; }
                            elseif (mb_stripos($actLower, 'actual') !== false || mb_stripos($actLower, 'update') !== false) { $rowClass = 'aud-row-update'; $badgeClass = 'bg-warning text-dark'; $actionLabel = 'Actualización'; }
                            elseif (mb_stripos($actLower, 'elim') !== false || mb_stripos($actLower, 'delete') !== false) { $rowClass = 'aud-row-delete'; $badgeClass = 'bg-danger'; $actionLabel = 'Eliminación'; }
                            $rowId = htmlspecialchars($r['id_auditoria'] ?? $r['id_auditoria']);
                        ?>
                            <tr class="aud-row <?php echo $rowClass; ?> clickable-row" data-id="<?php echo $rowId; ?>" style="cursor:pointer;">
                                <td><?php echo $dt; ?></td>
                                <td><?php echo htmlspecialchars($r['usuario'] ?? 'Sistema'); ?></td>
                                <td><?php echo htmlspecialchars($r['seccion'] ?? '-'); ?></td>
                                <td><span class="badge aud-action-badge <?php echo $badgeClass; ?>"><?php echo $actionLabel; ?></span></td>
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

<!-- Sección de Reportes -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><ion-icon name="bar-chart-outline" style="vertical-align:middle; margin-right:8px;"></ion-icon>Generación de Reportes</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <!-- Nota: onclick llama a funciones globales expuestas en app.js -->
                <button class="btn btn-primary w-100" onclick="generarReporteAuditoria('semanal')">
                    <ion-icon name="calendar-outline" class="me-2" style="vertical-align:middle"></ion-icon>Reporte Semanal
                </button>
                <small class="text-muted d-block text-center mt-1">Últimos 7 días</small>
            </div>
            <div class="col-md-6">
                <button class="btn btn-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReportePersonalizado">
                    <ion-icon name="calendar-number-outline" class="me-2" style="vertical-align:middle"></ion-icon>Reporte Personalizado
                </button>
                <small class="text-muted d-block text-center mt-1">Seleccionar rango de fechas</small>
            </div>
        </div>

        <!-- Formulario de reporte personalizado (colapsable) -->
        <div class="collapse mt-3" id="collapseReportePersonalizado">
            <div class="card card-body bg-light">
                <form id="formReporteAuditoriaPersonalizado" onsubmit="generarReporteAuditoriaPersonalizado(event)">
                    <div class="row">
                        <div class="col-md-5">
                            <label for="auditoria_reporte_fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="auditoria_reporte_fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-5">
                            <label for="auditoria_reporte_fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="auditoria_reporte_fecha_fin" name="fecha_fin" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <ion-icon name="search-outline" class="me-1" style="vertical-align:middle"></ion-icon>Generar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor de resultados del reporte -->
<div id="resultadoReporteAuditoriaContainer" style="display: none;" class="mb-4">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><ion-icon name="document-text-outline" class="me-2" style="vertical-align:middle"></ion-icon>Reporte de Auditoría</h5>
            <div>
                <button class="btn btn-success btn-sm me-2" onclick="exportarReporteExcel()">
                    <ion-icon name="download-outline" class="me-1" style="vertical-align:middle"></ion-icon>Excel
                </button>
                <button class="btn btn-light btn-sm me-2" onclick="imprimirReporteAuditoria()">
                    <ion-icon name="print-outline" class="me-1" style="vertical-align:middle"></ion-icon>Imprimir
                </button>
                <button class="btn btn-light btn-sm" onclick="cerrarReporteAuditoria()">
                    <ion-icon name="close-outline" style="vertical-align:middle"></ion-icon>
                </button>
            </div>
        </div>
        <div class="card-body" id="contenidoReporteAuditoria">
            <div id="headerReporteAuditoria" class="mb-3"></div>
            <div id="resumenReporteAuditoria" class="mb-4"></div>
            <div class="row mb-4" id="graficasReporte">
                <div class="col-md-4">
                    <canvas id="chartAuditoriaPorSeccion" style="max-height: 250px;"></canvas>
                </div>
                <div class="col-md-4">
                    <canvas id="chartAuditoriaPorAccion" style="max-height: 250px;"></canvas>
                </div>
                <div class="col-md-4">
                    <canvas id="chartAuditoriaPorUsuario" style="max-height: 250px;"></canvas>
                </div>
            </div>
            <div id="tablaReporteAuditoria"></div>
        </div>
    </div>
</div>

<!-- Estilos de impresión -->
<style>
@media print {
    #sidebar, .top-header, .btn, .card-header .btn, #collapseReportePersonalizado, form { display: none !important; }
    #resultadoReporteAuditoriaContainer { display: block !important; }
    body { background: white !important; padding: 0 !important; margin: 0 !important; overflow: visible !important; }
    .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; page-break-inside: avoid; margin-bottom: 20px !important; }
    table { width: 100% !important; font-size: 10pt !important; border-collapse: collapse !important; }
    th, td { border: 1px solid #ccc !important; padding: 6px !important; }
    canvas { max-height: 300px !important; width: auto !important; }
}
</style>