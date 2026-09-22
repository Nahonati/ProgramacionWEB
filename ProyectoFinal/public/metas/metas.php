<?php
session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// Traer todas las metas del usuario actual y su respectivo hábito (si existe)
$stmt = $conn->prepare("SELECT 
        m.id_meta,
        m.descripcion,
        m.fecha_inicio,
        m.fecha_fin,
        m.cantidad_objetivo,
        h.nombre AS nombre_habito
    FROM metas m
    INNER JOIN habitos h ON m.id_habito = h.id
    WHERE m.id_usuario = ?
    ORDER BY m.fecha_inicio DESC");
$stmt->execute([$id_usuario]);
$metas = $stmt->fetchAll(); // Almacenar todas las metas
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Metas - BestVersion</title>
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
                <a href="../../public/miperfil.php">Mi perfil</a>
            </div>
        </div>
    </nav>
<br>
<section class="section">
    <br><br>
<div class="container-registro">
    <br><br>
    <h1 class="title has-text-centered" style="color: #ffbb98 ;">Mis Metas</h1>

    <?php if (empty($metas)): ?>
    <!-- Mostrar mensaje si el usuario no tiene metas -->
    <div class="notification is-warning">No has registrado metas aún.</div>
    <?php else: ?>
    <!-- Mostrar metas si existen -->
    <div class="columns is-multiline">
        <?php foreach ($metas as $meta): ?>
        <div class="column is-half">
            <div class="card">
            <p><strong>Hábito:</strong> <?= htmlspecialchars($meta['nombre_habito'] ?? 'Hábito eliminado') ?></p>
            <p><strong>Descripción:</strong> <?= htmlspecialchars($meta['descripcion']) ?></p>
            <p><strong>Fecha Inicio:</strong> <?= htmlspecialchars($meta['fecha_inicio']) ?></p>
            <p><strong>Fecha Fin:</strong> <?= htmlspecialchars($meta['fecha_fin']) ?></p>
            <p><strong>Objetivo:</strong> <?= $meta['cantidad_objetivo'] ?> veces</p>

            <div class="buttons mt-3">
                <!-- Botón para ver el progreso de la meta -->
                <a href="progreso.php?id_meta=<?= $meta['id_meta'] ?>" class="boton">Ver Progreso</a>
                <!-- Botón para editar la meta -->
                <a href="editar_meta.php?id=<?= $meta['id_meta'] ?>" class="editar">Editar</a>
                <!-- Botón para eliminar la meta con confirmación -->
                <a href="borrar_meta.php?id=<?= $meta['id_meta'] ?>" class="eliminar" onclick="return confirm('¿Estás seguro de eliminar esta meta?')">Eliminar</a>
            </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Botón para crear una nueva meta -->
    <div class="has-text-centered mt-5">
    <a href="../../public/metas/crear_meta.php">
        <span class="boton">+ Crear nueva meta</span>
    </a>
    </div>
</div>
</section>

</body>
</html>