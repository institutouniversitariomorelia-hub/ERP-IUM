<?php
// views/presupuestos_list.php
// Llamada por PresupuestoController->index()
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

if (!is_array($presupuestos)) $presupuestos = [];

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
                    $presIdCheck = $presGeneral['id'] ?? ($presGeneral['id_presupuesto'] ?? 0);
                    // No ocultar los presupuestos del sistema; los protegemos en acciones (IDs 1 y 2)
                    
                    $monto = $presGeneral['monto_limite'] ?? 0;
                    $isPermanent = isset($presGeneral['es_permanente']) && intval($presGeneral['es_permanente']) === 1;
                    $isActive = isset($presGeneral['activo']) ? intval($presGeneral['activo']) === 1 : true;
                    $presId = $presGeneral['id'] ?? ($presGeneral['id_presupuesto'] ?? 0);
                    $fecha = $presGeneral['fecha'] ?? '';
                    // Ocultar presupuesto permanente en la vista
                    if ($isPermanent) { continue; }
                    
                    // Formatear moneda
                    if ($isPermanent) {
                        $montoFormateado = 'FONDO ILIMITADO';
                    } else {
                        $montoFormateado = '$' . number_format((float)$monto, 2);
                        try {
                            if (class_exists('NumberFormatter')) {
                                $formatter = new NumberFormatter('es-MX', NumberFormatter::CURRENCY);
                                if (is_numeric($monto)) {
                                    $montoFormateado = $formatter->formatCurrency($monto, 'MXN');
                                }
                            }
                        } catch (Exception $e) { /* Ignorar */ }
                    }
                    
                    // Calcular totales asignados y disponibles
                    $totalAsignado = 0;
                    $subPresupuestosDelGeneral = $subPresupuestos[$presId] ?? [];
                    foreach ($subPresupuestosDelGeneral as $sub) {
                        $totalAsignado += floatval($sub['monto_limite'] ?? 0);
                    }
                    
                    if ($isPermanent) {
                        $disponible = null; 
                        $porcentajeAsignado = 0;
                    } else {
                        $disponible = $monto - $totalAsignado;
                        $porcentajeAsignado = $monto > 0 ? ($totalAsignado / $monto) * 100 : 0;
                    }
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
                                    <?php
                                                        $isClosed = !$isActive;
                                                    ?>
                                                                        <?php if ($isClosed): ?>
                                                                            <span class="badge bg-dark">CERRADO</span>
                                                                        <?php else: ?>
                                                                            <span class="badge bg-success">ABIERTO</span>
                                                                        <?php endif; ?>
                                                    <?php if ($hasActions): ?>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                <ion-icon name="ellipsis-vertical-outline"></ion-icon>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <?php if (roleCan('edit','presupuestos')): ?>
                                                                    <li><button class="dropdown-item btn-edit-presupuesto" data-id="<?php echo $presId; ?>" data-bs-toggle="modal" data-bs-target="#modalPresupuestoGeneral">
                                                                        <ion-icon name="create-outline" class="me-2"></ion-icon>Editar
                                                                    </button></li>
                                                                <?php endif; ?>
                                                                <?php if (roleCan('edit','presupuestos')): ?>
                                                                    <?php if ($isActive && !in_array($presId, [1,2])): ?>
                                                                        <li><button class="dropdown-item btn-close-presupuesto" data-id="<?php echo $presId; ?>">
                                                                            <ion-icon name="log-out-outline" class="me-2"></ion-icon>Cerrar Presupuesto
                                                                        </button></li>
                                                                    <?php elseif (!$isActive): ?>
                                                                        <li><button class="dropdown-item btn-reopen-presupuesto" data-id="<?php echo $presId; ?>">
                                                                            <ion-icon name="log-in-outline" class="me-2"></ion-icon>Reabrir Presupuesto
                                                                        </button></li>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
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
                                        <p class="h6 text-warning mb-0 monto-display"><?php echo '$' . number_format($totalAsignado, 2); ?></p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h6 class="text-primary mb-1">Disponible</h6>
                                    <p class="h6 <?php echo ($disponible >= 0) ? 'text-success' : 'text-danger'; ?> mb-0 monto-display">
                                        <?php echo '$' . number_format($disponible, 2); ?>
                                    </p>
                                </div>
                            </div>
                            
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
                        
                        <?php if (!empty($subPresupuestosDelGeneral)): ?>
                        <div class="card-footer bg-light" style="border-radius: 0 0 15px 15px;">
                            <h6 class="text-muted mb-2">
                                <ion-icon name="layers-outline" style="vertical-align: middle;"></ion-icon>
                                Sub-presupuestos por Categoría (<?php echo count($subPresupuestosDelGeneral); ?>)
                            </h6>
                                <?php foreach ($subPresupuestosDelGeneral as $sub): 
                                    $subParent = $sub['parent_presupuesto'] ?? null;
                                    // Ocultar si es parent 9999
                                    if ($subParent == 10) continue;

                                    $subMonto = floatval($sub['monto_limite'] ?? 0);
                                    $subCategoria = htmlspecialchars($sub['cat_nombre'] ?? 'Sin categoría');
                                    $subId = $sub['id'] ?? ($sub['id_presupuesto'] ?? 0);
                                    $subNombre = htmlspecialchars($sub['nombre'] ?? '');
                                    $subGastado = floatval($sub['gastado'] ?? 0);
                                    $subPorcentaje = $subMonto > 0 ? round(($subGastado / $subMonto) * 100, 2) : 0;
                                    $enAlerta = $subPorcentaje >= 90;
                                    $claseAlerta = $enAlerta ? 'presupuesto-alerta' : '';
                                    
                                    $colorBarra = 'success';
                                    if ($subPorcentaje >= 90) $colorBarra = 'danger';
                                    elseif ($subPorcentaje >= 75) $colorBarra = 'warning';
                                ?>
                                <div class="d-flex justify-content-between align-items-center py-3 border-bottom sub-presupuesto-item <?php echo $claseAlerta; ?>" style="<?php echo $enAlerta ? 'border-left: 4px solid #dc3545; padding-left: 8px;' : ''; ?>">
                                    <div class="flex-grow-1 me-3">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <?php $subActivo = isset($sub['activo']) ? intval($sub['activo']) === 1 : true; ?>
                                            <small class="fw-bold text-primary"><?php echo $subCategoria; ?></small>
                                            <?php if (!$subActivo): ?>
                                                <span class="badge bg-secondary small">CERRADO</span>
                                            <?php else: ?>
                                                <span class="badge bg-success small">ABIERTO</span>
                                            <?php endif; ?>
                                            <?php if ($enAlerta): ?>
                                            <span class="badge badge-alerta" style="font-size: 0.7rem;">
                                                <ion-icon name="warning-outline" style="vertical-align: middle;"></ion-icon> ALERTA
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
                <div id="phantomPresupuestoSection" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Variable global para la gráfica
let chartPresupuestoInstance = null;

// Inicialización
(function() {
    function initPresupuestosGraficas() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initPresupuestosGraficas, 100);
            return;
        }
        
        // Evento para abrir el modal y cargar la gráfica
        jQuery('#btnVerGraficaPresupuestos').off('click').on('click', function() {
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
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPresupuestosGraficas);
    } else {
        initPresupuestosGraficas();
    }
})();

// Función para cargar la gráfica
function cargarGraficaPresupuestoVsGastado() {
    // 1. Cargar Gráfica Principal
    ajaxCall('presupuesto', 'getGraficaPresupuestoVsGastado', {}, 'GET')
        .done(function(data) {
            if (data.success && data.categorias && data.categorias.length > 0) {
                if (chartPresupuestoInstance) chartPresupuestoInstance.destroy();

                const ctx = document.getElementById('chartPresupuestoVsGastado').getContext('2d');
                chartPresupuestoInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.categorias,
                        datasets: [
                            { label: 'Presupuestado', data: data.presupuestos, backgroundColor: 'rgba(40, 167, 69, 0.7)' },
                            { label: 'Gastado', data: data.gastados, backgroundColor: 'rgba(220, 53, 69, 0.7)' }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: true }
                });

                // Indicadores
                const fmt = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
                let html = '';
                for (let i = 0; i < data.categorias.length; i++) {
                    let porc = Number(data.porcentajes[i] || 0);
                    let color = porc >= 90 ? 'danger' : (porc >= 70 ? 'warning' : 'success');
                    html += `<div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">${data.categorias[i]}</h6>
                                    <span class="badge bg-${color}">${porc.toFixed(2)}%</span>
                                </div>
                                <div class="small text-muted">
                                    Presupuesto: ${fmt.format(data.presupuestos[i])} | 
                                    Disponible: ${fmt.format(data.presupuestos[i] - data.gastados[i])}
                                </div>
                             </div>`;
                }
                $('#indicadoresPresupuesto').html(html);

                // 2. Ocultar sección de Presupuestos especiales (Reembolsos/Parent 10)
                const $phantom = $('#phantomPresupuestoSection');
                $phantom.empty().hide();

            } else {
                $('#chartPresupuestoVsGastado').parent().html('<p class="text-muted text-center">No hay datos.</p>');
                $('#indicadoresPresupuesto').html('<p class="text-muted text-center">Sin registros.</p>');
            }
        })
        .fail(function() {
            showError('Error al cargar la gráfica.');
        });
}
</script>