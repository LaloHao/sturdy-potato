# Sistema de Decisiones Comunitarias

Una plataforma web donde los usuarios pueden crear decisiones importantes de su vida y recibir votos de la comunidad para ayudarles a decidir.

## 🚀 Características Principales

### Sistema de Usuarios
- ✅ Registro y autenticación completa
- ✅ Perfil con karma y badges automáticos
- ✅ Badges: "Overthinker", "Decisivo", "Consejero"
- ✅ Sistema de karma por participación

### Gestión de Decisiones
- ✅ Crear decisiones con múltiples opciones (2-4)
- ✅ Categorías: Carrera, Técnica, Vida, Financiera, Startup
- ✅ Decisiones anónimas opcionales
- ✅ Fecha de expiración configurable
- ✅ Estados: Draft, Open, Decided, Expired, Archived

### Sistema de Votación
- ✅ Un voto por usuario por decisión
- ✅ Comentarios opcionales en votos
- ✅ Visualización con barras de progreso
- ✅ Resultados en tiempo real
- ✅ No puedes votar en tus propias decisiones

### Sistema de Comentarios
- ✅ Comentarios en decisiones independientes de los votos
- ✅ Avatares personalizados para cada usuario
- ✅ Timestamps relativos (ej: "hace 2 horas")
- ✅ Paginación de comentarios con scroll infinito
- ✅ Incremento automático de karma al comentar
- ✅ Edición y eliminación de comentarios propios
- ✅ Textarea auto-expandible para mayor comodidad
- ✅ Diálogo de confirmación antes de publicar o eliminar
- ✅ Notificación al dueño de la decisión cuando alguien comenta

### Dashboard Personal
- ✅ Estadísticas de decisiones y votos
- ✅ Karma acumulado visible
- ✅ Badge actual del usuario
- ✅ Acciones rápidas

## 📋 Requisitos

- PHP >= 8.2
- Composer
- Node.js >= 18
- NPM
- SQLite (incluido)

## 🛠️ Instalación

### 1. Clonar el repositorio (NO HAGAS FORK)
```bash
git clone https://github.com/Agilgob/reto-candidatos.git
cd reto-candidatos
```

### 2. Instalar dependencias de PHP
```bash
composer install
```

### 3. Configurar entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Instalar dependencias de Node
```bash
npm install
```

### 5. Crear y poblar la base de datos
```bash
# Crear las tablas
php artisan migrate

# Opcional: Poblar con datos de prueba (20 decisiones, 8 usuarios)
php artisan db:seed --class=DecisionSeeder
```

## 👥 Usuarios de Prueba

Si ejecutaste el seeder, puedes usar estos usuarios:

| Email | Nombre | Contraseña |
|-------|--------|------------|
| ana@test.com | Ana García | password123 |
| carlos@test.com | Carlos Rodríguez | password123 |
| maria@test.com | María López | password123 |
| juan@test.com | Juan Martínez | password123 |
| laura@test.com | Laura Sánchez | password123 |
| pedro@test.com | Pedro Gómez | password123 |
| sofia@test.com | Sofia Fernández | password123 |
| diego@test.com | Diego Ruiz | password123 |

## 🏃‍♂️ Ejecutar la Aplicación

### Terminal 1 - Backend Laravel
```bash
php artisan serve
```
La aplicación estará disponible en: http://127.0.0.1:8000

### Terminal 2 - Frontend Vite (desarrollo con hot reload)
```bash
npm run dev
```

### Para producción
```bash
npm run build
```

## 📁 Estructura del Proyecto

```
laravel-app/
├── app/
│   ├── Http/Controllers/
│   │   ├── DecisionController.php    # API de decisiones
│   │   ├── VoteController.php        # Sistema de votación
│   │   ├── CommentController.php     # Sistema de comentarios
│   │   ├── NotificationController.php # Sistema de notificaciones
│   │   └── DashboardController.php   # Dashboard
│   └── Models/
│       ├── Decision.php              # Modelo de decisión
│       ├── Option.php                # Opciones de decisión
│       ├── Vote.php                  # Votos
│       ├── Comment.php               # Comentarios
│       ├── CommentNotification.php   # Notificaciones de comentarios
│       └── User.php                  # Usuario extendido
├── database/
│   ├── data/
│   │   └── decisions.json           # Datos de prueba
│   ├── migrations/                  # Migraciones de BD
│   └── seeders/
│       └── DecisionSeeder.php       # Seeder con 20 decisiones
├── resources/
│   └── js/
│       ├── Components/
│       │   ├── Avatar.jsx           # Componente de avatar de usuario
│       │   └── Decisions/           # Componentes React
│       │       ├── CommentList.jsx  # Lista de comentarios
│       │       ├── CommentForm.jsx  # Formulario para crear comentarios
│       │       └── ...
│       │   ├── NotificationDropdown.jsx # Dropdown de notificaciones
│       │   └── ...
│       └── Pages/
│           └── Decisions/           # Páginas de decisiones
│               ├── Index.jsx        # Lista de decisiones
│               ├── Create.jsx       # Crear decisión
│               ├── Show.jsx         # Ver y votar
│               ├── MyDecisions.jsx  # Mis decisiones
│               └── VotedDecisions.jsx # Decisiones votadas
└── routes/
    └── web.php                      # Rutas de la aplicación
```

## 🔄 Datos de Prueba

El seeder incluye 20 decisiones realistas en español sobre:
- Cambios de trabajo y carrera profesional
- Decisiones técnicas (frameworks, bases de datos)
- Vida personal (mudanzas, relaciones)
- Inversiones y finanzas
- Emprendimiento y startups

Para regenerar los datos:
```bash
# Limpiar y repoblar todo
php artisan migrate:fresh --seed --seeder=DecisionSeeder

# Solo añadir más decisiones (mantiene usuarios)
php artisan db:seed --class=DecisionSeeder
```

## 🎯 Funcionalidades para Probar

1. **Registro/Login**: Crea una cuenta nueva o usa los usuarios de prueba
2. **Explorar Decisiones**: Ve todas las decisiones activas con filtros
3. **Votar**: Participa en decisiones abiertas con votos y comentarios
4. **Crear Decisión**: Pide ayuda a la comunidad con tu dilema
5. **Dashboard**: Ve tus estadísticas, karma y badge
6. **Mis Decisiones**: Gestiona y marca tus decisiones como resueltas
7. **Decisiones Votadas**: Revisa en qué has participado
8. **Comentarios**: Comenta en las decisiones para dar tu opinión independiente de tu voto

## 💬 Sistema de Comentarios

La plataforma ahora cuenta con un sistema de comentarios independiente de los votos, permitiendo a los usuarios expresar sus opiniones de manera más detallada.

### Capturas de Pantalla

#### Comentarios en una decisión
![Comentarios en una decisión](/capturas/3-comentarios.png)

#### Comentarios sin autenticación
![Comentarios sin autenticación](/capturas/comentarios-sin-autentication.png)

#### Notificaciones de comentarios
![Notificaciones de comentarios](/capturas/notification.png)

#### Tests de la funcionalidad
![Tests de la funcionalidad](/capturas/tests.png)

### Instrucciones para probar la funcionalidad

1. **Ver comentarios**: Navega a cualquier decisión para ver los comentarios existentes. Los comentarios son visibles para todos los usuarios, estén autenticados o no.

2. **Añadir comentarios**: 
   - Inicia sesión con cualquier cuenta
   - Navega a una decisión activa
   - Escribe tu comentario en el formulario en la parte superior de la sección de comentarios
   - Haz clic en "Enviar comentario"
   
3. **Visualización de tiempo relativo**:
   - Los comentarios muestran el tiempo transcurrido desde que fueron publicados (ej: "hace 5 minutos", "hace 2 horas")
   - Pasa el cursor sobre el timestamp para ver la fecha y hora exactas

4. **Recompensa de karma**:
   - Al añadir un comentario, tu karma aumentará automáticamente en 5 puntos
   - Esto puede cambiar tu badge si alcanzas los umbrales necesarios

5. **Scroll Infinito**:
   - Si hay más de 20 comentarios, se cargarán automáticamente más comentarios al llegar al final de la página
   - Un indicador de carga muestra cuando se están cargando más comentarios

6. **Editar y Eliminar Comentarios**:
   - Los usuarios pueden editar y eliminar sus propios comentarios
   - Botones de edición y eliminación aparecen solo para el autor del comentario
   - Diálogo de confirmación antes de eliminar un comentario
   
7. **Sistema de Notificaciones**:
   - Cuando un usuario comenta en una decisión, el dueño recibe una notificación
   - El icono de campana en la barra de navegación muestra el número de notificaciones no leídas
   - Al hacer clic en el icono, se muestra un dropdown con las notificaciones recientes
   - Las notificaciones no leídas tienen un fondo azul claro
   - Al hacer clic en una notificación, se marca como leída y redirige a la decisión correspondiente
   - Botón "Marcar todas como leídas" para limpiar todas las notificaciones pendientes

8. **Mejoras de UX**:
   - Textarea auto-expandible que crece con el contenido
   - Diálogo de confirmación antes de publicar un nuevo comentario
   - Notificaciones de éxito y error para acciones del usuario

### Decisiones Técnicas

1. **Modelo de datos independiente**:
   - Se creó un modelo `Comment` separado del modelo `Vote` para permitir comentarios sin necesidad de votar
   - Esto permite una mayor flexibilidad y separación de responsabilidades

2. **Timestamps relativos**:
   - Implementación de timestamps relativos para mejorar la experiencia del usuario
   - Se mantiene la fecha exacta accesible mediante tooltips para preservar el contexto temporal

3. **Avatares personalizados**:
   - Componente `Avatar` reutilizable que genera colores consistentes basados en el nombre de usuario
   - Muestra las iniciales del usuario cuando no hay imagen de avatar disponible

4. **Seguridad en las peticiones**:
   - Uso de Axios para manejar automáticamente los tokens CSRF
   - Validación tanto en el cliente como en el servidor para los comentarios
   - Verificación de autorización para editar/eliminar solo los comentarios propios

5. **API RESTful**:
   - Endpoints separados para listar (`GET`), crear (`POST`), editar (`PUT`) y eliminar (`DELETE`) comentarios
   - Paginación implementada en el backend para optimizar el rendimiento
   - Implementación de scroll infinito en el frontend

6. **UX Avanzada**:
   - TextArea auto-expandible que se ajusta al contenido a medida que escribes
   - Diálogos de confirmación para prevenir acciones accidentales
   - Intersection Observer para implementar scroll infinito de manera eficiente

7. **Sistema de Notificaciones**:
   - Implementación de un sistema personalizado de notificaciones con modelo dedicado
   - Notificación en tiempo real cuando se comenta en una decisión propia
   - Interfaz visual que muestra el estado leído/no leído de las notificaciones
   - Actualización automática del contador de notificaciones no leídas
   - Control de permisos para asegurar que solo el dueño vea sus notificaciones

8. **Pruebas automatizadas**:
   - Tests que validan tanto la funcionalidad como la seguridad del sistema de comentarios y notificaciones
   - Pruebas de integración para validar el flujo completo de la funcionalidad

## 🏗️ Stack Tecnológico

- **Backend**: Laravel 12.x
- **Frontend**: React con Inertia.js
- **Base de Datos**: SQLite
- **CSS**: Tailwind CSS
- **Iconos**: Heroicons
- **Build**: Vite

## 📝 API Endpoints

### Públicos
- `GET /api/decisions` - Lista de decisiones
- `GET /api/decisions/{id}` - Ver decisión
- `GET /api/decisions/{decision}/comments` - Listar comentarios de una decisión

### Autenticados
- `POST /api/decisions` - Crear decisión
- `PUT /api/decisions/{id}` - Actualizar decisión
- `DELETE /api/decisions/{id}` - Eliminar decisión
- `GET /api/my-decisions` - Mis decisiones
- `GET /api/voted-decisions` - Decisiones votadas
- `POST /api/votes` - Votar
- `DELETE /api/votes/{id}` - Eliminar voto
- `POST /api/decisions/{decision}/comments` - Crear comentario
- `PUT /api/decisions/{decision}/comments/{comment}` - Editar comentario
- `DELETE /api/decisions/{decision}/comments/{comment}` - Eliminar comentario
- `GET /api/notifications` - Listar notificaciones
- `GET /api/notifications/unread-count` - Obtener número de notificaciones no leídas
- `POST /api/notifications/{id}/mark-as-read` - Marcar notificación como leída
- `POST /api/notifications/mark-all-read` - Marcar todas las notificaciones como leídas

## 🐛 Solución de Problemas

### La base de datos está vacía
```bash
php artisan migrate:fresh --seed --seeder=DecisionSeeder
```

### Los estilos no cargan
```bash
npm run build
```

### Error de permisos en storage
```bash
chmod -R 775 storage bootstrap/cache
```

## 📄 Licencia

MIT License