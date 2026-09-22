<?php
session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['usuario_id'];
    $id_habito = $_POST['id_habito'] ?? null;
    $descripcion = trim($_POST['descripcion'] ?? '');
    $cantidad_objetivo = $_POST['cantidad_objetivo'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    // Validación de campos vacíos
    if (!$id_habito || !$descripcion || !$cantidad_objetivo || !$fecha_inicio || !$fecha_fin) {
        header("Location: ../../public/metas/crear_meta.php?error=faltan_datos");
        exit;
    }

    // Validar que la fecha de inicio no sea posterior a la de fin
    if (strtotime($fecha_inicio) > strtotime($fecha_fin)) {
        header("Location: ../../public/metas/crear_meta?error=fecha_invalida");
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO metas (descripcion, fecha_inicio, fecha_fin, cantidad_objetivo, id_usuario, id_habito)
                            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $descripcion,
            $fecha_inicio,
            $fecha_fin,
            $cantidad_objetivo,
            $id_usuario,
            $id_habito
        ]);

        header("Location: metas.php?success=1");
        exit;
    } catch (PDOException $e) {
        echo "Error al guardar la meta: " . $e->getMessage();
        exit;
    }
} else {
    header("Location: crear:meta.php");
    exit;
}