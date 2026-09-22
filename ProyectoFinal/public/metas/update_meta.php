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
    $id_meta = $_POST['id_meta'] ?? null;
    $id_habito = $_POST['id_habito'] ?? null;
    $descripcion = trim($_POST['descripcion'] ?? '');
    $cantidad_objetivo = $_POST['cantidad_objetivo'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    if (!$id_meta || !$id_habito || !$descripcion || !$cantidad_objetivo || !$fecha_inicio || !$fecha_fin) {
        header("Location: editar_meta.php?id=$id_meta&error=datos_invalidos");
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE metas SET descripcion = ?, fecha_inicio = ?, fecha_fin = ?, cantidad_objetivo = ?, id_habito = ? 
                            WHERE id_meta = ? AND id_usuario = ?");
        $stmt->execute([
            $descripcion,
            $fecha_inicio,
            $fecha_fin,
            $cantidad_objetivo,
            $id_habito,
            $id_meta,
            $id_usuario
        ]);

        header("Location: metas.php?updated=1");
        exit;

    } catch (PDOException $e) {
        echo "Error al actualizar la meta: " . $e->getMessage();
        exit;
    }
} else {
    header("Location: metas.php");
    exit;
}