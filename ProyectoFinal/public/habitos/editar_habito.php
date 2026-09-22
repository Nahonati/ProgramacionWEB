<?php

session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}


$id_usuario = $_SESSION['usuario_id'];
$id_habito = $_GET['id'] ?? null;

// Si no viene el ID del hábito, regresar a la lista
if (!$id_habito) {
    header("Location: home.php");
    exit;
}

// Buscar el hábito que pertenece al usuario
$stmt = $conn->prepare("SELECT * FROM habitos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id_habito, $id_usuario]);
$habito = $stmt->fetch();

// Si no existe o no es del usuario, no dejar continuar
if (!$habito) {
    echo "Hábito no encontrado o no tienes permiso para editarlo.";
    exit;
}

// Cargar las categorías y frecuencias para llenar el formulario
$frecuencias = $conn->query("SELECT id_frecuencia, descripcion FROM frecuencias")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Hábito - BestVersion</title>
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
<section>
    <div class="container">
        <div class="box">
        <h2 class="title has-text-centered" style="color: #ffbb98 ;">Editar hábito</h2>
            <div class="box">
                
                <!-- Formulario para editar el hábito -->
                <form action="update.php" method="POST">
                    <!-- Enviar el ID como campo oculto para saber qué hábito actualizar -->
                    <input type="hidden" name="id_habito" value="<?= htmlspecialchars($habito['id']) ?>">

                    <div class="form-registro">
                        <label class="label">Nombre del hábito</label>
                        <input class="input" type="text" name="nombre_habito" value="<?= htmlspecialchars($habito['nombre']) ?>" required>
                    </div>

                    <div class="form-registro">
                        <label>Descripción</label>
                        <textarea name="descripcion"><?= htmlspecialchars($habito['descripcion']) ?></textarea>
                    </div>

                    <div>
                        <label>Frecuencia</label>
                        <div">
                            <div class="select is-fullwidth">
                                <select name="id_frecuencia" required>
                                    <?php foreach ($frecuencias as $freq): ?>
                                        <option value="<?= $freq['id_frecuencia'] ?>" <?= $freq['id_frecuencia'] == $habito['id_frecuencia'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($freq['descripcion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div>
                            <button class="boton" type="submit" >Actualizar Habito</button>
                        </div>
                        <br>
                        <div>
                            <a href="habitos.php" class="boton">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
</body>
</html>