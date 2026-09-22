<?php
    /*
    * Recuerda:
    *   Activamos el manejo de sesiones: session_start();
    * Ademas:
    *   Es necesario verificamos si el usuario ha iniciado sesión correctamente.
    *   Si no existe la variable de sesión 'usuario_id', lo redirigimos al inicio.
    *   Esto evita que alguien sin permisos acceda directamente al script por la URL.
    */

    // Mostrar errores (útil durante desarrollo)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Variables iniciales
    $mensaje = "";
    $usuario = [];
    $roles = [];
    $estatuses = [];

    try {
        // Conexión PDO
        require '../../includes/conexion.php';

        /*
        * Cargamos los datos necesarios para los menús desplegables (roles y estatus).
        * Esto permite que el usuario edite el rol y el estatus en el formulario.
        */
        $roles = $conn->query("SELECT id, nombre FROM roles")->fetchAll(PDO::FETCH_ASSOC);
        $estatuses = $conn->query("SELECT id, descripcion FROM estatus_usuario")->fetchAll(PDO::FETCH_ASSOC);

        /*
        * Si se ha enviado el formulario con método POST, procesamos la edición.
        */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtenemos los datos del formulario        
            $id         = $_POST['id'];
            $nombres    = $_POST['nombres'];
            $a_paterno  = $_POST['a_paterno'];
            $a_materno  = $_POST['a_materno'];
            $email     = $_POST['email'];
            $rol_id     = $_POST['rol_id'];
            $estatus_id = $_POST['estatus_id'];

            /*
            * Verificamos que el correo electrónico no esté en uso por otro usuario
            * con un ID distinto al actual.
            */
            $check = $conn->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
            $check->execute([':email' => $email, ':id' => $id]);

            if ($check->rowCount() > 0) {
                $mensaje = "⚠️ El correo ya está en uso por otro usuario.";
            } else {
                // Si no hay conflicto, actualizamos los datos del usuario         
                $sql = "UPDATE usuarios SET 
                            nombres = :nombres,
                            a_paterno = :a_paterno,
                            a_materno = :a_materno,
                            email = :email,
                            rol_id = :rol_id,
                            estatus_id = :estatus_id,
                            fecha_actualizacion = NOW()
                        WHERE id = :id";

                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':nombres'    => $nombres,
                    ':a_paterno'  => $a_paterno,
                    ':a_materno'  => $a_materno,
                    ':email'     => $email,
                    ':rol_id'     => $rol_id,
                    ':estatus_id' => $estatus_id,
                    ':id'         => $id
                ]);

                // Redirigimos para evitar reenvío del formulario al refrescar (patrón PRG)
                header("Location: ../../public/admin/editar_usuario.php?id=$id&actualizado=1");
                exit;
            }

            // Si hubo error, recargamos los datos del usuario para mostrarlos
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Si es una petición GET con un ID válido, cargamos los datos del usuario
        elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si no se encuentra el usuario, mostramos un mensaje
            if (!$usuario) {
                $mensaje = "⚠️ Usuario no encontrado.";
            }
        } else {
            // Si no se envió un ID válido, mostramos mensaje de error        
            $mensaje = "❌ ID inválido.";
        }

        // Mensaje de éxito si venimos de la redirección después de guardar cambios
        if (isset($_GET['actualizado']) && $_GET['actualizado'] == 1) {
            $mensaje = "✅ Usuario actualizado correctamente.";
        }

    } catch (PDOException $e) {
        // Capturamos errores de PDO y los mostramos    
        $mensaje = "❌ Error: " . $e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Usuario</title>
<link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
<nav class="nav-container">
    <div class="contenido">
        <a href="../public/admin/administrador.php" class="nav-logo">BestVersion</a>
        <div class="nav-links">
            <a href="../../public/miperfil.php">Mi perfil</a>
        </div>
    </div>
</nav>


<!-- Mostramos el mensaje si existe -->
<?php if ($mensaje): ?>
    <p><strong><?= $mensaje ?></strong></p>
<?php endif; ?>

<!-- Mostramos el formulario solo si se cargaron los datos del usuario -->
<?php if (!empty($usuario)): ?>
    <div class="container">
        <div class="editar-container">
        <h2 class="titulo">Editar Usuario</h2>
            <form method="POST" action="../../public/admin/editar_usuario.php">
            <!-- ID oculto para saber qué usuario estamos editando -->
                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                <?php
                    /*
                     * IMPORTANTE:
                     * Usamos htmlspecialchars() para convertir caracteres especiales en entidades HTML.
                     * Además evitamos ataques XSS (Cross-Site Scripting)
                     * Así evitamos que un usuario malicioso pueda insertar código HTML o JavaScript en la página.
                     *   Ejemplo:
                     *      + Entrada del usuario: <script>alert("Hola")</script>
                     *      + Sin htmlspecialchars() -> el navegador ejecuta el script (¡riesgo!)
                     *      + Con htmlspecialchars() -> el navegador solo muestra el texto como tal
                     */
                ?>
                <div class="form-registro">
                    <label>Nombres:</label>
                    <input type="text" name="nombres" value="<?= htmlspecialchars($usuario['nombres']) ?>" required>
                </div>
                <div class="form-registro">
                    <label>Apellido Paterno:</label>
                    <input type="text" name="a_paterno" value="<?= htmlspecialchars($usuario['a_paterno']) ?>" required>
                </div>
                <div class="form-registro">
                    <label>Apellido Materno:</label>
                    <input type="text" name="a_materno" value="<?= htmlspecialchars($usuario['a_materno']) ?>">
                </div>
                <div class="form-registro">
                    <label>Correo:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>
                <div class="form-registro">
                    <label>Rol:</label>
                    <!-- Lista desplegable de roles -->
                    <select name="rol_id" required>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['id'] ?>" <?= $rol['id'] == $usuario['rol_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-registro">
                    <label>Estatus:</label>
                    <!-- Lista desplegable de estatus -->
                    <select name="estatus_id" required>
                    <?php foreach ($estatuses as $est): ?>
                        <option value="<?= $est['id'] ?>" <?= $est['id'] == $usuario['estatus_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($est['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                </div>
                <!-- Botón de enviar -->
                <button type="submit" class="boton">Guardar cambios</button>
            <br>
            <br>

                <!-- Enlace para cancelar y volver a la lista -->
                <a href="../../public/admin/administrador.php" class="boton">Cancelar</a>
                <a href="../../public/admin/administrador.php" class="boton">Volver</a>
            </form>
        </div>
        <div class="welcome">
                <img src="../../public/assests/img/editar.gif" alt="editar">
            </div>
    </div>
<?php endif; ?>
</body>
</html>