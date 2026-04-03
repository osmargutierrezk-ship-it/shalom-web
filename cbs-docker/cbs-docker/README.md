# 🎓 Colegio Bautista Shalom — Docker Setup

Stack completo con **PHP 8.2 + Apache**, **MySQL 8.0** y **phpMyAdmin**, todo
orquestado con Docker Compose.

---

## 📁 Estructura del proyecto

```
cbs-docker/
├── docker-compose.yml      ← Orquestación de servicios
├── Dockerfile              ← Imagen PHP + Apache personalizada
├── init.sql                ← Esquema y datos iniciales de la BD
├── README.md               ← Este archivo
└── html/                   ← Código fuente de la aplicación
    ├── db.php              ← Conexión centralizada (usa env vars)
    ├── index.html          ← Página principal
    ├── admisiones.html     ← Página de admisiones
    ├── registro.html       ← Login / Registro de usuarios
    ├── dashboard.php       ← Panel de administración
    ├── guardar.php         ← API: guardar solicitud de admisión
    ├── login.php           ← API: autenticación de usuarios
    └── registro.php        ← API: registro de nuevos usuarios
```

---

## 🚀 Levantar el proyecto

### Requisitos
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows / Mac)
- O `docker` + `docker compose` en Linux

### Pasos

```bash
# 1. Entrar a la carpeta del proyecto
cd cbs-docker

# 2. Construir imágenes y levantar todos los servicios
docker compose up --build

# 3. (opcional) Correr en segundo plano
docker compose up --build -d
```

### Accesos una vez levantado

| Servicio      | URL                          | Descripción                  |
|---------------|------------------------------|------------------------------|
| Aplicación    | http://localhost:8080        | Sitio web principal          |
| phpMyAdmin    | http://localhost:8081        | Gestor visual de la BD       |
| MySQL (externo)| `localhost:3306`            | Conexión directa a MySQL     |

---

## 🔑 Credenciales por defecto

| Recurso    | Usuario         | Contraseña   |
|------------|-----------------|--------------|
| MySQL root | `root`          | `root`       |
| App DB user| `cbs_user`      | `cbs_pass`   |
| Portal demo| `admin@cbs.edu.gt` | `Admin1234` |

> ⚠️ **Cambia estas credenciales antes de desplegar en producción.**
> Edita las variables en `docker-compose.yml` y vuelve a construir.

---

## 🗄️ Base de datos

El archivo `init.sql` se ejecuta automáticamente la **primera vez** que el
contenedor MySQL arranca. Crea:

- `solicitudes`  — formulario de admisión (`guardar.php`)
- `estudiantes`  — catálogo de alumnos (validación de código en `registro.php`)
- `usuarios`     — cuentas del portal (`login.php` / `registro.php`)

Si necesitas **reinicializar la BD** (borrar datos y volver a ejecutar el SQL):

```bash
docker compose down -v          # elimina contenedores Y volumen de datos
docker compose up --build       # vuelve a crear todo desde cero
```

---

## 🔧 Variables de entorno

Definidas en `docker-compose.yml`, disponibles en PHP vía `getenv()`:

| Variable  | Valor por defecto | Descripción          |
|-----------|-------------------|----------------------|
| `DB_HOST` | `db`              | Hostname del servicio MySQL |
| `DB_NAME` | `formulario_db`   | Nombre de la base de datos  |
| `DB_USER` | `root`            | Usuario MySQL               |
| `DB_PASS` | `root`            | Contraseña MySQL            |

---

## 🛠️ Comandos útiles

```bash
# Ver logs de todos los servicios
docker compose logs -f

# Ver logs solo del servidor web
docker compose logs -f web

# Reiniciar solo el servidor web (tras cambiar código PHP)
docker compose restart web

# Entrar al contenedor PHP
docker compose exec web bash

# Entrar al contenedor MySQL
docker compose exec db mysql -uroot -proot formulario_db

# Detener todo
docker compose down
```

---

## 📦 Logo e imágenes

Coloca el archivo `logo_cbs.png` (y `logo_shalom.png`) dentro de la carpeta
`html/` y se servirán automáticamente en `http://localhost:8080/logo_cbs.png`.

---

## 🏭 Producción (consideraciones mínimas)

1. Cambiar todas las contraseñas en `docker-compose.yml`
2. Agregar `HTTPS` con un proxy inverso (Nginx + Certbot, Traefik, etc.)
3. Eliminar los datos de prueba al final de `init.sql`
4. Agregar autenticación al `dashboard.php`
5. Configurar backups automáticos del volumen `cbs_db_data`
