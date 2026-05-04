<?php
session_start();

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 1) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../../includes/conexion_db.php';

$mensajeError = $_SESSION['flash_error'] ?? '';
$mensajeExito = $_SESSION['flash_ok'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_ok']);
$tickets = [];
$estados = [];
$buscar = trim($_GET['buscar'] ?? '');
$estadoId = (int)($_GET['estado'] ?? 0);

try {
    $estados = $conn->query("SELECT id_status, nombre FROM ticket_status ORDER BY id_status ASC")->fetchAll(PDO::FETCH_ASSOC);

    $sqlTickets = "SELECT
                    t.id_ticket,
                    t.titulo,
                    t.fecha_creacion,
                    tc.nombre AS categoria_nombre,
                    tp.nombre AS prioridad_nombre,
                    ts.nombre AS estado_nombre,
                    CONCAT(u.nombre, ' ', u.a_paterno) AS solicitante_nombre
                FROM tickets t
                INNER JOIN usuarios u ON u.id_usuario = t.usuario_reporta_id
                INNER JOIN ticket_categoria tc ON tc.id_ctgy = t.categoria_id
                INNER JOIN ticket_prioridad tp ON tp.id_prio = t.prioridad_id
                INNER JOIN ticket_status ts ON ts.id_status = t.estatus_id
                WHERE 1=1";

    $params = [];

    if ($buscar !== '') {
        $sqlTickets .= " AND (t.titulo LIKE :buscar OR t.id_ticket LIKE :buscar_id OR CONCAT(u.nombre, ' ', u.a_paterno) LIKE :buscar_usuario)";
        $params['buscar'] = '%' . $buscar . '%';
        $params['buscar_id'] = '%' . $buscar . '%';
        $params['buscar_usuario'] = '%' . $buscar . '%';
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

// --- NUEVA CONSULTA PARA USUARIOS ---
    $sqlUsuarios = "SELECT id_usuario, nombre, a_paterno, email, rol_id, dept_id FROM usuarios ORDER BY id_usuario DESC";
    $stmtUsuarios = $conn->query($sqlUsuarios);
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
    
    // Arreglo simple para traducir el número de rol a texto en la tabla
    $nombresRoles = [
        1 => 'Administrador',
        2 => 'Usuario',
        3 => 'Técnico'
    ];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador | Sistema de Tickets</title>

    <link rel="stylesheet" href="../../assets/css/estilo_home.css">
    <link rel="stylesheet" href="../../assets/css/estilo_admin.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <div class="admin-navbar">
            <div class="admin-brand">Sistema de Tickets</div>
            <div class="admin-actions">
                <span style="color: #64748b; font-size: 0.9rem;">
                    Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Técnico'); ?></strong>
                </span>    
                <span class="admin-pill">Rol: Administrador</span>  
                <div class="admin-navbar">
                    <div class="admin-actions">
                        <a href="dashboard_estadisticas.php" class="admin-btn" style="background-color: #6366f1; color: white; text-decoration: none;">
                            📊 Ver Estadísticas
                        </a>
                    <a href="../../cerrar_sesion.php" class="admin-btn admin-btn-danger">Cerrar sesión</a>
                    </div>
                </div>          
            </div>
        </div>

        <div class="admin-grid">
            <!-- Registrar usuarios (solo diseño) -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Registrar usuarios</div>
                    </div>
                </div>

                <form action="procesar_usuario.php" method="post">
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="nombre">Nombre(s)</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan" required />
                        </div>

                        <div class="admin-field">
                            <label for="a_paterno">Apellido paterno</label>
                            <input type="text" id="a_paterno" name="a_paterno" placeholder="Ej. Pérez" required />
                        </div>

                        <div class="admin-field">
                            <label for="email">Correo</label>
                            <input type="email" id="email" name="email" placeholder="correo@dominio.com" required />
                        </div>

                        <div class="admin-field">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" required />
                        </div>

                        <div class="admin-field">
                            <label for="rol">Rol</label>
                            <select id="rol" name="rol_id" required>
                                <option value="1">Administrador</option>
                                <option value="2" selected>Usuario</option>
                                <option value="3">Tecnico</option>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="dept">Departamento</label>
                            <select id="dept" name="dept_id" required>
                                <option value="1">Recursos Humanos</option>
                                <option value="2">Administración</option>
                                <option value="3">Produccion</option>
                                <option value="4">Arte</option>
                                <option value="5">Compositing</option>
                                <option value="6">2D</option>
                                <option value="7">3D</option>
                                <option value="8">Storyboard</option>
                                <option value="9">Render</option>
                            </select>
                        </div>
                    </div>

                    <div class="admin-btn-row">
                        <button type="submit" class="admin-btn admin-btn-primary">Registrar usuario</button>
                    </div>
                </form>
            </section>

            <!-- Tickets (solo diseño) -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Tickets</div>
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

                <form action="administrador.php" method="get">
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="buscar">Buscar</label>
                            <input type="text" id="buscar" name="buscar" placeholder="ID, titulo o usuario" value="<?php echo htmlspecialchars($buscar); ?>" />
                        </div>
                        <div class="admin-field">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado">
                                <option value="">Todos</option>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?php echo (int)$estado['id_status']; ?>" <?php echo ($estadoId === (int)$estado['id_status']) ? 'selected' : ''; ?>>
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
                                <th style="text-align:left; padding: 10px 8px;">Título</th>
                                <th style="text-align:left; padding: 10px 8px;">Fecha creación</th>
                                <th style="text-align:left; padding: 10px 8px;">Usuario</th>
                                <th style="text-align:left; padding: 10px 8px;">Categoría</th>
                                <th style="text-align:left; padding: 10px 8px;">Prioridad</th>
                                <th style="text-align:left; padding: 10px 8px;">Estatus</th>
                                <th style="text-align:left; padding: 10px 8px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($tickets) === 0): ?>
                                <tr>
                                    <td colspan="8" style="padding: 12px 8px; color:#64748b;">No hay tickets para mostrar.</td>
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
                                        <td style="padding: 10px 8px;">#<?php echo (int)$ticket['id_ticket']; ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($ticket['fecha_creacion']))); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($ticket['solicitante_nombre']); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($ticket['categoria_nombre']); ?></td>
                                        <td style="padding: 10px 8px;"><span class="<?php echo $prioridadClass; ?>"><?php echo htmlspecialchars($ticket['prioridad_nombre']); ?></span></td>
                                        <td style="padding: 10px 8px;"><span class="status-dot"></span><?php echo htmlspecialchars($ticket['estado_nombre']); ?></td>
                                        <td style="padding: 10px 8px;">
                                            <div class="table-actions" style="display: flex; gap: 8px; align-items: center;">
        
                                            <a href="ver_ticket.php?id=<?php echo (int)$ticket['id_ticket']; ?>" class="admin-btn admin-btn-primary" style="padding: 4px 8px; font-size: 0.8rem; text-decoration: none;">
                                                Ver / Asignar
                                            </a>

                                            <form action="cambiar_estatus.php" method="post" onsubmit="return confirm('¿Seguro que deseas cancelar este ticket? Esto lo ocultará como borrado lógico.');" style="margin: 0;">
                                                <input type="hidden" name="ticket_id" value="<?php echo (int)$ticket['id_ticket']; ?>">
                                                <input type="hidden" name="nuevo_estatus" value="5"> 
            
                                                <button type="submit" class="btn-small btn-delete" style="background-color: #b91c1c; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer;">
                                                    Cancelar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </section>
            <section class="admin-card" style="grid-column: 1 / -1; margin-top: 20px;">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Directorio de Usuarios</div>
                        <div class="admin-card-subtitle">Administra los accesos al sistema</div>
                    </div>
                </div>

                <div class="tickets-table-wrap">
                    <table class="table-custom" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding: 10px 8px;">ID</th>
                                <th style="text-align:left; padding: 10px 8px;">Nombre Completo</th>
                                <th style="text-align:left; padding: 10px 8px;">Correo Electrónico</th>
                                <th style="text-align:left; padding: 10px 8px;">Rol</th>
                                <th style="text-align:left; padding: 10px 8px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($usuarios) === 0): ?>
                                <tr>
                                    <td colspan="5" style="padding: 12px 8px; color:#64748b;">No hay usuarios registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $user): ?>
                                    <tr>
                                        <td style="padding: 10px 8px;">#<?php echo (int)$user['id_usuario']; ?></td>
                                        <td style="padding: 10px 8px; font-weight: bold;">
                                            <?php echo htmlspecialchars($user['nombre'] . ' ' . $user['a_paterno']); ?>
                                        </td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td style="padding: 10px 8px;">
                                            <span class="admin-pill" style="background-color: #e2e8f0; color: #334155;">
                                                <?php echo $nombresRoles[$user['rol_id']] ?? 'Desconocido'; ?>
                                            </span>
                                        </td>
                                        <td style="padding: 10px 8px;">
                                            <div class="table-actions" style="display: flex; gap: 8px;">
                                                <a href="editar_usuario.php?id=<?php echo (int)$user['id_usuario']; ?>" class="admin-btn admin-btn-primary" style="padding: 4px 8px; font-size: 0.8rem; text-decoration: none;">
                                                    Editar
                                                </a>
                                                
                                                <form action="suspender_usuario.php" method="post" style="margin: 0;" onsubmit="return confirm('¿Seguro que deseas suspender a este usuario?');">
                                                    <input type="hidden" name="usuario_id" value="<?php echo (int)$user['id_usuario']; ?>">
                                                    <button type="submit" class="btn-small btn-delete" style="background-color: #b91c1c; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer;">
                                                        Suspender
                                                    </button>
                                                </form>
                                            </div>
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
