-- ═══════════════════════════════════════════════════════════════
--  Colegio Bautista Shalom — Esquema inicial
--  Se ejecuta automáticamente al primer arranque del contenedor
--  de MySQL (docker-entrypoint-initdb.d).
-- ═══════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS formulario_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE formulario_db;

-- ── Tabla de solicitudes de admisión (guardar.php) ────────────
CREATE TABLE IF NOT EXISTS solicitudes (
  id         INT          NOT NULL AUTO_INCREMENT,
  padre      VARCHAR(120) NOT NULL,
  estudiante VARCHAR(120) NOT NULL,
  telefono   VARCHAR(30)  NOT NULL,
  email      VARCHAR(120) NOT NULL,
  nivel      VARCHAR(80)  NOT NULL,
  grado      VARCHAR(80)  NOT NULL,
  mensaje    TEXT,
  creado_en  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabla de estudiantes (registro.php lo consulta) ───────────
CREATE TABLE IF NOT EXISTS estudiantes (
  id       INT          NOT NULL AUTO_INCREMENT,
  codigo   VARCHAR(20)  NOT NULL UNIQUE,
  nombre   VARCHAR(120) NOT NULL,
  grado    VARCHAR(60),
  activo   TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabla de usuarios del portal (login.php / registro.php) ───
CREATE TABLE IF NOT EXISTS usuarios (
  id        INT          NOT NULL AUTO_INCREMENT,
  nombre    VARCHAR(120) NOT NULL,
  correo    VARCHAR(120) NOT NULL UNIQUE,
  telefono  VARCHAR(30),
  password  VARCHAR(255) NOT NULL,          -- almacenado con password_hash()
  codigo    VARCHAR(20)  NOT NULL UNIQUE,   -- FK a estudiantes.codigo
  creado_en TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_usuario_estudiante
    FOREIGN KEY (codigo) REFERENCES estudiantes(codigo)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════
--  Datos de prueba — elimina este bloque en producción
-- ═══════════════════════════════════════════════════════════════

-- Estudiantes de muestra
INSERT IGNORE INTO estudiantes (codigo, nombre, grado) VALUES
  ('CBS0001', 'María Pérez',    '2do. Básico'),
  ('CBS0002', 'Carlos López',   '1ro. Diversificado'),
  ('CBS0003', 'Ana García',     '5to. Primaria');

-- Usuario administrador de prueba
--   correo:   admin@cbs.edu.gt
--   password: Admin1234  (hash generado con password_hash en PHP 8.2)
INSERT IGNORE INTO usuarios (nombre, correo, telefono, password, codigo)
VALUES (
  'Administrador CBS',
  'admin@cbs.edu.gt',
  '+502 2234-5678',
  '$2y$12$92aCDDsSWMrXuNjXE5bMXOVLPeMkN0Wl4JO0JQXF3PEtHxVHnGwW2',
  'CBS0001'
);
