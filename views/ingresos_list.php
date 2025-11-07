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
                        <th>Fecha</th>
                        <th>Alumno</th>
                        <th>Categoría</th>
                        <th>Mes Corresp.</th>
                        <th>Año</th>
                        <th>Método Pago</th>
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
                                <td><?php echo htmlspecialchars($ingreso['fecha'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['alumno'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php
                                    // Definir $nombreCategoria igual que en el modal
                                    if (!empty($categorias) && isset($ingreso['id_categoria'])) {
                                        $nombreCategoria = obtenerNombreCategoria($ingreso['id_categoria'], $categorias);
                                        echo htmlspecialchars($nombreCategoria);
                                    } else {
                                        echo htmlspecialchars($ingreso['id_categoria'] ?? 'N/A');
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($ingreso['mes_correspondiente'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['anio'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['metodo_de_pago'] ?? 'N/A'); ?></td>
                                <td class="text-end text-success fw-bold"><?php echo $montoFormateado; ?></td>
                                <td class="text-center align-middle" style="vertical-align:middle; height:56px;">
                                    <div class="btn-responsive-sm d-flex justify-content-center align-items-center" style="gap:0.5rem; height:100%;">
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
                                        <!-- Botón de ojo para ver detalles -->
                                        <button class="btn btn-sm btn-info btn-view-ingreso" data-bs-toggle="modal" data-bs-target="#modalDetalleIngreso<?php echo $ingreso['folio_ingreso']; ?>" title="Ver detalles" style="background-color:#17a2b8; border:none;">
                                            <ion-icon name="eye-outline" style="font-size:1.2em;"></ion-icon>
                                        </button>
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
<?php if (!empty($ingresos)): ?>
        <?php foreach ($ingresos as $ingreso): ?>
                <?php
                        $monto = isset($ingreso['monto']) ? (float)$ingreso['monto'] : 0.0;
                        $montoFormateado = '$ ' . number_format($monto, 2);
                        if (class_exists('NumberFormatter')) {
                                try {
                                        $fmt = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
                                        $montoFormateado = $fmt->formatCurrency($monto, 'MXN');
                                } catch (Exception $e) { /* fallback */ }
                        }
                        if (!empty($categorias) && isset($ingreso['id_categoria'])) {
                                $nombreCategoria = obtenerNombreCategoria($ingreso['id_categoria'], $categorias);
                                $nombreCategoria = htmlspecialchars($nombreCategoria);
                        } else {
                                $nombreCategoria = htmlspecialchars($ingreso['id_categoria'] ?? 'N/A');
                        }
                ?>
                <!-- Modal de detalles -->
                <div class="modal fade" id="modalDetalleIngreso<?php echo $ingreso['folio_ingreso']; ?>" tabindex="-1" aria-labelledby="modalDetalleIngresoLabel<?php echo $ingreso['folio_ingreso']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 12px;">
                              <div class="modal-header" style="background-color:#B80000; color:#fff; border-radius: 12px 12px 0 0;">
                                <h5 class="modal-title" id="modalDetalleIngresoLabel<?php echo $ingreso['folio_ingreso']; ?>">
                                    <ion-icon name="eye-outline" style="vertical-align:middle; font-size:1.3em; margin-right:6px;"></ion-icon>
                                    Detalle del Ingreso
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="background:#f8f9fa;">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" style="background:white; border-radius:8px;">
                                        <tbody>
                                            <tr><th>Fecha</th><td><?php echo htmlspecialchars($ingreso['fecha'] ?? 'N/A'); ?></td></tr>
                                            <tr><th>Folio</th><td><?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 'N/A'); ?></td></tr>
                                            <tr><th>Alumno</th><td><?php echo htmlspecialchars($ingreso['alumno'] ?? 'N/A'); ?></td></tr>
                                            <tr><th>Matrícula</th><td><?php echo htmlspecialchars($ingreso['matricula'] ?? 'N/A'); ?></td></tr>
                                            <tr><th>Nivel</th><td><?php echo htmlspecialchars($ingreso['nivel'] ?? 'N/A'); ?></td></tr>
                                            <tr><th>Programa</th><td><?php echo htmlspecialchars($ingreso['programa'] ?? 'N/A'); ?></td></tr>
                                            <tr><th>Grado</th><td><?php echo htmlspecialchars($ingreso['grado'] ?? '-'); ?></td></tr>
                                            <tr><th>Grupo</th><td><?php echo htmlspecialchars($ingreso['grupo'] ?? '-'); ?></td></tr>
                                            <tr><th>Modalidad</th><td><?php echo (!empty($ingreso['modalidad']) && $ingreso['modalidad'] !== 'N/A' && $ingreso['modalidad'] !== '-') ? htmlspecialchars($ingreso['modalidad']) : 'N/A'; ?></td></tr>
                                            <tr><th>Categoría</th><td><?php echo $nombreCategoria; ?></td></tr>
                                            <tr><th>Concepto</th><td><?php echo htmlspecialchars($ingreso['concepto'] ?? 'N/A'); ?></td></tr>
                                            <tr><th>Mes Corresp.</th><td><?php echo htmlspecialchars($ingreso['mes_correspondiente'] ?? '-'); ?></td></tr>
                                            <tr><th>Año</th><td><?php echo htmlspecialchars($ingreso['anio'] ?? 'N/A'); ?></td></tr>
                                            <tr><th>Método Pago</th><td><?php echo htmlspecialchars($ingreso['metodo_de_pago'] ?? 'N/A'); ?></td></tr>
                                            <tr><th>Día Pago</th><td><?php echo htmlspecialchars($ingreso['dia_pago'] ?? '-'); ?></td></tr>
                                            <tr><th>Observaciones</th><td><?php echo (!empty($ingreso['observaciones']) && $ingreso['observaciones'] !== 'N/A' && $ingreso['observaciones'] !== '-') ? htmlspecialchars($ingreso['observaciones']) : 'N/A'; ?></td></tr>
                                            <tr><th>Monto</th><td class="fw-bold text-success"><?php echo $montoFormateado; ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>