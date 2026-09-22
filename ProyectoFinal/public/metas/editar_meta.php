<?php
session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}


$id_usuario = $_SESSION['usuario_id'];
$id_meta = $_GET['id'] ?? null;

// Si no hay ID, regresar al índice
if (!$id_meta) {
    header("Location: metas.php");
    exit;
}

// Consultar la meta para asegurar de que le pertenece al usuario actual
$stmt = $conn->prepare("SELECT * FROM metas WHERE id_meta = ? AND id_usuario = ?");
$stmt->execute([$id_meta, $id_usuario]);
$meta = $stmt->fetch();

// Si no se encuentra la meta o no pertenece al usuario
if (!$meta) {
    echo "Meta no encontrada o sin permisos.";
    exit;
}

// Traer todos los hábitos activos del usuario para llenar el select
$stmt = $conn->prepare("SELECT id, nombre FROM habitos WHERE usuario_id = ? AND activo = TRUE");
$stmt->execute([$id_usuario]);
$habitos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Meta - BestVersion</title>
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
    <div class="box">
        <div class="column is-half is-offset-one-quarter">
        <h1 class="title has-text-centered" style="color: #ffbb98 ;">Editar Meta</h1>

            <div class="container-registro">

                <!-- Formulario con los datos precargados -->
                <form action="../../public/metas/update_meta.php" method="POST">
                    <input type="hidden" name="id_meta" value="<?= $meta['id_meta'] ?>">

                    <div class="field">
                        <label class="label">Hábito relacionado</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="id_habito" required>
                                    <?php foreach ($habitos as $habito): ?>
                                        <option value="<?= $habito['id'] ?>" <?= $habito['id'] == $meta['id_habito'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($habito['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Descripción</label>
                        <div class="control">
                            <input class="input" type="text" name="descripcion" value="<?= htmlspecialchars($meta['descripcion']) ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Cantidad objetivo</label>
                        <div class="control">
                            <input class="input" type="number" name="cantidad_objetivo" value="<?= $meta['cantidad_objetivo'] ?>" min="1" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Fecha de inicio</label>
                        <div class="control">
                            <input class="input" type="date" name="fecha_inicio" value="<?= $meta['fecha_inicio'] ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Fecha de fin</label>
                        <div class="control">
                            <input class="input" type="date" name="fecha_fin" value="<?= $meta['fecha_fin'] ?>" required>
                        </div>
                    </div>

                    <div class="field is-grouped is-grouped-centered">
                        <div class="control">
                            <button type="submit" class="boton">Actualizar Meta</button>
                        </div>
                        <div class="control">
                            <br>
                            <a href="../../public/metas/metas.php" class="boton">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
</body>
</html>