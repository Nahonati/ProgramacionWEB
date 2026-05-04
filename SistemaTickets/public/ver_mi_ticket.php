<?php
session_start();

// 1. Verificamos que haya sesión (aquí permitimos al usuario normal, rol 2 usualmente)
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../includes/conexion_db.php';

$id_ticket = (int)($_GET['id'] ?? 0);
$usuario_sesion_id = $_SESSION['usuario_id'];

if ($id_ticket === 0) {
    header('Location: panel_usuario.php'); 
    exit();
}

// 2. LÓGICA PARA QUE EL USUARIO TAMBIÉN PUEDA COMENTAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_comentario'])) {
    $nuevo_comentario = trim($_POST['comentario'] ?? '');
    if ($nuevo_comentario !== '') {
        try {
            // Un usuario normal NUNCA crea notas internas, por eso es_nota_interna siempre es 0
            $sqlCom = "INSERT INTO ticket_comentarios (ticket_id, usuario_id, comentario, es_nota_interna) 
                       VALUES (:ticket_id, :usuario_id, :comentario, 0)";
            $stmtCom = $conn->prepare($sqlCom);
            $stmtCom->execute([
                ':ticket_id' => $id_ticket,
                ':usuario_id' => $usuario_sesion_id,
                ':comentario' => $nuevo_comentario
            ]);
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}

// 3. TRAER DATOS DEL TICKET (Seguridad: solo si él lo reportó)
$sqlTicket = "SELECT t.*, tc.nombre AS categoria_nombre, tp.nombre AS prioridad_nombre, ts.nombre AS estado_nombre
              FROM tickets t
              JOIN ticket_categoria tc ON t.categoria_id = tc.id_ctgy
              JOIN ticket_prioridad tp ON t.prioridad_id = tp.id_prio
              JOIN ticket_status ts ON t.estatus_id = ts.id_status
              WHERE t.id_ticket = :id AND t.usuario_reporta_id = :user_id";

$stmtTicket = $conn->prepare($sqlTicket);
$stmtTicket->execute([':id' => $id_ticket, ':user_id' => $usuario_sesion_id]);
$ticket = $stmtTicket->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    header('Location: panel_usuario.php');
    exit();
}

// 4. TRAER COMENTARIOS (FILTRANDO NOTAS INTERNAS)
// Aquí está el truco: es_nota_interna = 0
$sqlComentarios = "SELECT c.*, CONCAT(u.nombre, ' ', u.a_paterno) AS autor, u.rol_id 
                   FROM ticket_comentarios c 
                   JOIN usuarios u ON c.usuario_id = u.id_usuario 
                   WHERE c.ticket_id = :id AND c.es_nota_interna = 0 
                   ORDER BY c.fecha_creacion ASC";
$stmtComentarios = $conn->prepare($sqlComentarios);
$stmtComentarios->execute([':id' => $id_ticket]);
$comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Ticket #<?php echo $id_ticket; ?></title>
    <link rel="stylesheet" href="../assets/css/estilo_home.css">
    <link rel="stylesheet" href="../assets/css/estilo_admin.css">
</head>
<body class="admin-body">
    <div class="admin-container admin-container--narrow">

        <div class="admin-navbar">
            <div class="admin-brand">Estatus de mi Reporte</div>
            <a href="home.php" class="admin-btn admin-btn-secondary">Volver al Panel</a>
        </div>

        <section class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h1 style="font-size: 1.4rem; margin: 0;">#<?php echo $id_ticket; ?> - <?php echo htmlspecialchars($ticket['titulo']); ?></h1>
                    <p style="color: #64748b; font-size: 0.9rem;">Estado actual: <strong><?php echo htmlspecialchars($ticket['estado_nombre']); ?></strong></p>
                </div>
            </div>

            <div class="ticket-detail-desc">
                <p><?php echo htmlspecialchars($ticket['descripcion']); ?></p>
            </div>

            <div style="margin-top: 30px;">
                <h2 style="font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">Historial de Respuestas</h2>

                <div class="comment-stack">
                    <?php foreach ($comentarios as $com): ?>
                        <?php $esTecnico = ($com['rol_id'] == 3); ?>
                        <div class="comment-item <?php echo $esTecnico ? 'comment-item--tech' : 'comment-item--user'; ?>">
                            <div class="comment-item-head">
                                <span style="font-weight: bold;"><?php echo htmlspecialchars($com['autor']); ?> <?php echo $esTecnico ? '(Soporte Técnico)' : '(Tú)'; ?></span>
                                <span><?php echo date('d M, H:i', strtotime($com['fecha_creacion'])); ?></span>
                            </div>
                            <p class="comment-item-body"><?php echo htmlspecialchars($com['comentario']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form action="" method="post" style="margin-top: 20px;">
                    <div class="admin-field">
                        <textarea name="comentario" id="comentario_usuario" rows="3" placeholder="Escribe un mensaje al técnico..." required></textarea>
                    </div>
                    <div class="admin-btn-row">
                        <button type="submit" name="agregar_comentario" class="admin-btn admin-btn-primary">Enviar mensaje</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</body>
</html>