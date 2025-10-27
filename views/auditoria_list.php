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
                            <option value="<?php echo htmlspecialchars($user['username']); ?>" <?php echo ($filtrosActuales['usuario'] ?? '') === $user['username'] ? 'selected' : ''; ?>>
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
                <div class="col-md-2">
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                         <ion-icon name="filter-outline"></ion-icon> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm mb-4">
     <div class="card-header">Historial Global</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" style="font-size: 0.9em;">
                <thead><tr class="table-light"><th>Fecha</th><th>Usuario</th><th>Sección</th><th>Acción</th><th>Detalles</th></tr></thead>
                <tbody id="tablaAuditoria">
                    <?php if (empty($auditoriaLogs)): ?>
                        <tr><td colspan="5" class="text-center p-4 text-muted">No se encontraron registros con los filtros aplicados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($auditoriaLogs as $log): 
                            $fecha = new DateTime($log['fecha']);
                        ?>
                            <tr>
                                <td><?php echo $fecha->format('d/m/Y, g:i:s a'); ?></td>
                                <td><?php echo htmlspecialchars($log['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($log['seccion']); ?></td>
                                <td><?php echo htmlspecialchars($log['accion']); ?></td>
                                <td><?php echo htmlspecialchars($log['detalles']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">Historial de Cambios (solo actualizaciones)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" style="font-size: 0.9em;">
                <thead><tr class="table-light"><th>Fecha</th><th>Usuario</th><th>Sección</th><th>Detalles</th></tr></thead>
                <tbody id="tablaCambios">
                    <?php 
                        $cambiosEncontrados = false;
                        if (!empty($auditoriaLogs)) {
                            foreach ($auditoriaLogs as $log) {
                                if (stripos($log['accion'], 'actualización') !== false || stripos($log['accion'], 'update') !== false) {
                                    $fecha = new DateTime($log['fecha']);
                                    echo "<tr>";
                                    echo "<td>" . $fecha->format('d/m/Y, g:i:s a') . "</td>";
                                    echo "<td>" . htmlspecialchars($log['usuario']) . "</td>";
                                    echo "<td>" . htmlspecialchars($log['seccion']) . "</td>";
                                    echo "<td>" . htmlspecialchars($log['detalles']) . "</td>";
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