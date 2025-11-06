<?php
// views/ingresos_list.php (MOSTRANDO TODOS LOS CAMPOS)
// Llamada por IngresoController->index()
// Variables disponibles: $pageTitle, $activeModule, $ingresos, $currentUser (del layout)

// Helper para obtener el nombre de la categoría (acepta distintos esquemas: 'id'|'id_categoria' y 'nombre'|'nombre_categoria')
// Nota: Sería más eficiente hacer el JOIN en el Modelo como en Egresos, pero mantenemos esto en la vista
// para evitar tocar la lógica del controlador por ahora.
function obtenerNombreCategoria($idCategoria, $categorias) {
    if (empty($categorias) || !$idCategoria) return 'Desconocida';
    foreach ($categorias as $cat) {
        if ((isset($cat['id']) && $cat['id'] == $idCategoria) || (isset($cat['id_categoria']) && $cat['id_categoria'] == $idCategoria)) {
            return $cat['nombre'] ?? $cat['nombre_categoria'] ?? 'Desconocida';
        }
    }
    return 'Desconocida';
}

// Obtener categorías una vez (asumiendo que el controlador las pasa)
// Si no, necesitaríamos modificar el controlador para pasar $categorias a esta vista.
// $categorias = $categorias ?? []; // Descomentar si el controlador pasa 'categorias'

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
                        <th class="d-none d-md-table-cell">ID</th>
                        <th>Alumno</th>
                        <th class="d-none d-md-table-cell">Matrícula</th>
                        <th class="d-none d-md-table-cell">Nivel</th>
                        <th class="d-none d-md-table-cell">Programa</th>
                        <th class="d-none d-md-table-cell">Grado</th>
                        <th class="d-none d-md-table-cell">Grupo</th>
                        <th class="d-none d-md-table-cell">Modalidad</th>
                        <th class="d-none d-md-table-cell">Categoría</th>
                        <th>Concepto</th>
                        <th class="d-none d-md-table-cell">Mes Corresp.</th>
                        <th class="d-none d-md-table-cell">Año</th>
                        <th class="d-none d-md-table-cell">Método Pago</th>
                        <th class="d-none d-md-table-cell">Día Pago</th>
                        <th class="d-none d-md-table-cell">Observaciones</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaIngresos">
                    <?php if (empty($ingresos)): ?>
                        
                        <tr><td colspan="18" class="text-center p-4 text-muted">No hay ingresos registrados.</td></tr>
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

                            // Obtener nombre de categoría si el controlador pasó $categorias
                            if (!empty($categorias) && isset($ingreso['id_categoria'])) {
                                $nombreCategoria = obtenerNombreCategoria($ingreso['id_categoria'], $categorias);
                                $nombreCategoria = htmlspecialchars($nombreCategoria);
                            } else {
                                $nombreCategoria = htmlspecialchars($ingreso['id_categoria'] ?? 'N/A');
                            }

                            // Preparar descripción corta con tooltip
                            $observacionesCompleta = htmlspecialchars($ingreso['observaciones'] ?? '');
                            $observacionesCorta = mb_strimwidth($observacionesCompleta, 0, 25, '...');
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ingreso['fecha'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['alumno'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['matricula'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['nivel'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['programa'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['grado'] ?? '-'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['grupo'] ?? '-'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['modalidad'] ?? '-'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo $nombreCategoria; ?></td> 
                                <td><?php echo htmlspecialchars($ingreso['concepto'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['mes_correspondiente'] ?? '-'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['anio'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['metodo_de_pago'] ?? 'N/A'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($ingreso['dia_pago'] ?? '-'); ?></td>
                                <td class="d-none d-md-table-cell" title="<?php echo $observacionesCompleta; ?>">
                                    <?php echo $observacionesCorta ?: '-'; ?>
                                </td>
                                <td class="text-end text-success fw-bold"><?php echo $montoFormateado; ?></td>
                                <td class="text-center">
                                    <div class="btn-responsive-sm">
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
                                                <a class="btn btn-sm btn-primary ms-1" href="<?php echo 'generate_receipt.php?folio=' . urlencode($ingreso['folio_ingreso'] ?? $ingreso['id'] ?? 0); ?>" target="_blank" title="Generar Recibo">
                                                    <ion-icon name="document-text-outline"></ion-icon>
                                                </a>
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