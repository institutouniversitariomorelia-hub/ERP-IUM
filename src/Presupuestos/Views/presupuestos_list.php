<?php
// views/presupuestos_list.php
// Llamada por PresupuestoController->index()
// Variables disponibles: $pageTitle, $activeModule, $presupuestos, $currentUser (del layout)
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle); ?>: Sistema Jerárquico</h3>
    <div class="d-flex gap-2 w-100 w-md-auto">
        <button class="btn btn-outline-danger btn-sm flex-grow-1 flex-md-grow-0" id="btnVerGraficaPresupuestos">
            <ion-icon name="bar-chart-outline" style="vertical-align: middle;"></ion-icon> 
            <span class="d-none d-sm-inline">Ver Análisis</span>
            <span class="d-inline d-sm-none">Análisis</span>
        </button>
    </div>
</div>

<!-- Información del sistema -->
<div class="alert alert-info alert-info-custom d-flex align-items-center mb-4" style="border-left: 4px solid #0dcaf0;">
    <ion-icon name="information-circle-outline" style="font-size: 1.5rem; margin-right: 10px; color: #0dcaf0;"></ion-icon>
    <div>
        <strong>Sistema de Presupuestos Jerárquico:</strong><br>
        Primero cree un <strong>Presupuesto General</strong> por mes, luego asigne <strong>Sub-presupuestos</strong> por categoría dentro de cada presupuesto general.
    </div>
</div>

<?php
// Separar presupuestos generales de los presupuestos por categoría
$presupuestosGenerales = [];
$subPresupuestos = [];

foreach ($presupuestos as $presupuesto) {
    if (empty($presupuesto['id_categoria'])) {
        $presupuestosGenerales[] = $presupuesto;
    } else {
        $parentId = $presupuesto['parent_presupuesto'] ?? 0;
        if (!isset($subPresupuestos[$parentId])) {
            $subPresupuestos[$parentId] = [];
        }
        $subPresupuestos[$parentId][] = $presupuesto;
    }
}

// Verificar permisos
$hasActions = roleCan('edit','presupuestos') || roleCan('delete','presupuestos');
?>

<!-- Sección de Presupuestos Generales -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-success fw-bold mb-0">
                <ion-icon name="wallet-outline" style="vertical-align: middle;"></ion-icon>
                Presupuestos Generales
            </h4>
            <?php if (roleCan('add','presupuestos')): ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPresupuestoGeneral" id="btnNuevoPresupuestoGeneral">
                    <ion-icon name="add-circle-outline" class="me-1"></ion-icon>
                    Nuevo Presupuesto General
                </button>
            <?php endif; ?>
        </div>
        
        <?php if (empty($presupuestosGenerales)): ?>
            <div class="alert alert-warning text-center py-4" style="border-radius: 15px; background: linear-gradient(135deg, #fff3cd 0%, #fef7e0 100%); border: 2px dashed #ffc107;">
                <ion-icon name="wallet-outline" style="font-size: 3rem; color: #f39c12; opacity: 0.7;"></ion-icon>
                <h4 class="mt-3 text-warning">No hay Presupuestos Generales</h4>
                <p class="text-muted mb-3">Para comenzar a usar el sistema de presupuestos, primero debe crear un Presupuesto General.</p>
                <?php if (roleCan('add','presupuestos')): ?>
                <button class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#modalPresupuestoGeneral" id="btnPrimerPresupuesto">
                    <ion-icon name="add-circle-outline" class="me-2"></ion-icon>
                    Crear Primer Presupuesto General
                </button>
                <?php else: ?>
                <p class="text-muted small">Contacte al administrador para crear presupuestos.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row g-3 g-md-4">
                <?php foreach ($presupuestosGenerales as $presGeneral): 
                    $monto = $presGeneral['monto_limite'] ?? 0;
                    $presId = $presGeneral['id'] ?? ($presGeneral['id_presupuesto'] ?? 0);
                    $fecha = $presGeneral['fecha'] ?? '';
                    
                    // Formatear moneda
                    $montoFormateado = '$' . number_format((float)$monto, 2);
                    try {
                        if (class_exists('NumberFormatter')) {
                            $formatter = new NumberFormatter('es-MX', NumberFormatter::CURRENCY);
                            if (is_numeric($monto)) {
                                $montoFormateado = $formatter->formatCurrency($monto, 'MXN');
                            }
                        }
                    } catch (Exception $e) { /* Ignorar */ }
                    
                    // Calcular totales asignados y disponibles
                    $totalAsignado = 0;
                    $subPresupuestosDelGeneral = $subPresupuestos[$presId] ?? [];
                    foreach ($subPresupuestosDelGeneral as $sub) {
                        $totalAsignado += floatval($sub['monto_limite'] ?? 0);
                    }
                    $disponible = $monto - $totalAsignado;
                    $porcentajeAsignado = $monto > 0 ? ($totalAsignado / $monto) * 100 : 0;
                ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card presupuesto-card card-presupuesto-general h-100" style="border-radius: 15px; border-left: 5px solid #28a745;">
                        <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="text-success mb-0 fw-bold">
                                    <ion-icon name="calendar-outline" style="vertical-align: middle;"></ion-icon>
                                    <?php echo htmlspecialchars(date('F Y', strtotime($fecha))); ?>
                                </h5>
                                <?php if ($hasActions): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <ion-icon name="ellipsis-vertical-outline"></ion-icon>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if (roleCan('edit','presupuestos')): ?>
                                        <li><button class="dropdown-item btn-edit-presupuesto" data-id="<?php echo $presId; ?>" data-bs-toggle="modal" data-bs-target="#modalPresupuesto">
                                            <ion-icon name="create-outline" class="me-2"></ion-icon>Editar
                                        </button></li>
                                        <?php endif; ?>
                                        <?php if (roleCan('delete','presupuestos')): ?>
                                        <li><button class="dropdown-item text-danger btn-del-presupuesto" data-id="<?php echo $presId; ?>">
                                            <ion-icon name="trash-outline" class="me-2"></ion-icon>Eliminar
                                        </button></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="border-end">
                                        <h6 class="text-primary mb-1">Monto Total</h6>
                                        <p class="h5 text-success mb-0 monto-display"><?php echo $montoFormateado; ?></p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <h6 class="text-primary mb-1">Asignado</h6>
                                        <p class="h6 text-warning mb-0 monto-display">$<?php echo number_format($totalAsignado, 2); ?></p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h6 class="text-primary mb-1">Disponible</h6>
                                    <p class="h6 <?php echo $disponible >= 0 ? 'text-success' : 'text-danger'; ?> mb-0 monto-display">
                                        $<?php echo number_format($disponible, 2); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Barra de progreso -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">% Asignado</small>
                                    <span class="badge badge-porcentaje <?php echo $porcentajeAsignado > 90 ? 'bg-danger' : ($porcentajeAsignado > 70 ? 'bg-warning' : 'bg-success'); ?>">
                                        <?php echo number_format($porcentajeAsignado, 1); ?>%
                                    </span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar <?php echo $porcentajeAsignado > 90 ? 'bg-danger' : ($porcentajeAsignado > 70 ? 'bg-warning' : 'bg-success'); ?>" 
                                         style="width: <?php echo min($porcentajeAsignado, 100); ?>%"></div>
                                </div>
                            </div>
                            
                            <!-- Botón para agregar sub-presupuesto -->
                            <?php if (roleCan('add','presupuestos')): ?>
                            <div class="text-center">
                                <button class="btn btn-outline-primary btn-sm btn-add-sub btn-add-sub-presupuesto" 
                                        data-parent-id="<?php echo $presId; ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalSubPresupuesto">
                                    <ion-icon name="add-outline" class="me-1"></ion-icon>
                                    Agregar Sub-presupuesto
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Sub-presupuestos -->
                        <?php if (!empty($subPresupuestosDelGeneral)): ?>
                        <div class="card-footer bg-light" style="border-radius: 0 0 15px 15px;">
                            <h6 class="text-muted mb-2">
                                <ion-icon name="layers-outline" style="vertical-align: middle;"></ion-icon>
                                Sub-presupuestos por Categoría (<?php echo count($subPresupuestosDelGeneral); ?>)
                            </h6>
                            <?php foreach ($subPresupuestosDelGeneral as $sub): 
                                $subMonto = floatval($sub['monto_limite'] ?? 0);
                                $subCategoria = htmlspecialchars($sub['cat_nombre'] ?? 'Sin categoría');
                                $subId = $sub['id'] ?? ($sub['id_presupuesto'] ?? 0);
                                $subNombre = htmlspecialchars($sub['nombre'] ?? '');
                                $subGastado = floatval($sub['gastado'] ?? 0);
                                $subPorcentaje = $subMonto > 0 ? round(($subGastado / $subMonto) * 100, 2) : 0;
                                
                                // Determinar si está en alerta (>=90%)
                                $enAlerta = $subPorcentaje >= 90;
                                $claseAlerta = $enAlerta ? 'presupuesto-alerta' : '';
                                
                                // Color de la barra de progreso
                                $colorBarra = 'success';
                                if ($subPorcentaje >= 90) $colorBarra = 'danger';
                                elseif ($subPorcentaje >= 75) $colorBarra = 'warning';
                            ?>
                            <div class="d-flex justify-content-between align-items-center py-3 border-bottom sub-presupuesto-item <?php echo $claseAlerta; ?>" style="<?php echo $enAlerta ? 'border-left: 4px solid #dc3545; padding-left: 8px;' : ''; ?>">
                                <div class="flex-grow-1 me-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <small class="fw-bold text-primary"><?php echo $subCategoria; ?></small>
                                        <?php if ($enAlerta): ?>
                                        <span class="badge badge-alerta" style="font-size: 0.7rem;">
                                            <ion-icon name="warning-outline" style="vertical-align: middle;"></ion-icon>
                                            ALERTA
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($subNombre)): ?>
                                    <div class="text-muted small mb-1"><?php echo $subNombre; ?></div>
                                    <?php endif; ?>
                                    <div class="progress progress-custom mb-1" style="height: 8px;">
                                        <div class="progress-bar bg-<?php echo $colorBarra; ?>" role="progressbar" 
                                             style="width: <?php echo min($subPorcentaje, 100); ?>%;" 
                                             aria-valuenow="<?php echo $subPorcentaje; ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Gastado: <span class="fw-bold text-danger">$<?php echo number_format($subGastado, 2); ?></span>
                                            de <span class="fw-bold">$<?php echo number_format($subMonto, 2); ?></span>
                                        </small>
                                        <span class="badge bg-<?php echo $colorBarra; ?> badge-porcentaje">
                                            <?php echo $subPorcentaje; ?>%
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($hasActions): ?>
                                    <div class="btn-group btn-group-sm">
                                        <?php if (roleCan('edit','presupuestos')): ?>
                                        <button class="btn btn-outline-warning btn-sm btn-edit-presupuesto" 
                                                data-id="<?php echo $subId; ?>" 
                                                data-bs-toggle="modal" data-bs-target="#modalSubPresupuesto"
                                                title="Editar">
                                            <ion-icon name="create-outline"></ion-icon>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (roleCan('delete','presupuestos')): ?>
                                        <button class="btn btn-outline-danger btn-sm btn-del-presupuesto" 
                                                data-id="<?php echo $subId; ?>"
                                                title="Eliminar">
                                            <ion-icon name="trash-outline"></ion-icon>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Gráfica Presupuesto vs Gastado -->
<div class="modal fade" id="modalGraficaPresupuestos" tabindex="-1" aria-labelledby="modalGraficaPresupuestosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #B80000; color: white;">
                <h5 class="modal-title" id="modalGraficaPresupuestosLabel">
                    <ion-icon name="analytics-outline" style="vertical-align: middle; font-size: 1.5rem;"></ion-icon>
                    Análisis: Presupuesto vs Gastado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background-color: #f8f9fa;">
                <!-- Gráfica de Barras Comparativa -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <ion-icon name="bar-chart-outline" style="vertical-align: middle; color: #B80000;"></ion-icon>
                            Comparativa por Categoría
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chartPresupuestoVsGastado" style="max-height: 400px;"></canvas>
                    </div>
                </div>

                <!-- Indicadores de Progreso -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <ion-icon name="stats-chart-outline" style="vertical-align: middle; color: #B80000;"></ion-icon>
                            Porcentaje de Consumo por Categoría
                        </h6>
                    </div>
                    <div class="card-body" id="indicadoresPresupuesto">
                        <div class="text-center text-muted py-3">
                            <ion-icon name="hourglass-outline" style="font-size: 2rem;"></ion-icon>
                            <p class="mt-2">Cargando indicadores...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variable global para la gráfica
let chartPresupuestoInstance = null;

// Usar vanilla JavaScript para el evento inicial (antes de que jQuery se cargue)
(function() {
    // Esperar a que el DOM esté listo Y jQuery esté cargado
    function initPresupuestosGraficas() {
        if (typeof jQuery === 'undefined') {
            console.log('jQuery aún no cargado, esperando...');
            setTimeout(initPresupuestosGraficas, 100);
            return;
        }
        
        console.log('Script de presupuestos inicializado con jQuery');
        
        // Evento para abrir el modal y cargar la gráfica
        jQuery('#btnVerGraficaPresupuestos').on('click', function() {
            console.log('Botón Ver Análisis clickeado');
            jQuery('#modalGraficaPresupuestos').modal('show');
            cargarGraficaPresupuestoVsGastado();
        });

        // Limpiar gráfica al cerrar el modal
        jQuery('#modalGraficaPresupuestos').on('hidden.bs.modal', function() {
            if (chartPresupuestoInstance) {
                chartPresupuestoInstance.destroy();
                chartPresupuestoInstance = null;
            }
        });
    }
    
    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPresupuestosGraficas);
    } else {
        initPresupuestosGraficas();
    }
})();

// Función para cargar la gráfica de Presupuesto vs Gastado
function cargarGraficaPresupuestoVsGastado() {
    console.log('Cargando gráfica presupuesto vs gastado...');
    ajaxCall('presupuesto', 'getGraficaPresupuestoVsGastado', {}, 'GET')
        .done(function(data) {
            if (data.success && data.categorias.length > 0) {
                // Destruir gráfica anterior si existe
                if (chartPresupuestoInstance) {
                    chartPresupuestoInstance.destroy();
                }

                // Crear gráfica de barras
                const ctx = document.getElementById('chartPresupuestoVsGastado').getContext('2d');
                chartPresupuestoInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.categorias,
                        datasets: [
                            {
                                label: 'Presupuestado',
                                data: data.presupuestos,
                                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                                borderColor: 'rgba(40, 167, 69, 1)',
                                borderWidth: 2
                            },
                            {
                                label: 'Gastado',
                                data: data.gastados,
                                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += new Intl.NumberFormat('es-MX', {
                                            style: 'currency',
                                            currency: 'MXN'
                                        }).format(context.parsed.y);
                                        return label;
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

                // Crear indicadores de progreso
                const formatter = new Intl.NumberFormat('es-MX', {
                    style: 'currency',
                    currency: 'MXN'
                });

                let html = '';
                for (let i = 0; i < data.categorias.length; i++) {
                    const categoria = data.categorias[i];
                    const presupuesto = data.presupuestos[i];
                    const gastado = data.gastados[i];
                    const porcentaje = data.porcentajes[i];

                    let colorClass = 'success';
                    let icon = 'checkmark-circle';
                    
                    if (porcentaje >= 90) {
                        colorClass = 'danger';
                        icon = 'alert-circle';
                    } else if (porcentaje >= 70) {
                        colorClass = 'warning';
                        icon = 'warning';
                    }

                    html += `
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">${categoria}</h6>
                                <span class="badge bg-${colorClass}">
                                    <ion-icon name="${icon}" style="vertical-align: middle;"></ion-icon>
                                    ${porcentaje.toFixed(2)}%
                                </span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-${colorClass}" role="progressbar" 
                                     style="width: ${Math.min(porcentaje, 100)}%" 
                                     aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">
                                    ${formatter.format(gastado)}
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">
                                    Presupuesto: ${formatter.format(presupuesto)}
                                </small>
                                <small class="text-muted">
                                    Disponible: ${formatter.format(presupuesto - gastado)}
                                </small>
                            </div>
                        </div>
                    `;
                }
                $('#indicadoresPresupuesto').html(html);

            } else {
                document.getElementById('chartPresupuestoVsGastado').parentElement.innerHTML = 
                    '<p class="text-center text-muted py-5">No hay datos de presupuestos para mostrar</p>';
                $('#indicadoresPresupuesto').html('<p class="text-center text-muted py-3">No hay presupuestos registrados</p>');
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar gráfica presupuesto vs gastado:', xhr);
            alert('Error al cargar la gráfica. Por favor, intente nuevamente.');
        });
}
</script>