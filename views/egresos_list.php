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
            
            <table class="table table-hover table-striped table-sm mb-0" style="width:100%; font-family:'Segoe UI',Arial,sans-serif; font-size:1rem;">
                <thead>
                    <tr class="table-light">
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Destinatario</th>
                        <th>Forma de Pago</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaEgresos">
                    <?php if (empty($egresos)): ?>
                        <!-- Ajustar colspan al número total de columnas (6) -->
                        <tr><td colspan="6" class="text-center p-4 text-muted">No hay egresos registrados.</td></tr>
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
                                <td><?php echo htmlspecialchars($egreso['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                                <td><?php echo htmlspecialchars($egreso['destinatario'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($egreso['forma_pago'] ?? 'N/A'); ?></td>
                                <td class="text-end text-danger fw-bold"><?php echo $montoFormateado; ?></td>
                <td class="text-center align-middle" style="vertical-align:middle;">
                  <div class="btn-responsive-sm justify-content-center align-items-center" style="gap:0.5rem; display:flex;">
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
                                        <?php if (roleCan('view','egresos')): ?>
                                            <a class="btn btn-sm btn-primary ms-1" href="<?php echo 'generate_receipt_egreso.php?folio=' . urlencode($egreso['folio_egreso'] ?? $egreso['id'] ?? 0); ?>" target="_blank" title="Generar Recibo">
                                                <ion-icon name="document-text-outline"></ion-icon>
                                            </a>
                                        <?php endif; ?>
                                        <!-- Botón de ojo para ver detalles -->
                                        <button class="btn btn-sm btn-info btn-view-egreso" data-bs-toggle="modal" data-bs-target="#modalDetalleEgreso<?php echo $egreso['folio_egreso']; ?>" title="Ver detalles" style="background-color:#17a2b8; border:none;">
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
<?php if (!empty($egresos)): ?>
    <?php foreach ($egresos as $egreso): ?>
        <?php
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
        ?>
        <!-- Modal de detalles -->
        <div class="modal fade" id="modalDetalleEgreso<?php echo $egreso['folio_egreso']; ?>" tabindex="-1" aria-labelledby="modalDetalleEgresoLabel<?php echo $egreso['folio_egreso']; ?>" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px;">
              <div class="modal-header" style="background-color:#B80000; color:#fff; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="modalDetalleEgresoLabel<?php echo $egreso['folio_egreso']; ?>">
                  <ion-icon name="eye-outline" style="vertical-align:middle; font-size:1.3em; margin-right:6px;"></ion-icon>
                  Detalle del Egreso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body" style="background:#f8f9fa;">
                <div class="table-responsive">
                  <table class="table table-bordered mb-0" style="background:white; border-radius:8px;">
                    <tbody>
                      <tr>
                        <th style="width:40%;">Fecha</th>
                        <td><?php echo htmlspecialchars($egreso['fecha'] ?? 'N/A'); ?></td>
                      </tr>
                      <tr>
                        <th>Folio</th>
                        <td><?php echo htmlspecialchars($egreso['folio_egreso'] ?? 'N/A'); ?></td>
                      </tr>
                      <tr>
                        <th>Categoría</th>
                        <td><?php echo htmlspecialchars($egreso['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                      </tr>
                      <tr>
                        <th>Destinatario</th>
                        <td><?php echo htmlspecialchars($egreso['destinatario'] ?? 'N/A'); ?></td>
                      </tr>
                      <tr>
                        <th>Proveedor</th>
                        <td><?php echo htmlspecialchars($egreso['proveedor'] ?? '-'); ?></td>
                      </tr>
                      <tr>
                        <th>Descripción</th>
                        <td><?php echo htmlspecialchars($egreso['descripcion'] ?? '-'); ?></td>
                      </tr>
                      <tr>
                        <th>Forma de Pago</th>
                        <td><?php echo htmlspecialchars($egreso['forma_pago'] ?? 'N/A'); ?></td>
                      </tr>
                      <tr>
                        <th>Documento de Amparo</th>
                        <td><?php echo htmlspecialchars($egreso['documento_de_amparo'] ?? '-'); ?></td>
                      </tr>
                      <tr>
                        <th>Activo Fijo</th>
                        <td><?php echo htmlspecialchars($egreso['activo_fijo'] ?? 'NO'); ?></td>
                      </tr>
                      <tr>
                        <th>Monto</th>
                        <td class="fw-bold text-danger"><?php echo $montoFormateado; ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
  <?php endforeach; ?>
<?php endif; ?>