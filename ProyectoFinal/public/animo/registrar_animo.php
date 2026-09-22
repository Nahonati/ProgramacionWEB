<?php
session_start();
require '../../includes/conexion.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../public/animo/login.php");
    exit();
}

// Validar que llegó el estado de ánimo vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estado_animo_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $estado_animo_id = (int) $_POST['estado_animo_id'];
    

    try {
        // Verificar si ya se registró hoy (opcional: evitar duplicados)
        $sql = "SELECT COUNT(*) FROM registro_estado 
        WHERE usuario_id = :usuario_id 
        AND DATE(fecha_creacion) = CURDATE()";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':usuario_id' => $usuario_id]);

        $ya_registrado = $stmt->fetchColumn();

        if ($ya_registrado) {
            echo "Ya registraste tu estado de ánimo hoy.";
            echo '<a href="../../public/home.php">Volver</a>';
            exit;
        } else {
            // Insertar el nuevo registro
            $sql = "INSERT INTO registro_estado (usuario_id, estado_animo_id)
                    VALUES (:usuario_id, :estado_animo_id)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':estado_animo_id' => $estado_animo_id,
            ]);

            echo "Estado de ánimo registrado correctamente.";
            echo '<a href="../../public/home.php">Volver</a>';
            // Opcional: redireccionar
            // header("Location: miperfil.php");
        }

    } catch (PDOException $e) {
        die("Error al guardar estado de ánimo: " . $e->getMessage());
    }

} else {
    echo "Solicitud no válida.";
}
?>
