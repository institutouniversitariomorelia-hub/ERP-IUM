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
            <input type="hidden" name="pageSize" value="<?php echo (int)($filtrosActuales['pageSize'] ?? 10); ?>">
        </form>
    </div>
</div>

<!-- Historial Global (PRIMERO) -->
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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Movimientos Recientes (SEGUNDO) -->
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
                            // Determinar tipo de acción para badge y clase de fila
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
                            <tr class="aud-row <?php echo $rowClass; ?> clickable-row" data-id="<?php echo $rowId; ?>">
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
        <h5 class="mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>Generación de Reportes</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <button class="btn btn-primary w-100" onclick="generarReporteAuditoria('semanal')">
                    <i class="bi bi-calendar-week me-2"></i>Reporte Semanal
                </button>
                <small class="text-muted d-block text-center mt-1">Últimos 7 días</small>
            </div>
            <div class="col-md-6">
                <button class="btn btn-primary w-100" onclick="$('#collapseReportePersonalizado').collapse('toggle')">
                    <i class="bi bi-calendar-range me-2"></i>Reporte Personalizado
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
                            <input type="date" class="form-control" id="auditoria_reporte_fecha_inicio" name="fecha_inicio" autocomplete="off" required>
                        </div>
                        <div class="col-md-5">
                            <label for="auditoria_reporte_fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="auditoria_reporte_fecha_fin" name="fecha_fin" autocomplete="off" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-search me-1"></i>Generar
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
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Reporte de Auditoría</h5>
            <div>
                <button class="btn btn-success btn-sm me-2" onclick="exportarReporteExcel()">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </button>
                <button class="btn btn-light btn-sm me-2" onclick="imprimirReporteAuditoria()">
                    <i class="bi bi-printer me-1"></i>Imprimir
                </button>
                <button class="btn btn-light btn-sm" onclick="cerrarReporteAuditoria()">
                    <i class="bi bi-x-lg"></i>
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

<!-- Modal para ver detalles completos de un registro de auditoría -->
<div class="modal fade" id="modalAuditoriaDetalle" tabindex="-1" aria-labelledby="modalAuditoriaDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header modal-header-danger">
                <h5 class="modal-title" id="modalAuditoriaDetalleLabel">Detalle de Movimiento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="aud_detalle_body">
                    <!-- Info básica -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Fecha:</strong>
                            <div id="aud_det_fecha" class="text-muted">-</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Usuario:</strong>
                            <div id="aud_det_usuario" class="text-muted">-</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Sección:</strong>
                            <div id="aud_det_seccion" class="text-muted">-</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Tipo:</strong>
                            <div id="aud_det_accion">-</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Comparación visual para actualizaciones -->
                    <div id="aud_compare_container" style="display:none;">
                        <h6 class="mb-3">Cambios Realizados</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <small>❌ Valores Anteriores</small>
                                    </div>
                                    <div class="card-body" id="aud_old_values">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <small>✓ Valores Nuevos</small>
                                    </div>
                                    <div class="card-body" id="aud_new_values">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detalles generales -->
                    <div id="aud_det_detalles_container">
                        <h6 class="mb-2">Detalles</h6>
                        <div id="aud_det_detalles" class="p-3 bg-light rounded">-</div>
                    </div>
                    
                    <!-- Botón para ver JSON raw -->
                    <div class="mt-3">
                        <button id="aud_toggle_raw" type="button" class="btn btn-outline-secondary btn-sm">
                            <ion-icon name="code-outline"></ion-icon> Ver datos técnicos (JSON)
                        </button>
                    </div>
                    <pre id="aud_raw_consulta" style="display:none; max-height:300px; overflow-y:auto;" class="bg-dark text-light p-3 rounded mt-2">-</pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// ========== FUNCIONES DE REPORTES DE AUDITORÍA ==========
let chartAuditoriaPorSeccion = null;
let chartAuditoriaPorAccion = null;
let chartAuditoriaPorUsuario = null;
let datosReporteAuditoria = null;

// Función de notificación simple
function showNotification(message, type = 'info') {
    alert(message);
}

function generarReporteAuditoria(tipo) {
    const url = `<?php echo BASE_URL; ?>index.php?controller=auditoria&action=generarReporte&tipo=${tipo}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosReporteAuditoria = data;
                mostrarReporteAuditoria(data);
            } else {
                showNotification(data.error || 'Error al generar reporte', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión al generar reporte', 'danger');
        });
}

function generarReporteAuditoriaPersonalizado(event) {
    event.preventDefault();
    const fechaInicio = document.getElementById('auditoria_reporte_fecha_inicio').value;
    const fechaFin = document.getElementById('auditoria_reporte_fecha_fin').value;
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        showNotification('La fecha de inicio no puede ser mayor a la fecha fin', 'warning');
        return;
    }
    
    const url = `<?php echo BASE_URL; ?>index.php?controller=auditoria&action=generarReporte&tipo=personalizado&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosReporteAuditoria = data;
                mostrarReporteAuditoria(data);
                // Cerrar el collapse
                $('#collapseReportePersonalizado').collapse('hide');
            } else {
                showNotification(data.error || 'Error al generar reporte', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión al generar reporte', 'danger');
        });
}

function mostrarReporteAuditoria(data) {
    document.getElementById('resultadoReporteAuditoriaContainer').style.display = 'block';
    
    // Scroll suave al reporte
    document.getElementById('resultadoReporteAuditoriaContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Header
    let tipoTexto = data.tipo === 'semanal' ? 'Semanal (últimos 7 días)' : 'Personalizado';
    
    document.getElementById('headerReporteAuditoria').innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Tipo: <span class="badge bg-info">${tipoTexto}</span></h6>
                <p class="mb-0 text-muted">Período: ${formatDateAud(data.fechaInicio)} - ${formatDateAud(data.fechaFin)}</p>
            </div>
        </div>
    `;
    
    // Resumen
    document.getElementById('resumenReporteAuditoria').innerHTML = `
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total de Movimientos</h6>
                        <h3 class="text-primary mb-0">${data.totalLogs}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Secciones Afectadas</h6>
                        <h3 class="text-info mb-0">${Object.keys(data.porSeccion).length}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Usuarios Activos</h6>
                        <h3 class="text-success mb-0">${Object.keys(data.porUsuario).length}</h3>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Gráficas
    renderChartAuditoriaPorSeccion(data.porSeccion);
    renderChartAuditoriaPorAccion(data.porAccion);
    renderChartAuditoriaPorUsuario(data.porUsuario);
    
    // Tabla de logs
    let tablaHTML = `
        <h6 class="mb-3">Detalle de Movimientos</h6>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-info">
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Sección</th>
                        <th>Acción</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    if (data.logs.length > 0) {
        data.logs.forEach(log => {
            const fechaHora = formatDateTimeAud(log.fecha_hora);
            const usuario = log.usuario_nombre || 'Sistema';
            const seccion = log.seccion || '-';
            const accion = log.accion || '-';
            const detalles = log.detalles || '-';
            
            // Determinar clase de badge según acción
            let badgeClass = 'bg-secondary';
            let actionLabel = accion;
            const accionLower = accion.toLowerCase();
            
            if (accionLower.includes('inser') || accionLower.includes('registro')) {
                badgeClass = 'bg-success';
                actionLabel = 'Registro';
            } else if (accionLower.includes('actual') || accionLower.includes('update')) {
                badgeClass = 'bg-warning text-dark';
                actionLabel = 'Actualización';
            } else if (accionLower.includes('elim') || accionLower.includes('delete')) {
                badgeClass = 'bg-danger';
                actionLabel = 'Eliminación';
            }
            
            tablaHTML += `
                <tr>
                    <td><small>${fechaHora}</small></td>
                    <td>${usuario}</td>
                    <td>${seccion}</td>
                    <td><span class="badge ${badgeClass}">${actionLabel}</span></td>
                    <td><small>${detalles.substring(0, 50)}${detalles.length > 50 ? '...' : ''}</small></td>
                </tr>
            `;
        });
    } else {
        tablaHTML += '<tr><td colspan="5" class="text-center text-muted">No hay movimientos en este período</td></tr>';
    }
    
    tablaHTML += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('tablaReporteAuditoria').innerHTML = tablaHTML;
}

function renderChartAuditoriaPorSeccion(porSeccion) {
    const ctx = document.getElementById('chartAuditoriaPorSeccion');
    
    if (chartAuditoriaPorSeccion) {
        chartAuditoriaPorSeccion.destroy();
    }
    
    const labels = Object.keys(porSeccion);
    const datos = Object.values(porSeccion);
    
    chartAuditoriaPorSeccion = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: datos,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 10 }
                    }
                },
                title: {
                    display: true,
                    text: 'Por Sección'
                }
            }
        }
    });
}

function renderChartAuditoriaPorAccion(porAccion) {
    const ctx = document.getElementById('chartAuditoriaPorAccion');
    
    if (chartAuditoriaPorAccion) {
        chartAuditoriaPorAccion.destroy();
    }
    
    const labels = Object.keys(porAccion);
    const datos = Object.values(porAccion);
    
    // Colores según tipo de acción
    const colores = labels.map(label => {
        const labelLower = label.toLowerCase();
        if (labelLower.includes('inser') || labelLower.includes('registro')) return 'rgba(75, 192, 192, 0.8)';
        if (labelLower.includes('actual') || labelLower.includes('update')) return 'rgba(255, 206, 86, 0.8)';
        if (labelLower.includes('elim') || labelLower.includes('delete')) return 'rgba(255, 99, 132, 0.8)';
        return 'rgba(153, 102, 255, 0.8)';
    });
    
    chartAuditoriaPorAccion = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: datos,
                backgroundColor: colores
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 10 }
                    }
                },
                title: {
                    display: true,
                    text: 'Por Tipo de Acción'
                }
            }
        }
    });
}

function renderChartAuditoriaPorUsuario(porUsuario) {
    const ctx = document.getElementById('chartAuditoriaPorUsuario');
    
    if (chartAuditoriaPorUsuario) {
        chartAuditoriaPorUsuario.destroy();
    }
    
    const labels = Object.keys(porUsuario);
    const datos = Object.values(porUsuario);
    
    chartAuditoriaPorUsuario = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Movimientos',
                data: datos,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Por Usuario'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    ticks: {
                        font: { size: 9 }
                    }
                }
            }
        }
    });
}

function cerrarReporteAuditoria() {
    document.getElementById('resultadoReporteAuditoriaContainer').style.display = 'none';
}

function exportarReporteExcel() {
    if (!datosReporteAuditoria || !datosReporteAuditoria.movimientos) {
        alert('No hay datos de reporte para exportar');
        return;
    }

    // Crear CSV con BOM UTF-8
    let csv = '\uFEFF';
    
    // Encabezado del reporte
    const tipo = datosReporteAuditoria.tipo || 'Personalizado';
    const periodo = datosReporteAuditoria.periodo || '';
    csv += `Reporte de Auditoría - ${tipo}\n`;
    csv += `Período: ${periodo}\n`;
    csv += `Total de Movimientos: ${datosReporteAuditoria.totalMovimientos || 0}\n`;
    csv += `Secciones Afectadas: ${datosReporteAuditoria.seccionesAfectadas || 0}\n`;
    csv += `Usuarios Activos: ${datosReporteAuditoria.usuariosActivos || 0}\n`;
    csv += '\n';
    
    // Encabezados de la tabla
    csv += 'Fecha/Hora,Usuario,Sección,Acción,Detalles\n';
    
    // Datos
    datosReporteAuditoria.movimientos.forEach(mov => {
        const fecha = mov.fecha_hora || '-';
        const usuario = (mov.usuario_nombre || '-').replace(/,/g, ';');
        const seccion = (mov.seccion || '-').replace(/,/g, ';');
        const accion = (mov.accion || '-').replace(/,/g, ';');
        const detalles = (mov.detalles || '-').replace(/,/g, ';').replace(/\n/g, ' ');
        
        csv += `${fecha},${usuario},${seccion},${accion},"${detalles}"\n`;
    });
    
    // Descargar archivo
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    const fecha = new Date().toISOString().split('T')[0];
    link.setAttribute('href', url);
    link.setAttribute('download', `Reporte_Auditoria_${tipo}_${fecha}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function imprimirReporteAuditoria() {
    // Ocultar gráficas temporalmente
    const graficas = document.getElementById('graficasReporte');
    if (graficas) {
        graficas.style.display = 'none';
    }
    
    window.print();
    
    // Restaurar gráficas después de imprimir
    setTimeout(() => {
        if (graficas) {
            graficas.style.display = '';
        }
    }, 100);
}

function formatDateAud(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatDateTimeAud(dateTimeStr) {
    if (!dateTimeStr) return '-';
    const date = new Date(dateTimeStr);
    return date.toLocaleDateString('es-MX', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<style>
@media print {
    /* Ocultar elementos de navegación y botones */
    #sidebar, 
    .top-header, 
    .btn, 
    .card-header .btn,
    #collapseReportePersonalizado:not(.show) {
        display: none !important;
    }
    
    /* Ocultar gráficas durante impresión */
    #graficasReporte {
        display: none !important;
    }
    
    /* Mostrar contenido colapsado si está activo */
    .collapse.show {
        display: block !important;
        height: auto !important;
    }
    
    /* Estilos para el contenedor principal */
    body {
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .main-content {
        margin: 0 !important;
        padding: 15px !important;
        width: 100% !important;
    }
    
    /* Estilos para tarjetas */
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        page-break-inside: avoid;
        margin-bottom: 15px !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 2px solid #dee2e6 !important;
        padding: 10px 15px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .card-body {
        padding: 15px !important;
    }
    
    /* Estilos para tablas */
    table {
        width: 100% !important;
        font-size: 9pt !important;
        border-collapse: collapse !important;
    }
    
    table thead {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    table th, 
    table td {
        padding: 6px 8px !important;
        border: 1px solid #dee2e6 !important;
        text-align: left !important;
    }
    
    table th {
        font-weight: bold !important;
        font-size: 9pt !important;
    }
    
    /* Estilos para badges */
    .badge {
        border: 1px solid #000 !important;
        padding: 2px 6px !important;
        font-size: 8pt !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .badge.bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
    }
    
    .badge.bg-success {
        background-color: #198754 !important;
        color: white !important;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }
    
    .badge.bg-info {
        background-color: #0dcaf0 !important;
        color: #000 !important;
    }
    
    /* Encabezados */
    h2, h3, h4, h5 {
        page-break-after: avoid;
        margin-top: 10px !important;
        margin-bottom: 8px !important;
    }
    
    h2 {
        font-size: 16pt !important;
    }
    
    h3 {
        font-size: 14pt !important;
    }
    
    h5 {
        font-size: 11pt !important;
    }
    
    /* Evitar saltos de página dentro de elementos */
    tr, 
    .alert,
    .card {
        page-break-inside: avoid;
    }
    
    /* Márgenes de página */
    @page {
        margin: 1.5cm;
    }
}
</style>
