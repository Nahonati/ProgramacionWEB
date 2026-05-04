<?php
session_start();

// 1. Verificamos que solo el administrador tenga acceso
if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 1) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../../includes/conexion_db.php';

// 2. Obtenemos el ID del usuario, ya sea por la URL (GET) o por el formulario (POST)
$id_usuario = (int)($_GET['id'] ?? $_POST['id_usuario'] ?? 0);

// Si no hay un ID válido, lo regresamos al panel
if ($id_usuario === 0) {
    header('Location: administrador.php');
    exit();
}

$mensajeError = '';

// 3. LÓGICA PARA GUARDAR LOS CAMBIOS (Cuando se envía el formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $a_paterno = trim($_POST['a_paterno'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // La contraseña puede venir vacía
    $rol_id = (int)($_POST['rol_id'] ?? 0);
    $dept_id = (int)($_POST['dept_id'] ?? 0);

    if ($nombre && $a_paterno && $email && $rol_id && $dept_id) {
        try {
            // Verificamos que el correo no esté usando por OTRO usuario
            $stmtCheck = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = :email AND id_usuario != :id");
            $stmtCheck->execute([':email' => $email, ':id' => $id_usuario]);
            
            if ($stmtCheck->rowCount() > 0) {
                $mensajeError = "Error: El correo $email ya pertenece a otro usuario.";
            } else {
                // Si escribieron una contraseña nueva, la actualizamos
                if ($password !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE usuarios SET nombre = :nombre, a_paterno = :a_paterno, email = :email, password = :password, rol_id = :rol_id, dept_id = :dept_id WHERE id_usuario = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':nombre' => $nombre, ':a_paterno' => $a_paterno, ':email' => $email,
                        ':password' => $passwordHash, ':rol_id' => $rol_id, ':dept_id' => $dept_id, ':id' => $id_usuario
                    ]);
                } else {
                    // Si dejaron la contraseña en blanco, actualizamos todo lo demás EXCEPTO la contraseña
                    $sql = "UPDATE usuarios SET nombre = :nombre, a_paterno = :a_paterno, email = :email, rol_id = :rol_id, dept_id = :dept_id WHERE id_usuario = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':nombre' => $nombre, ':a_paterno' => $a_paterno, ':email' => $email,
                        ':rol_id' => $rol_id, ':dept_id' => $dept_id, ':id' => $id_usuario
                    ]);
                }

                $_SESSION['flash_ok'] = "Datos de $nombre actualizados correctamente.";
                header('Location: administrador.php');
                exit();
            }
        } catch (PDOException $e) {
            $mensajeError = "Error en la base de datos al actualizar.";
        }
    } else {
        $mensajeError = "Por favor, completa todos los campos obligatorios.";
    }
}

// 4. LÓGICA PARA MOSTRAR LOS DATOS ACTUALES (Cuando recién entra a la página)
$stmtUser = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
$stmtUser->execute([':id' => $id_usuario]);
$usuarioInfo = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Si quisieron buscar un usuario que no existe, lo sacamos de aquí
if (!$usuarioInfo) {
    header('Location: administrador.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | Sistema de Tickets</title>
    <link rel="stylesheet" href="../../assets/css/estilo_home.css">
    <link rel="stylesheet" href="../../assets/css/estilo_admin.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        
        <div class="admin-navbar">
            <div class="admin-brand">Sistema de Tickets</div>
            <div class="admin-actions">
                <a href="administrador.php" class="admin-btn" style="background: #e2e8f0; color: #334155; text-decoration: none;">Volver al Panel</a>
            </div>
        </div>

        <div class="admin-grid">
            <section class="admin-card" style="grid-column: 1 / -1; max-width: 800px; margin: 0 auto; width: 100%;">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Editar Usuario #<?php echo $id_usuario; ?></div>
                        <div class="admin-card-subtitle">Modifica los datos o asigna una nueva contraseña.</div>
                    </div>
                </div>

                <?php if ($mensajeError !== ''): ?>
                    <div class="admin-note" style="color:#b91c1c; font-weight:700; margin-bottom: 15px;">
                        <?php echo htmlspecialchars($mensajeError); ?>
                    </div>
                <?php endif; ?>

                <form action="editar_usuario.php" method="post">
                    <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">

                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="nombre">Nombre(s)</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuarioInfo['nombre']); ?>" required />
                        </div>

                        <div class="admin-field">
                            <label for="a_paterno">Apellido paterno</label>
                            <input type="text" id="a_paterno" name="a_paterno" value="<?php echo htmlspecialchars($usuarioInfo['a_paterno']); ?>" required />
                        </div>

                        <div class="admin-field">
                            <label for="email">Correo</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuarioInfo['email']); ?>" required />
                        </div>

                        <div class="admin-field">
                            <label for="password">Nueva Contraseña</label>
                            <input type="password" id="password" name="password" placeholder="Dejar en blanco para no cambiarla" />
                        </div>

                        <div class="admin-field">
                            <label for="rol">Rol</label>
                            <select id="rol" name="rol_id" required>
                                <option value="1" <?php echo ($usuarioInfo['rol_id'] == 1) ? 'selected' : ''; ?>>Administrador</option>
                                <option value="2" <?php echo ($usuarioInfo['rol_id'] == 2) ? 'selected' : ''; ?>>Usuario</option>
                                <option value="3" <?php echo ($usuarioInfo['rol_id'] == 3) ? 'selected' : ''; ?>>Tecnico</option>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="dept">Departamento</label>
                            <select id="dept" name="dept_id" required>
                                <option value="1" <?php echo ($usuarioInfo['dept_id'] == 1) ? 'selected' : ''; ?>>Recursos Humanos</option>
                                <option value="2" <?php echo ($usuarioInfo['dept_id'] == 2) ? 'selected' : ''; ?>>Administración</option>
                                <option value="3" <?php echo ($usuarioInfo['dept_id'] == 3) ? 'selected' : ''; ?>>Produccion</option>
                                <option value="4" <?php echo ($usuarioInfo['dept_id'] == 4) ? 'selected' : ''; ?>>Arte</option>
                                <option value="5" <?php echo ($usuarioInfo['dept_id'] == 5) ? 'selected' : ''; ?>>Compositing</option>
                                <option value="6" <?php echo ($usuarioInfo['dept_id'] == 6) ? 'selected' : ''; ?>>2D</option>
                                <option value="7" <?php echo ($usuarioInfo['dept_id'] == 7) ? 'selected' : ''; ?>>3D</option>
                                <option value="8" <?php echo ($usuarioInfo['dept_id'] == 8) ? 'selected' : ''; ?>>Storyboard</option>
                                <option value="9" <?php echo ($usuarioInfo['dept_id'] == 9) ? 'selected' : ''; ?>>Render</option>
                            </select>
                        </div>
                    </div>

                    <div class="admin-btn-row" style="margin-top: 20px;">
                        <button type="submit" class="admin-btn admin-btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</body>
</html>