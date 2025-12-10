
<?php
// src/Presupuestos/Controllers/PresupuestoController.php

require_once __DIR__ . '/../Models/PresupuestoModel.php';
require_once __DIR__ . '/../../Categorias/Models/CategoriaModel.php';
require_once __DIR__ . '/../../Auditoria/Models/AuditoriaModel.php';

class PresupuestoController {
    private $db;
    private $presupuestoModel;
    private $categoriaModel;
    private $auditoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->presupuestoModel = new PresupuestoModel($dbConnection);
        $this->categoriaModel = new CategoriaModel($dbConnection);
        $this->auditoriaModel = new AuditoriaModel($dbConnection);
    }

    /**
     * Acción principal: Muestra la lista de presupuestos.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login'); exit; }

        $presupuestos = $this->presupuestoModel->getAllPresupuestos();
        
        // Calcular gastado para cada presupuesto
        foreach ($presupuestos as &$p) {
            $p['gastado'] = $this->presupuestoModel->getGastadoEnPresupuesto($p['id_presupuesto']);
            $p['porcentaje'] = $p['monto_limite'] > 0 ? round(($p['gastado'] / $p['monto_limite']) * 100, 2) : 0;
        }

        $pageTitle = "Presupuestos";
        $activeModule = "presupuestos";

        $this->renderView('presupuestos_list', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'presupuestos' => $presupuestos
        ]);
    }

    /**
     * Acción AJAX: Guarda/Actualiza un presupuesto.
     */
    public function save() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        // (debug logging removed)

        $data = $_POST;
        // <-- ¡CORRECCIÓN 1: AÑADIR EL ID DE USUARIO DE LA SESIÓN!
        $data['id_user'] = $_SESSION['user_id'];
        
        // Permitir que el id venga como 'id', 'id_presupuesto' o 'presupuesto_id' (según el formulario)
        $id = $data['id'] ?? $data['id_presupuesto'] ?? $data['presupuesto_id'] ?? null;
        $response = ['success' => false];

        // Validación básica (ajustada a nuestra BD)
        // El formulario envía el campo como 'monto' (name="monto") — mapear a 'monto_limite' para el modelo
        if (isset($data['monto'])) {
            $data['monto_limite'] = $data['monto'];
        }

        // Normalización robusta de id_categoria
        // Acepta numérico como string; si viene texto accidental, extrae dígitos
        if (!isset($data['id_categoria']) && isset($data['categoria'])) {
            $data['id_categoria'] = $data['categoria'];
        }
        $rawCat = $data['id_categoria'] ?? '';
        if (!is_numeric($rawCat)) {
            $rawCat = preg_replace('/\D+/', '', (string)$rawCat);
        }
        $data['id_categoria'] = (int)($rawCat !== '' ? $rawCat : 0);

       // Validaciones: monto obligatorio. Fecha se asigna automáticamente si no viene (nuevo esquema)
       if (empty($data['monto_limite'])) {
           $response['error'] = 'Monto límite es obligatorio.';
             echo json_encode($response);
             exit;
         }
        // Si no se envió fecha (nuevo esquema), asignar la fecha actual
        if (empty($data['fecha'])) {
            $data['fecha'] = date('Y-m-d');
        }
         if (!is_numeric($data['monto_limite']) || $data['monto_limite'] <= 0) {
             $response['error'] = 'El monto debe ser un número positivo.';
             echo json_encode($response);
             exit;
         }
        // Determinar si es Presupuesto General o por Categoría
        $isGeneral = false;
        $parentPres = isset($data['parent_presupuesto']) ? (int)$data['parent_presupuesto'] : 0;
        $catId = isset($data['id_categoria']) ? (int)$data['id_categoria'] : 0;

        if ($catId <= 0) {
            // Si no viene categoría válida, lo consideramos presupuesto general
            $isGeneral = true;
        }

        // Si es presupuesto por categoría, forzar selección de presupuesto general padre
        if (!$isGeneral && $parentPres <= 0) {
            $response['error'] = 'Para un presupuesto por categoría debe seleccionar un Presupuesto General padre.';
            echo json_encode($response);
            exit;
        }

        try {
            // Si no existen presupuestos en la BD y se está creando uno nuevo, forzar que sea general
            if (empty($id)) {
                $all = $this->presupuestoModel->getAllPresupuestos();
                if (empty($all) && !$isGeneral) {
                    $response['error'] = 'El primer presupuesto debe ser GENERAL. Cree primero un Presupuesto General sin categoría.';
                    echo json_encode($response);
                    exit;
                }
            }
            // El modelo ahora hace un simple INSERT o UPDATE
            // Antes de guardar: si es presupuesto por categoría, validar que la suma de hijos no exceda el padre
            
            // Obtener datos anteriores si es actualización
            $oldData = null;
            if (!empty($id)) {
                $oldData = $this->presupuestoModel->getPresupuestoById($id);
            }
            
            if (!$isGeneral && $parentPres > 0) {
                $sumaHijos = $this->presupuestoModel->getSumaPresupuestosHijos($parentPres);
                $nuevoMonto = floatval($data['monto_limite']);
                // Si estamos editando un hijo, restar su monto anterior para validar correctamente
                if ($oldData) {
                    $oldMonto = $oldData['monto_limite'] ?? 0;
                    $sumaHijos = max(0, $sumaHijos - floatval($oldMonto));
                }
                $parent = $this->presupuestoModel->getPresupuestoById($parentPres);
                $parentLimite = floatval($parent['monto_limite'] ?? 0);
                if (($sumaHijos + $nuevoMonto) > $parentLimite) {
                    $response['error'] = 'La suma de los presupuestos por categoría excedería el monto del Presupuesto General seleccionado.';
                    echo json_encode($response);
                    exit;
                }
            }

            $success = $this->presupuestoModel->savePresupuesto($data, $id);

            if ($success) {
                // Si no existen triggers en la BD para presupuestos, hacemos fallback en PHP
                if (!$this->auditoriaModel->hasTriggerForTable('presupuestos')) {
                    if (empty($id)) {
                        // Inserción
                        $this->auditoriaModel->addLog('Presupuesto', 'Insercion', null, null, json_encode($data), null, null, $_SESSION['user_id'] ?? null);
                    } else {
                        // Actualización
                        $oldValor = $oldData ? json_encode($oldData) : null;
                        $newValor = json_encode($data);
                        $this->auditoriaModel->addLog('Presupuesto', 'Actualizacion', null, $oldValor, $newValor, null, null, $_SESSION['user_id'] ?? null);
                    }
                }
                $response['success'] = true;
            } else {
                 $response['error'] = 'No se pudo guardar el presupuesto en la base de datos.';
            }
        } catch (Exception $e) {
            error_log("Error en PresupuestoController->save: " . $e->getMessage());
            $response['error'] = $e->getMessage() ?: 'Error interno del servidor al guardar.';
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Acción AJAX: Obtiene los datos de un presupuesto específico (para editar).
     */
     public function getPresupuestoData() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        $id = $_GET['id'] ?? 0;
        $presupuesto = $this->presupuestoModel->getPresupuestoById($id);

        if ($presupuesto) {
            echo json_encode($presupuesto);
        } else {
             echo json_encode(['error' => 'Presupuesto no encontrado.']);
        }
        exit;
     }
     
     /**
      * Acción AJAX: Obtiene todas las categorías (para llenar el select del modal).
      * NOTA: Esto es para un formulario, lo dejamos aunque la lógica del modelo cambió.
      */
      public function getAllCategorias() {
           header('Content-Type: application/json');
           if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }
           
           $categorias = $this->categoriaModel->getAllCategorias();
         echo json_encode($categorias);
           exit;
      }

     /**
      * Acción AJAX: Devuelve todos los presupuestos disponibles (ID y monto) en JSON.
      * Usado por el frontend para permitir asignar un presupuesto a un egreso.
      */
     public function getAllPresupuestos() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

         $pres = $this->presupuestoModel->getAllPresupuestos();
         // Normalizar salida: id, monto_limite, fecha, nombre. Añadir disponible calculado.
         $out = [];
         foreach ($pres as $p) {
             $idp = $p['id_presupuesto'] ?? ($p['id'] ?? null);
             $monto = floatval($p['monto_limite'] ?? ($p['monto'] ?? 0));
             $gastado = $this->presupuestoModel->getGastadoEnPresupuesto($idp);
             $esPerm = isset($p['es_permanente']) && intval($p['es_permanente']) === 1;
             $disponible = $esPerm ? null : ($monto - floatval($gastado));
             $out[] = [
                 'id' => $idp,
                 'monto_limite' => $monto,
                 'fecha' => $p['fecha'] ?? null,
                'id_categoria' => $p['id_categoria'] ?? null,
                'cat_nombre' => $p['cat_nombre'] ?? ($p['categoria'] ?? null),
                'parent_presupuesto' => $p['parent_presupuesto'] ?? null,
                'nombre' => $p['nombre'] ?? null,
                'gastado' => floatval($gastado),
                'disponible' => $disponible,
                'es_permanente' => $esPerm ? 1 : 0,
                'activo' => $p['activo'] ?? 0
             ];
         }
         echo json_encode($out);
         exit;
     }

     /**
      * Acción AJAX: Devuelve solo sub-presupuestos para dropdown de egresos
      */
     public function getSubPresupuestos() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

         $subPresupuestos = $this->presupuestoModel->getSubPresupuestos();
         echo json_encode($subPresupuestos);
         exit;
     }

    /**
     * Acción AJAX: Devuelve sub-presupuestos filtrados para creación de egresos.
     * Regla: Mostrar únicamente los subpresupuestos cuyo mes/año == mes/año actual
     * y además incluir siempre los hijos del presupuesto 9999 (presupuesto fantasma).
     */
    public function getFilteredSubPresupuestos() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        // Consulta corregida: Trae todos los hijos activos sin importar el mes
        $query = "SELECT p.id_presupuesto, NULL AS nombre, p.fecha, p.monto_limite, c.nombre AS cat_nombre, p.id_categoria, p.parent_presupuesto,
                             p.es_permanente, p.activo,
                             COALESCE((SELECT SUM(e.monto) FROM egresos e WHERE e.id_presupuesto = p.id_presupuesto), 0) AS gastado
                        FROM presupuestos p
                        LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
                        WHERE p.parent_presupuesto IS NOT NULL
                        AND (
                            p.parent_presupuesto = 10
                            OR p.activo = 1 
                            OR p.es_permanente = 1
                        )
                        ORDER BY p.fecha DESC, p.id_presupuesto DESC";

        $result = $this->db->query($query); // Usamos query directo, ya no requerimos bind_param de fecha
        
        $out = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['gastado'] = floatval($row['gastado'] ?? 0);
                // Si es permanente, marcar disponible como NULL para que el frontend lo trate como ilimitado
                $esPerm = isset($row['es_permanente']) && intval($row['es_permanente']) === 1;
                if ($esPerm) {
                    $row['disponible'] = null;
                } else {
                    $row['disponible'] = floatval($row['monto_limite'] ?? 0) - $row['gastado'];
                }
                $out[] = $row;
            }
        }
        
        echo json_encode($out);
        exit;
    }

    /**
     * Acción AJAX: Cierra un presupuesto (activo = 0). Protege IDs del sistema.
     */
    public function close() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $response = ['success' => false];
        if ($id <= 0) { $response['error'] = 'ID inválido.'; echo json_encode($response); exit; }

        // Protección: no cerrar presupuestos del sistema (IDs 1 y 2)
        if (in_array($id, [1,2])) {
            $response['error'] = 'Este presupuesto del sistema no puede cerrarse.';
            echo json_encode($response); exit;
        }

        try {
            $old = $this->presupuestoModel->getPresupuestoById($id);
            // Propagar cierre a sub-presupuestos
            $success = $this->presupuestoModel->setActivoCascade($id, 0);
            if ($success) {
                if (!$this->auditoriaModel->hasTriggerForTable('presupuestos')) {
                    $this->auditoriaModel->addLog('Presupuesto', 'Cerrar', null, json_encode($old), json_encode(['activo' => 0]), null, null, $_SESSION['user_id'] ?? null);
                }
                $response['success'] = true;
            } else {
                $response['error'] = 'No se pudo cerrar el presupuesto.';
            }
        } catch (Exception $e) {
            error_log('Error PresupuestoController->close: ' . $e->getMessage());
            $response['error'] = 'Error interno al cerrar.';
        }
        echo json_encode($response); exit;
    }

    /**
     * Acción AJAX: Reabre un presupuesto (activo = 1).
     */
    public function reopen() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $response = ['success' => false];
        if ($id <= 0) { $response['error'] = 'ID inválido.'; echo json_encode($response); exit; }

        try {
            $old = $this->presupuestoModel->getPresupuestoById($id);
            // Propagar reapertura a sub-presupuestos
            $success = $this->presupuestoModel->setActivoCascade($id, 1);
            if ($success) {
                if (!$this->auditoriaModel->hasTriggerForTable('presupuestos')) {
                    $this->auditoriaModel->addLog('Presupuesto', 'Reabrir', null, json_encode($old), json_encode(['activo' => 1]), null, null, $_SESSION['user_id'] ?? null);
                }
                $response['success'] = true;
            } else {
                $response['error'] = 'No se pudo reabrir el presupuesto.';
            }
        } catch (Exception $e) {
            error_log('Error PresupuestoController->reopen: ' . $e->getMessage());
            $response['error'] = 'Error interno al reabrir.';
        }
        echo json_encode($response); exit;
    }

     /**
      * Acción AJAX: Devuelve solo presupuestos generales (sin parent_presupuesto)
      * Para usar en el dropdown al crear sub-presupuestos
      */
     public function getPresupuestosGenerales() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

         // Obtener solo presupuestos generales (parent_presupuesto IS NULL)
         $query = "SELECT id_presupuesto, NULL AS nombre, fecha, monto_limite 
                   FROM presupuestos 
                   WHERE parent_presupuesto IS NULL 
                   ORDER BY fecha DESC, id_presupuesto DESC";
         $result = $this->db->query($query);
         
         $presupuestos = [];
         if ($result) {
             while ($row = $result->fetch_assoc()) {
                 $presupuestos[] = $row;
             }
         }
         
         echo json_encode($presupuestos);
         exit;
     }

    /**
     * Acción AJAX: Devuelve explícitamente los presupuestos de reembolso (IDs 1 y 2 si existen).
     * Usado por el flujo de reembolso para garantizar que sólo se ofrezcan esos presupuestos.
     */
    public function getReembolsoPresupuestos() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        $query = "SELECT p.id_presupuesto, p.parent_presupuesto, p.fecha, p.monto_limite, p.id_categoria, p.es_permanente, c.nombre AS cat_nombre
                  FROM presupuestos p
                  LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
                  WHERE p.id_presupuesto IN (1,2)
                  ORDER BY p.id_presupuesto ASC";
        $res = $this->db->query($query);
        $out = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $idp = $row['id_presupuesto'] ?? ($row['id'] ?? null);
                $gastado = $this->presupuestoModel->getGastadoEnPresupuesto($idp);
                $esPerm = isset($row['es_permanente']) && intval($row['es_permanente']) === 1;
                $disponible = $esPerm ? null : (floatval($row['monto_limite'] ?? 0) - floatval($gastado));
                $row['gastado'] = floatval($gastado);
                $row['disponible'] = $disponible;
                $row['es_permanente'] = $esPerm ? 1 : 0;
                $out[] = $row;
            }
        }
        echo json_encode($out);
        exit;
    }

     /**
      * Acción AJAX: Devuelve el conteo de presupuestos en alerta (>=90% consumidos)
      */
     public function getAlertasCount() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

         $alertas = $this->presupuestoModel->getPresupuestosEnAlerta();
         echo json_encode(['count' => count($alertas), 'alertas' => $alertas]);
         exit;
     }

    /**
     * Acción AJAX: Elimina un presupuesto.
     */
    public function delete() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $id = $_POST['id'] ?? 0;
        $response = ['success' => false];

        if ($id > 0) {
            try {
                // Obtener datos del presupuesto antes de eliminarlo
                $presupuesto = $this->presupuestoModel->getPresupuestoById($id);
                    // Proteger Presupuesto Fantasma y sus hijos: no permitir eliminación
                    $presIdToCheck = $presupuesto['id_presupuesto'] ?? $presupuesto['id'] ?? 0;
                    $parentOfPres = $presupuesto['parent_presupuesto'] ?? null;
                    if ($presIdToCheck == 10 || $parentOfPres == 10) {
                        $response['error'] = 'El presupuesto del sistema no se puede eliminar ni modificar desde la interfaz.';
                        echo json_encode($response); exit;
                    }

                    // Protección histórica: sólo permitir eliminar presupuestos del mes en curso
                    if ($presupuesto && !empty($presupuesto['fecha'])) {
                        $presYm = date('Y-m', strtotime($presupuesto['fecha']));
                        $currentYm = date('Y-m');
                        if ($presYm < $currentYm) {
                            $response['error'] = 'No se puede eliminar un presupuesto histórico (cerrado).';
                            echo json_encode($response); exit;
                        }
                    }
                
                $success = $this->presupuestoModel->deletePresupuesto($id);
                if ($success) {
                    if (!$this->auditoriaModel->hasTriggerForTable('presupuestos')) {
                        $oldValor = $presupuesto ? json_encode($presupuesto) : null;
                        $this->auditoriaModel->addLog('Presupuesto', 'Eliminacion', null, $oldValor, null, null, null, $_SESSION['user_id'] ?? null);
                    }
                    $response['success'] = true;
                } else {
                    $response['error'] = 'No se pudo eliminar el presupuesto de la base de datos.';
                }
            } catch (Exception $e) {
                 error_log("Error en PresupuestoController->delete: " . $e->getMessage());
                 $response['error'] = 'Error interno del servidor al eliminar.';
            }
        } else {
            $response['error'] = 'ID de presupuesto inválido.';
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Acción AJAX: Obtiene datos para la gráfica Presupuesto vs Gastado
     * Retorna JSON con categorías, presupuestos y gastos
     */
    public function getGraficaPresupuestoVsGastado() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success' => false, 'error' => 'No autorizado']); 
            exit; 
        }

        try {
                        $query = "SELECT 
                                                c.nombre as categoria,
                                                p.monto_limite as presupuesto,
                                                COALESCE(
                                                        (SELECT SUM(e.monto) 
                                                         FROM egresos e 
                                                         WHERE e.id_categoria = p.id_categoria), 
                                                        0
                                                ) as gastado
                                         FROM presupuestos p
                                         INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                                         WHERE p.monto_limite > 0
                                             AND COALESCE(p.parent_presupuesto, 0) <> 10
                                             AND p.id_presupuesto <> 10
                                             AND p.id_presupuesto <> 11
                                             AND p.id_categoria <> 21
                                         ORDER BY c.nombre ASC";
            
            $result = $this->db->query($query);
            $categorias = [];
            $presupuestos = [];
            $gastados = [];
            $porcentajes = [];
            
            while ($row = $result->fetch_assoc()) {
                $presupuesto = floatval($row['presupuesto']);
                $gastado = floatval($row['gastado']);
                $porcentaje = $presupuesto > 0 ? round(($gastado / $presupuesto) * 100, 2) : 0;
                
                $categorias[] = $row['categoria'];
                $presupuestos[] = $presupuesto;
                $gastados[] = $gastado;
                $porcentajes[] = $porcentaje;
            }
            
            echo json_encode([
                'success' => true,
                'categorias' => $categorias,
                'presupuestos' => $presupuestos,
                'gastados' => $gastados,
                'porcentajes' => $porcentajes
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Acción AJAX: Devuelve la comparativa Presupuesto vs Gastado para un presupuesto padre específico
     * Parámetro GET/POST: parent_id
     */
    public function getGraficaPresupuestoPorPadre() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $parentId = $_REQUEST['parent_id'] ?? null;
        if (empty($parentId)) { echo json_encode(['success' => false, 'error' => 'parent_id requerido']); exit; }

        try {
            $query = "SELECT c.nombre as categoria, p.monto_limite as presupuesto, COALESCE((SELECT SUM(e.monto) FROM egresos e WHERE e.id_presupuesto = p.id_presupuesto), 0) as gastado
                      FROM presupuestos p
                      INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                      WHERE p.parent_presupuesto = ?
                      ORDER BY c.nombre ASC";

            $stmt = $this->db->prepare($query);
            if (!$stmt) { echo json_encode(['success' => false, 'error' => 'Error en consulta']); exit; }
            $stmt->bind_param('i', $parentId);
            $stmt->execute();
            $res = $stmt->get_result();

            $categorias = [];
            $presupuestos = [];
            $gastados = [];
            $porcentajes = [];

            while ($row = $res->fetch_assoc()) {
                $presupuesto = floatval($row['presupuesto']);
                $gastado = floatval($row['gastado']);
                $porcentaje = $presupuesto > 0 ? round(($gastado / $presupuesto) * 100, 2) : 0;

                $categorias[] = $row['categoria'];
                $presupuestos[] = $presupuesto;
                $gastados[] = $gastado;
                $porcentajes[] = $porcentaje;
            }

            echo json_encode([
                'success' => true,
                'categorias' => $categorias,
                'presupuestos' => $presupuestos,
                'gastados' => $gastados,
                'porcentajes' => $porcentajes
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

     /**
     * Función helper para renderizar vistas.
     */
    protected function renderView($view, $data = []) {
        extract($data);
        ob_start();
        $viewPath = __DIR__ . "/../Views/{$view}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            error_log("Vista no encontrada: " . $viewPath);
            echo "<div class='alert alert-danger'>Error: No se encontró la vista.</div>";
        }
        $content = ob_get_clean();
        require __DIR__ . '/../../../shared/Views/layout.php';
    }
}
?>