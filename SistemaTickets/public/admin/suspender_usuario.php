<?php
session_start();

// 1. Verificamos que solo el administrador tenga acceso
if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 1) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../../includes/conexion_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = (int)($_POST['usuario_id'] ?? 0);

    // 2. Seguridad: Evitar que el admin en sesión se borre a sí mismo
    if ($usuario_id === (int)$_SESSION['usuario_id']) {
        $_SESSION['flash_error'] = "Por seguridad, no puedes eliminar tu propia cuenta de administrador.";
        header('Location: administrador.php');
        exit();
    }

    if ($usuario_id > 0) {
        try {
            // 3. Borrado físico (Hard Delete) de la base de datos
            $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $usuario_id]);

            $_SESSION['flash_ok'] = "Usuario #$usuario_id eliminado permanentemente del sistema.";
            
        } catch (PDOException $e) {
            // 4. Atrapamos el error si el usuario ya tiene tickets (Error de llave foránea)
            if ($e->getCode() == '23000') {
                $_SESSION['flash_error'] = "No se puede eliminar este usuario porque ya tiene tickets asociados a su nombre. (Requiere borrado lógico).";
            } else {
                $_SESSION['flash_error'] = "Error al intentar eliminar el usuario: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['flash_error'] = "ID de usuario no válido.";
    }
}

// 5. Regresamos a la pantalla del panel
header('Location: administrador.php');
exit();
?>