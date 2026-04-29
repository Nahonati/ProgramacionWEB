<?php 
    // Destruir todo lo referente a la sesión
    session_start();
    session_unset();
    session_destroy();

    // Redirigir al index
    header("Location: index.php");
    exit();

?>