<?php
session_start();
require '../../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener hábitos activos del usuario
$query = "SELECT h.id, h.nombre, h.descripcion, h.hora, h.fecha_creacion, f.descripcion AS frecuencia
        FROM habitos h
        LEFT JOIN frecuencias f ON h.id_frecuencia = f.id_frecuencia
        WHERE h.usuario_id = :usuario_id AND h.activo = 1
        ORDER BY h.fecha_creacion DESC";

$stmt = $conn->prepare($query);
$stmt->execute(['usuario_id' => $usuario_id]);
$habitos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar cuáles ya fueron registrados hoy
$hoy = date('Y-m-d');
$stmt_hist = $conn->prepare("SELECT habito_id FROM registro_habitos 
                            WHERE DATE(fecha_creacion) = :hoy AND habito_id IN (
                            SELECT id FROM habitos WHERE usuario_id = :usuario_id
                            )");
$stmt_hist->execute(['hoy' => $hoy, 'usuario_id' => $usuario_id]);
$completados_hoy = array_column($stmt_hist->fetchAll(PDO::FETCH_ASSOC), 'habito_id');

// Guardar los IDs de los hábitos completados hoy para usarlos en los botones
$completados_hoy = [];
while ($row = $stmt_hist->fetch(PDO::FETCH_ASSOC)) {
    $completados_hoy[] = $row['id_habito'];
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Hábitos</title>
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

    

    <?php if (empty($habitos)): ?>
        <div class="section">
            <h2 class="title has-text-centered" style="color: #ffbb98 ;">Mis Hábitos</h2>
            <div class="notification is-warning">No has registrado habitos aún.</div>
            <a href="../../public/habitos/crear_habito.php" class="boton"> + Crear mi primer hábito</a>
        </div>
    <?php else: ?>
        <div class="habitos-grid">
            <?php foreach ($habitos as $habito): ?>
                <br><br>
                <div class="card">
                    <h2 class="titulo2"><?= htmlspecialchars($habito['nombre']) ?></h2>

                    <?php if (!empty($habito['descripcion'])): ?>
                        <p><?= htmlspecialchars($habito['descripcion']) ?></p>
                    <?php endif; ?>

                    <p><strong>Hora:</strong> <?= htmlspecialchars($habito['hora'] ?? '') ?></p>
                    <p><strong>Frecuencia:</strong> <?= htmlspecialchars($habito['frecuencia'] ?? 'No definida') ?></p>
                    <p><strong>Fecha de registro:</strong> <?= date('d/m/Y', strtotime($habito['fecha_creacion'])) ?></p>
                    
                    <!-- Acciones -->
                    <div class="acciones">
                        <a class="editar" href="../../public/habitos/editar_habito.php?id=<?= $habito['id'] ?>">Editar</a>
                        <a class="eliminar" href="../../public/habitos/borrar_habito.php?id=<?= $habito['id'] ?>" onclick="return confirm('¿Eliminar hábito?')">Eliminar</a>

                        <?php if (!in_array($habito['id'], $completados_hoy)): ?>
                            <a class="completo "href="../../public/habitos/habito_completo.php?id=<?= $habito['id'] ?>" class="completar">Marcar como completado</a>
                        <?php else: ?>
                            <span class="completado">✅ Completado hoy</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <a href="crear_habito.php">
        <span class="boton"> Crear un nuevo habito</span>
            </a>        
        </div>
    <?php endif; ?>
    
</body>
</html>
