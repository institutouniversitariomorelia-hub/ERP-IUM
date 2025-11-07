<?php
// views/ingresos_list.php (VISTA SIMPLIFICADA)
// Llamada por IngresoController->index()
// Variables disponibles: $pageTitle, $activeModule, $ingresos, $currentUser (del layout)
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle ?? 'Ingresos'); ?>: Historial Reciente</h3>
    <?php if (roleCan('add','ingresos')): ?>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalIngreso" id="btnNuevoIngreso">
            <ion-icon name="add-circle-outline" class="me-1"></ion-icon> Agregar Ingreso
        </button>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            
            <table class="table table-hover table-striped table-sm mb-0">
                <thead>
                    <tr class="table-light">
                        <th class="d-none d-md-table-cell">ID</th>
                        <th>Alumno</th>
                        <th class="d-none d-md-table-cell">Matrícula</th>
                        <th class="d-none d-md-table-cell">Nivel</th>
                        <th class="d-none d-md-table-cell">Programa</th>
                        <th class="d-none d-md-table-cell">Grado</th>
                        <th class="d-none d-md-table-cell">Grupo</th>
                        <th>Concepto</th>
                        <th class="d-none d-md-table-cell">Método Pago</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaIngresos">
                    <?php if (empty($ingresos)): ?>
                        
                        <tr><td colspan="11" class="text-center p-4 text-muted">No hay ingresos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ingresos as $ingreso):
                            $monto = isset($ingreso['monto']) ? (float)$ingreso['monto'] : 0.0;
                            $montoFormateado = '$ ' . number_format($monto, 2);
                            if (class_exists('NumberFormatter')) {
                                try {
                                    $fmt = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
                                    $montoFormateado = $fmt->formatCurrency($monto, 'MXN');
                                } catch (Exception $e) { /* fallback */ }
                            }
                        ?>
                            <tr>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['alumno'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['matricula'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['nivel'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['programa'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['grado'] ?? '-'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['grupo'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['concepto'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['metodo_de_pago'] ?? 'N/A'); ?></td>
                                <td class="text-end text-success fw-bold"><?php echo $montoFormateado; ?></td>
                                <td class="text-center">
                                    <div class="btn-responsive-sm">
                                            <?php if (roleCan('edit','ingresos')): ?>
                                                <button class="btn btn-sm btn-warning btn-edit-ingreso"
                                                        data-id="<?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 0); ?>"
                                                        data-bs-toggle="modal" data-bs-target="#modalIngreso"
                                                        title="Editar Ingreso">
                                                     <ion-icon name="create-outline"></ion-icon>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (roleCan('delete','ingresos')): ?>
                                                <button class="btn btn-sm btn-danger btn-del-ingreso"
                                                        data-id="<?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 0); ?>"
                                                        title="Eliminar Ingreso">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (roleCan('view','ingresos')): ?>
                                                <a class="btn btn-sm btn-primary ms-1" href="<?php echo 'generate_receipt_ingreso.php?folio=' . urlencode($ingreso['folio_ingreso'] ?? $ingreso['id'] ?? 0); ?>" target="_blank" title="Generar Recibo">
                                                    <ion-icon name="document-text-outline"></ion-icon>
                                                </a>
                                            <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>