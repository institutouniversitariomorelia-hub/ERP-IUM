<?php
// views/ingresos_list.php (VISTA MEJORADA CON MODAL DE DETALLES)
// Llamada por IngresoController->index()
// Variables disponibles: $pageTitle, $activeModule, $ingresos, $currentUser (del layout)
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle ?? 'Ingresos'); ?>: Historial Reciente</h3>
    <div class="d-flex gap-2 w-100 w-md-auto flex-wrap">
        <?php if (roleCan('add','ingresos')): ?>
            <button class="btn btn-danger btn-sm flex-grow-1 flex-md-grow-0 order-1" data-bs-toggle="modal" data-bs-target="#modalIngreso" id="btnNuevoIngreso">
                <ion-icon name="add-circle-outline" class="me-1"></ion-icon>
                <span class="d-none d-sm-inline">Agregar Ingreso</span>
                <span class="d-inline d-sm-none">Agregar</span>
            </button>
        <?php endif; ?>
        <button class="btn btn-outline-secondary btn-sm flex-grow-1 flex-md-grow-0 order-2" onclick="window.open('generate_receipt_blanco_ingreso.php', '_blank')">
            <ion-icon name="document-outline" style="vertical-align: middle;"></ion-icon>
            <span class="d-none d-sm-inline">Recibo en Blanco</span>
            <span class="d-inline d-sm-none">Blanco</span>
        </button>
        <?php $isViewingReembolsos = !empty($show_reembolsos); ?>
        <?php if ($isViewingReembolsos): ?>
            <a class="btn btn-outline-primary btn-sm" href="index.php?controller=ingreso&action=index" title="Ver Ingresos Activos">
                <ion-icon name="eye-off-outline"></ion-icon> Ver Activos
            </a>
        <?php else: ?>
            <a class="btn btn-outline-danger btn-sm" href="index.php?controller=ingreso&action=index&ver_reembolsos=1" title="Ver Reembolsos">
                <ion-icon name="eye-outline"></ion-icon> Ver Reembolsos
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Buscador de Ingresos -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <ion-icon name="search-outline" style="font-size: 1.2em; color: #B80000;"></ion-icon>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" id="searchIngresos" placeholder="Buscar por folio o alumno...">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearchIngresos" style="display:none;">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <ion-icon name="calendar-outline" style="font-size: 1.2em; color: #B80000;"></ion-icon>
                    </span>
                    <input type="date" class="form-control" id="fechaInicioIngresos" placeholder="Fecha inicio">
                    <input type="date" class="form-control" id="fechaFinIngresos" placeholder="Fecha fin">
                    <button class="btn btn-outline-secondary" type="button" id="clearDateIngresos" style="display:none;">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
            </div>
        </div>
        <small class="text-muted d-block mt-1">
            <span id="resultCountIngresos"></span>
        </small>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-sm mb-0">
                <thead>
                    <tr class="table-light">
                        <th>Alumno</th>
                        <th class="d-none d-lg-table-cell">Nivel</th>
                        <th class="d-none d-lg-table-cell">Programa</th>
                        <th class="d-none d-xl-table-cell">Grado</th>
                        <th class="d-none d-xl-table-cell">Grupo</th>
                        <th class="d-none d-md-table-cell">Concepto</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaIngresos">
                    <?php if (empty($ingresos)): ?>
                        <tr><td colspan="8" class="text-center p-4 text-muted">No hay ingresos registrados.</td></tr>
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
                            <tr data-fecha="<?php echo htmlspecialchars($ingreso['fecha'] ?? ''); ?>">
                                <td>
                                    <?php echo htmlspecialchars($ingreso['alumno'] ?? 'N/A'); ?>
                                    <br class="d-md-none">
                                    <small class="d-md-none text-muted"><?php echo htmlspecialchars($ingreso['nombre_categoria'] ?? 'N/A'); ?></small>
                                </td>
                                <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($ingreso['nivel'] ?? 'N/A'); ?></td>
                                <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($ingreso['programa'] ?? 'N/A'); ?></td>
                                <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($ingreso['grado'] ?? '-'); ?></td>
                                <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($ingreso['grupo'] ?? '-'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['nombre_categoria'] ?? 'N/A'); ?></td>
                                <?php if (!empty($show_reembolsos)): ?>
                                    <td class="text-end text-danger fw-bold"><?php echo $montoFormateado; ?></td>
                                <?php else: ?>
                                    <td class="text-end text-success fw-bold"><?php echo $montoFormateado; ?></td>
                                <?php endif; ?>
                                <td class="text-center align-middle">
                                    <div class="d-flex flex-column flex-sm-row gap-1 justify-content-center align-items-center">
                                        <?php
                                            $estatusIngreso = isset($ingreso['estatus']) ? intval($ingreso['estatus']) : 1;
                                            $estaReembolsado = ($estatusIngreso === 0);
                                            $viendoReembolsos = !empty($show_reembolsos);
                                        ?>
                                        <?php if (!$viendoReembolsos && !$estaReembolsado && roleCan('edit','ingresos')): ?>
                                            <button class="btn btn-sm btn-warning btn-edit-ingreso"
                                                    data-id="<?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 0); ?>"
                                                    data-bs-toggle="modal" data-bs-target="#modalIngreso"
                                                    title="Editar Ingreso">
                                                <ion-icon name="create-outline"></ion-icon>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (!$viendoReembolsos && !$estaReembolsado && roleCan('delete','ingresos')): ?>
                                            <!-- Reemplazamos eliminar por reembolsar -->
                                            <button class="btn btn-sm btn-danger btn-reembolsar-ingreso"
                                                    data-id="<?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 0); ?>"
                                                    data-alumno="<?php echo htmlspecialchars($ingreso['alumno'] ?? ''); ?>"
                                                    data-monto="<?php echo htmlspecialchars($ingreso['monto'] ?? 0); ?>"
                                                    title="Reembolsar Ingreso">
                                                <ion-icon name="arrow-undo-outline"></ion-icon>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (roleCan('view','ingresos')): ?>
                                            <a class="btn btn-sm btn-primary" href="<?php echo 'generate_receipt.php?folio=' . urlencode($ingreso['folio_ingreso'] ?? 0) . '&tipo=ingreso'; ?>" target="_blank" title="Imprimir Recibo">
                                                <ion-icon name="print-outline"></ion-icon>
                                            </a>
                                            <a class="btn btn-sm btn-secondary" href="<?php echo 'generate_receipt.php?folio=' . urlencode($ingreso['folio_ingreso'] ?? 0) . '&tipo=ingreso&reimpresion=1'; ?>" target="_blank" title="Reimprimir Recibo">
                                                <ion-icon name="document-attach-outline"></ion-icon>
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

<!-- Modales de Detalles -->
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
        ?>
        <!-- Modal de detalles del ingreso -->
        <div class="modal fade" id="modalDetalleIngreso<?php echo $ingreso['folio_ingreso']; ?>" tabindex="-1" aria-labelledby="modalDetalleIngresoLabel<?php echo $ingreso['folio_ingreso']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content" style="border-radius: 12px;">
                    <div class="modal-header" style="background-color:#B80000; color:#fff; border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title" id="modalDetalleIngresoLabel<?php echo $ingreso['folio_ingreso']; ?>">
                            <ion-icon name="eye-outline" style="vertical-align:middle; font-size:1.3em; margin-right:6px;"></ion-icon>
                            Detalle del Ingreso
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="background:#f8f9fa; max-height: 70vh; overflow-y: auto;">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" style="background:white; border-radius:8px;">
                                <tbody>
                                    <tr>
                                        <th style="width:35%;">Folio</th>
                                        <td><?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de Pago</th>
                                        <td><?php echo htmlspecialchars($ingreso['fecha'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Alumno</th>
                                        <td><?php echo htmlspecialchars($ingreso['alumno'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Matrícula</th>
                                        <td><?php echo htmlspecialchars($ingreso['matricula'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nivel Académico</th>
                                        <td><?php echo htmlspecialchars($ingreso['nivel'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Programa</th>
                                        <td><?php echo htmlspecialchars($ingreso['programa'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Grado</th>
                                        <td><?php echo htmlspecialchars($ingreso['grado'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Modalidad</th>
                                        <td><?php echo htmlspecialchars($ingreso['modalidad'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Grupo</th>
                                        <td><?php echo htmlspecialchars($ingreso['grupo'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Categoría</th>
                                        <td><?php echo htmlspecialchars($ingreso['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Método de Pago</th>
                                        <td>
                                            <?php 
                                            if (!empty($ingreso['metodos_pago_detalle'])) {
                                                // Tiene pagos divididos
                                                echo '<span class="badge bg-info text-dark">Pago Dividido (' . $ingreso['num_pagos'] . ' métodos)</span><br>';
                                                echo '<small>' . htmlspecialchars($ingreso['metodos_pago_detalle']) . '</small>';
                                            } else {
                                                // Pago único
                                                echo htmlspecialchars($ingreso['metodo_de_pago'] ?? 'N/A');
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Mes Correspondiente</th>
                                        <td><?php echo htmlspecialchars($ingreso['mes_correspondiente'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Año</th>
                                        <td><?php echo htmlspecialchars($ingreso['anio'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Observaciones</th>
                                        <td><?php echo htmlspecialchars($ingreso['observaciones'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Monto</th>
                                        <td class="fw-bold text-success"><?php echo $montoFormateado; ?></td>
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

<!-- Modal de Gráficas de Ingresos -->
<div class="modal fade" id="modalGraficasIngresos" tabindex="-1" aria-labelledby="modalGraficasIngresosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #28a745; color: white;">
                <h5 class="modal-title" id="modalGraficasIngresosLabel">
                    <ion-icon name="analytics-outline" style="vertical-align: middle; font-size: 1.5rem;"></ion-icon>
                    Análisis de Ingresos
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
                                    <ion-icon name="pie-chart-outline" style="vertical-align: middle; color: #28a745;"></ion-icon>
                                    Distribución por Categoría
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartIngresosCategoria" style="max-height: 350px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfica de Barras: Ingresos por Mes -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">
                                    <ion-icon name="bar-chart-outline" style="vertical-align: middle; color: #28a745;"></ion-icon>
                                    Ingresos Mensuales (Últimos 6 Meses)
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartIngresosMes" style="max-height: 350px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales para las gráficas de ingresos
let chartIngresosCategoriaInstance = null;
let chartIngresosMesInstance = null;

// Usar vanilla JavaScript para el evento inicial (antes de que jQuery se cargue)
(function() {
    // Esperar a que el DOM esté listo Y jQuery esté cargado
    function initIngresosGraficas() {
        if (typeof jQuery === 'undefined') {
            console.log('jQuery aún no cargado para ingresos, esperando...');
            setTimeout(initIngresosGraficas, 100);
            return;
        }
        
        console.log('Script de ingresos inicializado con jQuery');
        
        // Evento para abrir el modal y cargar las gráficas
        jQuery('#btnVerGraficasIngresos').on('click', function() {
            console.log('Botón Ver Gráficas Ingresos clickeado');
            jQuery('#modalGraficasIngresos').modal('show');
            cargarGraficasIngresos();
        });

        // Limpiar gráficas al cerrar el modal
        jQuery('#modalGraficasIngresos').on('hidden.bs.modal', function() {
            if (chartIngresosCategoriaInstance) {
                chartIngresosCategoriaInstance.destroy();
                chartIngresosCategoriaInstance = null;
            }
            if (chartIngresosMesInstance) {
                chartIngresosMesInstance.destroy();
                chartIngresosMesInstance = null;
            }
        });
    }
    
    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initIngresosGraficas);
    } else {
        initIngresosGraficas();
    }
})();

function cargarGraficasIngresos() {
    console.log('Cargando gráficas de ingresos...');
    // Cargar gráfica de categorías
    ajaxCall('ingreso', 'getGraficaIngresosPorCategoria', {}, 'GET')
        .done(function(data) {
            if (data.success && data.categorias.length > 0) {
                if (chartIngresosCategoriaInstance) {
                    chartIngresosCategoriaInstance.destroy();
                }

                const ctx = document.getElementById('chartIngresosCategoria').getContext('2d');
                chartIngresosCategoriaInstance = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.categorias,
                        datasets: [{
                            data: data.montos,
                            backgroundColor: [
                                'rgba(40, 167, 69, 0.8)',
                                'rgba(23, 162, 184, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(111, 66, 193, 0.8)',
                                'rgba(232, 62, 140, 0.8)',
                                'rgba(32, 201, 151, 0.8)'
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
                document.getElementById('chartIngresosCategoria').parentElement.innerHTML = 
                    '<p class="text-center text-muted py-5">No hay datos de ingresos por categoría</p>';
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar gráfica ingresos por categoría:', xhr);
        });

    // Cargar gráfica de meses
    ajaxCall('ingreso', 'getGraficaIngresosPorMes', {}, 'GET')
        .done(function(data) {
            if (data.success && data.meses.length > 0) {
                if (chartIngresosMesInstance) {
                    chartIngresosMesInstance.destroy();
                }

                const ctx = document.getElementById('chartIngresosMes').getContext('2d');
                chartIngresosMesInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.meses,
                        datasets: [{
                            label: 'Ingresos',
                            data: data.montos,
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
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
                                        return 'Ingresos: ' + new Intl.NumberFormat('es-MX', {
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
                document.getElementById('chartIngresosMes').parentElement.innerHTML = 
                    '<p class="text-center text-muted py-5">No hay datos de ingresos mensuales</p>';
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar gráfica ingresos por mes:', xhr);
        });
}

// Funcionalidad de Reembolsos: abrir modal de creación de egreso pre-llenado
document.addEventListener('DOMContentLoaded', function() {
    // Delegación: botones .btn-reembolsar-ingreso
    document.querySelectorAll('.btn-reembolsar-ingreso').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var id = this.getAttribute('data-id');
            var alumno = this.getAttribute('data-alumno');
            var monto = this.getAttribute('data-monto');

            // Obtener únicamente los presupuestos de reembolso y forzar el ID real (11)
            ajaxCall('presupuesto', 'getReembolsoPresupuestos', {}, 'GET')
                .done(function(pres) {
                    var chosenPres = null;
                    if (Array.isArray(pres)) {
                        for (var i = 0; i < pres.length; i++) {
                            try {
                                var p = pres[i];
                                // Preferir el sub-presupuesto de reembolsos (ID 11)
                                if (p.id_presupuesto && parseInt(p.id_presupuesto) === 11) { chosenPres = p; break; }
                            } catch(e) { }
                        }
                    }

                    var prefill = {
                        folio_ingreso: id,
                        alumno: alumno,
                        monto: monto,
                        fecha: (new Date()).toISOString().slice(0,10),
                        // Para reembolsos usar categoría fija ID 21 (IUM REEMBOLSOS) y presupuesto fijo ID 11
                        id_categoria: 21,
                        proveedor: 'Reembolsos',
                        documento_de_amparo: 'Recibo de ingreso #' + id,
                        from_ingreso: id
                    };
                    if (chosenPres) prefill.id_presupuesto = chosenPres.id_presupuesto || chosenPres.id;
                    // Si no se encontró presupuesto candidato, usar ID 11 por defecto (Fondo de Reembolsos)
                    if (!prefill.id_presupuesto) prefill.id_presupuesto = 11;

                    // Exponer para que el modal lo lea y abrirlo explícitamente con helper
                    window.PREFILL_EGRESO = prefill;
                    try {
                        if (typeof window.openReembolsoModal === 'function') {
                            window.openReembolsoModal(id, alumno, monto);
                        } else if (typeof window.openEgresoModalWithPrefill === 'function') {
                            window.openEgresoModalWithPrefill(prefill);
                        } else {
                            var modalEl = document.getElementById('modalEgreso');
                            if (modalEl && typeof bootstrap !== 'undefined') {
                                var bs = new bootstrap.Modal(modalEl, { backdrop: 'static' });
                                bs.show();
                            } else {
                                $('#modalEgreso').modal('show');
                            }
                        }
                    } catch(e) {
                        console.error('Error mostrando modal reembolso:', e);
                        $('#modalEgreso').modal('show');
                    }
                })
                .fail(function(xhr) {
                    console.error('Error cargando presupuestos para reembolso:', xhr);
                    alert('Error cargando datos de presupuestos para reembolso. Revisa la consola.');
                });
        });
    });
});
</script>

<script>
// Inicializar envío de reembolso y prellenado de campos del modal
(function(){
    function initReembolsoUI(){
        if (typeof jQuery === 'undefined') { setTimeout(initReembolsoUI, 100); return; }
        // Asegurar fecha y documento se prellenan cuando se abre el modal
        jQuery('#modalReembolso').on('show.bs.modal', function(){
            var today = new Date().toISOString().slice(0,10);
            jQuery('#reem_fecha').val(today);
            var folio = jQuery('#reem_folio_origen').val() || '';
            jQuery('#reem_documento_de_amparo').val(folio ? ('Recibo de ingreso #' + folio) : '');
        });
        // Conectar el submit al handler de EgresosModule
        if (window.EgresosModule && typeof window.EgresosModule.initSubmitReembolso === 'function') {
            window.EgresosModule.initSubmitReembolso();
        } else {
            // Fallback: prevenir navegación y hacer POST manual
            jQuery('#formReembolso').off('submit.fallback').on('submit.fallback', function(ev){
                ev.preventDefault();
                var formData = jQuery(this).serializeArray();
                var hasFolio = formData.some(function(p){ return p.name === 'reem_folio_origen'; });
                if (!hasFolio) formData.push({ name: 'reem_folio_origen', value: jQuery('#reem_folio_origen').val() });
                jQuery.ajax({
                    url: BASE_URL + 'index.php?controller=egreso&action=save',
                    method: 'POST',
                    data: jQuery.param(formData),
                    dataType: 'json'
                }).done(function(resp){
                    if (resp && resp.success) { jQuery('#modalReembolso').modal('hide'); location.reload(); }
                    else { alert((resp && resp.error) ? resp.error : 'Error al guardar reembolso.'); }
                }).fail(function(){ alert('Error de comunicación al guardar reembolso'); });
            });
        }
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initReembolsoUI); }
    else { initReembolsoUI(); }
})();
</script>

<!-- Modal de Reembolso (Sistema) -->
<div class="modal fade" id="modalReembolso" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <ion-icon name="alert-circle-outline" style="vertical-align: bottom;"></ion-icon>
                    Confirmar Reembolso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formReembolso" method="post" action="index.php?controller=egreso&action=save">
                    <input type="hidden" id="reem_folio_origen" name="reem_folio_origen">

                    <!-- Estos campos los fuerza el backend (11/21); se mantienen ocultos -->
                    <!-- Nota: Usaremos selects visibles para que el usuario vea lo que se forzará -->
                    <!-- Los valores se establecerán programáticamente a 11 y 21 -->
                    <input type="hidden" id="reem_proveedor" name="proveedor" value="Reembolsos">

                    <div class="alert alert-warning">
                        <small>Esta acción cancelará el ingreso original y generará un egreso de reembolso automáticamente.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Reembolso</label>
                            <input type="date" class="form-control" id="reem_fecha" name="fecha" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto a Devolver</label>
                            <input type="text" class="form-control fw-bold text-danger" id="reem_monto" name="monto" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Destinatario</label>
                            <input type="text" class="form-control" id="reem_destinatario" name="destinatario" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Forma de Pago</label>
                            <select class="form-select" id="reem_forma_pago" name="forma_pago" required>
                                <option value="Efectivo" selected>Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Tarjeta D.">Tarjeta D.</option>
                                <option value="Tarjeta C.">Tarjeta C.</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" id="reem_id_categoria" name="id_categoria" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Presupuesto</label>
                            <select class="form-select" id="reem_id_presupuesto" name="id_presupuesto" required></select>
                        </div>

                        <div class="col-12 mt-2">
                            <label class="form-label">Documento de Amparo</label>
                            <input type="text" class="form-control" id="reem_documento_de_amparo" name="documento_de_amparo" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="reem_descripcion" name="descripcion" placeholder="Motivo del reembolso" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Presupuesto Afectado</label>
                            <input type="text" class="form-control bg-light" value="Fondo de Reembolsos (Sistema)" readonly>
                        </div>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger fw-bold">
                            <ion-icon name="arrow-undo-outline"></ion-icon> Confirmar Reembolso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>