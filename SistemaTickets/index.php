<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/estilo_index.css">
    <title>Login | Sistema de Tickets</title>
    
</head>
<body>
    <main class="login-card">
        <div class="logo">ST</div>
        <h1>Iniciar sesión</h1>
        <p class="subtitle">Sistema de Tickets</p>

        <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
            <div class="error-box" id="errorBox">Usuario o contrasena no validos.</div>
        <?php endif; ?>


        <form action="includes/validar_sesion.php" method="post" autocomplete="off">

        <?php
            if (isset($_GET['error'])) {
                if ($_GET['error'] == 1) {
                    echo '<div style="color: white; background-color: #dc3545; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
                            <strong>Error:</strong> Correo o contraseña incorrectos.
                          </div>';
                }
            }
        ?>
            
            <div class="field">
                <label for="usuario">Usuario o correo</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
            </div>

            <div class="field">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
            </div>

            <div class="login-options">
                <label class="remember" for="remember">
                    <input type="checkbox" id="remember" name="remember">
                    Recordarme
                </label>
                <a href="#" class="forgot">Olvidé mi contraseña</a>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <p class="footer">Mesa de ayuda y seguimiento de incidencias</p>
    </main>
</body>
</html>
