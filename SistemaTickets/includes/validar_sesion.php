<?php
    session_start();
    require __DIR__ . '/conexion_db.php';

    // 1. Recibir datos del formulario (Cambié el nombre de la variable para no sobreescribirla luego)
    $email_ingresado = $_POST['usuario'] ?? '';
    $password_ingresada = $_POST['password'] ?? '';

    if ($email_ingresado === '' || $password_ingresada === '') {
        header("Location: ../index.php?error=1");
        exit();
    }

    // 2. Conexión y búsqueda en la base de datos
    $sql ="SELECT * FROM usuarios WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['email' => $email_ingresado]);
    
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Validar si existe el correo y si la contraseña coincide
    if($datos_usuario && password_verify($password_ingresada, $datos_usuario['password'])) { 
        
        // (Nota: Quité la validación del estatus temporalmente porque no existe en la BD)

        // 4. Variables de sesión con los nombres EXACTOS de tu BD
        $_SESSION['usuario_id'] = $datos_usuario['id_usuario']; 
        $_SESSION['nombre_completo'] = $datos_usuario['nombre'] . " " . $datos_usuario['a_paterno'];
        $_SESSION['rol_id'] = $datos_usuario['rol_id'];
        
        // ¡Tip pro! Te servirá mucho guardar el departamento en la sesión para el futuro
        $_SESSION['dept_id'] = $datos_usuario['dept_id']; 

        // 5. Redirigimos según los 3 roles de tu sistema
        if ($datos_usuario['rol_id'] == 1) {
            // Administrador
            header("Location: ../public/admin/administrador.php");
            exit();
        } elseif ($datos_usuario['rol_id'] == 3) {
            // Técnico de Soporte
            header("Location: ../public/tecnico/dashboard.php");
            exit();
        } else {
            // Usuario normal (Rol 2)
            header("Location: ../public/home.php");
            exit();
        }

    } else {
        // Datos incorrectos o contraseña inválida
        header("Location: ../index.php?error=1");
        exit();
    }
?>