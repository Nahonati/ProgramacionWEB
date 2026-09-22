<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../public/css/style.css">

</head>
<body>
<div class="container">
        <div class="registro-container">
            <h2 class="titulo2">Registra tu Cuenta!</h2>
            <form action="../includes/procesar_usuario.php" method="POST" onsubmit="return validarFormulario();">
                <div class="form-registro">
                    <label for="nombres">Nombres</label>
                    <input type="text" id="nombres" name="nombres" required placeholder="Ingresa tu nombre">
                </div>
                <div class="form-registro">
                    <label for="a_paterno">Apellido Paterno</label>
                    <input type="text" id="a_paterno" name="a_paterno" required placeholder="Ingresa tu apellido paterno">
                </div>
                <div class="form-registro">
                    <label for="a_materno">Apellido Materno</label>
                    <input type="text" id="a_materno" name="a_materno" required placeholder="Ingresa tu apellido paterno">
                </div>
                <div class="form-registro">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="Ingresa tu correo electrónico">
                </div>
                <div class="form-registro">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                    <p class="nota">La contraseña debe tener al menos 8 caracteres</p>
                </div>
                <div class="form-registro">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirma tu contraseña">
                </div>
                <button type="submit" class="boton">Registrarse</button>
            </form>
        </div>
        <div class="welcome">
            <img src="../public/assests/img/registro.gif" alt="welcome">
        </div>
    </div>
</body>
</html>