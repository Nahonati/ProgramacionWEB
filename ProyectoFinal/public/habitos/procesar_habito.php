<?php

session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar que la petición venga del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario     = $_SESSION['usuario_id'];
    $nombre  = trim($_POST['nombre'] ?? '');
    $descripcion    = trim($_POST['descripcion'] ?? '');
    $id_frecuencia  = $_POST['id_frecuencia'] ?? '';
    $hora = date('H:i:s');

    // Validaciones mínimas para evitar que lleguen campos vacíos
    if (
        empty($nombre) || 
        !is_numeric($id_frecuencia)
    ) {
        // Si algo falla, regresar a create con error por campos incompletos
        header("Location: ../../public/habitos/crear_habito.php?error=campos_obligatorios");
        exit;
    }

    $dias_personalizados = null;
    if ((int)$id_frecuencia === 3 && !empty($_POST['dias_personalizados'])) {
    $dias_personalizados = implode(',', $_POST['dias_personalizados']); // Ejemplo: "Lunes,Miércoles,Viernes"
}
    try {
        // Preparar e insertar el nuevo hábito en la base de datos
        $stmt = $conn->prepare("INSERT INTO habitos 
        (nombre, descripcion, usuario_id, id_frecuencia, hora, dias_personalizados)
        VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nombre,
            $descripcion,
            $id_usuario,
            $id_frecuencia,
            $hora,
            $dias_personalizados
]);

        // Todo salió bien, redirigir a la lista con un mensaje de éxito
        header("Location: ../../public/habitos/habitos.php?success=1");
        exit;

    } catch (PDOException $e) {
        // Este error en desarrollo
        echo "Error al guardar el hábito: " . $e->getMessage();
        exit;
    }
} else {
    // Si alguien entra aquí sin mandar formulario, regresar a create
    header("Location: ../../public/habitos/crear_habito.php");
    exit;
}