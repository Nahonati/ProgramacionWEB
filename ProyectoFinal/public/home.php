<?php
session_start();

    ini_set('display_errors', 1);
    error_reporting(E_ALL);


        // conexion con la base de datos
        require '../includes/conexion.php';

        $sql = "SELECT id, nombre FROM estado_animo ORDER BY nombre ASC";
        $stmt = $conn->query($sql);
        $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $id_usuario = $_SESSION['usuario_id'];
        $hoy = date('Y-m-d');

        // Obtener todos los hábitos activos del usuario
        $stmt = $conn->prepare("SELECT * FROM habitos WHERE usuario_id = ? AND activo = 1");
        $stmt->execute([$id_usuario]);
        $habitos_hoy = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BestVersion - Home</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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


    <div class="cards-grid">
        <!-- Estado de Ánimo Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">¿Cómo te sientes hoy?</h3>
            <form method="POST" action="../public/animo/registrar_animo.php">
                </div>
                    <div class="select is-fullwidth">
                        <div class="form-registro">
                            <select name="estado_animo_id" id="estado_animo" required>
                                <option value="">-- Selecciona tu estado de ánimo --</option>
                                <?php foreach ($estados as $estado): ?>
                                    <option option value="<?= $estado['id'] ?>">
                                        <?= htmlspecialchars($estado['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="boton">Registrar</button>
                        </div>
                    </div>
                <div>
                    <img src="../public/assests/img/animo.gif" alt="animo">
                </div>
                </div>
            </form>
    </div>

    <div class="cards-grid">
    <br><br><br>

    <!-- Habitos Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hábitos de hoy</h3>
        </div>

        <?php if (empty($habitos_hoy)): ?>
            <p>No tienes hábitos registrados para hoy.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($habitos_hoy as $habito): ?>
                    <li>
                        <strong><?= htmlspecialchars($habito['nombre']) ?></strong>
                        <?php if (!empty($habito['descripcion'])): ?>
                            <br><small><?= htmlspecialchars($habito['descripcion']) ?></small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <br>
        <a href="../public/habitos/habitos.php" class="circulo">+</a>
        <div>
            <img src="../public/assests/img/habitos.gif" alt="habito">
        </div>
    </div>
</div>


    <div class="cards-grid">
        <!-- Progreso Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reporte de Habitos</h3>
            </div>
            <div>
                <a href="../public/habitos/reportes.php"><img src="../public/assests/img/progreso.gif" alt="progreso"></a>
            </div>
        </div>
    </div>
    
</body>
<?php
    include('footer.html')
?>
</html>

