<?php
session_start();

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 1) {
    header('Location: ../../index.php');
    exit();
}
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
                <span class="admin-pill">Rol: Administrador</span>
                <span class="admin-pill"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? ''); ?></span>
            </div>
        </div>

        <div class="admin-grid">
            <!-- Registrar usuarios (solo diseño) -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Registrar usuarios</div>
                        <div class="admin-card-subtitle">Formulario estático para diseño (sin lógica)</div>
                    </div>
                </div>

                <form action="#" method="post">
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="nombre">Nombre(s)</label>
                            <input type="text" id="nombre" placeholder="Ej. Juan" />
                        </div>

                        <div class="admin-field">
                            <label for="a_paterno">Apellido paterno</label>
                            <input type="text" id="a_paterno" placeholder="Ej. Pérez" />
                        </div>

                        <div class="admin-field">
                            <label for="email">Correo</label>
                            <input type="text" id="email" placeholder="correo@dominio.com" />
                        </div>

                        <div class="admin-field">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" placeholder="••••••••" />
                        </div>

                        <div class="admin-field">
                            <label for="rol">Rol</label>
                            <select id="rol">
                                <option value="1">Administrador</option>
                                <option value="2" selected>Usuario</option>
                                <option value="3">Tecnico</option>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="dept">Departamento</label>
                            <select id="dept">
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

                    <div class="admin-note">
                        Nota: botones y campos son solo para el diseño. En la siguiente etapa conectamos con backend/DB.
                    </div>
                </form>
            </section>

            <!-- Tickets (solo diseño) -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Tickets</div>
                        <div class="admin-card-subtitle">Vista estática de tabla con acciones (sin lógica)</div>
                    </div>
                </div>

                <div class="tickets-table-wrap">
                    <table class="table-custom" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding: 10px 8px;">ID</th>
                                <th style="text-align:left; padding: 10px 8px;">Título</th>
                                <th style="text-align:left; padding: 10px 8px;">Usuario</th>
                                <th style="text-align:left; padding: 10px 8px;">Categoría</th>
                                <th style="text-align:left; padding: 10px 8px;">Prioridad</th>
                                <th style="text-align:left; padding: 10px 8px;">Estatus</th>
                                <th style="text-align:left; padding: 10px 8px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 10px 8px;">#102</td>
                                <td style="padding: 10px 8px;">No enciende el monitor</td>
                                <td style="padding: 10px 8px;">María López</td>
                                <td style="padding: 10px 8px;">Hardware</td>
                                <td style="padding: 10px 8px;"><span class="badge-prio-media">Media</span></td>
                                <td style="padding: 10px 8px;">
                                    <span class="status-dot" style="border-color:#198754;"></span>Nuevo
                                </td>
                                <td style="padding: 10px 8px;">
                                    <div class="table-actions">
                                        <button type="button" class="btn-small btn-delete">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 8px;">#103</td>
                                <td style="padding: 10px 8px;">Error al abrir Excel</td>
                                <td style="padding: 10px 8px;">Carlos Rivera</td>
                                <td style="padding: 10px 8px;">Software</td>
                                <td style="padding: 10px 8px;"><span class="badge-prio-alta">Alta</span></td>
                                <td style="padding: 10px 8px;">
                                    <span class="status-dot" style="border-color:#ffc107; background:#ffc107;"></span>En proceso
                                </td>
                                <td style="padding: 10px 8px;">
                                    <button type="button" class="btn-small btn-delete">Eliminar</button>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 8px;">#104</td>
                                <td style="padding: 10px 8px;">No tengo acceso a la red</td>
                                <td style="padding: 10px 8px;">Ana Torres</td>
                                <td style="padding: 10px 8px;">Redes</td>
                                <td style="padding: 10px 8px;"><span class="badge-prio-alta">Alta</span></td>
                                <td style="padding: 10px 8px;">
                                    <span class="status-dot" style="border-color:#dc2626;"></span>En espera
                                </td>
                                <td style="padding: 10px 8px;">
                                    <button type="button" class="btn-small btn-delete">Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="admin-note">
                    En la siguiente etapa: conectamos datos reales desde `tickets` y agregamos lógica de eliminar.
                </div>
            </section>
        </div>
    </div>
</body>
</html>
