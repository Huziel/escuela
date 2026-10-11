# SICE - Sistema Integral de Control Escolar

ERP académico moderno para instituciones educativas de nivel medio superior y superior (CETIS, CBTIS, CONALEP, COBACH, CECyTE, Universidades Tecnológicas).

## Tecnologías

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Vue 3 (Options API), Bootstrap 5 |
| Base de datos | MySQL |
| API | REST con Laravel Sanctum |
| Autenticación | Sanctum + Spatie Permissions (RBAC) |
| Gráficas | Chart.js |
| PDF | DomPDF (Barryvdh) |
| Excel | Maatwebsite Excel |

## Requisitos

- PHP 8.2+
- Composer 2.x
- MySQL 5.7+
- Node.js 20+ y npm

## Instalación rápida

```bash
# 1. Clonar o copiar el proyecto
cd sice

# 2. Instalar dependencias PHP
composer install

# 3. Configurar entorno
cp .env.example .env

# 4. Generar clave de aplicación
php artisan key:generate

# 5. Crear base de datos MySQL
mysql -u root -p123456789 -e "CREATE DATABASE IF NOT EXISTS sice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Instalar dependencias frontend
npm install

# 8. Construir assets
npm run build

# 9. Iniciar servidor
php artisan serve
```

Acceder a: http://localhost:8000

## Credenciales de prueba

| Rol | Usuario | Contraseña |
|-----|---------|-----------|
| Super Admin | admin | password123 |
| Control Escolar | control | password123 |
| Admin | direccion | password123 |
| Docente (15) | docente1 | password123 |
| Alumno (50) | alumno1 | password123 |
| Tutor (35) | tutor1 | password123 |

## Configuración .env

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sice
DB_USERNAME=root
DB_PASSWORD=123456789
```

## Arquitectura

```
sice/
├── app/
│   ├── Helpers/          # AuditoriaHelper
│   ├── Http/Controllers/Api/V1/
│   │   ├── AuthController.php
│   │   └── DashboardController.php
│   ├── Interfaces/       # BaseRepositoryInterface
│   ├── Models/           # User, Acceso, Noticia, Aviso, etc.
│   ├── Modules/
│   │   ├── Alumnos/      # Controllers, Models, Repositories, Services, etc.
│   │   ├── Asistencia/
│   │   ├── Auditoria/
│   │   ├── Calificaciones/
│   │   ├── Configuracion/
│   │   ├── Dashboard/
│   │   ├── Docentes/
│   │   ├── Especialidades/
│   │   ├── Grupos/
│   │   ├── Horarios/
│   │   ├── Inscripciones/
│   │   ├── Materias/
│   │   ├── Notificaciones/
│   │   ├── Regularizacion/
│   │   ├── Reportes/
│   │   ├── Tutores/
│   │   └── Usuarios/
│   ├── Providers/        # ModulesServiceProvider, PermissionsServiceProvider
│   ├── Repositories/     # BaseRepository
│   ├── Services/         # BaseService
│   └── Traits/           # ApiResponse
├── database/
│   ├── migrations/       # 25 migraciones
│   └── seeders/          # 13 seeders con datos de prueba
├── resources/
│   ├── js/               # Vue 3 SPA (Options API)
│   │   ├── components/
│   │   ├── layouts/      # MainLayout.vue
│   │   ├── router/       # Vue Router
│   │   ├── stores/       # Pinia (auth, app)
│   │   └── views/        # 18 vistas organizadas por módulo
│   ├── sass/             # Estilos Bootstrap 5 personalizados
│   └── views/            # Blade templates (app.blade.php, reportes PDF)
└── routes/
    ├── api.php           # API v1 con todas las rutas
    └── web.php           # SPA entry point
```

## Patrón de Diseño

Cada módulo sigue el patrón Repositorio-Servicio:

```
Controller -> Service -> Repository -> Model
     ↓           ↓           ↓
  Request    Auditoria   BaseRepository
  Resource
  Policy
```

## Roles y Permisos

| Rol | Permisos destacados |
|-----|-------------------|
| super_admin | Todos los permisos |
| admin | Todos los permisos |
| control_escolar | Alumnos, Inscripciones, Calificaciones, Asistencia, Reportes |
| docente | Calificaciones, Asistencia, Alumnos (lectura) |
| tutor | Calificaciones (lectura), Asistencia (lectura) |
| alumno | Calificaciones (lectura), Asistencia (lectura) |

## API REST Endpoints (v1)

### Autenticación
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | /api/v1/login | Iniciar sesión |
| POST | /api/v1/logout | Cerrar sesión |
| POST | /api/v1/logout-all | Cerrar todas las sesiones |
| GET | /api/v1/user | Usuario autenticado |
| POST | /api/v1/refresh-token | Refrescar token |
| POST | /api/v1/change-password | Cambiar contraseña |
| POST | /api/v1/forgot-password | Recuperar contraseña |
| POST | /api/v1/reset-password | Restablecer contraseña |

### Dashboard
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | /api/v1/dashboard/stats | Estadísticas generales |
| GET | /api/v1/dashboard/charts | Datos para gráficas |

### CRUD Endpoints (todos requieren auth:sanctum + permisos)
| Módulo | Endpoint base |
|--------|--------------|
| Alumnos | /api/v1/alumnos |
| Docentes | /api/v1/docentes |
| Grupos | /api/v1/grupos |
| Especialidades | /api/v1/especialidades |
| Materias | /api/v1/materias |
| Inscripciones | /api/v1/inscripciones |
| Calificaciones | /api/v1/calificaciones |
| Asistencia | /api/v1/asistencia |
| Tutores | /api/v1/tutores |
| Regularización | /api/v1/regularizacion |
| Horarios | /api/v1/horarios |
| Usuarios | /api/v1/usuarios |
| Reportes | /api/v1/reportes |
| Notificaciones | /api/v1/notificaciones |
| Auditoría | /api/v1/auditoria |
| Configuración | /api/v1/configuracion |

Cada endpoint soporta: GET (listar con paginación/filtros), POST (crear), GET/{id} (ver), PUT/{id} (actualizar), DELETE/{id} (eliminación lógica).

## Modelo de Base de Datos

El sistema incluye 25 tablas con relaciones completas:

```
users (1) ── (1) alumnos
users (1) ── (1) docentes
users (1) ── (1) tutores
especialidades (1) ── (N) alumnos
especialidades (1) ── (N) materias
especialidades (1) ── (N) grupos
grupos (1) ── (N) alumnos
grupos (1) ── (N) horarios
docentes (1) ── (N) horarios
docentes (N) ── (N) materias (docente_materia)
docentes (N) ── (N) grupos (docente_grupo)
alumnos (1) ── (N) calificaciones
alumnos (1) ── (N) asistencias
alumnos (1) ── (N) inscripciones
inscripciones (N) ── (N) materias (inscripcion_materia)
```

## Datos de prueba generados

- 3 usuarios administrativos
- 8 especialidades
- 15 docentes
- 35 tutores
- 50 alumnos
- ~240 materias
- ~12 grupos
- 50 inscripciones
- ~800 calificaciones
- ~300 asistencias
- 5 noticias
- Configuraciones del sistema

## Licencia

Sistema propietario para uso educativo.
