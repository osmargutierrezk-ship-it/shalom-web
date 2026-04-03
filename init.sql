-- ═══════════════════════════════════════════════════════════════
--  Colegio Bautista Shalom — Esquema PostgreSQL
--  Ejecutar en Render → PostgreSQL → Query tab
--  (o conectar con psql y correr este archivo)
-- ═══════════════════════════════════════════════════════════════

-- ── Tabla de solicitudes de admisión (guardar.php) ────────────
CREATE TABLE IF NOT EXISTS solicitudes (
    id         SERIAL       PRIMARY KEY,
    padre      VARCHAR(120) NOT NULL,
    estudiante VARCHAR(120) NOT NULL,
    telefono   VARCHAR(30)  NOT NULL,
    email      VARCHAR(120) NOT NULL,
    nivel      VARCHAR(80)  NOT NULL,
    grado      VARCHAR(80)  NOT NULL,
    mensaje    TEXT,
    creado_en  TIMESTAMP    NOT NULL DEFAULT NOW()
);

-- ── Tabla de estudiantes ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS estudiantes (
    id      SERIAL       PRIMARY KEY,
    codigo  VARCHAR(20)  NOT NULL UNIQUE,
    nombre  VARCHAR(120) NOT NULL,
    grado   VARCHAR(60),
    activo  BOOLEAN      NOT NULL DEFAULT TRUE
);

-- ── Tabla de usuarios del portal ─────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id        SERIAL       PRIMARY KEY,
    nombre    VARCHAR(120) NOT NULL,
    correo    VARCHAR(120) NOT NULL UNIQUE,
    telefono  VARCHAR(30),
    password  VARCHAR(255) NOT NULL,
    codigo    VARCHAR(20)  NOT NULL UNIQUE REFERENCES estudiantes(codigo)
                           ON UPDATE CASCADE ON DELETE RESTRICT,
    creado_en TIMESTAMP    NOT NULL DEFAULT NOW()
);

-- ── Datos de prueba (eliminar en producción) ──────────────────
INSERT INTO estudiantes (codigo, nombre, grado)
VALUES
    ('CBS0001', 'María Pérez',  '2do. Básico'),
    ('CBS0002', 'Carlos López', '1ro. Diversificado'),
    ('CBS0003', 'Ana García',   '5to. Primaria')
ON CONFLICT (codigo) DO NOTHING;

-- Usuario admin de prueba:  admin@cbs.edu.gt / Admin1234
INSERT INTO usuarios (nombre, correo, telefono, password, codigo)
VALUES (
    'Administrador CBS',
    'admin@cbs.edu.gt',
    '+502 2234-5678',
    '$2y$12$92aCDDsSWMrXuNjXE5bMXOVLPeMkN0Wl4JO0JQXF3PEtHxVHnGwW2',
    'CBS0001'
) ON CONFLICT DO NOTHING;
