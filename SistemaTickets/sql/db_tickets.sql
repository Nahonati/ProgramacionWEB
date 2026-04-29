/*Seminario de Titulación*/

-- Crear la base de datos
CREATE DATABASE db_tickets; 

USE db_tickets; 

/* TABLA DE ROLES */
CREATE TABLE roles (
    id_rol TINYINT UNSIGNED PRIMARY KEY, 
    nombre_rol VARCHAR(30) NOT NULL
);

/* Valores de la tabla roles*/
INSERT INTO roles (id_rol, nombre_rol) VALUES
(1, 'Administrador'),
(2, 'Usuario'),
(3, 'Tecnico');

/* TABLA DEPARTAMENTOS */
CREATE TABLE departamentos (
    id_dept TINYINT UNSIGNED PRIMARY KEY, 
    nombre_dept VARCHAR(30) NOT NULL
);

/* Valores de la tabla departamentos*/
INSERT INTO departamentos (id_dept, nombre_dept) VALUES
(1, 'Recursos Humanos'),
(2, 'Administración'),
(3, 'Produccion'),
(4, 'Arte'),
(5, 'Compositing'),
(6, '2D'),
(7, '3D'),
(8, 'Storyboard'),
(9, 'Render'); 

/* TABLA DE USUARIOS */
CREATE TABLE usuarios (
    id_usuario INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    a_paterno VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id TINYINT UNSIGNED NOT NULL DEFAULT 2,
    dept_id TINYINT UNSIGNED NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Declaración de las llaves foráneas
    FOREIGN KEY (rol_id) REFERENCES roles(id_rol),
    FOREIGN KEY (dept_id) REFERENCES departamentos(id_dept)
);

/* TABLA DE TICKET_STATUS */
CREATE TABLE ticket_status (
    id_status TINYINT UNSIGNED PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

/* Valores de la tabla ticket_status */
INSERT INTO ticket_status (id_status, nombre) VALUES
(1, 'Nuevo'),
(2, 'En proceso'),
(3, 'En espera'),
(4, 'Resuelto'),
(5, 'Cerrado');

/* TABLA DE PRIORIDADES */
CREATE TABLE ticket_prioridad (
    id_prio TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    tiempo_estimado_horas INT NOT NULL  
);

/* Valores de la tabla ticket_prioridad*/
INSERT INTO ticket_prioridad (nombre, tiempo_estimado_horas) VALUES 
('Baja', 72),
('Media', 24),
('Alta', 4),
('Crítica', 1);

/* TABLA DE TICKET_CATEGORIA */
CREATE TABLE ticket_categoria (
    id_ctgy TINYINT UNSIGNED PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL, 
    descripcion VARCHAR(100) NULL
);

/* Valores de la tabla ticket_categoria*/
INSERT INTO ticket_categoria (id_ctgy, nombre, descripcion) VALUES
(1, 'Hardware', 'PC, monitores, teclados'),
(2, 'Software', 'Windows, Office, programas internos'),
(3, 'Redes', 'Internet, IPs'),
(4, 'Accesos', 'Contraseñas'),
(5, 'Telefonia', NULL);

/* TABLA DE TICKETS*/
CREATE TABLE tickets (
    id_ticket INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100),
    descripcion TEXT,
    usuario_reporta_id INT UNSIGNED NOT NULL, 
    tecnico_asignado_id INT UNSIGNED NULL,  
    categoria_id TINYINT UNSIGNED,
    prioridad_id TINYINT UNSIGNED,
    estatus_id TINYINT UNSIGNED,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_atencion TIMESTAMP NULL,
    fecha_resolucion TIMESTAMP NULL,

    -- Declaración de las llaves foráneas
    FOREIGN KEY (usuario_reporta_id) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (tecnico_asignado_id) REFERENCES usuarios(id_usuario),  
    FOREIGN KEY (categoria_id) REFERENCES ticket_categoria(id_ctgy),
    FOREIGN KEY (prioridad_id) REFERENCES ticket_prioridad(id_prio),
    FOREIGN KEY (estatus_id) REFERENCES ticket_status(id_status)
);

/*TABLA DE TICKET_HISTORY*/
CREATE TABLE tickets_history (
    id_history INT UNSIGNED PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL, 
    usuario_que_cambio_id INT UNSIGNED NOT NULL,
    estatus_anterior TINYINT UNSIGNED NULL, 
    estatus_nuevo TINYINT UNSIGNED NOT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Declaración de las llaves foráneas
    FOREIGN KEY (ticket_id) REFERENCES tickets(id_ticket),
    FOREIGN KEY (usuario_que_cambio_id) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (estatus_anterior) REFERENCES ticket_status(id_status),
    FOREIGN KEY (estatus_nuevo) REFERENCES ticket_status(id_status)
);

/*TABLA DE TICKET_COMENTARIOS*/
CREATE TABLE ticket_comentarios (
    id_coment INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    comentario TEXT NOT NULL,
    es_nota_interna BOOLEAN DEFAULT FALSE, 
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_comentario_ticket 
        FOREIGN KEY (ticket_id) REFERENCES tickets(id_ticket),
        
    CONSTRAINT fk_comentario_usuario 
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario)
);