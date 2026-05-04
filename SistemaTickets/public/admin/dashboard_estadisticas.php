<?php
session_start();

// Verificamos que sea Administrador (Rol 1)
if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 1) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../../includes/conexion_db.php';

// --- NUEVO: LÓGICA DE FILTROS DE TIEMPO ---
$filtro = $_GET['periodo'] ?? 'todos';
$condicionFecha = "";
$condicionFechaJoin = ""; // Usado para los LEFT JOIN

switch ($filtro) {
    case 'semana':
        $condicionFecha = " AND YEARWEEK(fecha_creacion, 1) = YEARWEEK(CURDATE(), 1)";
        $condicionFechaJoin = " AND YEARWEEK(t.fecha_creacion, 1) = YEARWEEK(CURDATE(), 1)";
        $tituloPeriodo = "esta Semana";
        break;
    case 'mes':
        $condicionFecha = " AND MONTH(fecha_creacion) = MONTH(CURDATE()) AND YEAR(fecha_creacion) = YEAR(CURDATE())";
        $condicionFechaJoin = " AND MONTH(t.fecha_creacion) = MONTH(CURDATE()) AND YEAR(t.fecha_creacion) = YEAR(CURDATE())";
        $tituloPeriodo = "este Mes";
        break;
    case 'año':
        $condicionFecha = " AND YEAR(fecha_creacion) = YEAR(CURDATE())";
        $condicionFechaJoin = " AND YEAR(t.fecha_creacion) = YEAR(CURDATE())";
        $tituloPeriodo = "este Año";
        break;
    default:
        $condicionFecha = "";
        $condicionFechaJoin = "";
        $tituloPeriodo = "Todo el Tiempo";
        break;
}

// 1. OBTENER MÉTRICAS RÁPIDAS (KPIs) - Ahora con filtro
$total_tickets = $conn->query("SELECT COUNT(*) FROM tickets WHERE 1=1 $condicionFecha")->fetchColumn();
$tickets_resueltos = $conn->query("SELECT COUNT(*) FROM tickets WHERE estatus_id = 4 $condicionFecha")->fetchColumn();
$tickets_pendientes = $total_tickets - $tickets_resueltos;


// 2. DATOS PARA EL GRÁFICO DE ESTADOS (Gráfico de Pastel) - Ahora con filtro
$sqlEstados = "SELECT ts.nombre, COUNT(t.id_ticket) as total 
               FROM ticket_status ts 
               LEFT JOIN tickets t ON ts.id_status = t.estatus_id $condicionFechaJoin
               GROUP BY ts.id_status, ts.nombre";
$datosEstados = $conn->query($sqlEstados)->fetchAll(PDO::FETCH_ASSOC);

$labelsEstados = [];
$valoresEstados = [];
foreach ($datosEstados as $row) {
    $labelsEstados[] = $row['nombre'];
    $valoresEstados[] = $row['total'];
}


// 3. DATOS PARA EL GRÁFICO DE CATEGORÍAS (Gráfico de Barras) - Ahora con filtro
$sqlCategorias = "SELECT tc.nombre, COUNT(t.id_ticket) as total 
                  FROM ticket_categoria tc 
                  LEFT JOIN tickets t ON tc.id_ctgy = t.categoria_id $condicionFechaJoin
                  GROUP BY tc.id_ctgy, tc.nombre";
$datosCategorias = $conn->query($sqlCategorias)->fetchAll(PDO::FETCH_ASSOC);

$labelsCategorias = [];
$valoresCategorias = [];
foreach ($datosCategorias as $row) {
    $labelsCategorias[] = $row['nombre'];
    $valoresCategorias[] = $row['total'];
}


// 4. NUEVO: DATOS PARA EL GRÁFICO DE TÉCNICOS
$sqlTecnicos = "SELECT CONCAT(u.nombre, ' ', u.a_paterno) as tecnico, COUNT(t.id_ticket) as total 
                FROM usuarios u
                INNER JOIN tickets t ON u.id_usuario = t.tecnico_asignado_id
                WHERE u.rol_id = 3 $condicionFechaJoin
                GROUP BY u.id_usuario";
$datosTecnicos = $conn->query($sqlTecnicos)->fetchAll(PDO::FETCH_ASSOC);

$labelsTec = []; 
$valoresTec = [];
foreach($datosTecnicos as $r) { 
    $labelsTec[] = $r['tecnico']; 
    $valoresTec[] = $r['total']; 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas | Sistema de Tickets</title>
    <link rel="stylesheet" href="../../assets/css/estilo_home.css">
    <link rel="stylesheet" href="../../assets/css/estilo_admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Estilos rápidos para los botones de filtro */
        .btn-filtro {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
            transition: all 0.2s;
        }
        .btn-filtro.activo { background-color: #3b82f6; color: white; }
        .btn-filtro.inactivo { background-color: white; color: #64748b; border: 1px solid #cbd5e1; }
    </style>
</head>
<body class="admin-body">
    <div class="admin-container">
        
        <div class="admin-navbar">
            <div class="admin-brand">Dashboard: <?php echo $tituloPeriodo; ?></div>
            <div class="admin-actions" style="display: flex; align-items: center; gap: 15px;">
                
                <div style="background: #f1f5f9; padding: 5px; border-radius: 6px; display: flex; gap: 5px;">
                    <a href="?periodo=todos" class="btn-filtro <?php echo $filtro == 'todos' ? 'activo' : 'inactivo'; ?>">Todos</a>
                    <a href="?periodo=año" class="btn-filtro <?php echo $filtro == 'año' ? 'activo' : 'inactivo'; ?>">Año</a>
                    <a href="?periodo=mes" class="btn-filtro <?php echo $filtro == 'mes' ? 'activo' : 'inactivo'; ?>">Mes</a>
                    <a href="?periodo=semana" class="btn-filtro <?php echo $filtro == 'semana' ? 'activo' : 'inactivo'; ?>">Semana</a>
                </div>

                <a href="administrador.php" class="admin-btn">Volver al Panel</a>
            </div>
        </div>

        <div class="admin-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 20px;">
            <div class="admin-card" style="text-align: center; padding: 20px;">
                <h3 style="margin: 0; color: #64748b; font-size: 1rem;">Total de Tickets</h3>
                <p style="font-size: 2.5rem; font-weight: bold; color: #0f172a; margin: 10px 0 0 0;"><?php echo $total_tickets; ?></p>
            </div>
            <div class="admin-card" style="text-align: center; padding: 20px; border-bottom: 4px solid #eab308;">
                <h3 style="margin: 0; color: #64748b; font-size: 1rem;">Tickets Pendientes</h3>
                <p style="font-size: 2.5rem; font-weight: bold; color: #eab308; margin: 10px 0 0 0;"><?php echo $tickets_pendientes; ?></p>
            </div>
            <div class="admin-card" style="text-align: center; padding: 20px; border-bottom: 4px solid #22c55e;">
                <h3 style="margin: 0; color: #64748b; font-size: 1rem;">Tickets Resueltos</h3>
                <p style="font-size: 2.5rem; font-weight: bold; color: #22c55e; margin: 10px 0 0 0;"><?php echo $tickets_resueltos; ?></p>
            </div>
        </div>

        <div class="admin-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 20px;">
            
            <section class="admin-card">
                <div class="admin-card-header">
                    <div class="admin-card-title">Tickets por Estado</div>
                </div>
                <div style="position: relative; height: 300px; width: 100%; display: flex; justify-content: center;">
                    <canvas id="chartEstados"></canvas>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header">
                    <div class="admin-card-title">Carga de Trabajo por Técnico</div>
                </div>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="chartTecnicos"></canvas>
                </div>
            </section>

        </div>

        <div class="admin-grid" style="grid-template-columns: 1fr;">
            <section class="admin-card">
                <div class="admin-card-header">
                    <div class="admin-card-title">Tickets por Categoría</div>
                </div>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="chartCategorias"></canvas>
                </div>
            </section>
        </div>

    </div>

    <script>
        // --- 1. Gráfico de Estados (Pastel) ---
        const ctxEstados = document.getElementById('chartEstados').getContext('2d');
        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labelsEstados); ?>,
                datasets: [{
                    data: <?php echo json_encode($valoresEstados); ?>,
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#22c55e', '#8b5cf6'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // --- 2. Gráfico de Técnicos (Barras Horizontales o Verticales) ---
        const ctxTecnicos = document.getElementById('chartTecnicos').getContext('2d');
        new Chart(ctxTecnicos, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labelsTec); ?>,
                datasets: [{
                    label: 'Tickets Asignados',
                    data: <?php echo json_encode($valoresTec); ?>,
                    backgroundColor: '#10b981', // Verde esmeralda para diferenciar
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: { legend: { display: false } }
            }
        });

        // --- 3. Gráfico de Categorías (Barras) ---
        const ctxCategorias = document.getElementById('chartCategorias').getContext('2d');
        new Chart(ctxCategorias, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labelsCategorias); ?>,
                datasets: [{
                    label: 'Cantidad de Tickets',
                    data: <?php echo json_encode($valoresCategorias); ?>,
                    backgroundColor: '#6366f1',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>