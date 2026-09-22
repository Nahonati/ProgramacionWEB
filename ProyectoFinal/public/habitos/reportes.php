<?php
session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT id, nombre FROM habitos WHERE usuario_id = ? AND activo = TRUE");
$stmt->execute([$id_usuario]);
$habitos = $stmt->fetchAll();

// Ahora obtener el historial de la semana actual para cada hábito
$historial = [];
foreach ($habitos as $habito) {
    $stmt = $conn->prepare("SELECT fecha_creacion, completado FROM registro_habitos
        WHERE habito_id = ? AND YEARWEEK(fecha_creacion, 1) = YEARWEEK(CURDATE(), 1)
        ORDER BY fecha_creacion");
    $stmt->execute([$habito['id']]);
    $historial[$habito['id']] = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte Semanal - BestVersion</title>
  <link rel="stylesheet" href="../../public/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  
</head>
<body>

<nav class="nav-container">
        <div class="contenido">
            <a href="../../public/home.php" class="nav-logo">BestVersion</a>
            <div class="nav-links">
                <a href="../../public/metas/metas.php">Mis metas</a>
                <a href="../../public/habitos/habitos.php">Mis habitos</a>
                <a href="../../public/animo/estado_animo.php">Estado de Animo</a>
                <a href="../../public/miperfil.php">Mi perfil</a>
            </div>
        </div>
    </nav>

<section class="section">
    
<div class="nav-logo">
    <br><br>
    <h2 class="title has-text-centered" style="color: #ffbb98 ;">Reporte de Hábitos</h2>
    <br>
</div>

  <div class="container-registro">
    <?php if (empty($habitos)): ?>
      <!-- Si no hay hábitos activos, se muestra este aviso -->
      <div>No tienes hábitos activos registrados.</div>
    <?php else: ?>
      <!-- Recorrer cada hábito activo y motrar su historial de la semana -->
      <?php foreach ($habitos as $habito): ?>
        <div class="box">
          <h2 class="subtitle"><strong><?= htmlspecialchars($habito['nombre']) ?></strong></h2>
          <table class="table is-fullwidth is-bordered is-striped">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $dias_registrados = $historial[$habito['id']] ?? [];
                if (empty($dias_registrados)) {
                    echo '<tr><td colspan="2">No hay registros esta semana.</td></tr>';
                } else {
                    foreach ($dias_registrados as $registro) {
                        echo '<tr>';
                        echo '<td>' . date('d/m/Y', strtotime($registro['fecha_creacion'])) . '</td>';
                        echo '<td><span class="tag ' . ($registro['completado'] ? 'is-success' : 'is-danger') . '">' . ($registro['completado'] ? 'Cumplido' : 'Pendiente') . '</span></td>';
                        echo '</tr>';
                    }
                }
              ?>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

</body>
</html>