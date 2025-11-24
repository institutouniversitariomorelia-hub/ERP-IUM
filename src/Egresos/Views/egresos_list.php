<?php
// views/egresos_list.php (MOSTRANDO TODOS LOS CAMPOS Y NOMBRE CATEGORÍA v2)
// Llamada por EgresoController->index()
// Variables disponibles: $pageTitle, $activeModule, $egresos, $currentUser (del layout)
?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle ?? 'Egresos'); ?>: Historial Reciente</h3>
    <div class="d-flex gap-2 w-100 w-md-auto">
        <button class="btn btn-outline-danger btn-sm flex-grow-1 flex-md-grow-0" id="btnVerGraficasEgresos">
            <ion-icon name="pie-chart-outline" style="vertical-align: middle;"></ion-icon> 
            <span class="d-none d-sm-inline">Ver Gráficas</span>
            <span class="d-inline d-sm-none">Gráficas</span>
        </button>
        <?php if (roleCan('add','egresos')): ?>
            <button class="btn btn-danger btn-sm flex-grow-1 flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modalEgreso" id="btnNuevoEgreso">
                <ion-icon name="add-circle-outline" class="me-1"></ion-icon> 
                <span class="d-none d-sm-inline">Agregar Egreso</span>
                <span class="d-inline d-sm-none">Agregar</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Buscador de Egresos -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <ion-icon name="search-outline" style="font-size: 1.2em; color: #B80000;"></ion-icon>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" id="searchEgresos" placeholder="Buscar por folio o destinatario...">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearchEgresos" style="display:none;">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <ion-icon name="calendar-outline" style="font-size: 1.2em; color: #B80000;"></ion-icon>
                    </span>
                    <input type="date" class="form-control" id="fechaInicioEgresos" placeholder="Fecha inicio">
                    <input type="date" class="form-control" id="fechaFinEgresos" placeholder="Fecha fin">
                    <button class="btn btn-outline-secondary" type="button" id="clearDateEgresos" style="display:none;">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
            </div>
        </div>
        <small class="text-muted d-block mt-1">
            <span id="resultCountEgresos"></span>
        </small>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            
            <table class="table table-hover table-striped table-sm mb-0" style="width:100%; font-family:'Segoe UI',Arial,sans-serif; font-size:1rem;">
                <thead>
                    <tr class="table-light">
                        <th>Fecha</th>
                        <th class="d-none d-lg-table-cell">Categoría</th>
                        <th>Destinatario</th>
                        <th class="d-none d-md-table-cell">Forma de Pago</th>
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
                            <tr data-fecha="<?php echo htmlspecialchars($egreso['fecha'] ?? ''); ?>">
                                <td><?php echo htmlspecialchars($egreso['fecha'] ?? 'N/A'); ?></td>
                                <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($egreso['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($egreso['destinatario'] ?? 'N/A'); ?>
                                    <br class="d-md-none">
                                    <small class="d-md-none text-muted"><?php echo htmlspecialchars($egreso['forma_pago'] ?? 'N/A'); ?></small>
                                </td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($egreso['forma_pago'] ?? 'N/A'); ?></td>
                                <td class="text-end text-danger fw-bold"><?php echo $montoFormateado; ?></td>
                <td class="text-center align-middle" style="vertical-align:middle;">
                  <div class="d-flex flex-column flex-sm-row gap-1 justify-content-center align-items-center">
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
                                            <a class="btn btn-sm btn-primary ms-1" href="<?php echo 'generate_receipt.php?folio=' . urlencode($egreso['folio_egreso'] ?? 0) . '&tipo=egreso'; ?>" target="_blank" title="Imprimir Recibo">
                                                <ion-icon name="print-outline"></ion-icon>
                                            </a>
                                            <a class="btn btn-sm btn-secondary ms-1" href="<?php echo 'generate_receipt.php?folio=' . urlencode($egreso['folio_egreso'] ?? 0) . '&tipo=egreso&reimpresion=1'; ?>" target="_blank" title="Reimprimir">
                                                <ion-icon name="document-attach-outline"></ion-icon>
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

<!-- Modal de Gráficas de Egresos -->
<div class="modal fade" id="modalGraficasEgresos" tabindex="-1" aria-labelledby="modalGraficasEgresosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #dc3545; color: white;">
                <h5 class="modal-title" id="modalGraficasEgresosLabel">
                    <ion-icon name="analytics-outline" style="vertical-align: middle; font-size: 1.5rem;"></ion-icon>
                    Análisis de Egresos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background-color: #f8f9fa;">
                <div class="row">
                    <!-- Gráfica de Pastel: Distribución por Categoría -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">
                                    <ion-icon name="pie-chart-outline" style="vertical-align: middle; color: #dc3545;"></ion-icon>
                                    Distribución por Categoría
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartEgresosCategoria" style="max-height: 350px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfica de Barras: Egresos por Mes -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">
                                    <ion-icon name="bar-chart-outline" style="vertical-align: middle; color: #dc3545;"></ion-icon>
                                    Egresos Mensuales (Últimos 6 Meses)
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartEgresosMes" style="max-height: 350px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales para las gráficas de egresos
let chartEgresosCategoriaInstance = null;
let chartEgresosMesInstance = null;

// Usar vanilla JavaScript para el evento inicial (antes de que jQuery se cargue)
(function() {
    // Esperar a que el DOM esté listo Y jQuery esté cargado
    function initEgresosGraficas() {
        if (typeof jQuery === 'undefined') {
            console.log('jQuery aún no cargado para egresos, esperando...');
            setTimeout(initEgresosGraficas, 100);
            return;
        }
        
        console.log('Script de egresos inicializado con jQuery');
        
        // Evento para abrir el modal y cargar las gráficas
        jQuery('#btnVerGraficasEgresos').on('click', function() {
            console.log('Botón Ver Gráficas Egresos clickeado');
            jQuery('#modalGraficasEgresos').modal('show');
            cargarGraficasEgresos();
        });

        // Limpiar gráficas al cerrar el modal
        jQuery('#modalGraficasEgresos').on('hidden.bs.modal', function() {
            if (chartEgresosCategoriaInstance) {
                chartEgresosCategoriaInstance.destroy();
                chartEgresosCategoriaInstance = null;
            }
            if (chartEgresosMesInstance) {
                chartEgresosMesInstance.destroy();
                chartEgresosMesInstance = null;
            }
        });
    }
    
    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEgresosGraficas);
    } else {
        initEgresosGraficas();
    }
})();

function cargarGraficasEgresos() {
    console.log('Cargando gráficas de egresos...');
    // Cargar gráfica de categorías
    ajaxCall('egreso', 'getGraficaEgresosPorCategoria', {}, 'GET')
        .done(function(data) {
            if (data.success && data.categorias.length > 0) {
                if (chartEgresosCategoriaInstance) {
                    chartEgresosCategoriaInstance.destroy();
                }

                const ctx = document.getElementById('chartEgresosCategoria').getContext('2d');
                chartEgresosCategoriaInstance = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.categorias,
                        datasets: [{
                            data: data.montos,
                            backgroundColor: [
                                'rgba(220, 53, 69, 0.8)',
                                'rgba(253, 126, 20, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(108, 117, 125, 0.8)',
                                'rgba(111, 66, 193, 0.8)',
                                'rgba(220, 53, 140, 0.8)'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'right',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += new Intl.NumberFormat('es-MX', {
                                            style: 'currency',
                                            currency: 'MXN'
                                        }).format(context.parsed);
                                        
                                        // Calcular porcentaje
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        label += ` (${percentage}%)`;
                                        
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('chartEgresosCategoria').parentElement.innerHTML = 
                    '<p class="text-center text-muted py-5">No hay datos de egresos por categoría</p>';
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar gráfica egresos por categoría:', xhr);
        });

    // Cargar gráfica de meses
    ajaxCall('egreso', 'getGraficaEgresosPorMes', {}, 'GET')
        .done(function(data) {
            if (data.success && data.meses.length > 0) {
                if (chartEgresosMesInstance) {
                    chartEgresosMesInstance.destroy();
                }

                const ctx = document.getElementById('chartEgresosMes').getContext('2d');
                chartEgresosMesInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.meses,
                        datasets: [{
                            label: 'Egresos',
                            data: data.montos,
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Egresos: ' + new Intl.NumberFormat('es-MX', {
                                            style: 'currency',
                                            currency: 'MXN'
                                        }).format(context.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString('es-MX');
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('chartEgresosMes').parentElement.innerHTML = 
                    '<p class="text-center text-muted py-5">No hay datos de egresos mensuales</p>';
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar gráfica egresos por mes:', xhr);
        });
}
</script>