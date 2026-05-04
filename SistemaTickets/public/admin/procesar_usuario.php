<?php
session_start();

// 1. Verificamos que solo el administrador pueda hacer esto
if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['rol_id'] ?? 0) !== 1) {
    header('Location: ../../index.php');
    exit();
}

require __DIR__ . '/../../includes/conexion_db.php';

// 2. Revisamos si los datos llegaron por el formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recolectamos los datos y limpiamos los espacios en blanco
    $nombre = trim($_POST['nombre'] ?? '');
    $a_paterno = trim($_POST['a_paterno'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol_id = (int)($_POST['rol_id'] ?? 0);
    $dept_id = (int)($_POST['dept_id'] ?? 0);

    // Validamos que no vengan vacíos
    if ($nombre && $a_paterno && $email && $password && $rol_id && $dept_id) {
        
        try {
            // 3. Verificamos que el correo no esté registrado ya en la base de datos
            $stmtCheck = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
            $stmtCheck->execute([':email' => $email]);
            
            if ($stmtCheck->rowCount() > 0) {
                $_SESSION['flash_error'] = "Error: El correo $email ya pertenece a otro usuario.";
            } else {
                
                // 4. Encriptamos la contraseña por seguridad (¡Punto extra para tu tesis!)
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // 5. Insertamos al nuevo usuario
                $sql = "INSERT INTO usuarios (nombre, a_paterno, email, password, rol_id, dept_id) 
                        VALUES (:nombre, :a_paterno, :email, :password, :rol_id, :dept_id)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':a_paterno' => $a_paterno,
                    ':email' => $email,
                    ':password' => $passwordHash,
                    ':rol_id' => $rol_id,
                    ':dept_id' => $dept_id
                ]);

                // Guardamos el mensaje de éxito
                $_SESSION['flash_ok'] = "¡Éxito! El usuario $nombre $a_paterno fue registrado correctamente.";
            }
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Error en la base de datos: " . $e->getMessage();
        }

    } else {
        $_SESSION['flash_error'] = "Por favor, completa todos los campos del formulario.";
    }
}

// 6. Regresamos a la pantalla del administrador
header("Location: administrador.php");
exit();
?>