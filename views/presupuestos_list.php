<?php
// views/presupuestos_list.php
// Llamada por PresupuestoController->index()
// Variables disponibles: $pageTitle, $activeModule, $presupuestos, $currentUser (del layout)
?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle); ?>: Resumen General</h3>
    <div class="d-flex gap-2 w-100 w-md-auto">
        <button class="btn btn-outline-danger btn-sm flex-grow-1 flex-md-grow-0" id="btnVerGraficaPresupuestos">
            <ion-icon name="bar-chart-outline" style="vertical-align: middle;"></ion-icon> 
            <span class="d-none d-sm-inline">Ver Análisis</span>
            <span class="d-inline d-sm-none">Análisis</span>
        </button>
        <?php if (roleCan('add','presupuestos')): ?>
            <button class="btn btn-danger btn-sm flex-grow-1 flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modalPresupuesto" id="btnNuevoPresupuesto">
                <ion-icon name="add-circle-outline" class="me-1"></ion-icon> 
                <span class="d-none d-sm-inline">Agregar Presupuesto</span>
                <span class="d-inline d-sm-none">Agregar</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="action-menu mb-4 d-none">
    <button class="btn active">Ver Presupuesto</button>
    <button class="btn" data-bs-toggle="modal" data-bs-target="#modalPresupuesto">Agregar</button>
    <button class="btn">Actualizar</button> </div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <?php 
            // Verificar si el usuario tiene permisos para editar o eliminar
            $hasActions = roleCan('edit','presupuestos') || roleCan('delete','presupuestos');
            $colspan = $hasActions ? '4' : '3';
            ?>
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr class="table-light">
                        <th>Categoría</th>
                        <th class="text-end">Monto Límite</th>
                        <th class="d-none d-md-table-cell">Fecha Asig.</th>
                        <?php if ($hasActions): ?>
                            <th class="text-center">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="tablaPresupuestos">
                    <?php if (empty($presupuestos)): ?>
                        <tr><td colspan="<?php echo $colspan; ?>" class="text-center p-4 text-muted">No hay presupuestos asignados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($presupuestos as $presupuesto):
                            $monto = $presupuesto['monto_limite'] ?? ($presupuesto['monto'] ?? 0);
                            $montoFormateado = number_format((float)$monto, 2);
                            try {
                                if (class_exists('NumberFormatter')) {
                                    $formatter = new NumberFormatter('es-MX', NumberFormatter::CURRENCY);
                                    if (is_numeric($monto)) {
                                        $montoFormateado = $formatter->formatCurrency($monto, 'MXN');
                                    }
                                }
                            } catch (Exception $e) { /* Ignorar */ }
                            $categoria = htmlspecialchars($presupuesto['cat_nombre'] ?? '-');
                            $fechaDisplay = htmlspecialchars($presupuesto['fecha'] ?? '-');
                            $presId = $presupuesto['id'] ?? ($presupuesto['id_presupuesto'] ?? 0);
                            $catId = $presupuesto['id_categoria'] ?? null;
                        ?>
                            <tr>
                                <td>
                                    <?php echo $categoria; ?>
                                    <br class="d-md-none">
                                    <small class="d-md-none text-muted"><?php echo $fechaDisplay; ?></small>
                                </td>
                                <td class="text-end fw-bold"><?php echo $montoFormateado; ?></td>
                                <td class="d-none d-md-table-cell"><?php echo $fechaDisplay; ?></td>
                                <?php if ($hasActions): ?>
                                    <td class="text-center align-middle">
                                        <div class="d-flex flex-column flex-sm-row gap-1 justify-content-center align-items-center">
                                            <?php if (roleCan('edit','presupuestos')): ?>
                                                <button class="btn btn-sm btn-warning btn-edit-presupuesto"
                                                        data-id="<?php echo $presId; ?>"
                                                        data-cat="<?php echo htmlspecialchars($catId); ?>"
                                                        data-bs-toggle="modal" data-bs-target="#modalPresupuesto"
                                                        title="Editar Presupuesto">
                                                     <ion-icon name="create-outline"></ion-icon>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (roleCan('delete','presupuestos')): ?>
                                                <button class="btn btn-sm btn-danger btn-del-presupuesto"
                                                        data-id="<?php echo $presId; ?>"
                                                        title="Eliminar Presupuesto">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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