CREATE DATABASE login_db3;

USE login_db3;

CREATE TABLE estatus_usuario(
    id TINYINT UNSIGNED PRIMARY KEY,
    descripcion VARCHAR(50)
);

INSERT INTO estatus_usuario (id, descripcion) VALUES
(1, 'Activo'),
(2, 'Inactivo'),
(3, 'Suspendido'),
(4, 'Pendiente'),
(5, 'Eliminado');

CREATE TABLE roles (
    id TINYINT UNSIGNED PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL
);

INSERT INTO roles (id, nombre) VALUES
(1, 'Administrador'),
(2, 'Usuario'); 

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    a_paterno VARCHAR(50) NOT NULL,
    a_materno VARCHAR(50),
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id TINYINT UNSIGNED NOT NULL DEFAULT 2,
    FOREIGN KEY (rol_id) REFERENCES roles(id),
    estatus_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (estatus_id) REFERENCES estatus_usuario(id),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP    
);

INSERT INTO usuarios (nombres, a_paterno, a_materno, email, password, rol_id, estatus_id) VALUES 
('Natalia', 'Otero', 'Barragán', 'nahomi@alumnos.udg.mx', 'hola', 1, 1);

CREATE TABLE habitos(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion VARCHAR(200),
    hora time,
    activo boolean DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    id_frecuencia TINYINT UNSIGNED NOT NULL,
    dias_personalizados VARCHAR(100) DEFAULT NULL
    FOREIGN KEY (id_frecuencia) REFERENCES frecuencias(id_frecuencia);
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
); 

CREATE TABLE frecuencias (
    id_frecuencia TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

INSERT INTO frecuencias (descripcion) VALUES
('Diaria'),
('Semanal'),
('Personalizada');

CREATE TABLE registro_habitos(
    id INT AUTO_INCREMENT PRIMARY KEY,
    habito_id INT,
    id_usuario INT,
    FOREIGN KEY (habito_id) REFERENCES habitos(id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    completado boolean,
    hora_registro time
); 

CREATE TABLE estado_animo(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    usuario_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    descripcion VARCHAR(200)
); 

INSERT INTO estado_animo(id, nombre) VALUES
(1, 'Calmado'),
(2, 'Feliz'),
(3, 'Con energía'),
(4, 'Irritado'),
(5, 'Triste'),
(6, 'Ansioso'),
(7, 'Confundido'),
(8, 'Apático'),
(9, 'Estresado'),
(10, 'Desmotivado');

CREATE TABLE registro_estado(
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT ,
    estado_animo_id INT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (estado_animo_id) REFERENCES estado_animo(id)
);

CREATE TABLE metas (
    id_meta INT AUTO_INCREMENT PRIMARY KEY,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    cantidad_objetivo INT NOT NULL,
    id_usuario INT NOT NULL,
    id_habito INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_habito) REFERENCES habitos(id)
) ENGINE=InnoDB;