🚀 TechGate - Tienda de Productos Tecnológicos

Bienvenido al repositorio oficial de TechGate, una plataforma de comercio electrónico orientada a la venta y comparación de componentes y productos tecnológicos. Este proyecto es desarrollado para la materia Arquitectura de Software utilizando el framework Laravel bajo una arquitectura monolítica.

📌 Alcance del Sprint 1

En este primer sprint nos enfocamos en el diseño e implementación del núcleo del dominio de productos, su categorización y la lógica de compatibilidad entre componentes:

Producto (Producto): Gestión de inventario, stock, precios y ofertas.

Categorías (Categorias): Organización jerárquica de productos.

Comparador de Productos (Comparador de productos): Herramienta para comparar especificaciones técnicas y verificar armados de equipos.

Compatibilidad entre Componentes (Compatibilidad entre componentes): Evaluación de reglas de compatibilidad técnica entre distintos productos (p. ej. Socket de CPU y Placa Madre).

🛠️ Requisitos Previos

Asegúrate de tener instaladas las siguientes herramientas en tu entorno local:

PHP: >= 8.2 (con extensiones pdo, mbstring, openssl, tokenizer, xml)

Composer: >= 2.x

Git: >= 2.x

Base de Datos: MySQL o PostgreSQL >= 8.0 / MariaDB

(Opcional) Node.js & NPM: >= 18.x (para compilación de assets de frontend)

🚀 Guía de Inicio Rápido (Entorno Local)

Sigue estos pasos para clonar e iniciar el proyecto en tu máquina local:

1. Clonar el Repositorio

git clone https://github.com/TU_USUARIO/techgate.git
cd techgate


2. Instalar Dependencias de PHP

composer install


3. Configurar el Archivo de Entorno

Copia el archivo de configuración de ejemplo para crear tu archivo .env:

cp .env.example .env


Abre el archivo .env y configura los datos de conexión a tu base de datos:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=techgate_db
DB_USERNAME=root
DB_PASSWORD=tu_contraseña


4. Generar la Clave de Aplicación

php artisan key:generate


5. Ejecutar Migraciones y Seeders

Asegúrate de haber creado la base de datos techgate_db en tu Gestor de BD y ejecuta:

php artisan migrate --seed


6. Iniciar el Servidor de Desarrollo

php artisan serve


La aplicación estará disponible en http://127.0.0.1:8000.

🌿 Convenciones y Flujo de Trabajo en Git

Para mantener un historial limpio y evitar conflictos de código en el equipo, trabajamos bajo un flujo basado en ramas (Feature Branching):

Reglas Principales

La rama main está protegida: No se deben hacer commits directamente en main.

Creación de ramas por funcionalidad: Cada nueva tarea o clase debe desarrollarse en su propia rama saliente de main.

Nomenclatura de Ramas

Funcionalidades/Clases: feature/nombre-de-la-funcionalidad (ej. feature/producto-model, feature/compatibilidad-service)

Correcciones de errores: bugfix/descripcion-del-bug

Refactorización: refactor/nombre-modulo

Flujo paso a paso para desarrollar:

Actualizar main e iniciar nueva rama:

git checkout main
git pull origin main
git checkout -b feature/clase-producto


Hacer commits periódicos y descriptivos:

git add .
git commit -m "feat: implementar modelo y migración de Producto"


Subir la rama a GitHub:

git push origin feature/clase-producto


Crear Pull Request (PR):

Ve a GitHub y abre un Pull Request hacia la rama main.

Asigna al menos a un compañero del equipo como revisor (Reviewer).

Una vez aprobado y pasadas las revisiones, realiza el Merge.

👥 Equipo de Desarrollo

Proyecto desarrollado para la asignatura de Arquitectura de Software.
