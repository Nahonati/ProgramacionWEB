<?php
session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$id_meta = $_GET['id'] ?? null;

// Si no se proporciona un ID válido, redirigir 
if (!$id_meta) {
    header("Location: ../../public/metas/metas.php");
    exit;
}

try {
    // Verificar que la meta que se quiere eliminar le pertenezca al usuario logueado
    $stmt = $conn->prepare("SELECT * FROM metas WHERE id_meta = ? AND id_usuario = ?");
    $stmt->execute([$id_meta, $id_usuario]);
    $meta = $stmt->fetch();

    // Si no existe la meta o no pertenece al usuario, redirigircon error
    if (!$meta) {
        header("Location: ../../public/metas/metas.php?error=notfound");
        exit;
    }

    // Una vez verificado, eliminar la meta de la base de datos
    $stmt = $conn->prepare("DELETE FROM metas WHERE id_meta = ? AND id_usuario = ?");
    $stmt->execute([$id_meta, $id_usuario]);

    // Redirigir con mensaje de éxito
    header("Location: ../../public/metas/metas.php?deleted=1");
    exit;

} catch (PDOException $e) {
    // En caso de error de base de datos
    echo "Error al eliminar la meta: " . $e->getMessage();
    exit;
}