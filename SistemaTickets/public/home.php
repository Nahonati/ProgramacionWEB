<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit();
}

require __DIR__ . '/../includes/conexion_db.php';

$mensajeExito = $_SESSION['flash_ok'] ?? '';
$mensajeError = $_SESSION['flash_error'] ?? '';
$oldInput = $_SESSION['flash_old_input'] ?? [];
unset($_SESSION['flash_ok'], $_SESSION['flash_error'], $_SESSION['flash_old_input']);

$categorias = [];
$prioridades = [];
$estados = [];
$tickets = [];

try {
    $categorias = $conn->query("SELECT id_ctgy, nombre FROM ticket_categoria ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    $prioridades = $conn->query("SELECT id_prio, nombre FROM ticket_prioridad ORDER BY id_prio ASC")->fetchAll(PDO::FETCH_ASSOC);
    $estados = $conn->query("SELECT id_status, nombre FROM ticket_status ORDER BY id_status ASC")->fetchAll(PDO::FETCH_ASSOC);

    $buscar = trim($_GET['buscar'] ?? '');
    $estadoId = (int)($_GET['estado'] ?? 0);

    $sqlTickets = "SELECT
                    t.id_ticket,
                    t.titulo,
                    t.fecha_creacion,
                    tp.nombre AS prioridad_nombre,
                    tc.nombre AS categoria_nombre,
                    ts.nombre AS estado_nombre,
                    CONCAT(u.nombre, ' ', u.a_paterno) AS solicitante_nombre,
                    CASE
                        WHEN tec.id_usuario IS NULL THEN 'Sin asignar'
                        ELSE CONCAT(tec.nombre, ' ', tec.a_paterno)
                    END AS tecnico_nombre
                FROM tickets t
                INNER JOIN ticket_prioridad tp ON tp.id_prio = t.prioridad_id
                INNER JOIN ticket_categoria tc ON tc.id_ctgy = t.categoria_id
                INNER JOIN ticket_status ts ON ts.id_status = t.estatus_id
                INNER JOIN usuarios u ON u.id_usuario = t.usuario_reporta_id
                LEFT JOIN usuarios tec ON tec.id_usuario = t.tecnico_asignado_id
                WHERE t.usuario_reporta_id = :usuario_id";

    $params = ['usuario_id' => (int)$_SESSION['usuario_id']];

    if ($buscar !== '') {
        $sqlTickets .= " AND (t.titulo LIKE :buscar OR t.id_ticket LIKE :buscar_num)";
        $params['buscar'] = '%' . $buscar . '%';
        $params['buscar_num'] = '%' . $buscar . '%';
    }

    if ($estadoId > 0) {
        $sqlTickets .= " AND t.estatus_id = :estado_id";
        $params['estado_id'] = $estadoId;
    }

    $sqlTickets .= " ORDER BY t.id_ticket DESC";

    $stmtTickets = $conn->prepare($sqlTickets);
    $stmtTickets->execute($params);
    $tickets = $stmtTickets->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensajeError = 'No se pudo cargar la informacion de tickets.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio | Sistema de Tickets</title>
    <link rel="stylesheet" href="../assets/css/estilo_home.css">
    <link rel="stylesheet" href="../assets/css/estilo_admin.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <div class="admin-navbar">
            <div class="admin-brand">Sistema de Tickets</div>
            <div class="admin-actions">
                <span class="admin-nav-greeting">
                    Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?></strong>
                </span>
                <span class="admin-pill">Rol: Colaborador</span>
                <a href="../cerrar_sesion.php" class="admin-btn admin-btn-danger">Cerrar sesión</a>
            </div>
        </div>

        <div class="admin-grid">
            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Nuevo ticket</div>
                    </div>
                </div>

                <?php if ($mensajeExito !== ''): ?>
                    <div class="admin-note" style="color:#166534; font-weight:700; margin-bottom: 8px;">
                        <?php echo htmlspecialchars($mensajeExito); ?>
                    </div>
                <?php endif; ?>

                <?php if ($mensajeError !== ''): ?>
                    <div class="admin-note" style="color:#b91c1c; font-weight:700; margin-bottom: 8px;">
                        <?php echo htmlspecialchars($mensajeError); ?>
                    </div>
                <?php endif; ?>

                <form action="crear_ticket.php" method="post">
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="titulo_ticket">Titulo</label>
                            <input type="text" id="titulo_ticket" name="titulo_ticket" placeholder="Ej. No abre el sistema de nomina" value="<?php echo htmlspecialchars($oldInput['titulo_ticket'] ?? ''); ?>" />
                        </div>
                        <div class="admin-field">
                            <label for="categoria_ticket">Categoria</label>
                            <select id="categoria_ticket" name="categoria_ticket">
                                <option value="">Selecciona una categoria</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo (int)$categoria['id_ctgy']; ?>" <?php echo ((int)($oldInput['categoria_ticket'] ?? 0) === (int)$categoria['id_ctgy']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="admin-field">
                            <label for="prioridad_ticket">Prioridad</label>
                            <select id="prioridad_ticket" name="prioridad_ticket">
                                <?php foreach ($prioridades as $prioridad): ?>
                                    <option value="<?php echo (int)$prioridad['id_prio']; ?>" <?php echo ((int)($oldInput['prioridad_ticket'] ?? 0) === (int)$prioridad['id_prio']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prioridad['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="admin-field admin-field--spaced-top">
                        <label for="descripcion_ticket">Descripcion</label>
                        <textarea id="descripcion_ticket" name="descripcion_ticket" rows="4" placeholder="Describe el problema con el mayor detalle posible"><?php echo htmlspecialchars($oldInput['descripcion_ticket'] ?? ''); ?></textarea>
                    </div>

                    <div class="admin-btn-row">
                        <button type="submit" class="admin-btn admin-btn-primary">Crear ticket</button>
                    </div>
                </form>
            </section>

            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Tickets de soporte</div>
                    </div>
                </div>

                <form action="home.php" method="get">
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="buscar">Buscar</label>
                            <input type="text" id="buscar" name="buscar" placeholder="ID o titulo" value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>" />
                        </div>
                        <div class="admin-field">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado">
                                <option value="">Todos</option>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?php echo (int)$estado['id_status']; ?>" <?php echo ((int)($_GET['estado'] ?? 0) === (int)$estado['id_status']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($estado['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="admin-btn-row">
                        <button type="submit" class="admin-btn admin-btn-primary">Buscar</button>
                    </div>
                </form>

                <div class="admin-divider"></div>

                <div class="tickets-table-wrap">
                    <table class="table-custom table-custom--wide" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding: 10px 8px;">ID</th>
                                <th style="text-align:left; padding: 10px 8px;">Titulo</th>
                                <th style="text-align:left; padding: 10px 8px;">Ultima modificacion</th>
                                <th style="text-align:left; padding: 10px 8px;">Fecha apertura</th>
                                <th style="text-align:left; padding: 10px 8px;">Prioridad</th>
                                <th style="text-align:left; padding: 10px 8px;">Tecnico</th>
                                <th style="text-align:left; padding: 10px 8px;">Categoria</th>
                                <th style="text-align:left; padding: 10px 8px;">Solicitante</th>
                                <th style="text-align:left; padding: 10px 8px;">Estado</th>
                                <th style="text-align:center; padding: 10px 8px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($tickets) === 0): ?>
                                <tr>
                                    <td colspan="10" style="padding: 12px 8px; color:#64748b; text-align:center;">No hay tickets para mostrar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <?php
                                        $prioridadClass = 'badge-prio-media';
                                        if (strcasecmp($ticket['prioridad_nombre'], 'Alta') === 0 || strcasecmp($ticket['prioridad_nombre'], 'Crítica') === 0) {
                                            $prioridadClass = 'badge-prio-alta';
                                        }
                                    ?>
                                    <tr>
                                        <td style="padding: 10px 8px;"><?php echo (int)$ticket['id_ticket']; ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($ticket['fecha_creacion']))); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($ticket['fecha_creacion']))); ?></td>
                                        <td style="padding: 10px 8px;"><span class="<?php echo $prioridadClass; ?>"><?php echo htmlspecialchars($ticket['prioridad_nombre']); ?></span></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($ticket['tecnico_nombre']); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($ticket['categoria_nombre']); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($ticket['solicitante_nombre']); ?></td>
                                        <td style="padding: 10px 8px;"><span class="status-dot"></span><?php echo htmlspecialchars($ticket['estado_nombre']); ?></td>
                                        
                                        <td style="padding: 10px 8px; text-align:center;">
                                            <a href="ver_mi_ticket.php?id=<?php echo (int)$ticket['id_ticket']; ?>" class="admin-btn admin-btn-primary admin-btn-table">Ver ticket</a>
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