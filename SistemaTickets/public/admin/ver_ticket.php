<?php
session_start();

// 1. Verificamos que solo el administrador tenga acceso
if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 1) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../../includes/conexion_db.php';

// 2. Obtenemos el ID del ticket desde la URL
$id_ticket = (int)($_GET['id'] ?? $_POST['id_ticket'] ?? 0);

if ($id_ticket === 0) {
    header('Location: administrador.php');
    exit();
}

$mensajeExito = '';
$mensajeError = '';

// 3. LÓGICA PARA ASIGNAR EL TÉCNICO (Cuando se envía el formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tecnico_id = (int)($_POST['tecnico_id'] ?? 0);
    $nuevo_estatus = (int)($_POST['estatus_id'] ?? 0);

    try {
        // Actualizamos el ticket con el nuevo técnico y el estatus
        $sql = "UPDATE tickets SET tecnico_asignado_id = :tecnico, estatus_id = :estatus WHERE id_ticket = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':tecnico' => $tecnico_id > 0 ? $tecnico_id : null, // Si es 0, lo dejamos NULL (sin asignar)
            ':estatus' => $nuevo_estatus,
            ':id' => $id_ticket
        ]);
        
        $mensajeExito = "¡El ticket ha sido actualizado y asignado correctamente!";
    } catch (PDOException $e) {
        $mensajeError = "Error al asignar el técnico: " . $e->getMessage();
    }
}

// 4. TRAER LOS DATOS DEL TICKET
// Usamos LEFT JOIN para el técnico, porque podría estar "Sin Asignar" todavía.
$sqlTicket = "SELECT t.*, 
                tc.nombre AS categoria_nombre, 
                tp.nombre AS prioridad_nombre, 
                ts.nombre AS estado_nombre,
                CONCAT(u.nombre, ' ', u.a_paterno) AS solicitante,
                u.email AS correo_solicitante,
                CONCAT(tec.nombre, ' ', tec.a_paterno) AS tecnico_actual
              FROM tickets t
              JOIN ticket_categoria tc ON t.categoria_id = tc.id_ctgy
              JOIN ticket_prioridad tp ON t.prioridad_id = tp.id_prio
              JOIN ticket_status ts ON t.estatus_id = ts.id_status
              JOIN usuarios u ON t.usuario_reporta_id = u.id_usuario
              LEFT JOIN usuarios tec ON t.tecnico_asignado_id = tec.id_usuario
              WHERE t.id_ticket = :id";

$stmtTicket = $conn->prepare($sqlTicket);
$stmtTicket->execute([':id' => $id_ticket]);
$ticketInfo = $stmtTicket->fetch(PDO::FETCH_ASSOC);

if (!$ticketInfo) {
    header('Location: administrador.php');
    exit();
}

// 5. TRAER LA LISTA DE TÉCNICOS (Rol = 3) PARA EL SELECT
$sqlTecnicos = "SELECT id_usuario, CONCAT(nombre, ' ', a_paterno) AS nombre_completo FROM usuarios WHERE rol_id = 3 ORDER BY nombre ASC";
$tecnicos = $conn->query($sqlTecnicos)->fetchAll(PDO::FETCH_ASSOC);

// 6. TRAER LA LISTA DE ESTATUS (Para que el admin también pueda cambiarlo a "En proceso")
$estados = $conn->query("SELECT id_status, nombre FROM ticket_status ORDER BY id_status ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Ticket | Sistema de Tickets</title>
    <link rel="stylesheet" href="../../assets/css/estilo_home.css">
    <link rel="stylesheet" href="../../assets/css/estilo_admin.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        
        <div class="admin-navbar">
            <div class="admin-brand">Sistema de Tickets</div>
            <div class="admin-actions">
                <a href="administrador.php" class="admin-btn" style="background: #e2e8f0; color: #334155; text-decoration: none;">Volver al Panel</a>
            </div>
        </div>

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
                    <p style="color: #334155; line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($ticketInfo['descripcion'] ?? 'Sin descripción detallada.'); ?></p>
                </div>

                <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <span style="display: block; font-size: 0.8rem; color: #64748b; font-weight: bold;">Usuario Solicitante:</span>
                        <span style="color: #0f172a;"><?php echo htmlspecialchars($ticketInfo['solicitante']); ?></span><br>
                        <span style="font-size: 0.85rem color: #64748b;"><?php echo htmlspecialchars($ticketInfo['correo_solicitante']); ?></span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.8rem; color: #64748b; font-weight: bold;">Técnico Actual:</span>
                        <span style="color: #0f172a; font-weight: bold;">
                            <?php echo $ticketInfo['tecnico_actual'] ? htmlspecialchars($ticketInfo['tecnico_actual']) : '<span style="color:#b91c1c;">Sin Asignar</span>'; ?>
                        </span>
                    </div>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Gestión del Ticket</div>
                    </div>
                </div>

                <?php if ($mensajeExito !== ''): ?>
                    <div class="admin-note" style="color:#166534; font-weight:700; margin-bottom: 15px;">
                        <?php echo $mensajeExito; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($mensajeError !== ''): ?>
                    <div class="admin-note" style="color:#b91c1c; font-weight:700; margin-bottom: 15px;">
                        <?php echo $mensajeError; ?>
                    </div>
                <?php endif; ?>

                <form action="ver_ticket.php?id=<?php echo $id_ticket; ?>" method="post">
                    <input type="hidden" name="id_ticket" value="<?php echo $id_ticket; ?>">

                    <div class="admin-field" style="margin-bottom: 15px;">
                        <label for="tecnico_id">Asignar a Técnico:</label>
                        <select id="tecnico_id" name="tecnico_id" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                            <option value="0">-- Dejar sin asignar --</option>
                            <?php foreach ($tecnicos as $tec): ?>
                                <option value="<?php echo $tec['id_usuario']; ?>" <?php echo ($ticketInfo['tecnico_asignado_id'] == $tec['id_usuario']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tec['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-field" style="margin-bottom: 20px;">
                        <label for="estatus_id">Cambiar Estatus:</label>
                        <select id="estatus_id" name="estatus_id" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['id_status']; ?>" <?php echo ($ticketInfo['estatus_id'] == $estado['id_status']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($estado['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span style="font-size: 0.75rem; color: #64748b;">Si lo asignas, es buena práctica cambiarlo a "En proceso".</span>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%;">Guardar Asignación</button>
                </form>
            </section>

        </div>
    </div>
</body>
</html>