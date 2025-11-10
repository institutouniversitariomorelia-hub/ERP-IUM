<!-- views/reportes.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>
                Generación de Reportes
            </h2>
        </div>
    </div>

    <!-- Tabs de navegación -->
    <ul class="nav nav-tabs mb-4" id="reportesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ingresos-tab" data-bs-toggle="tab" data-bs-target="#ingresos-panel" type="button" role="tab">
                <i class="bi bi-arrow-down-circle text-success me-1"></i>
                Ingresos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="egresos-tab" data-bs-toggle="tab" data-bs-target="#egresos-panel" type="button" role="tab">
                <i class="bi bi-arrow-up-circle text-danger me-1"></i>
                Egresos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="consolidado-tab" data-bs-toggle="tab" data-bs-target="#consolidado-panel" type="button" role="tab">
                <i class="bi bi-graph-up me-1"></i>
                Consolidado
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reportesTabContent">
        <!-- Panel de Ingresos -->
        <div class="tab-pane fade show active" id="ingresos-panel" role="tabpanel">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <button class="btn btn-success w-100 mb-2" onclick="generarReporteIngresos('semanal')">
                                    <i class="bi bi-calendar-week me-2"></i>Reporte Semanal
                                </button>
                                <small class="text-muted d-block text-center">Últimos 7 días</small>
                            </div>
                            
                            <div class="mb-3">
                                <button class="btn btn-success w-100 mb-2" onclick="generarReporteIngresos('mensual')">
                                    <i class="bi bi-calendar-month me-2"></i>Reporte Mensual
                                </button>
                                <small class="text-muted d-block text-center">Mes actual</small>
                            </div>

                            <hr>

                            <form id="formReporteIngresosPersonalizado" onsubmit="generarReporteIngresosPersonalizado(event)">
                                <h6 class="mb-3"><i class="bi bi-calendar-range me-2"></i>Rango Personalizado</h6>
                                
                                <div class="mb-3">
                                    <label for="ingresos_fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="ingresos_fecha_inicio" name="fecha_inicio" autocomplete="off" required>
                                </div>

                                <div class="mb-3">
                                    <label for="ingresos_fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="ingresos_fecha_fin" name="fecha_fin" autocomplete="off" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Generar Reporte
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 mb-4">
                    <div id="resultadoIngresosContainer" style="display: none;">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Reporte de Ingresos</h5>
                                <button class="btn btn-light btn-sm" onclick="imprimirReporteIngresos()">
                                    <i class="bi bi-printer me-1"></i>Imprimir
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="headerIngresos" class="mb-3"></div>
                                <div id="resumenIngresos" class="mb-4"></div>
                                <div id="graficaIngresos" class="mb-4">
                                    <canvas id="chartIngresos" style="max-height: 300px;"></canvas>
                                </div>
                                <div id="tablaIngresos"></div>
                            </div>
                        </div>
                    </div>
                    <div id="placeholderIngresos" class="text-center text-muted py-5">
                        <i class="bi bi-file-earmark-bar-graph" style="font-size: 4rem;"></i>
                        <p class="mt-3">Selecciona un tipo de reporte para ver los resultados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Egresos -->
        <div class="tab-pane fade" id="egresos-panel" role="tabpanel">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <button class="btn btn-danger w-100 mb-2" onclick="generarReporteEgresos('semanal')">
                                    <i class="bi bi-calendar-week me-2"></i>Reporte Semanal
                                </button>
                                <small class="text-muted d-block text-center">Últimos 7 días</small>
                            </div>
                            
                            <div class="mb-3">
                                <button class="btn btn-danger w-100 mb-2" onclick="generarReporteEgresos('mensual')">
                                    <i class="bi bi-calendar-month me-2"></i>Reporte Mensual
                                </button>
                                <small class="text-muted d-block text-center">Mes actual</small>
                            </div>

                            <hr>

                            <form id="formReporteEgresosPersonalizado" onsubmit="generarReporteEgresosPersonalizado(event)">
                                <h6 class="mb-3"><i class="bi bi-calendar-range me-2"></i>Rango Personalizado</h6>
                                
                                <div class="mb-3">
                                    <label for="egresos_fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="egresos_fecha_inicio" name="fecha_inicio" autocomplete="off" required>
                                </div>

                                <div class="mb-3">
                                    <label for="egresos_fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="egresos_fecha_fin" name="fecha_fin" autocomplete="off" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Generar Reporte
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 mb-4">
                    <div id="resultadoEgresosContainer" style="display: none;">
                        <div class="card shadow-sm">
                            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Reporte de Egresos</h5>
                                <button class="btn btn-light btn-sm" onclick="imprimirReporteEgresos()">
                                    <i class="bi bi-printer me-1"></i>Imprimir
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="headerEgresos" class="mb-3"></div>
                                <div id="resumenEgresos" class="mb-4"></div>
                                <div id="graficaEgresos" class="mb-4">
                                    <canvas id="chartEgresos" style="max-height: 300px;"></canvas>
                                </div>
                                <div id="tablaEgresos"></div>
                            </div>
                        </div>
                    </div>
                    <div id="placeholderEgresos" class="text-center text-muted py-5">
                        <i class="bi bi-file-earmark-bar-graph" style="font-size: 4rem;"></i>
                        <p class="mt-3">Selecciona un tipo de reporte para ver los resultados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Consolidado -->
        <div class="tab-pane fade" id="consolidado-panel" role="tabpanel">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <button class="btn btn-primary w-100 mb-2" onclick="generarReporteConsolidado('semanal')">
                                    <i class="bi bi-calendar-week me-2"></i>Reporte Semanal
                                </button>
                                <small class="text-muted d-block text-center">Últimos 7 días</small>
                            </div>
                            
                            <div class="mb-3">
                                <button class="btn btn-primary w-100 mb-2" onclick="generarReporteConsolidado('mensual')">
                                    <i class="bi bi-calendar-month me-2"></i>Reporte Mensual
                                </button>
                                <small class="text-muted d-block text-center">Mes actual</small>
                            </div>

                            <hr>

                            <form id="formReporteConsolidadoPersonalizado" onsubmit="generarReporteConsolidadoPersonalizado(event)">
                                <h6 class="mb-3"><i class="bi bi-calendar-range me-2"></i>Rango Personalizado</h6>
                                
                                <div class="mb-3">
                                    <label for="consolidado_fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="consolidado_fecha_inicio" name="fecha_inicio" autocomplete="off" required>
                                </div>

                                <div class="mb-3">
                                    <label for="consolidado_fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="consolidado_fecha_fin" name="fecha_fin" autocomplete="off" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Generar Reporte
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 mb-4">
                    <div id="resultadoConsolidadoContainer" style="display: none;">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Reporte Consolidado</h5>
                                <button class="btn btn-light btn-sm" onclick="imprimirReporteConsolidado()">
                                    <i class="bi bi-printer me-1"></i>Imprimir
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="headerConsolidado" class="mb-3"></div>
                                <div id="resumenConsolidado" class="mb-4"></div>
                                <div id="graficaConsolidado" class="mb-4">
                                    <canvas id="chartConsolidado" style="max-height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="placeholderConsolidado" class="text-center text-muted py-5">
                        <i class="bi bi-file-earmark-bar-graph" style="font-size: 4rem;"></i>
                        <p class="mt-3">Selecciona un tipo de reporte para ver los resultados</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let chartIngresos = null;
let chartEgresos = null;
let chartConsolidado = null;

// Variables para almacenar datos de reportes
let datosReporteIngresos = null;
let datosReporteEgresos = null;
let datosReporteConsolidado = null;

// Función de notificación simple
function showNotification(message, type = 'info') {
    alert(message);
}

// ========== FUNCIONES PARA INGRESOS ==========
function generarReporteIngresos(tipo) {
    const url = `<?php echo BASE_URL; ?>index.php?controller=reporte&action=generarIngresos&tipo=${tipo}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosReporteIngresos = data;
                mostrarReporteIngresos(data);
            } else {
                showNotification(data.error || 'Error al generar reporte', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'danger');
        });
}

function generarReporteIngresosPersonalizado(event) {
    event.preventDefault();
    const fechaInicio = document.getElementById('ingresos_fecha_inicio').value;
    const fechaFin = document.getElementById('ingresos_fecha_fin').value;
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        showNotification('La fecha de inicio no puede ser mayor a la fecha fin', 'warning');
        return;
    }
    
    const url = `<?php echo BASE_URL; ?>index.php?controller=reporte&action=generarIngresos&tipo=personalizado&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosReporteIngresos = data;
                mostrarReporteIngresos(data);
            } else {
                showNotification(data.error || 'Error al generar reporte', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'danger');
        });
}

function mostrarReporteIngresos(data) {
    document.getElementById('placeholderIngresos').style.display = 'none';
    document.getElementById('resultadoIngresosContainer').style.display = 'block';
    
    // Header
    let tipoTexto = data.tipo === 'semanal' ? 'Semanal (últimos 7 días)' : 
                    data.tipo === 'mensual' ? 'Mensual (mes actual)' : 
                    'Personalizado';
    
    document.getElementById('headerIngresos').innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Tipo: <span class="badge bg-success">${tipoTexto}</span></h6>
                <p class="mb-0 text-muted">Período: ${formatDate(data.fechaInicio)} - ${formatDate(data.fechaFin)}</p>
            </div>
        </div>
    `;
    
    // Resumen
    document.getElementById('resumenIngresos').innerHTML = `
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Ingresos</h6>
                        <h3 class="text-success mb-0">$${formatMoney(data.total)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Cantidad</h6>
                        <h3 class="text-primary mb-0">${data.cantidad}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Promedio</h6>
                        <h3 class="text-info mb-0">$${formatMoney(data.cantidad > 0 ? data.total / data.cantidad : 0)}</h3>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Gráfica por categoría
    renderChartIngresos(data.porCategoria);
    
    // Tabla de ingresos
    let tablaHTML = `
        <h6 class="mb-3">Detalle de Ingresos</h6>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-success">
                    <tr>
                        <th>Fecha</th>
                        <th>Alumno</th>
                        <th>Concepto</th>
                        <th>Categoría</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    if (data.ingresos.length > 0) {
        data.ingresos.forEach(ingreso => {
            tablaHTML += `
                <tr>
                    <td>${formatDate(ingreso.fecha)}</td>
                    <td>${ingreso.alumno || '-'}</td>
                    <td>${ingreso.concepto || '-'}</td>
                    <td>${ingreso.nombre_categoria || '-'}</td>
                    <td class="text-end">$${formatMoney(ingreso.monto)}</td>
                </tr>
            `;
        });
    } else {
        tablaHTML += '<tr><td colspan="5" class="text-center text-muted">No hay ingresos en este período</td></tr>';
    }
    
    tablaHTML += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('tablaIngresos').innerHTML = tablaHTML;
}

function renderChartIngresos(porCategoria) {
    const ctx = document.getElementById('chartIngresos');
    
    if (chartIngresos) {
        chartIngresos.destroy();
    }
    
    const labels = Object.keys(porCategoria);
    const datos = Object.values(porCategoria);
    
    chartIngresos = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: datos,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right'
                },
                title: {
                    display: true,
                    text: 'Ingresos por Categoría'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': $' + formatMoney(context.parsed);
                        }
                    }
                }
            }
        }
    });
}

function imprimirReporteIngresos() {
    window.print();
}

// ========== FUNCIONES PARA EGRESOS ==========
function generarReporteEgresos(tipo) {
    const url = `<?php echo BASE_URL; ?>index.php?controller=reporte&action=generarEgresos&tipo=${tipo}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosReporteEgresos = data;
                mostrarReporteEgresos(data);
            } else {
                showNotification(data.error || 'Error al generar reporte', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'danger');
        });
}

function generarReporteEgresosPersonalizado(event) {
    event.preventDefault();
    const fechaInicio = document.getElementById('egresos_fecha_inicio').value;
    const fechaFin = document.getElementById('egresos_fecha_fin').value;
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        showNotification('La fecha de inicio no puede ser mayor a la fecha fin', 'warning');
        return;
    }
    
    const url = `<?php echo BASE_URL; ?>index.php?controller=reporte&action=generarEgresos&tipo=personalizado&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosReporteEgresos = data;
                mostrarReporteEgresos(data);
            } else {
                showNotification(data.error || 'Error al generar reporte', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'danger');
        });
}

function mostrarReporteEgresos(data) {
    document.getElementById('placeholderEgresos').style.display = 'none';
    document.getElementById('resultadoEgresosContainer').style.display = 'block';
    
    let tipoTexto = data.tipo === 'semanal' ? 'Semanal (últimos 7 días)' : 
                    data.tipo === 'mensual' ? 'Mensual (mes actual)' : 
                    'Personalizado';
    
    document.getElementById('headerEgresos').innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Tipo: <span class="badge bg-danger">${tipoTexto}</span></h6>
                <p class="mb-0 text-muted">Período: ${formatDate(data.fechaInicio)} - ${formatDate(data.fechaFin)}</p>
            </div>
        </div>
    `;
    
    document.getElementById('resumenEgresos').innerHTML = `
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Egresos</h6>
                        <h3 class="text-danger mb-0">$${formatMoney(data.total)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Cantidad</h6>
                        <h3 class="text-primary mb-0">${data.cantidad}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Promedio</h6>
                        <h3 class="text-info mb-0">$${formatMoney(data.cantidad > 0 ? data.total / data.cantidad : 0)}</h3>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    renderChartEgresos(data.porCategoria);
    
    let tablaHTML = `
        <h6 class="mb-3">Detalle de Egresos</h6>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-danger">
                    <tr>
                        <th>Fecha</th>
                        <th>Destinatario</th>
                        <th>Concepto</th>
                        <th>Categoría</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    if (data.egresos.length > 0) {
        data.egresos.forEach(egreso => {
            tablaHTML += `
                <tr>
                    <td>${formatDate(egreso.fecha)}</td>
                    <td>${egreso.destinatario || '-'}</td>
                    <td>${egreso.concepto || '-'}</td>
                    <td>${egreso.nombre_categoria || '-'}</td>
                    <td class="text-end">$${formatMoney(egreso.monto)}</td>
                </tr>
            `;
        });
    } else {
        tablaHTML += '<tr><td colspan="5" class="text-center text-muted">No hay egresos en este período</td></tr>';
    }
    
    tablaHTML += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('tablaEgresos').innerHTML = tablaHTML;
}

function renderChartEgresos(porCategoria) {
    const ctx = document.getElementById('chartEgresos');
    
    if (chartEgresos) {
        chartEgresos.destroy();
    }
    
    const labels = Object.keys(porCategoria);
    const datos = Object.values(porCategoria);
    
    chartEgresos = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: datos,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right'
                },
                title: {
                    display: true,
                    text: 'Egresos por Categoría'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': $' + formatMoney(context.parsed);
                        }
                    }
                }
            }
        }
    });
}

function imprimirReporteEgresos() {
    window.print();
}

// ========== FUNCIONES PARA CONSOLIDADO ==========
function generarReporteConsolidado(tipo) {
    const url = `<?php echo BASE_URL; ?>index.php?controller=reporte&action=generarConsolidado&tipo=${tipo}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosReporteConsolidado = data;
                mostrarReporteConsolidado(data);
            } else {
                showNotification(data.error || 'Error al generar reporte', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'danger');
        });
}

function generarReporteConsolidadoPersonalizado(event) {
    event.preventDefault();
    const fechaInicio = document.getElementById('consolidado_fecha_inicio').value;
    const fechaFin = document.getElementById('consolidado_fecha_fin').value;
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        showNotification('La fecha de inicio no puede ser mayor a la fecha fin', 'warning');
        return;
    }
    
    const url = `<?php echo BASE_URL; ?>index.php?controller=reporte&action=generarConsolidado&tipo=personalizado&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosReporteConsolidado = data;
                mostrarReporteConsolidado(data);
            } else {
                showNotification(data.error || 'Error al generar reporte', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'danger');
        });
}

function mostrarReporteConsolidado(data) {
    document.getElementById('placeholderConsolidado').style.display = 'none';
    document.getElementById('resultadoConsolidadoContainer').style.display = 'block';
    
    let tipoTexto = data.tipo === 'semanal' ? 'Semanal (últimos 7 días)' : 
                    data.tipo === 'mensual' ? 'Mensual (mes actual)' : 
                    'Personalizado';
    
    document.getElementById('headerConsolidado').innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Tipo: <span class="badge bg-primary">${tipoTexto}</span></h6>
                <p class="mb-0 text-muted">Período: ${formatDate(data.fechaInicio)} - ${formatDate(data.fechaFin)}</p>
            </div>
        </div>
    `;
    
    const balanceClass = data.balance >= 0 ? 'text-success' : 'text-danger';
    const balanceIcon = data.balance >= 0 ? '↑' : '↓';
    
    document.getElementById('resumenConsolidado').innerHTML = `
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Ingresos</h6>
                        <h3 class="text-success mb-0">$${formatMoney(data.totalIngresos)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Egresos</h6>
                        <h3 class="text-danger mb-0">$${formatMoney(data.totalEgresos)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Balance</h6>
                        <h3 class="${balanceClass} mb-0">${balanceIcon} $${formatMoney(Math.abs(data.balance))}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Estado</h6>
                        <h5 class="mb-0">
                            ${data.balance >= 0 
                                ? '<span class="badge bg-success">Superávit</span>' 
                                : '<span class="badge bg-danger">Déficit</span>'}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    renderChartConsolidado(data);
}

function renderChartConsolidado(data) {
    const ctx = document.getElementById('chartConsolidado');
    
    if (chartConsolidado) {
        chartConsolidado.destroy();
    }
    
    chartConsolidado = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Ingresos', 'Egresos', 'Balance'],
            datasets: [{
                label: 'Montos',
                data: [data.totalIngresos, data.totalEgresos, Math.abs(data.balance)],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    data.balance >= 0 ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 159, 64, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Resumen Financiero'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Monto: $' + formatMoney(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + formatMoney(value);
                        }
                    }
                }
            }
        }
    });
}

function exportarConsolidadoExcel() {
    if (!datosReporteConsolidado) {
        alert('No hay datos de reporte para exportar');
        return;
    }
    
    let csv = '\uFEFF';
    csv += 'Reporte Consolidado\n';
    csv += `Tipo: ${datosReporteConsolidado.tipo === 'semanal' ? 'Semanal' : datosReporteConsolidado.tipo === 'mensual' ? 'Mensual' : 'Personalizado'}\n`;
    csv += `Período: ${formatDate(datosReporteConsolidado.fechaInicio)} - ${formatDate(datosReporteConsolidado.fechaFin)}\n`;
    csv += `Total Ingresos: $${formatMoney(datosReporteConsolidado.totalIngresos)}\n`;
    csv += `Total Egresos: $${formatMoney(datosReporteConsolidado.totalEgresos)}\n`;
    csv += `Balance: $${formatMoney(Math.abs(datosReporteConsolidado.balance))}\n`;
    csv += `Estado: ${datosReporteConsolidado.balance >= 0 ? 'Superávit' : 'Déficit'}\n`;
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `Reporte_Consolidado_${datosReporteConsolidado.tipo}_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
}

function imprimirReporteConsolidado() {
    const graficas = document.querySelectorAll('#contenidoReporteConsolidado .grafica-reporte');
    graficas.forEach(g => g.style.display = 'none');
    window.print();
    setTimeout(() => graficas.forEach(g => g.style.display = 'block'), 100);
}

// ========== FUNCIONES AUXILIARES ==========
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatMoney(amount) {
    return parseFloat(amount).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<style>
@media print {
    /* Ocultar elementos de navegación y botones */
    #sidebar, 
    .top-header, 
    .btn, 
    .nav-tabs, 
    .card-header .btn,
    .collapse:not(.show) {
        display: none !important;
    }
    
    /* Ocultar gráficas durante impresión */
    .grafica-reporte {
        display: none !important;
    }
    
    /* Mostrar contenido colapsado */
    .collapse {
        display: block !important;
        height: auto !important;
    }
    
    /* Estilos para el contenedor principal */
    body {
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .main-content {
        margin: 0 !important;
        padding: 15px !important;
        width: 100% !important;
    }
    
    /* Estilos para tarjetas */
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        page-break-inside: avoid;
        margin-bottom: 15px !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 2px solid #dee2e6 !important;
        padding: 10px 15px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .card-body {
        padding: 15px !important;
    }
    
    /* Estilos para tablas */
    table {
        width: 100% !important;
        font-size: 9pt !important;
        border-collapse: collapse !important;
    }
    
    table thead {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    table th, 
    table td {
        padding: 6px 8px !important;
        border: 1px solid #dee2e6 !important;
        text-align: left !important;
    }
    
    table th {
        font-weight: bold !important;
        font-size: 9pt !important;
    }
    
    /* Estilos para badges */
    .badge {
        border: 1px solid #000 !important;
        padding: 2px 6px !important;
        font-size: 8pt !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .badge.bg-success {
        background-color: #198754 !important;
        color: white !important;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }
    
    /* Encabezados */
    h2, h3, h4, h5 {
        page-break-after: avoid;
        margin-top: 10px !important;
        margin-bottom: 8px !important;
    }
    
    h2 {
        font-size: 16pt !important;
    }
    
    h3 {
        font-size: 14pt !important;
    }
    
    h5 {
        font-size: 11pt !important;
    }
    
    /* Evitar saltos de página dentro de elementos */
    tr, 
    .alert,
    .card {
        page-break-inside: avoid;
    }
    
    /* Márgenes de página */
    @page {
        margin: 1.5cm;
    }
}
</style>
