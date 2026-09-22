<?php
session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.php");
    exit();
}


$id_usuario = $_SESSION['usuario_id'];
$id_habito = $_GET['id'] ?? null;
$fecha_hoy = date('Y-m-d');

// Si no se proporcionó ID, redirigir a la lista
if (!$id_habito) {
    header("Location: ../../public/habitos/habitos.php");
    exit;
}

try {
    // Primero revisar que el hábito pertenezca al usuario y esté activo
    $stmt = $conn->prepare("INSERT INTO registro_habitos (fecha_creacion, completado, habito_id, id_usuario) VALUES (?, 1, ?, ?)");
    $stmt->execute([$fecha_hoy, $id_habito, $id_usuario]);
    $habito = $stmt->fetch();

    // Si no existe o no le pertenece, redirigir
    if (!$habito) {
        header("Location: ../../public/habitos/habitos.php?error=notfound");
        exit;
    }

    // Revisar si ya marcó este hábito como completado hoy
    $stmt = $conn->prepare("SELECT id FROM registro_habitos WHERE habito_id = ? AND fecha_creacion = ?");
    $stmt->execute([$id_habito, $fecha_hoy]);
    $existe = $stmt->fetch();

    // Si ya existe una entrada para hoy, redirigir y evitar duplicados
    if ($existe) {
        header("Location: ../../public/habitos/habitos.php?already_done=1");
        exit;
    }

    // Si no existe, entonces insertar una entrada marcándolo como completado
    $stmt = $conn->prepare("INSERT INTO registro_habitos (fecha_creacion, completado, habito_id) VALUES (?, 1, ?)");
    $stmt->execute([$fecha_hoy, $id_habito]);

    // Redirigir con mensaje de éxito
    header("Location: ../../public/habitos/habitos.php?done=1");
    exit;

} catch (PDOException $e) {
    // En caso de error con la BD
    echo "Error al marcar hábito como completado: " . $e->getMessage();
    exit;
}