<?php
    /*
    * Recuerda:
    *   Activamos el manejo de sesiones: session_start();
    * Ademas:
    *   Es necesario verificamos si el usuario ha iniciado sesión correctamente.
    *   Si no existe la variable de sesión 'usuario_id', lo redirigimos al inicio.
    *   Esto evita que alguien sin permisos acceda directamente al script por la URL.
    */

    // Mostrar errores (solo en entorno de desarrollo)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Validamos que se haya enviado un parámetro 'id' por la URL y que sea un número válido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("ID de usuario inválido.");
    }

    // Convertimos el valor recibido a entero por seguridad
    $id = intval($_GET['id']);

    try {
        // Conectamos a la base de datos
        require '../../includes/conexion.php';

        /*
        * Antes de eliminar, verificamos que el usuario realmente exista.
        * Esto evita intentar eliminar un ID que no existe y mejora la experiencia del usuario.
        */
        $verificar = $conn->prepare("SELECT id FROM usuarios WHERE id = :id");
        $verificar->execute([':id' => $id]);

        if ($verificar->rowCount() === 0) {
            echo ("Usuario no encontrado.");
            exit;
        }

        /*
        * Si el usuario existe, lo eliminamos usando una consulta preparada.
        * Esto también protege contra inyecciones SQL.
        */
        $eliminar = $conn->prepare("DELETE FROM usuarios WHERE id = :id");
        $eliminar->execute([':id' => $id]);

        // Redirigimos a la lista de usuarios después de la eliminación
        header("Location: ../../public/admin/administrador.php");
        exit;

    } catch (PDOException $e) {
        // Capturamos y mostramos errores de base de datos si ocurren
        die ("Error PDO: " . $e->getMessage());
    }
?>