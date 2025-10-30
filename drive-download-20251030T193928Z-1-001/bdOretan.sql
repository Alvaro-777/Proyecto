
CREATE DATABASE `oretan-ia`
  CHARSET utf8 COLLATE utf8_unicode_ci;
USE `oretan-ia`;
CREATE TABLE Usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(100) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    tipo ENUM('free', 'premium') NOT NULL DEFAULT 'free',
    creditos INT DEFAULT 0,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Pago (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    creditos_obtenidos INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    metodo VARCHAR(50),
    valido BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id)
);

CREATE TABLE Archivo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    peso INT,
    tipo VARCHAR(50),
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id)
);

CREATE TABLE IA (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('texto_audio', 'predictiva', 'chatbot') NOT NULL,
    costo_creditos INT NOT NULL,
    entrada_permitida ENUM('texto', 'documento', 'ambas') NOT NULL,
    accesible_anonimos BOOLEAN DEFAULT FALSE,
    url_externa VARCHAR(255)
);

CREATE TABLE HistorialUsoIA (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NULL,
    ia_id INT NULL,
    archivo_id INT NULL,
    texto_input TEXT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_anonimo VARCHAR(45) NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id),
    FOREIGN KEY (ia_id) REFERENCES IA(id),
    FOREIGN KEY (archivo_id) REFERENCES Archivo(id)
);