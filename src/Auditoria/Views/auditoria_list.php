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
                        <option value="Registro" <?php echo ($filtrosActuales['accion_tipo'] ?? '') === 'Registro' ? 'selected' : ''; ?>>
                            Registro
                        </option>
                        <option value="Actualización" <?php echo ($filtrosActuales['accion_tipo'] ?? '') === 'Actualización' ? 'selected' : ''; ?>>
                            Actualización
                        </option>
                        <option value="Eliminación" <?php echo ($filtrosActuales['accion_tipo'] ?? '') === 'Eliminación' ? 'selected' : ''; ?>>
                            Eliminación
                        </option>
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
                <div class="col-md-2">
                    <a href="<?php echo BASE_URL; ?>index.php?controller=auditoria&action=index" class="btn btn-outline-secondary btn-sm w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
            <!-- Segunda fila de filtros: (Acción y búsqueda libre) eliminadas por requerimiento -->
            <!-- Paginación y tamaño de página (oculto, se puede ajustar) -->
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="pageSize" value="<?php echo (int)($filtrosActuales['pageSize'] ?? 10); ?>">
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
        <!-- Reporte semanal (últimos 7 días) -->
        <button type="button"
            class="btn btn-primary w-100 mb-2"
            onclick="auditoriaVista_generarReporte('semanal')">
            Reporte Semanal
        </button>
        <small class="text-light">Últimos 7 días</small>

        <!-- Reporte personalizado (abre modal de fechas) -->
        <button type="button"
                class="btn btn-primary w-100 mb-2"
                data-bs-toggle="modal"
                data-bs-target="#modalReportePersonalizado">
            Reporte Personalizado
        </button>
        <small class="text-light">Seleccionar rango de fechas</small>

        <!-- Formulario de reporte personalizado (colapsable) -->
        <div class="collapse mt-3" id="collapseReportePersonalizado">
            <div class="card card-body bg-light">
                <form id="formReporteAuditoriaPersonalizado" onsubmit="auditoriaVista_generarReportePersonalizado(event)">
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
                <button class="btn btn-light btn-sm me-2" onclick="auditoriaVista_imprimir()">
                    <i class="bi bi-printer me-1"></i>Imprimir
                </button>
                <button class="btn btn-light btn-sm" onclick="auditoriaVista_cerrar()">
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

<!-- Modal para reporte personalizado -->
<div class="modal fade" id="modalReportePersonalizado" tabindex="-1" aria-labelledby="modalReportePersonalizadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form onsubmit="auditoriaVista_generarReportePersonalizado(event)">
        <div class="modal-header">
          <h5 class="modal-title" id="modalReportePersonalizadoLabel">Reporte Personalizado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="rep_fecha_inicio" class="form-label">Fecha inicio</label>
            <input type="date" class="form-control" id="rep_fecha_inicio" name="fecha_inicio" required>
          </div>
          <div class="mb-3">
            <label for="rep_fecha_fin" class="form-label">Fecha fin</label>
            <input type="date" class="form-control" id="rep_fecha_fin" name="fecha_fin" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Generar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// ========== FUNCIONES DE REPORTES DE AUDITORÍA ==========
console.log('[Auditoría] Script de reportes cargado');

let chartAuditoriaPorSeccion = null;
let chartAuditoriaPorAccion = null;
let chartAuditoriaPorUsuario = null;
let datosReporteAuditoria = null;

// Función de notificación simple
function showNotification(message, type = 'info') {
    try {
        if (window && typeof window.showNotification === 'function' && window.showNotification !== showNotification) {
            window.showNotification(message, type);
            return;
        }
        if ((type === 'danger' || type === 'error') && window && typeof window.showError === 'function') {
            window.showError(message);
            return;
        }
        if (type === 'success' && window && typeof window.showSuccess === 'function') {
            window.showSuccess(message);
            return;
        }
    } catch (e) {
        console.warn('No se pudo delegar la notificación:', e);
    }
    if (window && typeof window.showError === 'function') {
        window.showError(message);
    } else {
        console.warn('Notificación:', message);
    }
}

function auditoriaVista_generarReporte(tipo) {
    console.log('[Auditoría] generarReporteAuditoria llamada con tipo:', tipo);
    const url = `<?php echo BASE_URL; ?>index.php?controller=auditoria&action=generarReporte&tipo=${tipo}`;
    console.log('[Auditoría] URL:', url);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Guardar el tipo y las fechas para las funciones de exportar/imprimir
                data.tipo = tipo;
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

function auditoriaVista_generarReportePersonalizado(event) {
    event.preventDefault();
    // Leer las fechas desde el modal principal de reporte personalizado
    const fechaInicio = document.getElementById('rep_fecha_inicio').value;
    const fechaFin = document.getElementById('rep_fecha_fin').value;

    if (!fechaInicio || !fechaFin) {
        showNotification('Selecciona ambas fechas para generar el reporte', 'warning');
        return;
    }

    if (new Date(fechaInicio) > new Date(fechaFin)) {
        showNotification('La fecha de inicio no puede ser mayor a la fecha fin', 'warning');
        return;
    }

    const url = `<?php echo BASE_URL; ?>index.php?controller=auditoria&action=generarReporte&tipo=personalizado&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Guardar el tipo y las fechas para las funciones de exportar/imprimir
                data.tipo = 'personalizado';
                data.fechaInicio = fechaInicio;
                data.fechaFin = fechaFin;
                datosReporteAuditoria = data;
                mostrarReporteAuditoria(data);

                // Cerrar el modal de reporte personalizado
                const modalEl = document.getElementById('modalReportePersonalizado');
                if (modalEl && window.bootstrap) {
                    const modalInstance = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                    modalInstance.hide();
                }
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
    
    if (data.movimientos && data.movimientos.length > 0) {
        data.movimientos.forEach(log => {
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

function auditoriaVista_cerrar() {
    document.getElementById('resultadoReporteAuditoriaContainer').style.display = 'none';
}

function auditoriaVista_exportarExcel() {
    if (!datosReporteAuditoria || !datosReporteAuditoria.movimientos) {
        showNotification('No hay datos de reporte para exportar', 'danger');
        return;
    }

    // Usar archivo dedicado para exportar
    const tipo = datosReporteAuditoria.tipo || 'personalizado';
    const fechaInicio = datosReporteAuditoria.fechaInicio || '';
    const fechaFin = datosReporteAuditoria.fechaFin || '';

    const url = `<?php echo BASE_URL; ?>src/Reportes/Generators/reporte_auditoria.php?tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&formato=excel`;
    window.location.href = url;
}

function auditoriaVista_imprimir() {
    if (!datosReporteAuditoria || !datosReporteAuditoria.movimientos) {
        showNotification('No hay datos de reporte para imprimir', 'danger');
        return;
    }
    
    // Abrir ventana nueva con vista de impresión
    const tipo = datosReporteAuditoria.tipo || 'personalizado';
    const fechaInicio = datosReporteAuditoria.fechaInicio || '';
    const fechaFin = datosReporteAuditoria.fechaFin || '';

    const url = `<?php echo BASE_URL; ?>src/Reportes/Generators/reporte_auditoria.php?tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&formato=html`;
    window.open(url, '_blank');
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

// Event listener para abrir modal de detalles al hacer click en filas
document.addEventListener('DOMContentLoaded', function() {
    // Delegación de eventos para filas clickeables
    document.body.addEventListener('click', function(e) {
        const row = e.target.closest('.clickable-row');
        if (row) {
            const auditoriaId = row.getAttribute('data-id');
            if (auditoriaId) {
                abrirModalDetalleAuditoria(auditoriaId);
            }
        }
    });
});

// Función helper para formatear valores bonitos
function formatearValorBonito(key, value, allData = null) {
    if (value === null || value === undefined || value === '') return '<span class="text-muted">(vacío)</span>';
    
    // Formatear id_user mostrando nombre si está disponible
    if (key === 'id_user' && allData) {
        const nombre = allData.nombre || allData.username || null;
        if (nombre) {
            return `<span class="badge bg-primary">${nombre}</span> <small class="text-muted">(ID: ${value})</small>`;
        }
    }
    
    // Parsear arrays JSON (como pagos)
    if (typeof value === 'string' && value.startsWith('[{')) {
        try {
            const arr = JSON.parse(value);
            if (Array.isArray(arr)) {
                let html = '<div class="ms-2">';
                arr.forEach((item, idx) => {
                    html += `<div class="badge bg-secondary me-1 mb-1">`;
                    if (item.metodo) html += `${item.metodo}: `;
                    if (item.monto) html += `$${parseFloat(item.monto).toFixed(2)}`;
                    html += `</div>`;
                });
                html += '</div>';
                return html;
            }
        } catch (e) {}
    }
    
    // Formatear fechas
    if (key.toLowerCase().includes('fecha') && value.match(/^\d{4}-\d{2}-\d{2}/)) {
        const fecha = new Date(value + 'T00:00:00');
        return fecha.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
    }
    
    // Formatear montos
    if ((key.toLowerCase().includes('monto') || key.toLowerCase().includes('precio')) && !isNaN(value)) {
        return `$${parseFloat(value).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }
    
    return value;
}

// Función para abrir el modal con detalles de auditoría
function abrirModalDetalleAuditoria(auditoriaId) {
    fetch(`<?php echo BASE_URL; ?>index.php?controller=auditoria&action=getDetalle&id=${auditoriaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const auditoria = data.data;
                const accion = (auditoria.accion || '').trim();
                
                // Formatear fecha correctamente
                let fechaFormateada = '-';
                if (auditoria.fecha_hora) {
                    try {
                        // Parsear la fecha sin conversión UTC
                        const partes = auditoria.fecha_hora.split(/[- :]/);
                        if (partes.length >= 3) {
                            const fecha = new Date(partes[0], partes[1]-1, partes[2], partes[3]||0, partes[4]||0, partes[5]||0);
                            fechaFormateada = fecha.toLocaleString('es-MX', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            });
                        }
                    } catch (e) {
                        console.error('Error formateando fecha:', e);
                        fechaFormateada = auditoria.fecha_hora;
                    }
                }
                
                // Llenar encabezado
                document.getElementById('aud_det_fecha').textContent = fechaFormateada;
                document.getElementById('aud_det_usuario').textContent = auditoria.usuario || 'Sistema';
                document.getElementById('aud_det_seccion').textContent = auditoria.seccion || '-';
                document.getElementById('aud_det_accion').textContent = accion;
                
                const oldValor = auditoria.old_valor || '';
                const newValor = auditoria.new_valor || '';
                
                console.log('Debug - Acción:', accion, 'oldValor:', oldValor ? 'Existe' : 'Vacío', 'newValor:', newValor ? 'Existe' : 'Vacío');
                
                // ========== ACTUALIZACIÓN (Comparativa) ==========
                if (oldValor && newValor) {
                    document.getElementById('aud_compare_container').style.display = 'block';
                    document.getElementById('aud_det_detalles_container').style.display = 'none';
                    
                    try {
                        const oldJson = JSON.parse(oldValor);
                        const newJson = JSON.parse(newValor);
                        
                        let oldHtml = '<table class="table table-sm table-hover mb-0"><tbody>';
                        let newHtml = '<table class="table table-sm table-hover mb-0"><tbody>';
                        
                        // Ordenar claves: IDs primero, luego resto
                        const priorityKeys = ['id', 'id_user', 'id_categoria', 'id_presupuesto', 'folio_egreso', 'folio_ingreso', 'fecha'];
                        const allKeys = [...new Set([...Object.keys(oldJson), ...Object.keys(newJson)])];
                        const orderedKeys = [
                            ...priorityKeys.filter(k => allKeys.includes(k)),
                            ...allKeys.filter(k => !priorityKeys.includes(k))
                        ];
                        
                        orderedKeys.forEach(key => {
                            const oldVal = oldJson[key];
                            const newVal = newJson[key];
                            const oldFormatted = formatearValorBonito(key, oldVal, oldJson);
                            const newFormatted = formatearValorBonito(key, newVal, newJson);
                            
                            // Detectar cambio
                            const changed = JSON.stringify(oldVal) !== JSON.stringify(newVal);
                            const bgClass = changed ? 'table-warning' : '';
                            const icon = changed ? '🔄' : '';
                            
                            oldHtml += `<tr class="${bgClass}"><td class="text-end pe-2 fw-semibold" style="width:35%">${icon} ${key}:</td><td>${oldFormatted}</td></tr>`;
                            newHtml += `<tr class="${bgClass}"><td class="text-end pe-2 fw-semibold" style="width:35%">${icon} ${key}:</td><td>${newFormatted}</td></tr>`;
                        });
                        
                        oldHtml += '</tbody></table>';
                        newHtml += '</tbody></table>';
                        
                        document.getElementById('aud_old_values').innerHTML = oldHtml;
                        document.getElementById('aud_new_values').innerHTML = newHtml;
                    } catch (e) {
                        console.error('Error parseando JSON:', e);
                        document.getElementById('aud_compare_container').style.display = 'block';
                        document.getElementById('aud_det_detalles_container').style.display = 'none';
                        document.getElementById('aud_old_values').innerHTML = `<pre class="mb-0 small">${oldValor}</pre>`;
                        document.getElementById('aud_new_values').innerHTML = `<pre class="mb-0 small">${newValor}</pre>`;
                    }
                } 
                // ========== INSERCIÓN ==========
                else if (newValor && !oldValor) {
                    document.getElementById('aud_compare_container').style.display = 'none';
                    document.getElementById('aud_det_detalles_container').style.display = 'block';
                    
                    try {
                        const json = JSON.parse(newValor);
                        let html = '<div class="card border-success shadow-sm"><div class="card-header bg-success text-white"><strong>✅ Nuevo Registro Creado</strong></div>';
                        html += '<div class="card-body"><table class="table table-sm table-striped mb-0"><tbody>';
                        
                        const keys = Object.keys(json);
                        const priorityKeys = ['id', 'folio_egreso', 'folio_ingreso', 'id_user', 'id_categoria', 'id_presupuesto', 'fecha', 'nombre'];
                        const orderedKeys = [
                            ...priorityKeys.filter(k => keys.includes(k)),
                            ...keys.filter(k => !priorityKeys.includes(k))
                        ];
                        
                        orderedKeys.forEach(key => {
                            const formatted = formatearValorBonito(key, json[key], json);
                            html += `<tr><td class="text-end fw-semibold text-success" style="width:30%">${key}:</td><td>${formatted}</td></tr>`;
                        });
                        
                        html += '</tbody></table></div></div>';
                        document.getElementById('aud_det_detalles').innerHTML = html;
                    } catch (e) {
                        console.error('Error:', e);
                        document.getElementById('aud_det_detalles').innerHTML = `<div class="alert alert-success"><strong>Nuevo registro</strong><pre class="mb-0 mt-2">${newValor}</pre></div>`;
                    }
                }
                // ========== ELIMINACIÓN ==========
                else if (oldValor && !newValor) {
                    document.getElementById('aud_compare_container').style.display = 'none';
                    document.getElementById('aud_det_detalles_container').style.display = 'block';
                    
                    try {
                        const json = JSON.parse(oldValor);
                        let html = '<div class="card border-danger shadow-sm"><div class="card-header bg-danger text-white"><strong>🗑️ Registro Eliminado</strong></div>';
                        html += '<div class="card-body"><table class="table table-sm table-striped mb-0"><tbody>';
                        
                        const keys = Object.keys(json);
                        const priorityKeys = ['id', 'folio_egreso', 'folio_ingreso', 'id_user', 'id_categoria', 'id_presupuesto', 'fecha', 'nombre'];
                        const orderedKeys = [
                            ...priorityKeys.filter(k => keys.includes(k)),
                            ...keys.filter(k => !priorityKeys.includes(k))
                        ];
                        
                        orderedKeys.forEach(key => {
                            const formatted = formatearValorBonito(key, json[key], json);
                            html += `<tr><td class="text-end fw-semibold text-danger" style="width:30%">${key}:</td><td class="text-decoration-line-through">${formatted}</td></tr>`;
                        });
                        
                        html += '</tbody></table></div></div>';
                        document.getElementById('aud_det_detalles').innerHTML = html;
                    } catch (e) {
                        console.error('Error:', e);
                        document.getElementById('aud_det_detalles').innerHTML = `<div class="alert alert-danger"><strong>Registro eliminado</strong><pre class="mb-0 mt-2">${oldValor}</pre></div>`;
                    }
                }
                // ========== SIN DATOS ==========
                else {
                    document.getElementById('aud_compare_container').style.display = 'none';
                    document.getElementById('aud_det_detalles_container').style.display = 'block';
                    document.getElementById('aud_det_detalles').innerHTML = '<div class="alert alert-warning mb-0"><strong>⚠️ Sin detalles disponibles</strong><br>La información se ha perdido (eliminación en cascada).</div>';
                }
                
                // Datos técnicos JSON
                const rawData = {
                    id: auditoria.id_auditoria,
                    fecha_hora: auditoria.fecha_hora,
                    seccion: auditoria.seccion,
                    accion: auditoria.accion,
                    old_valor: oldValor || null,
                    new_valor: newValor || null,
                    folio_egreso: auditoria.folio_egreso || null,
                    folio_ingreso: auditoria.folio_ingreso || null
                };
                document.getElementById('aud_raw_consulta').textContent = JSON.stringify(rawData, null, 2);
                
                // Abrir modal
                const modal = new bootstrap.Modal(document.getElementById('modalAuditoriaDetalle'));
                modal.show();
            } else {
                showError('Error al cargar detalles: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error al cargar los detalles de auditoría');
        });
}

// Toggle para mostrar/ocultar JSON raw
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('aud_toggle_raw');
    const rawPre = document.getElementById('aud_raw_consulta');
    
    if (toggleBtn && rawPre) {
        toggleBtn.addEventListener('click', function() {
            if (rawPre.style.display === 'none') {
                rawPre.style.display = 'block';
                toggleBtn.innerHTML = '<ion-icon name="code-slash-outline"></ion-icon> Ocultar datos técnicos';
            } else {
                rawPre.style.display = 'none';
                toggleBtn.innerHTML = '<ion-icon name="code-outline"></ion-icon> Ver datos técnicos (JSON)';
            }
        });
    }
});
</script>

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