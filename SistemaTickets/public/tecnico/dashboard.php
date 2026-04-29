<?php
session_start();

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 3) {
    header('Location: ../../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tecnico | Sistema de Tickets</title>
</head>
<body>
    <h1>Dashboard Tecnico</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? ''); ?></p>
</body>
</html>
