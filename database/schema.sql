CREATE DATABASE IF NOT EXISTS eduka_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eduka_db;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    docente_id INT,
    cupos_totales INT NOT NULL,
    cupos_disponibles INT NOT NULL,
    horario VARCHAR(100),
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    FOREIGN KEY (docente_id) REFERENCES usuarios(id)
);

CREATE TABLE matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    curso_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente','confirmada','anulada') DEFAULT 'pendiente',
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
    FOREIGN KEY (curso_id) REFERENCES cursos(id)
);

CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo VARCHAR(50) DEFAULT 'Yape',
    codigo_yape VARCHAR(50) NULL,
    metodo_verificacion ENUM('codigo','comprobante') DEFAULT 'comprobante',
    comprobante VARCHAR(255),
    estado ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id)
);

CREATE TABLE lista_espera (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    curso_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
    FOREIGN KEY (curso_id) REFERENCES cursos(id)
);

CREATE TABLE yape_operaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    monto DECIMAL(10,2) NOT NULL,
    nombre_pagador VARCHAR(150),
    telefono_pagador VARCHAR(20),
    usado TINYINT(1) DEFAULT 0,
    fecha_operacion DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE login_intentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (nombre) VALUES ('administrador'), ('estudiante'), ('docente');

-- Usuario admin para pruebas (password: password123)
INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES
('Admin', 'Eduka', 'admin@eduka.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uDutXuQiO', 1);