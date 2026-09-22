<?php
session_start();
require '../../includes/conexion.php';

// Verifica que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../public/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT ea.nombre AS estado
        FROM registro_estado ra
        JOIN estado_animo ea ON ra.estado_animo_id = ea.id
        WHERE ra.usuario_id = :usuario_id
        AND DATE(ra.fecha_creacion) = CURDATE()
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute([':usuario_id' => $usuario_id]);
$estado = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Animo</title>
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
    <div>
        <h2 class="title has-text-centered" style="color: #ffbb98;">Tu estado de animo hoy es:</h2>
        <?php if ($estado): ?>
        <p>
            <?= htmlspecialchars($estado['estado']) ?>
        </p>
    <?php else: ?>
        <p>No haz registrado tu estado de ánimo hoy.</p>
    <?php endif; ?>
    </div>
    <img src="../../public/assests/img/estado.gif" alt="estado">
    <?php
    include('../../public/footer.html')
?>
</body>
</html>