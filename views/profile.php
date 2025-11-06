<?php
// views/profile.php
// Esta vista es cargada por UserController->profile() dentro de layout.php
// Las variables $pageTitle, $activeModule, $users y $currentUser (del layout) están disponibles aquí.
?>

<h3 class="text-danger mb-4"><?php echo htmlspecialchars($pageTitle); ?></h3>

<div class="card shadow-sm mb-5">
    <div class="card-header bg-danger text-white">
        Datos de Acceso
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <label class="fw-bold">Nombre</label>
                <p><?php echo htmlspecialchars($currentUser['nombre']); ?></p> 
            </div>
            <div class="col-12 col-md-6">
                <label class="fw-bold">Rol</label>
                 <p class="text-danger fw-bold"><?php echo htmlspecialchars($currentUser['rol']); ?></p>
            </div>
        </div>
        <div class="btn-responsive-sm mt-3">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalCambiarPassword" id="btnCambiarMiPassword">Cambiar mi contraseña</button>
            <?php if ($currentUser['rol'] === 'SU'): ?>
                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalUsuario" id="btnRegistrarUsuario">Registrar Usuario (SU)</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<h3 class="text-danger mb-3">Usuarios registrados</h3>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-sm mb-0">
                <thead>
                    <tr class="table-light">
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaUsuarios">
                    <?php if (empty($users)): // La variable $users viene del UserController ?>
                        <tr><td colspan="4" class="text-center p-4 text-muted">No hay usuarios registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($user['rol']); ?></span></td>
                                <td class="text-center">
                                    <div class="btn-responsive-sm">
                                        <button class="btn btn-sm btn-warning btn-edit-user" 
                                                data-id="<?php echo $user['id']; ?>" 
                                                data-nombre="<?php echo htmlspecialchars($user['nombre']); ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                data-rol="<?php echo htmlspecialchars($user['rol']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalUsuario" 
                                                title="Editar Usuario">
                                            <ion-icon name="create-outline"></ion-icon> Editar
                                        </button>
                                        <button class="btn btn-sm btn-secondary btn-change-pass" 
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>" 
                                                data-bs-toggle="modal" data-bs-target="#modalCambiarPassword"
                                                title="Cambiar Contraseña">
                                            <ion-icon name="lock-open-outline"></ion-icon> Contraseña
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-delete-user" 
                                                data-id="<?php echo $user['id']; ?>" 
                                                <?php echo ($user['id'] == $currentUser['id']) ? 'disabled' : ''; ?> 
                                                title="Eliminar Usuario">
                                            <ion-icon name="trash-outline"></ion-icon> Eliminar
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