<?php
// views/dashboard.php
// Vista del Dashboard Ejecutivo con gráficas interactivas
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-danger mb-0">
        <ion-icon name="stats-chart-outline" style="vertical-align: middle;"></ion-icon>
        Dashboard Ejecutivo
    </h3>
    <button class="btn btn-outline-danger btn-sm" onclick="location.reload()">
        <ion-icon name="refresh-outline"></ion-icon> Actualizar
    </button>
</div>

<!-- Tarjetas de Resumen Mensual -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Ingresos del Mes</h6>
                        <h3 class="mb-0 text-success" id="totalIngresosMes">$0.00</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <ion-icon name="trending-up" style="font-size: 2rem; color: #28a745;"></ion-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Egresos del Mes</h6>
                        <h3 class="mb-0 text-danger" id="totalEgresosMes">$0.00</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <ion-icon name="trending-down" style="font-size: 2rem; color: #dc3545;"></ion-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0" style="border-left: 4px solid #B80000 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Balance del Mes</h6>
                        <h3 class="mb-0" id="balanceMes" style="color: #B80000;">$0.00</h3>
                    </div>
                    <div class="bg-secondary bg-opacity-10 p-3 rounded">
                        <ion-icon name="cash-outline" style="font-size: 2rem; color: #B80000;"></ion-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráfica Principal: Ingresos vs Egresos por Mes -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0">
                        <ion-icon name="bar-chart-outline" style="vertical-align: middle; color: #B80000;"></ion-icon>
                        <span id="tituloComparativa">Ingresos vs Egresos (Últimos 6 Meses)</span>
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-danger" onclick="cambiarPeriodoComparativa(1)">
                            <ion-icon name="calendar-outline"></ion-icon> 1 Mes
                        </button>
                        <button type="button" class="btn btn-outline-danger active" id="btn6meses" onclick="cambiarPeriodoComparativa(6)">
                            <ion-icon name="calendar-outline"></ion-icon> 6 Meses
                        </button>
                        <button type="button" class="btn btn-danger" onclick="imprimirComparativa()">
                            <ion-icon name="print-outline"></ion-icon> Imprimir
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Buscador por Mes/Año -->
                <div class="row mb-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label"><small><strong>Buscar por Mes</strong></small></label>
                        <select id="mesBusqueda" class="form-select form-select-sm">
                            <option value="">Todos los meses</option>
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><small><strong>Buscar por Año</strong></small></label>
                        <select id="anioBusqueda" class="form-select form-select-sm">
                            <option value="">Año actual</option>
                            <?php
                            $anioActual = date('Y');
                            for ($i = $anioActual; $i >= $anioActual - 5; $i--) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-danger btn-sm w-100" onclick="buscarComparativa()">
                            <ion-icon name="search-outline"></ion-icon> Buscar
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="limpiarBusqueda()">
                            <ion-icon name="refresh-outline"></ion-icon> Limpiar
                        </button>
                    </div>
                </div>
                
                <canvas id="chartIngresosEgresos" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Gráficas de Distribución por Categoría -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <ion-icon name="pie-chart-outline" style="vertical-align: middle; color: #28a745;"></ion-icon>
                    Distribución de Ingresos
                </h5>
            </div>
            <div class="card-body">
                <canvas id="chartIngresosCategorias" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <ion-icon name="pie-chart-outline" style="vertical-align: middle; color: #dc3545;"></ion-icon>
                    Distribución de Egresos
                </h5>
            </div>
            <div class="card-body">
                <canvas id="chartEgresosCategorias" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Alertas Presupuestales y Tendencia -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <ion-icon name="warning-outline" style="vertical-align: middle; color: #ff9800;"></ion-icon>
                    Alertas Presupuestales
                </h5>
            </div>
            <div class="card-body" id="alertasPresupuesto" style="max-height: 300px; overflow-y: auto;">
                <div class="text-center text-muted py-3">
                    <ion-icon name="hourglass-outline" style="font-size: 2rem;"></ion-icon>
                    <p class="mt-2">Cargando alertas...</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <ion-icon name="analytics-outline" style="vertical-align: middle; color: #B80000;"></ion-icon>
                    Tendencia de Balance
                </h5>
            </div>
            <div class="card-body">
                <canvas id="chartTendenciaBalance" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Script de inicialización de gráficas -->
<script>
// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando Dashboard...');
    
    // Cargar todas las gráficas
    cargarResumenMensual();
    cargarGraficaIngresosEgresos(6); // Por defecto 6 meses
    cargarGraficaIngresosCategorias();
    cargarGraficaEgresosCategorias();
    cargarAlertasPresupuesto();
    cargarTendenciaBalance();
});

// Variable global para guardar la instancia del chart
let chartIngresosEgresosInstance = null;
let periodoActualComparativa = 6;
let mesBusquedaActual = null;
let anioBusquedaActual = null;

// Función para cambiar el período de la comparativa
function cambiarPeriodoComparativa(meses) {
    periodoActualComparativa = meses;
    
    // Actualizar botones activos
    document.querySelectorAll('.btn-group .btn-outline-danger').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.closest('button').classList.add('active');
    
    // Actualizar título
    const titulo = meses === 1 ? 'Ingresos vs Egresos (Último Mes)' : `Ingresos vs Egresos (Últimos ${meses} Meses)`;
    document.getElementById('tituloComparativa').textContent = titulo;
    
    // Limpiar búsqueda específica
    mesBusquedaActual = null;
    anioBusquedaActual = null;
    document.getElementById('mesBusqueda').value = '';
    document.getElementById('anioBusqueda').value = '';
    
    // Recargar gráfica
    cargarGraficaIngresosEgresos(meses);
}

// Función para buscar comparativa por mes/año específico
function buscarComparativa() {
    const mes = document.getElementById('mesBusqueda').value;
    const anio = document.getElementById('anioBusqueda').value;
    
    if (!mes || !anio) {
        showError('Por favor selecciona mes y año para buscar');
        return;
    }
    
    mesBusquedaActual = mes;
    anioBusquedaActual = anio;
    
    // Actualizar título
    const mesesNombres = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    document.getElementById('tituloComparativa').textContent = `Ingresos vs Egresos (${mesesNombres[mes]} ${anio})`;
    
    // Cargar gráfica con parámetros específicos
    cargarGraficaIngresosEgresos(1, mes, anio);
}

// Función para limpiar búsqueda
function limpiarBusqueda() {
    mesBusquedaActual = null;
    anioBusquedaActual = null;
    document.getElementById('mesBusqueda').value = '';
    document.getElementById('anioBusqueda').value = '';
    
    // Volver a la vista por defecto (6 meses)
    periodoActualComparativa = 6;
    document.getElementById('tituloComparativa').textContent = 'Ingresos vs Egresos (Últimos 6 Meses)';
    
    // Reactivar botón 6 meses
    document.querySelectorAll('.btn-group .btn-outline-danger').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById('btn6meses').classList.add('active');
    
    cargarGraficaIngresosEgresos(6);
}

// Función para imprimir comparativa
function imprimirComparativa() {
    // Construir ruta relativa desde el index.php hasta el archivo de generación
    let url = `generate_comparativa_dashboard.php?formato=html`;
    
    if (mesBusquedaActual && anioBusquedaActual) {
        // Si hay búsqueda específica, usar esos parámetros
        url += `&mes=${mesBusquedaActual}&anio=${anioBusquedaActual}`;
    } else {
        // Si no, usar el período actual
        url += `&meses=${periodoActualComparativa}`;
    }
    
    window.open(url, '_blank');
}

// Función para cargar resumen mensual (tarjetas superiores)
function cargarResumenMensual() {
    ajaxCall('dashboard', 'getResumenMensual', {}, 'GET')
        .done(function(data) {
            if (data.success) {
                const formatter = new Intl.NumberFormat('es-MX', {
                    style: 'currency',
                    currency: 'MXN'
                });
                
                $('#totalIngresosMes').text(formatter.format(data.ingresos));
                $('#totalEgresosMes').text(formatter.format(data.egresos));
                $('#balanceMes').text(formatter.format(data.balance));
                
                // Cambiar color del balance según sea positivo o negativo
                if (data.balance >= 0) {
                    $('#balanceMes').removeClass('text-danger').addClass('text-success');
                } else {
                    $('#balanceMes').removeClass('text-success').addClass('text-danger');
                }
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar resumen mensual:', xhr);
        });
}

// Función para cargar gráfica de ingresos vs egresos
function cargarGraficaIngresosEgresos(meses = 6, mesEspecifico = null, anioEspecifico = null) {
    let params = {};
    
    if (mesEspecifico && anioEspecifico) {
        // Búsqueda específica por mes/año
        params = { mes: mesEspecifico, anio: anioEspecifico };
    } else {
        // Búsqueda por rango de meses
        params = { meses: meses };
    }
    
    ajaxCall('dashboard', 'getIngresosEgresosPorMes', params, 'GET')
        .done(function(data) {
            if (data.success) {
                const ctx = document.getElementById('chartIngresosEgresos').getContext('2d');
                
                // Destruir gráfica anterior si existe
                if (chartIngresosEgresosInstance) {
                    chartIngresosEgresosInstance.destroy();
                }
                
                chartIngresosEgresosInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.meses,
                        datasets: [
                            {
                                label: 'Ingresos',
                                data: data.ingresos,
                                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                                borderColor: 'rgba(40, 167, 69, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Egresos',
                                data: data.egresos,
                                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 1
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
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar gráfica ingresos/egresos:', xhr);
        });
}

// Función para cargar gráfica de ingresos por categoría
function cargarGraficaIngresosCategorias() {
    ajaxCall('dashboard', 'getIngresosPorCategoria', {}, 'GET')
        .done(function(data) {
            if (data.success && data.categorias.length > 0) {
                const ctx = document.getElementById('chartIngresosCategorias').getContext('2d');
                new Chart(ctx, {
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
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('chartIngresosCategorias').parentElement.innerHTML = 
                    '<p class="text-center text-muted py-5">No hay datos de ingresos por categoría</p>';
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar gráfica ingresos por categoría:', xhr);
        });
}

// Función para cargar gráfica de egresos por categoría
function cargarGraficaEgresosCategorias() {
    ajaxCall('dashboard', 'getEgresosPorCategoria', {}, 'GET')
        .done(function(data) {
            if (data.success && data.categorias.length > 0) {
                const ctx = document.getElementById('chartEgresosCategorias').getContext('2d');
                new Chart(ctx, {
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
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('chartEgresosCategorias').parentElement.innerHTML = 
                    '<p class="text-center text-muted py-5">No hay datos de egresos por categoría</p>';
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar gráfica egresos por categoría:', xhr);
        });
}

// Función para cargar alertas presupuestales
function cargarAlertasPresupuesto() {
    ajaxCall('dashboard', 'getAlertasPresupuesto', {}, 'GET')
        .done(function(data) {
            if (data.success) {
                const $container = $('#alertasPresupuesto');
                
                if (data.alertas.length === 0) {
                    $container.html(`
                        <div class="text-center text-success py-3">
                            <ion-icon name="checkmark-circle-outline" style="font-size: 3rem;"></ion-icon>
                            <p class="mt-2 mb-0">Todos los presupuestos están bajo control</p>
                        </div>
                    `);
                } else {
                    let html = '';
                    data.alertas.forEach(function(alerta) {
                        let colorClass = 'success';
                        let icon = 'checkmark-circle';
                        
                        if (alerta.porcentaje >= 90) {
                            colorClass = 'danger';
                            icon = 'alert-circle';
                        } else if (alerta.porcentaje >= 70) {
                            colorClass = 'warning';
                            icon = 'warning';
                        }
                        
                        const formatter = new Intl.NumberFormat('es-MX', {
                            style: 'currency',
                            currency: 'MXN'
                        });
                        
                        html += `
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">${alerta.categoria}</h6>
                                    <span class="badge bg-${colorClass}">
                                        <ion-icon name="${icon}" style="vertical-align: middle;"></ion-icon>
                                        ${alerta.porcentaje}%
                                    </span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-${colorClass}" role="progressbar" 
                                         style="width: ${alerta.porcentaje}%" 
                                         aria-valuenow="${alerta.porcentaje}" aria-valuemin="0" aria-valuemax="100">
                                        ${formatter.format(alerta.gastado)}
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Presupuesto: ${formatter.format(alerta.presupuesto)}
                                </small>
                            </div>
                        `;
                    });
                    $container.html(html);
                }
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar alertas presupuesto:', xhr);
        });
}

// Función para cargar tendencia de balance
function cargarTendenciaBalance() {
    ajaxCall('dashboard', 'getTendenciaBalance', {}, 'GET')
        .done(function(data) {
            if (data.success) {
                const ctx = document.getElementById('chartTendenciaBalance').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.meses,
                        datasets: [{
                            label: 'Balance Mensual',
                            data: data.balances,
                            borderColor: 'rgba(184, 0, 0, 1)',
                            backgroundColor: 'rgba(184, 0, 0, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
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
                                        return 'Balance: ' + new Intl.NumberFormat('es-MX', {
                                            style: 'currency',
                                            currency: 'MXN'
                                        }).format(context.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString('es-MX');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        })
        .fail(function(xhr) {
            console.error('Error al cargar tendencia de balance:', xhr);
        });
}
</script>
