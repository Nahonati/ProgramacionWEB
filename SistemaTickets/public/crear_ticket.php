<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit();
}

require __DIR__ . '/../includes/conexion_db.php';

$tituloTicket = trim($_POST['titulo_ticket'] ?? '');
$categoriaTicket = (int)($_POST['categoria_ticket'] ?? 0);
$prioridadTicket = (int)($_POST['prioridad_ticket'] ?? 0);
$descripcionTicket = trim($_POST['descripcion_ticket'] ?? '');
$usuarioId = (int)$_SESSION['usuario_id'];

$_SESSION['flash_old_input'] = [
    'titulo_ticket' => $tituloTicket,
    'categoria_ticket' => $categoriaTicket,
    'prioridad_ticket' => $prioridadTicket,
    'descripcion_ticket' => $descripcionTicket
];

if ($tituloTicket === '' || $categoriaTicket <= 0 || $prioridadTicket <= 0 || $descripcionTicket === '') {
    $_SESSION['flash_error'] = 'Completa todos los campos obligatorios para crear el ticket.';
    header('Location: home.php');
    exit();
}

try {
    $sqlInsert = "INSERT INTO tickets (titulo, descripcion, usuario_reporta_id, tecnico_asignado_id, categoria_id, prioridad_id, estatus_id)
                  VALUES (:titulo, :descripcion, :usuario_reporta_id, NULL, :categoria_id, :prioridad_id, 1)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->execute([
        'titulo' => $tituloTicket,
        'descripcion' => $descripcionTicket,
        'usuario_reporta_id' => $usuarioId,
        'categoria_id' => $categoriaTicket,
        'prioridad_id' => $prioridadTicket
    ]);

    unset($_SESSION['flash_old_input']);
    $_SESSION['flash_ok'] = 'Ticket creado correctamente.';
} catch (PDOException $e) {
    $_SESSION['flash_error'] = 'No se pudo crear el ticket. Intenta nuevamente.';
}

header('Location: home.php');
exit();
