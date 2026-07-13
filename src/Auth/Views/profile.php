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
            <div class="col-12 col-md-4">
                <label class="fw-bold">Nombre</label>
                <p><?php echo htmlspecialchars($currentUser['nombre']); ?></p> 
            </div>
            <div class="col-12 col-md-4">
                <label class="fw-bold">Usuario</label>
                <p><?php echo htmlspecialchars($currentUser['username']); ?></p>
            </div>
            <div class="col-12 col-md-4">
                <label class="fw-bold">Rol</label>
                <p class="text-danger fw-bold"><?php echo htmlspecialchars($currentUser['rol']); ?></p>
            </div>
        </div>
        <?php if ($currentUser['rol'] === 'SU'): ?>
        <div class="btn-responsive-sm mt-3">
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalEditarMiPerfil" id="btnEditarMiPerfil">
                <ion-icon name="create-outline" style="vertical-align: middle;"></ion-icon> Editar Mi Perfil
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (roleCan('view','user')): ?>
<div class="mb-4">
    <button class="btn btn-outline-danger" id="btnToggleUsuarios">
        <ion-icon name="people-outline" style="vertical-align: middle;"></ion-icon> 
        <span id="toggleUsuariosText">Ver Usuarios Registrados</span>
        <ion-icon name="chevron-down-outline" id="toggleUsuariosIcon" style="vertical-align: middle;"></ion-icon>
    </button>
</div>

<div id="seccionUsuariosRegistrados" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-danger mb-0">Usuarios registrados</h3>
        <?php if (roleCan('add','user')): ?>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalUsuario" id="btnRegistrarUsuario">
                <ion-icon name="person-add-outline" style="vertical-align: middle;"></ion-icon> Registrar Nuevo Usuario
            </button>
        <?php endif; ?>
    </div>
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
                        <?php if (empty($users)): ?>
                            <tr><td colspan="4" class="text-center p-4 text-muted">No hay usuarios registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($user['rol']); ?></span></td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                                <?php if (roleCan('edit','user')): ?>
                                                    <button class="btn btn-sm btn-warning btn-edit-user" 
                                                            data-id="<?php echo $user['id']; ?>" 
                                                            data-nombre="<?php echo htmlspecialchars($user['nombre']); ?>"
                                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                            data-rol="<?php echo htmlspecialchars($user['rol']); ?>"
                                                            data-bs-toggle="modal" data-bs-target="#modalEditarMiPerfil" 
                                                            title="Editar Usuario">
                                                        <ion-icon name="create-outline"></ion-icon>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (roleCan('change_pass','user')): ?>
                                                        <button class="btn btn-sm btn-secondary btn-change-pass" 
                                                            data-username="<?php echo htmlspecialchars($user['username']); ?>" 
                                                            data-bs-toggle="modal" data-bs-target="#modalCambiarPasswordUser"
                                                            title="Cambiar Contraseña">
                                                        <ion-icon name="lock-closed-outline"></ion-icon>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (roleCan('delete','user')): ?>
                                                    <button class="btn btn-sm btn-danger btn-delete-user" 
                                                            data-id="<?php echo $user['id']; ?>" 
                                                            <?php echo ($user['id'] == $currentUser['id']) ? 'disabled' : ''; ?> 
                                                            title="Eliminar Usuario">
                                                        <ion-icon name="trash-outline"></ion-icon>
                                                    </button>
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
</div>
<?php endif; ?>