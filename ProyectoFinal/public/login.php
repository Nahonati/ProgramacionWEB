<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="login">
        <h2 class="titulo">Iniciar Sesión</h2>
        <form action="../includes/validar.php" method="POST" onsubmit="return validarFormulario();">
            <div class="inicio">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="usuario" name="usuario" required placeholder="Ingresa tu correo electrónico">
            </div>
            <div class="inicio">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
            </div>
            <button type="submit" class="boton">Iniciar sesión</button>
        </form>
        <div class="registro">
            <p>¿No tienes una cuenta? <a href="../public/registro.php">Regístrate aquí</a></p>
        </div>
    </div>
    <img src="../public/assests/img/login.gif" alt="imginicio" class="img-login">
</body>
</html>