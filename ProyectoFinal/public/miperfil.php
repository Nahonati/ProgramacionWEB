<?php
session_start();

// conexion con la base de datos
require '../includes/conexion.php';

$id_usuario = $_SESSION['usuario_id'];

$sql = "SELECT u.nombres, u.a_paterno, u.a_materno, u.email, e.descripcion AS estatus, u.fecha_creacion
        FROM usuarios u
        JOIN estatus_usuario e ON u.estatus_id = e.id
        WHERE u.id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">

</head>
<body>
<nav class="nav-container">
        <div class="contenido">
            <a href="../public/home.php" class="nav-logo">BestVersion</a>
            <div class="nav-links">
                <a href="../public/metas/metas.php">Mis metas</a>
                <a href="../public/habitos/habitos.php">Mis habitos</a>
                <a href="../public/animo/estado_animo.php">Estado de Animo</a>
                <a href="../public/miperfil.php">Mi perfil</a>
            </div>
        </div>
    </nav>
    <br><br><br><br><br>
    <br><br><br><br><br>
    <div class="container">
        <br><br><br><br>
        <div class="box">
            <br><br><br><br>
        <div class="fotoperfil"><img src="../public/assests/img/usuario.svg" alt="foto"></div>
            <h2 class="title has-text-centered" style="color: #ffbb98 ;">Mi Perfil</h2>
            <div class="usuario">
                <label for="nombre" class="perfil">Nombre:</label>
                <p><?= htmlspecialchars($usuario['nombres']) ?></p>
                <label for="apaterno" class="perfil">Apellido Paterno:</label>
                <p><?= htmlspecialchars($usuario['a_paterno']) ?></p>
                <label for="amaterno" class="perfil">Apellido Materno:</label>
                <p><?= htmlspecialchars($usuario['a_materno']) ?></p>
                <label for="correo" class="perfil">Correo:</label>
                <p><?= htmlspecialchars($usuario['email']) ?></p>
                <label for="estatus" class="perfil">Estatus:</label>
                <p><?= htmlspecialchars($usuario['estatus']) ?></p>
                <label for="fechacreacion" class="perfil">Miembro desde:</label>
                <p><?= htmlspecialchars($usuario['fecha_creacion']) ?></p>
            </div>
            <br>
            <a href="../public/cerrar.php" class="boton">Cerrar Sesión</a>
        </div>
        <br>
        <div class="welcome"><img src="../public/assests/img/login.gif" alt="perfil"></div>
    </div>
</body>
</html>

