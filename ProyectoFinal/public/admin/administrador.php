<?php
    /*
    * Recuerda:
    *   Activamos el manejo de sesiones: session_start();
    * Ademas:
    *   Es necesario verificamos si el usuario ha iniciado sesión correctamente.
    *   Si no existe la variable de sesión 'usuario_id', lo redirigimos al inicio.
    *   Esto evita que alguien sin permisos acceda directamente al script por la URL.
    */

    // Mostrar errores (solo en entorno de desarrollo)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Importamos el archivo de conexión a la base de datos (PDO)    
    require '../../includes/conexion.php';

    /*
    * Este script muestra una lista de usuarios.
    * Si se envía un parámetro de búsqueda (GET['busqueda']), 
    * se filtra por nombre o correo electrónico.
    */

    // Procesar inserción de usuario si se envió el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insertar_usuario'])) {
        $nombres = $_POST['nombres'] ?? '';
        $a_paterno = $_POST['a_paterno'] ?? '';
        $a_materno = $_POST['a_materno'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $mensaje_insert = '';

        if ($nombres && $a_paterno && $a_materno && $email && $password) {
            // Hashear la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $sql_insert = "INSERT INTO usuarios (nombres, a_paterno, a_materno, email, password, fecha_creacion, fecha_actualizacion) VALUES (:nombres, :a_paterno, :a_materno, :email, :password, NOW(), NOW())";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->execute([
                    ':nombres' => $nombres,
                    ':a_paterno' => $a_paterno,
                    ':a_materno' => $a_materno,
                    ':email' => $email,
                    ':password' => $password_hash
                ]);
                $mensaje_insert = 'Usuario insertado correctamente.';
            } catch (PDOException $e) {
                $mensaje_insert = 'Error al insertar usuario: ' . $e->getMessage();
            }
        } else {
            $mensaje_insert = 'Todos los campos son obligatorios.';
        }
    }

    // Verificamos si hay una búsqueda (GET), si no, se deja como cadena vacía
    $busqueda = $_GET['busqueda'] ?? '';

    if ($busqueda !== '') {
        // Si hay un valor de búsqueda, preparamos una consulta que lo utilice
        // Se utiliza LIKE con % para encontrar coincidencias parciales en nombres o correos        
        $sql = "SELECT 
                    u.id,
                    u.nombres AS nombre_usuario,
                    u.a_paterno,
                    u.a_materno,
                    u.email,
                    r.nombre AS rol,
                    e.descripcion AS estatus,
                    u.fecha_creacion,
                    u.fecha_actualizacion
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                JOIN estatus_usuario e ON u.estatus_id = e.id
                WHERE u.nombres LIKE :busqueda OR u.email LIKE :busqueda";
    
        // Preparamos y ejecutamos la consulta con un valor escapado de forma segura    
        $stmt = $conn->prepare($sql);
        $stmt->execute([':busqueda' => "%$busqueda%"]);
    } else {

        // Si no hay búsqueda, simplemente seleccionamos todos los usuarios        
        $sql = "SELECT 
                    u.id,
                    u.nombres AS nombre_usuario,
                    u.a_paterno,
                    u.a_materno,
                    u.email,
                    r.nombre AS rol,
                    e.descripcion AS estatus,
                    u.fecha_creacion,
                    u.fecha_actualizacion
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                JOIN estatus_usuario e ON u.estatus_id = e.id";
    
        // Ejecutamos la consulta directamente (sin parámetros)
        $stmt = $conn->query($sql);
    }

    // Obtenemos todos los resultados en un arreglo asociativo    
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador</title>
    <link rel="stylesheet" href="../../public/css/style.css">

</head>
<body>

<nav class="nav-container">
    <div class="contenido">
        <a href="../public/home.php" class="nav-logo">BestVersion</a>
        <div class="nav-links">
            <a href="../../public/miperfil.php">Mi perfil</a>
        </div>
    </div>
</nav>

    <div class="busqueda">
        <h2 class="title has-text-centered" style="color: #ffbb98 ;">Bienvenido Administrador!</h2>
        <h2 class="title has-text-centered" style="color: #ffbb98 ;">Usuarios Registrados</h2>
        <?php if (!empty($mensaje_insert)): ?>
            <div class="mensaje-insert <?= strpos($mensaje_insert, 'correctamente') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($mensaje_insert) ?>
            </div>
        <?php endif; ?>
        <!-- Formulario de inserción de usuario -->
        <form method="POST" action="" class="insertar-usuario">
            <fieldset>
                <legend>Agregar nuevo usuario</legend>
                <input type="hidden" name="insertar_usuario" value="1">
                <label>Nombres:
                    <input type="text" name="nombres" required>
                </label>
                <label>Apellido Paterno:
                    <input type="text" name="a_paterno" required>
                </label>
                <label>Apellido Materno:
                    <input type="text" name="a_materno" required>
                </label>
                <label>Correo:
                    <input type="email" name="email" required>
                </label>
                <label>Contraseña:
                    <input type="password" name="password" required>
                </label>
                <button type="submit">Agregar Usuario</button>
            </fieldset>
        </form>
        <!-- Formulario de búsqueda -->
        <form method="GET" action="../../public/admin/administrador.php">
        <label>Buscar por nombre o correo:</label>
        <!-- Mantenemos el valor ingresado después de hacer la búsqueda -->
        <input type="text" name="busqueda" value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
        <button type="submit">Buscar</button>
        </form>
        <img src="../../public/assests/img/admin.gif" alt="admin">
        <br>
    </div>

    <!-- Tabla de resultados -->
    <table>
        <thead>
        <tr>
            <th>Nombres</th>
            <th>Apellido Paterno</th>
            <th>Apellido Materno</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Estatus</th>
            <th>Creado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
        </tr>
        </thead>
        
        <tbody>
            <!-- Recorremos cada usuario y generamos una fila por cada uno -->
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                <td><?= $usuario['nombre_usuario'] ?></td>
                <td><?= $usuario['a_paterno'] ?></td>
                <td><?= $usuario['a_materno'] ?></td>
                <td><?= $usuario['email'] ?></td>
                <td><?= $usuario['rol'] ?></td>
                <td><?= $usuario['estatus'] ?></td>
                <td><?= $usuario['fecha_creacion'] ?></td>
                <td><?= $usuario['fecha_actualizacion'] ?></td>
                <td>
                    <!-- Botón para editar, enviando el ID del usuario por GET -->
                    <a class="boton2" href="../../public/admin/editar_usuario.php?id=<?= $usuario['id'] ?>">Editar</a>
                    <!-- Botón para eliminar con confirmación -->
                    <a class="boton2 eliminar" href="eliminar_usuario.php?id=<?= $usuario['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este usuario?');">Eliminar</a>
                </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
</body>
</html>