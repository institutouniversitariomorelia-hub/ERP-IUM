<?php
// views/login.php
// Esta vista es llamada por AuthController->login()
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP IUM - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos específicos del login (puedes usar los que te di antes) */
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f4f6f9; }
        .login-card { width: 100%; max-width: 400px; border:none; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); }
        .login-card .card-header { background-color: #B80000; color: white; border-top-left-radius: 8px; border-top-right-radius: 8px; }
        .btn-danger { background-color: #B80000; border-color: #B80000; }
        .btn-danger:hover { background-color: #9A0000; border-color: #9A0000; }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-header text-center p-3">
            <h4>Sistema ERP IUM</h4>
        </div>
        <div class="card-body p-4">
            <h5 class="card-title text-center mb-4">Iniciar Sesión</h5>
            
            <?php 
            // Mostrar el mensaje de error si el controlador lo pasó a través de la URL
            if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>index.php?controller=auth&action=processLogin" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-danger">Entrar</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>