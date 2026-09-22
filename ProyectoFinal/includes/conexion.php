<?php
    $host = 'localhost';
    $dbname = 'login_db3';
    $user = 'root';
    $pass = '0401n';

    try {
        /*
         * Creamos una nueva conexión a la base de datos usando PDO (PHP Data Objects).
         * - Se especifica el tipo de base de datos: mysql
         * - Host: servidor donde está la base de datos
         * - dbname: nombre de la base de datos
         * - charset=utf8: asegura que se usen correctamente los acentos y caracteres especiales
         */
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

        /*
         * Configuramos el modo de errores para que PDO lance excepciones (EXCEPTION MODE).
         * Esto nos ayuda a detectar y manejar errores de forma controlada con try-catch.
         */        
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
    } catch (PDOException $e) {
        /*
         * Si ocurre un error en la conexión, se captura la excepción y se muestra un mensaje.
         * Esto evita que se muestre un error técnico no controlado.
         */        
        die("Error de conexión: " . $e->getMessage());
    }
?>