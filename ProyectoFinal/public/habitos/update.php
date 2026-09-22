<?php
session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Solo aceptar peticiones POST desde el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario    = $_SESSION['usuario_id'];
    $id_habito     = $_POST['id_habito'] ?? null;
    $nombre_habito = trim($_POST['nombre_habito'] ?? '');
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $id_frecuencia = $_POST['id_frecuencia'] ?? null;

    // Validación básica antes de guardar los cambios
    if (!$id_habito || !$nombre_habito ||  !$id_frecuencia) {
        header("Location: ../../public/habitos/editar_habito.php?id=$id_habito&error=campos_obligatorios");
        exit;
    }

    try {
        // Verificar que el hábito le pertenezca al usuario que está logueado
        $stmt = $conn->prepare("SELECT id FROM habitos WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id_habito, $id_usuario]);

        if (!$stmt->fetch()) {
            // Si no lo encuentra o no es suyo, regresarlo
            header("Location: ../../public/habitos/habitos.php?error=notfound");
            exit;
        }

        // Todo bien, actualizar el hábito
        $stmt = $conn->prepare("UPDATE habitos 
                            SET nombre = ?, descripcion = ?, id_frecuencia = ?
                            WHERE id = ?");
        $stmt->execute([
            $nombre_habito,
            $descripcion,
            $id_frecuencia,
            $id_habito
        ]);

        // Redirigir con mensaje de éxito
        header("Location: ../../public/habitos/habitos.php?updated=1");
        exit;

    } catch (PDOException $e) {
        // Solo durante desarrollo
        echo "Error al actualizar el hábito: " . $e->getMessage();
        exit;
    }
} else {
    // Si alguien entra aquí sin enviar POST, redirigirlo
    header("Location: ../../public/habitos/habitos.php");
    exit;
}