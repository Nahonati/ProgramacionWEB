<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio | Sistema de Tickets</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom px-3">
        <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-ticket-simple me-2"></i>HELPDESK</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="fa-solid fa-headset me-1"></i> Soporte</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Tickets</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-chart-line me-1"></i> Gestión</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-gear me-1"></i> Configuración</a></li>
            </ul>
        <div class="d-flex align-items-center text-white dropdown">
            <span class="me-3 text-end" style="font-size: 0.8rem; line-height: 1;">
                <?php echo isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : 'Usuario Prueba'; ?><br>
                <small class="text-white-50">Entidad Raíz</small>
            </span>
                
            <a href="#" class="text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-info text-white rounded d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 35px; height: 35px; cursor: pointer;">
                    <?php 
                        if(isset($_SESSION['nombre_completo'])) {
                            $porciones = explode(" ", $_SESSION['nombre_completo']);
                            $iniciales = substr($porciones[0], 0, 1); 
                            if(count($porciones) > 1) {
                                $iniciales .= substr($porciones[1], 0, 1);
                            }
                            echo strtoupper($iniciales); 
                        } else {
                            echo "US"; 
                        }
                        ?>
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end mt-2 shadow">
                    <li>
                        <span class="dropdown-item-text text-muted" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-user me-2"></i>Mi Perfil
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger fw-bold" href="../cerrar_sesion.php">
                            <i class="fa-solid fa-power-off me-2"></i>Cerrar sesión
                        </a>
                    </li>
                </ul>
            </div>
    </nav>

    <div class="container-fluid py-3 px-4">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-muted" style="font-size: 0.9rem;">
                <i class="fa-solid fa-house"></i> Inicio / Soporte / <strong>Tickets</strong>
            </div>
            <div class="d-flex">
                <button class="btn btn-secondary btn-sm me-2"><i class="fa-solid fa-plus"></i></button>
                <div class="input-group input-group-sm w-auto">
                    <input type="text" class="form-control" placeholder="Buscar...">
                    <button class="btn btn-outline-secondary bg-white"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
        </div>

        <div class="filter-box shadow-sm">
            <div class="row align-items-center g-2 mb-2">
                <div class="col-auto">
                    <button class="btn btn-light btn-sm border"><i class="fa-solid fa-minus"></i></button>
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm">
                        <option>Características - Estado</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm">
                        <option>es</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm">
                        <option>No resuelto</option>
                        <option>Nuevo</option>
                        <option>En proceso</option>
                    </select>
                </div>
            </div>
            <div>
                <button class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-filter"></i> regla</button>
                <button class="btn btn-primary btn-sm ms-2" style="background-color: #5c4b8a; border: none;"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
            </div>
        </div>

        <div class="d-flex align-items-center mb-2 bg-white p-2 border rounded shadow-sm">
            <button class="btn btn-light btn-sm border me-3"><i class="fa-solid fa-reply"></i> Acciones</button>
            <i class="fa-solid fa-trash-can text-danger mx-2" style="cursor: pointer;"></i>
            <i class="fa-solid fa-wrench text-secondary mx-2" style="cursor: pointer;"></i>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-custom border">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30px;"><input class="form-check-input" type="checkbox"></th>
                        <th>ID</th>
                        <th>TÍTULO</th>
                        <th>ÚLTIMA MODIFICACIÓN</th>
                        <th>FECHA DE APERTURA</th>
                        <th>PRIORIDAD</th>
                        <th>ASIGNADO A - TÉCNICO</th>
                        <th>CATEGORÍA</th>
                        <th>SOLICITANTE</th>
                        <th>ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input class="form-check-input" type="checkbox"></td>
                        <td>24906</td>
                        <td><a href="#" class="text-decoration-none" style="color: #4c3c7c;">Error de Modalidad (24906)</a></td>
                        <td>13-04-2026 17:34</td>
                        <td>13-04-2026 15:39</td>
                        <td><span class="badge badge-prio-alta">Muy alta</span></td>
                        <td>Carlos Soporte <i class="fa-solid fa-circle-info text-primary ms-1"></i></td>
                        <td>Software - Falla</td>
                        <td>Natalia Directora <i class="fa-solid fa-circle-info text-primary ms-1"></i></td>
                        <td><span class="status-dot"></span> En curso (asignada)</td>
                    </tr>
                    
                    <tr>
                        <td><input class="form-check-input" type="checkbox"></td>
                        <td>24904</td>
                        <td><a href="#" class="text-decoration-none" style="color: #4c3c7c;">RESPALDOS DE NOMINAS</a></td>
                        <td>13-04-2026 14:22</td>
                        <td>13-04-2026 13:42</td>
                        <td><span class="badge badge-prio-media">Media</span></td>
                        <td>Carlos Soporte <i class="fa-solid fa-circle-info text-primary ms-1"></i></td>
                        <td>Sistemas - Respaldos</td>
                        <td>Ana Creativa <i class="fa-solid fa-circle-info text-primary ms-1"></i></td>
                        <td><span class="status-dot"></span> En curso (asignada)</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
