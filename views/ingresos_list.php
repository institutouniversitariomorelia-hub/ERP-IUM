<?php
// views/ingresos_list.php (MOSTRANDO TODOS LOS CAMPOS)
// Llamada por IngresoController->index()
// Variables disponibles: $pageTitle, $activeModule, $ingresos, $currentUser (del layout)

// Helper para obtener el nombre de la categoría (necesitamos $categorias del controlador)
// Nota: Sería más eficiente hacer el JOIN en el Modelo como en Egresos.
function obtenerNombreCategoria($idCategoria, $categorias) {
    foreach ($categorias as $cat) {
        if ($cat['id'] == $idCategoria) {
            return $cat['nombre'];
        }
    }
    return 'Desconocida'; // O el ID si prefieres: $idCategoria;
}

// Obtener categorías una vez (asumiendo que el controlador las pasa)
// Si no, necesitaríamos modificar el controlador para pasar $categorias a esta vista.
// $categorias = $categorias ?? []; // Descomentar si el controlador pasa 'categorias'

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle ?? 'Ingresos'); ?>: Historial Reciente</h3>
    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalIngreso" id="btnNuevoIngreso">
        <ion-icon name="add-circle-outline" class="me-1"></ion-icon> Agregar Ingreso
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            
            <table class="table table-hover table-sm mb-0" style="font-size: 0.8em; white-space: nowrap;">
                <thead>
                    <tr class="table-light">
                        <th>Fecha</th>
                        <th>ID</th> 
                        <th>Alumno</th>
                        <th>Matrícula</th>
                        <th>Nivel</th>
                        <th>Programa</th>
                        <th>Grado</th>
                        <th>Grupo</th>
                        <th>Modalidad</th>
                        <th>Categoría</th> 
                        <th>Concepto</th>
                        <th>Mes Corresp.</th>
                        <th>Año</th>
                        <th>Método Pago</th>
                        <th>Día Pago</th>
                        <th>Observaciones</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaIngresos">
                    <?php if (empty($ingresos)): ?>
                        
                        <tr><td colspan="18" class="text-center p-4 text-muted">No hay ingresos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ingresos as $ingreso):
                            $monto = $ingreso['monto'] ?? 0;
                            $montoFormateado = number_format($monto, 2);
                            try {
                                if (class_exists('NumberFormatter')) {
                                     $formatter = new NumberFormatter('es-MX', NumberFormatter::CURRENCY);
                                     if (is_numeric($monto)) {
                                         $montoFormateado = $formatter->formatCurrency($monto, 'MXN');
                                     }
                                }
                            } catch (Exception $e) { /* Ignorar */ }

                            // Obtener nombre de categoría (requiere $categorias)
                            // **IMPORTANTE**: Para que esto funcione, IngresoController DEBE pasar
                            // la lista de categorías a esta vista, similar a como lo hace para el modal.
                            // Si no, mostramos el ID.
                            // $nombreCategoria = obtenerNombreCategoria($ingreso['id_categoria'] ?? 0, $categorias);
                            $nombreCategoria = htmlspecialchars($ingreso['id_categoria'] ?? 'N/A'); // Temporal: Muestra ID

                            // Preparar descripción corta con tooltip
                            $observacionesCompleta = htmlspecialchars($ingreso['observaciones'] ?? '');
                            $observacionesCorta = mb_strimwidth($observacionesCompleta, 0, 25, '...');
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ingreso['fecha'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['alumno'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['matricula'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['nivel'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['programa'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['grado'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['grupo'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['modalidad'] ?? '-'); ?></td>
                                <td><?php echo $nombreCategoria; ?></td> 
                                <td><?php echo htmlspecialchars($ingreso['concepto'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['mes_correspondiente'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['anio'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['metodo_de_pago'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ingreso['dia_pago'] ?? '-'); ?></td>
                                <td title="<?php echo $observacionesCompleta; ?>">
                                    <?php echo $observacionesCorta ?: '-'; ?>
                                </td>
                                <td class="text-end text-success fw-bold"><?php echo $montoFormateado; ?></td>
                                <td class="text-center">
                                    
                                    <button class="btn btn-sm btn-warning btn-edit-ingreso"
                                            data-id="<?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 0); ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalIngreso"
                                            title="Editar Ingreso">
                                         <ion-icon name="create-outline"></ion-icon>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-del-ingreso"
                                            data-id="<?php echo htmlspecialchars($ingreso['folio_ingreso'] ?? 0); ?>"
                                            title="Eliminar Ingreso">
                                        <ion-icon name="trash-outline"></ion-icon>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>