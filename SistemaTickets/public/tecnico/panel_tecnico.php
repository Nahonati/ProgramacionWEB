<?php
session_start();

// 1. Verificamos que solo el técnico (rol 3) tenga acceso
if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 3) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../../includes/conexion_db.php';

// 2. Obtenemos el ID del técnico que acaba de iniciar sesión
$tecnico_id = $_SESSION['usuario_id'];

// 3. Consultamos SOLO los tickets asignados a este técnico
$sqlTickets = "SELECT t.id_ticket, t.titulo, t.fecha_creacion, 
                tc.nombre AS categoria, 
                tp.nombre AS prioridad, 
                ts.nombre AS estatus,
                CONCAT(u.nombre, ' ', u.a_paterno) AS solicitante
               FROM tickets t
               JOIN ticket_categoria tc ON t.categoria_id = tc.id_ctgy
               JOIN ticket_prioridad tp ON t.prioridad_id = tp.id_prio
               JOIN ticket_status ts ON t.estatus_id = ts.id_status
               JOIN usuarios u ON t.usuario_reporta_id = u.id_usuario
               WHERE t.tecnico_asignado_id = :tecnico_id
               ORDER BY t.estatus_id ASC, t.id_ticket DESC"; // Ordenamos por estatus para ver los abiertos primero

$stmt = $conn->prepare($sqlTickets);
$stmt->execute([':tecnico_id' => $tecnico_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Técnico | Sistema de Tickets</title>
    <link rel="stylesheet" href="../../assets/css/estilo_home.css">
    <link rel="stylesheet" href="../../assets/css/estilo_admin.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        
        <div class="admin-navbar">
            <div class="admin-brand">Portal del Técnico</div>
            <div class="admin-actions" style="display: flex; gap: 15px; align-items: center;">
                <span style="color: #64748b; font-size: 0.9rem;">
                    Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Técnico'); ?></strong>
                </span>
                <span class="admin-pill">Rol: Tecnico</span> 
                <a href="../../logout.php" class="admin-btn admin-btn-danger">Cerrar Sesión</a>
            </div>
        </div>

        <div class="admin-grid">
            <section class="admin-card" style="grid-column: 1 / -1;">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Mis Tickets Asignados</div>
                        <div class="admin-card-subtitle">Aquí verás los problemas que el Administrador te ha delegado.</div>
                    </div>
                </div>

                <div class="tickets-table-wrap" style="margin-top: 15px;">
                    <table class="table-custom" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0;">
                                <th style="text-align:left; padding: 10px 8px;">Folio</th>
                                <th style="text-align:left; padding: 10px 8px;">Problema</th>
                                <th style="text-align:left; padding: 10px 8px;">Solicitante</th>
                                <th style="text-align:left; padding: 10px 8px;">Prioridad</th>
                                <th style="text-align:left; padding: 10px 8px;">Estatus</th>
                                <th style="text-align:left; padding: 10px 8px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($tickets) === 0): ?>
                                <tr>
                                    <td colspan="6" style="padding: 15px 8px; text-align: center; color:#64748b;">
                                        ¡Excelente trabajo! No tienes tickets pendientes en este momento.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px 8px; font-weight: bold; color: #475569;">#<?php echo $ticket['id_ticket']; ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($ticket['solicitante']); ?></td>
                                        <td style="padding: 10px 8px;">
                                            <span class="admin-pill" style="background: #fee2e2; color: #991b1b;">
                                                <?php echo htmlspecialchars($ticket['prioridad']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 10px 8px;">
                                            <span class="admin-pill" style="background: #e0e7ff; color: #3730a3;">
                                                <?php echo htmlspecialchars($ticket['estatus']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 10px 8px;">
                                            <a href="atender_ticket.php?id=<?php echo $ticket['id_ticket']; ?>" class="admin-btn admin-btn-primary" style="padding: 6px 12px; font-size: 0.8rem; text-decoration: none;">
                                                Atender
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</body>
</html>