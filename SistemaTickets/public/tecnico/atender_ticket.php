<?php
session_start();

// 1. Verificamos que sea un Técnico (Rol 3)
if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 3) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../../includes/conexion_db.php';

$id_ticket = (int)($_GET['id'] ?? $_POST['id_ticket'] ?? 0);
$tecnico_id = $_SESSION['usuario_id'];

if ($id_ticket === 0) {
    header('Location: panel_tecnico.php'); 
    exit();
}

$mensajeExito = '';
$mensajeError = '';

// 2. LÓGICA PARA FORMULARIOS (Estatus o Comentario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CASO A: Actualizar Estatus
    if (isset($_POST['actualizar_estatus'])) {
        $nuevo_estatus = (int)($_POST['estatus_id'] ?? 0);
        try {
            $sql = "UPDATE tickets SET estatus_id = :estatus WHERE id_ticket = :id AND tecnico_asignado_id = :tec_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':estatus' => $nuevo_estatus, ':id' => $id_ticket, ':tec_id' => $tecnico_id]);
            $mensajeExito = "¡El estatus ha sido actualizado correctamente!";
        } catch (PDOException $e) {
            $mensajeError = "Error al actualizar: " . $e->getMessage();
        }
    }
    
    // CASO B: Agregar un Comentario
    if (isset($_POST['agregar_comentario'])) {
        $nuevo_comentario = trim($_POST['comentario'] ?? '');
        // Verificamos si marcó la casilla de nota interna
        $es_nota_interna = isset($_POST['es_nota_interna']) ? 1 : 0; 

        if ($nuevo_comentario !== '') {
            try {
                // Insertamos respetando los nombres de tu tabla
                $sqlCom = "INSERT INTO ticket_comentarios (ticket_id, usuario_id, comentario, es_nota_interna) 
                           VALUES (:ticket_id, :usuario_id, :comentario, :es_nota_interna)";
                $stmtCom = $conn->prepare($sqlCom);
                $stmtCom->execute([
                    ':ticket_id' => $id_ticket,
                    ':usuario_id' => $tecnico_id,
                    ':comentario' => $nuevo_comentario,
                    ':es_nota_interna' => $es_nota_interna
                ]);
                $mensajeExito = "Comentario guardado en la bitácora.";
            } catch (PDOException $e) {
                $mensajeError = "Error al guardar el comentario: " . $e->getMessage();
            }
        }
    }
}

// 3. TRAER LOS DATOS DEL TICKET
$sqlTicket = "SELECT t.*, tc.nombre AS categoria_nombre, tp.nombre AS prioridad_nombre, ts.nombre AS estado_nombre,
                CONCAT(u.nombre, ' ', u.a_paterno) AS solicitante, u.email AS correo_solicitante
              FROM tickets t
              JOIN ticket_categoria tc ON t.categoria_id = tc.id_ctgy
              JOIN ticket_prioridad tp ON t.prioridad_id = tp.id_prio
              JOIN ticket_status ts ON t.estatus_id = ts.id_status
              JOIN usuarios u ON t.usuario_reporta_id = u.id_usuario
              WHERE t.id_ticket = :id AND t.tecnico_asignado_id = :tec_id";
$stmtTicket = $conn->prepare($sqlTicket);
$stmtTicket->execute([':id' => $id_ticket, ':tec_id' => $tecnico_id]);
$ticketInfo = $stmtTicket->fetch(PDO::FETCH_ASSOC);

if (!$ticketInfo) {
    header('Location: panel_tecnico.php');
    exit();
}

// 4. TRAER LOS COMENTARIOS DE ESTE TICKET
// Usamos tu llave id_coment y traemos el campo es_nota_interna
$sqlComentarios = "SELECT c.id_coment, c.comentario, c.fecha_creacion, c.es_nota_interna, 
                          CONCAT(u.nombre, ' ', u.a_paterno) AS autor, u.rol_id 
                   FROM ticket_comentarios c 
                   JOIN usuarios u ON c.usuario_id = u.id_usuario 
                   WHERE c.ticket_id = :id 
                   ORDER BY c.fecha_creacion ASC";
$stmtComentarios = $conn->prepare($sqlComentarios);
$stmtComentarios->execute([':id' => $id_ticket]);
$comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);

$estados = $conn->query("SELECT id_status, nombre FROM ticket_status ORDER BY id_status ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atender Ticket | Sistema de Tickets</title>
    <link rel="stylesheet" href="../../assets/css/estilo_home.css">
    <link rel="stylesheet" href="../../assets/css/estilo_admin.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        
        <div class="admin-navbar">
            <div class="admin-brand">Portal del Técnico</div>
            <div class="admin-actions">
                <a href="panel_tecnico.php" class="admin-btn" style="background: #e2e8f0; color: #334155; text-decoration: none;">Volver a mis tickets</a>
            </div>
        </div>

        <?php if ($mensajeExito !== ''): ?>
            <div class="admin-note" style="color:#166534; background:#dcfce7; padding:10px; border-radius:5px; font-weight:bold; margin-bottom: 15px;">
                <?php echo $mensajeExito; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($mensajeError !== ''): ?>
            <div class="admin-note" style="color:#b91c1c; background:#fee2e2; padding:10px; border-radius:5px; font-weight:bold; margin-bottom: 15px;">
                <?php echo $mensajeError; ?>
            </div>
        <?php endif; ?>

        <div class="admin-grid" style="grid-template-columns: 2fr 1fr;">
            
            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Ticket #<?php echo $ticketInfo['id_ticket']; ?>: <?php echo htmlspecialchars($ticketInfo['titulo']); ?></div>
                        <div class="admin-card-subtitle">Reportado el <?php echo date('d M Y, H:i', strtotime($ticketInfo['fecha_creacion'])); ?></div>
                    </div>
                    <div>
                        <span class="admin-pill" style="background: #e0e7ff; color: #3730a3;"><?php echo htmlspecialchars($ticketInfo['categoria_nombre']); ?></span>
                        <span class="admin-pill" style="background: #fee2e2; color: #991b1b;"><?php echo htmlspecialchars($ticketInfo['prioridad_nombre']); ?></span>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background-color: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <h3 style="margin-top: 0; font-size: 0.9rem; color: #64748b; text-transform: uppercase;">Descripción del problema</h3>
                    <p style="color: #334155; line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($ticketInfo['descripcion'] ?? 'Sin descripción.'); ?></p>
                    
                    <div style="margin-top: 15px; font-size: 0.85rem; border-top: 1px solid #e2e8f0; padding-top: 10px;">
                        <span style="color: #64748b; font-weight: bold;">Solicitante:</span> <span style="color: #0f172a;"><?php echo htmlspecialchars($ticketInfo['solicitante']); ?> (<?php echo htmlspecialchars($ticketInfo['correo_solicitante']); ?>)</span>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h3 style="font-size: 1.1rem; color: #334155; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px;">Bitácora del Ticket</h3>
                    
                    <?php if (count($comentarios) === 0): ?>
                        <p style="color: #94a3b8; font-style: italic; font-size: 0.9rem;">Aún no hay comentarios en este ticket.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px;">
                            <?php foreach ($comentarios as $com): ?>
                                <?php 
                                    // Definimos colores: Azul para respuestas normales, Amarillo/Naranja si es nota interna
                                    $fondo = ($com['es_nota_interna'] == 1) ? '#fffbeb' : '#ffffff';
                                    $bordeIzq = ($com['es_nota_interna'] == 1) ? '#f59e0b' : '#3b82f6';
                                ?>
                                <div style="background: <?php echo $fondo; ?>; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0; border-left: 4px solid <?php echo $bordeIzq; ?>; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <span style="font-weight:bold; font-size: 0.85rem; color: #475569;">
                                            <?php echo htmlspecialchars($com['autor']); ?>
                                            <?php if($com['es_nota_interna'] == 1): ?>
                                                <span style="background: #fef3c7; color: #d97706; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 5px;">Nota Interna</span>
                                            <?php endif; ?>
                                        </span>
                                        <span style="font-size: 0.75rem; color: #94a3b8;"><?php echo date('d M, H:i', strtotime($com['fecha_creacion'])); ?></span>
                                    </div>
                                    <p style="margin: 0; font-size: 0.95rem; color: #334155; white-space: pre-wrap;"><?php echo htmlspecialchars($com['comentario']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form action="atender_ticket.php?id=<?php echo $id_ticket; ?>" method="post" style="background: #f1f5f9; padding: 15px; border-radius: 8px;">
                        <input type="hidden" name="id_ticket" value="<?php echo $id_ticket; ?>">
                        
                        <label for="comentario" style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px; font-size: 0.9rem;">Añadir actualización / nota:</label>
                        <textarea id="comentario" name="comentario" rows="3" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; resize: vertical; font-family: inherit;" placeholder="Describe qué acción tomaste o manda un mensaje al usuario..." required></textarea>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <div>
                                <input type="checkbox" id="es_nota_interna" name="es_nota_interna" value="1">
                                <label for="es_nota_interna" style="font-size: 0.85rem; color: #64748b; cursor: pointer;">Solo visible para técnicos (Nota interna)</label>
                            </div>
                            <button type="submit" name="agregar_comentario" class="admin-btn" style="background-color: #3b82f6; color: white; border: none;">Publicar Comentario</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="admin-card" style="height: fit-content;">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Progreso</div>
                    </div>
                </div>

                <form action="atender_ticket.php?id=<?php echo $id_ticket; ?>" method="post">
                    <input type="hidden" name="id_ticket" value="<?php echo $id_ticket; ?>">

                    <div class="admin-field" style="margin-bottom: 20px;">
                        <label for="estatus_id" style="font-weight: bold; color: #334155;">Estatus actual del ticket:</label>
                        <select id="estatus_id" name="estatus_id" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; margin-top: 5px;">
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['id_status']; ?>" <?php echo ($ticketInfo['estatus_id'] == $estado['id_status']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($estado['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" name="actualizar_estatus" class="admin-btn admin-btn-primary" style="width: 100%; padding: 10px;">Guardar Cambio de Estatus</button>
                </form>
            </section>

        </div>
    </div>
</body>
</html>