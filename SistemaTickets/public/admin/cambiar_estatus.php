<?php
session_start();

// Validamos que sea el administrador
if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 1) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../../includes/conexion_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = (int)($_POST['ticket_id'] ?? 0);
    $nuevo_estatus = (int)($_POST['nuevo_estatus'] ?? 5);

    if ($ticket_id > 0) {
        try {
            // AQUÍ ESTÁ LA MAGIA DEL BORRADO LÓGICO: Solo actualizamos el estatus
            $sql = "UPDATE tickets SET estatus_id = :estatus WHERE id_ticket = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':estatus' => $nuevo_estatus, 
                ':id' => $ticket_id
            ]);

            $_SESSION['flash_ok'] = "Ticket #$ticket_id actualizado correctamente.";
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Error al actualizar el ticket.";
        }
    } else {
        $_SESSION['flash_error'] = "ID de ticket no válido.";
    }
}

// Lo regresamos a la pantalla del administrador
header("Location: administrador.php");
exit();
?>