<?php
// views/egresos_list.php (MOSTRANDO TODOS LOS CAMPOS Y NOMBRE CATEGORÍA v2)
// Llamada por EgresoController->index()
// Variables disponibles: $pageTitle, $activeModule, $egresos, $currentUser (del layout)
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle ?? 'Egresos'); ?>: Historial Reciente</h3>
    <?php if (roleCan('add','egresos')): ?>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalEgreso" id="btnNuevoEgreso">
            <ion-icon name="add-circle-outline" class="me-1"></ion-icon> Agregar Egreso
        </button>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            
            <table class="table table-hover table-striped table-sm mb-0">
                <thead>
                    <tr class="table-light">
                        <th>Fecha</th>
                        <th class="d-none d-md-table-cell">ID</th>
                        <th>Destinatario</th>
                        <th>Categoría</th>
                        <th class="d-none d-md-table-cell">Proveedor</th>
                        <th class="d-none d-md-table-cell">Descripción</th>
                        <th>Forma Pago</th>
                        <th class="d-none d-md-table-cell">Doc. Amparo</th>
                        <th class="d-none d-md-table-cell">Activo Fijo</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaEgresos">
                    <?php if (empty($egresos)): ?>
                        {/* Ajustar colspan al número total de columnas (11) */}
                        <tr><td colspan="11" class="text-center p-4 text-muted">No hay egresos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($egresos as $egreso):
                            $monto = $egreso['monto'] ?? 0;
                            $montoFormateado = number_format($monto, 2);
                            try {
                                if (class_exists('NumberFormatter')) {
                                     $formatter = new NumberFormatter('es-MX', NumberFormatter::CURRENCY);
                                     if (is_numeric($monto)) {
                                         $montoFormateado = $formatter->formatCurrency($monto, 'MXN');
                                     }
                                }
                            } catch (Exception $e) { /* Ignorar */ }

                            // Preparar descripción corta con tooltip para la completa
                            $descripcionCompleta = htmlspecialchars($egreso['descripcion'] ?? '');
                            $descripcionCorta = mb_strimwidth($descripcionCompleta, 0, 25, '...'); // Acortar a 25 caracteres
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($egreso['fecha'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($egreso['folio_egreso'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($egreso['destinatario'] ?? 'N/A'); ?></td>
                                
                                <td><?php echo htmlspecialchars($egreso['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($egreso['proveedor'] ?? '-'); ?></td>
                                <td class="d-none d-md-table-cell" title="<?php echo $descripcionCompleta; ?>"> 
                                    <?php echo $descripcionCorta ?: '-'; ?> 
                                </td>
                                <td><?php echo htmlspecialchars($egreso['forma_pago'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($egreso['documento_de_amparo'] ?? '-'); ?></td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge <?php echo ($egreso['activo_fijo'] ?? 'NO') === 'SI' ? 'bg-info text-dark' : 'bg-light text-secondary'; ?>">
                                        <?php echo htmlspecialchars($egreso['activo_fijo'] ?? 'NO'); ?>
                                    </span>
                                </td>
                                <td class="text-end text-danger fw-bold"><?php echo $montoFormateado; ?></td>
                                <td class="text-center">
                                    <div class="btn-responsive-sm">
                                        <?php if (roleCan('edit','egresos')): ?>
                                            <button class="btn btn-sm btn-warning btn-edit-egreso"
                                                    data-id="<?php echo htmlspecialchars($egreso['folio_egreso'] ?? 0); ?>"
                                                    data-bs-toggle="modal" data-bs-target="#modalEgreso"
                                                    title="Editar Egreso">
                                                 <ion-icon name="create-outline"></ion-icon>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (roleCan('delete','egresos')): ?>
                                            <button class="btn btn-sm btn-danger btn-del-egreso"
                                                    data-id="<?php echo htmlspecialchars($egreso['folio_egreso'] ?? 0); ?>"
                                                    title="Eliminar Egreso">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
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