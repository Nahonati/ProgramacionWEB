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

// Validar que se haya recibido un ID válido por GET
if (!$id_habito || !is_numeric($id_habito)) {
    header("Location: ../../public/habitos/habitos.php?error=missing_id");
    exit;
}

try {
    // Verificar que el hábito realmente le pertenezca al usuario actual
    $stmt = $conn->prepare("SELECT id FROM habitos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id_habito, $id_usuario]);
    $habito = $stmt->fetch();

    if (!$habito) {
        // Si el hábito no pertenece al usuario o no existe, no se permite seguir
        header("Location: ../../public/habitos/habitos.php?error=not_found");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM registro_habitos WHERE habito_id = ?");
    $stmt->execute([$id_habito]);

    // eliminar metas relacionadas con ese hábito (si existen)
    $stmt = $conn->prepare("DELETE FROM metas WHERE id_habito = ?");
    $stmt->execute([$id_habito]);

    // Finalmente eliminar el hábito en sí
    $stmt = $conn->prepare("DELETE FROM habitos WHERE id = ?");
    $stmt->execute([$id_habito]);

    // Redirigir de vuelta al listado con mensaje de éxito
    header("Location: ../../public/habitos/habitos.php?deleted=1");
    exit;

} catch (PDOException $e) {
    // En caso de error de base de datos
    echo "Error al eliminar el hábito: " . $e->getMessage();
    exit;
}