<?php
    session_start();
    require '../includes/conexion.php';

    // Desde el formulario
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Conexion a la base de datos
    $sql ="SELECT * FROM usuarios WHERE email = :email";
    $stmt = $conn->prepare($sql); // limpiar, evita la inyeccion sql
    $stmt->execute(['email'=>$usuario]);
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);


    if($usuario && password_verify($password, $usuario['password'])){ // cifra la contraseña
        // Validamos estatus
    if ($usuario['estatus_id'] == 3 || $usuario['estatus_id'] == 5) {
        // Usuario suspendido o eliminado
        header("Location: login.php?error=estatus");
        exit();
    }
        $_SESSION['usuario_id'] = $usuario['id']; // (variables) de la base de datos
        $_SESSION['nombre_completo'] = $usuario['nombres'] . " " . $usuario['a_paterno'];
        $_SESSION['rol_id'] = $usuario['rol_id'];

        // Redirigimos según el rol
        if ($usuario['rol_id'] == 1) {
        // Si es administrador
        header("Location: ../public/admin/administrador.php"); // página exclusiva del admin
        exit();
    } else {
        // Usuario común
        header("Location: ../public/home.php");
        exit();
    }
} else {
    // Datos incorrectos
    header("Location: ../public/login.php?error=1");
    exit();
}
    
?>