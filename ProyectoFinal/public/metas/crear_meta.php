<?php


session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// Obtener los hábitos activos del usuario para poder vincular una meta a alguno de ellos
$stmt = $conn->prepare("SELECT id, nombre FROM habitos WHERE usuario_id = ? AND activo = TRUE");
$stmt->execute([$id_usuario]);
$habitos = $stmt->fetchAll();

// Si no hay hábitos activos, el usuario debe crear uno primero
if (empty($habitos)) {
    echo "<p>No tienes hábitos activos. Crea uno antes de establecer una meta.</p>";
    echo '<a href="../habitos/crear_habitos.php">Crear hábito</a>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Meta - BestVersion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav class="nav-container">
        <div class="contenido">
            <a href="../public/home.php" class="nav-logo">BestVersion</a>
            <div class="nav-links">
                <a href="../../public/metas/metas.php">Mis metas</a>
                <a href="../../public/habitos/habitos.php">Mis habitos</a>
                <a href="../../public/animo/estado_animo.php">Estado de Animo</a>
                <a href="../../public/miperfil.php">Mi perfil</a>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main>
        <section class="section">
            <div class="container">
                <!-- Mensajes de error que pueden venir por la URL -->
                <?php if (isset($_GET['error'])): ?>
                    <div class="notification is-danger is-light">
                        <button class="delete"></button>
                        <?php
                        // Muestra mensajes personalizados según el tipo de error detectado
                        switch ($_GET['error']) {
                            case 'faltan_datos':
                                echo "Todos los campos son obligatorios. Por favor, completa el formulario.";
                                break;
                            case 'fecha_invalida':
                                echo "La fecha de inicio no puede ser posterior a la fecha de fin.";
                                break;
                            default:
                                echo "Ocurrió un error desconocido. Intenta de nuevo.";
                        }
                        ?>
                    </div>
                <?php endif; ?>


                <div class="container-registro">
                    <div class="card">
                        <h1 class="title has-text-centered" style="color: #ffbb98 ;">Crear nueva meta</h1>

                        <!-- Formulario para registrar una nueva meta -->
                        <form action="../../public/metas/procesar_meta.php" method="POST">
                            <div class="field">
                                <label class="label">Hábito relacionado</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="id_habito" required>
                                            <?php foreach ($habitos as $habito): ?>
                                                <option value="<?= $habito['id'] ?>">
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
                                    <input class="input" type="text" name="descripcion" placeholder="Ej: Meditar 10 veces" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Cantidad objetivo</label>
                                <div class="control">
                                    <input class="input" type="number" name="cantidad_objetivo" min="1" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Fecha de inicio</label>
                                <div class="control">
                                    <input class="input" type="date" name="fecha_inicio" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Fecha de fin</label>
                                <div class="control">
                                    <input class="input" type="date" name="fecha_fin" required>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="field is-grouped is-grouped-centered">
                                <div class="control">
                                    <button type="submit" class="boton">Guardar meta</button>
                                </div>
                                <br>
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
    </main>
    <img src="../../public/assests/img/metas.gif" alt="meta">
</body>
</html>