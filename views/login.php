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
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        :root {
            --primary-color: #B80000;
            --primary-hover: #9A0000;
            --primary-light: rgba(184, 0, 0, 0.1);
            --text-color: #2c3e50;
            --border-color: #e1e5e9;
            --shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        body { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            background: linear-gradient(135deg, #B80000 0%, #FF6B6B 25%, #FFFFFF 50%, #FFE0E0 75%, #B80000 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 20px;
            position: relative;
        }
        
        /* Animación del fondo difuminado */
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Overlay difuminado adicional */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 20%, rgba(184, 0, 0, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(255, 255, 255, 0.4) 0%, transparent 50%),
                        radial-gradient(circle at 50% 50%, rgba(184, 0, 0, 0.2) 0%, transparent 70%);
            pointer-events: none;
            z-index: -1;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            position: relative;
        }
        
        .login-card { 
            border: none; 
            border-radius: 16px; 
            box-shadow: var(--shadow);
            background: white;
            overflow: hidden;
            position: relative;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="60" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
        }
        
        .logo-section {
            position: relative;
            z-index: 2;
        }
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            backdrop-filter: blur(10px);
            padding: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: brightness(1.1) drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .login-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 2;
        }
        
        .login-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0.5rem 0 0;
            position: relative;
            z-index: 2;
        }
        
        .login-body {
            padding: 2rem 1.5rem;
        }
        
        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-floating .form-control {
            height: 3.5rem;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafbfc;
        }
        
        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem var(--primary-light);
            background: white;
        }
        
        .form-floating label {
            padding-left: 3rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #6c757d;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }
        
        .form-floating .form-control:focus + label + .input-icon {
            color: var(--primary-color);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(184, 0, 0, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%);
            color: #721c24;
        }
        
        .login-footer {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        @media (max-width: 480px) {
            .login-header {
                padding: 1.5rem 1rem;
            }
            
            .login-body {
                padding: 1.5rem 1rem;
            }
            
            .logo-icon {
                width: 60px;
                height: 60px;
                padding: 8px;
            }
        }
        
        /* Animación de entrada */
        .login-container {
            animation: slideIn 0.6s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Easter Egg GIF */
        #easterEggGif {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
            width: 100%;
            max-width: 420px;
            height: auto;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
            border-radius: 16px;
            display: none;
            animation: easterEggAppear 0.3s ease-out;
            cursor: pointer;
        }
        
        @keyframes easterEggAppear {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.5);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
        
        #easterEggOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 998;
            display: none;
            animation: fadeIn 0.3s ease-out;
            cursor: pointer;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Easter Egg Overlay y GIF -->
    <div id="easterEggOverlay" onclick="closeEasterEgg()"></div>
    <img id="easterEggGif" src="<?php echo BASE_URL; ?>public/images/gif.gif" alt="Easter Egg" onclick="closeEasterEgg()" />
    
    <div class="login-container">
        <div class="card login-card">
            <!-- Header con logo -->
            <div class="login-header">
                <div class="logo-section">
                    <div class="logo-icon" id="logoIcon">
                        <img src="<?php echo BASE_URL; ?>public/logo ium blanco.png" alt="Logo IUM" />
                    </div>
                    <h1 class="login-title">Sistema ERP IUM</h1>
                    <p class="login-subtitle">Instituto Universitario Morelia</p>
                </div>
            </div>
            
            <!-- Cuerpo del formulario -->
            <div class="login-body">
                <?php 
                // Mostrar el mensaje de error si el controlador lo pasó a través de la URL
                if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <ion-icon name="alert-circle" style="margin-right: 0.5rem; font-size: 1.2rem;"></ion-icon>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>index.php?controller=auth&action=processLogin" method="POST">
                    <!-- Campo Usuario -->
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Usuario" required>
                        <label for="username">Usuario</label>
                        <ion-icon name="person-outline" class="input-icon"></ion-icon>
                    </div>
                    
                    <!-- Campo Contraseña -->
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                        <label for="password">Contraseña</label>
                        <ion-icon name="lock-closed-outline" class="input-icon"></ion-icon>
                    </div>
                    
                    <!-- Botón de login -->
                    <button type="submit" class="btn btn-login">
                        <ion-icon name="log-in-outline" style="margin-right: 0.5rem;"></ion-icon>
                        Iniciar Sesión
                    </button>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="login-footer">
                <small>© 2025 Instituto Universitario Morelia - Todos los derechos reservados</small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Asegurar que el overlay esté oculto al cargar
        window.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('easterEggOverlay');
            const gif = document.getElementById('easterEggGif');
            if (overlay) overlay.style.display = 'none';
            if (gif) gif.style.display = 'none';
        });
        
        // Easter Egg - 5 clics en el logo
        let clickCount = 0;
        let clickTimer = null;
        
        const logoIcon = document.getElementById('logoIcon');
        if (logoIcon) {
            logoIcon.addEventListener('click', function() {
                clickCount++;
                
                // Resetear el contador después de 2 segundos de inactividad
                clearTimeout(clickTimer);
                clickTimer = setTimeout(() => {
                    clickCount = 0;
                }, 2000);
                
                // Si se hacen 5 clics, mostrar el easter egg
                if (clickCount === 5) {
                    showEasterEgg();
                    clickCount = 0;
                }
            });
        }
        
        function showEasterEgg() {
            const overlay = document.getElementById('easterEggOverlay');
            const gif = document.getElementById('easterEggGif');
            if (overlay && gif) {
                overlay.style.display = 'block';
                gif.style.display = 'block';
            }
        }
        
        function closeEasterEgg() {
            const overlay = document.getElementById('easterEggOverlay');
            const gif = document.getElementById('easterEggGif');
            if (overlay) overlay.style.display = 'none';
            if (gif) gif.style.display = 'none';
        }
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEasterEgg();
            }
        });
    </script>
</body>
</html>