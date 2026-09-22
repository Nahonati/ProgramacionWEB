<?php
session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
// Obtener el ID de la meta desde el parámetro GET
$id_meta = isset($_GET['id_meta']) ? (int)$_GET['id_meta'] : 0;
if ($id_meta <= 0) {
    header("Location: ../../public/metas/metas.php");
    exit;
}

// Consultar la información de la meta y el hábito relacionado
$stmt = $conn->prepare("SELECT m.*, h.nombre FROM metas m
                    JOIN habitos h ON m.id_habito = h.id
                    WHERE m.id_meta = ? AND m.id_usuario = ?");
$stmt->execute([$id_meta, $id_usuario]);
$meta = $stmt->fetch();

// Si no se encontró la meta, mostrar error
if (!$meta) {
    echo "Meta no encontrada.";
    exit;
}

// Consultar cuántas veces se ha completado el hábito dentro del rango de fechas de la meta
$completados = $conn->prepare("SELECT COUNT(*) FROM registro_habitos
                            WHERE habito_id = ? AND id_usuario = ? AND completado = 1
                            AND fecha_creacion BETWEEN ? AND ?");
$completados->execute([$meta['id_habito'], $id_usuario, $meta['fecha_inicio'], $meta['fecha_fin']]);
$total_completado = $completados->fetchColumn();

// Calcular el porcentaje de cumplimiento, con un máximo de 100%
$porcentaje = min(100, round(($total_completado / $meta['cantidad_objetivo']) * 100));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Progreso de la Meta</title>
<link rel="stylesheet" href="../../public/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav class="nav-container">
        <div class="contenido">
            <a href="../../public/home.php" class="nav-logo">BestVersion</a>
            <div class="nav-links">
                <a href="../../public/metas/metas.php">Mis metas</a>
                <a href="../../public/habitos/habitos.php">Mis habitos</a>
                <a href="../../public/animo/estado_animo.php">Estado de Animo</a>
                <a href="miperfil.php">Mi perfil</a>
            </div>
        </div>
    </nav>

<section class="section">
<h1 class="title has-text-centered" style="color: #ffbb98 ;">Progreso de la Meta</h1>

<div class="card">
    <!-- Información de la meta -->
    <div class="box">
    <p><strong>Hábito:</strong> <?= htmlspecialchars($meta['nombre']) ?></p>
    <p><strong>Descripción:</strong> <?= htmlspecialchars($meta['descripcion']) ?></p>
    <p><strong>Fecha Inicio:</strong> <?= htmlspecialchars($meta['fecha_inicio']) ?></p>
    <p><strong>Fecha Fin:</strong> <?= htmlspecialchars($meta['fecha_fin']) ?></p>
    <p><strong>Meta Objetivo:</strong> <?= $meta['cantidad_objetivo'] ?> veces</p>
    <p><strong>Completado:</strong> <?= $total_completado ?> veces</p>

    <p class="mt-3"><strong>Progreso:</strong></p>
    <progress class="progress is-primary" value="<?= $porcentaje ?>" max="100"><?= $porcentaje ?>%</progress>
    <p><?= $porcentaje ?>% completado</p>
    </div>
    <a  class="boton"href="../../public/metas/metas.php">Volver</a>

</div>
</section>
</body>
</html>