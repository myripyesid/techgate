# 🚀 TechGate - Arquitectura MVC + Servicios + DI + Docker (Entregable #2)

## 📌 Descripción del Proyecto
Monolito web desarrollado en **Laravel 13** para la gestión de productos, categorías, comparación de componentes y evaluación de compatibilidad de hardware en **TechGate** (Tienda de Artículos Tecnológicos). Esta entrega amplía el dominio con un **comparador profesional**, un **armador de PC virtual**, **reglas de compatibilidad**, **servicios web (proveer y consumir)**, **inversión de dependencias**, **internacionalización (2 idiomas)** y **despliegue con Docker en AWS**.

---

## 🛠️ Cambios e Implementaciones en este Push

### 1. 🗄️ Modelos y Base de Datos (Eloquent & Migraciones)
* **Entidades del dominio:** `User`, `Category`, `Product`, `Cart` + `CartItem`, `Order` + `OrderItem`, `PaymentMethod`, `ProductComparator` y `ComponentCompatibility`.
* **Catálogo técnico:** `Product` guarda `especificaciones` (JSON), `marca`, `modelo`, `lanzamiento`, `es_vendible` y `confianza`. 95 productos en 14 categorías.
* **Enriquecimiento de datos:** las 14 tarjetas gráficas incluyen `rt_cores`, `tmus`, `rops`, `cache_mb`, `longitud_mm`, `proceso_nm` y `conector_poder` (fuentes oficiales / TechPowerUp).

### 2. 🎮 Controladores Web (MVC Monolítico)
* **`ProductController` / `CategoryController`:** catálogo, ficha técnica y CRUD (solo administrador).
* **`CartController` / `OrderController` / `PaymentMethodController`:** flujo de compra completo.
* **`ProductComparatorController`:** comparación directa 2 a 2 con gráficos e índice de rendimiento.
* **`PcBuilderController`:** armador de PC, evaluación de compatibilidad en vivo (AJAX) y "agregar todo al carrito".
* **`Api\ProductApiController`:** servicio web JSON que se **provee** al equipo siguiente.
* **`AlliedProductController`:** **consume** el servicio web del equipo anterior.
* **`ReportController` / `LanguageController`:** reportes (DI) y cambio de idioma.

### 3. 🎨 Interfaz de Usuario (Vistas con Blade & Bootstrap 5)
* **Plantilla Base (`layouts/app.blade.php`):** cabecera, menú principal, menú de usuario desplegable, selector de idioma **ES/EN**, clima de Medellín y pie de página.
* **Módulo de Catálogo:** vista "Todo" con destacados por categoría, ficha técnica e iconos SVG por categoría.
* **Comparador (`comparator/index`):** tarjetas "versus", veredicto, **gráficos Chart.js** (barras, radar) y tabla de especificaciones.
* **Armador (`builder/index`):** selección por slots con compatibilidad y consumo en tiempo real.
* **Productos aliados (`allied/index`):** catálogo consumido del servicio de otro equipo.

### 4. ⚙️ Servicios e Inversión de Dependencias
* **`ProductComparisonService`:** comparación ponderada de dos productos de la misma categoría.
* **`CompatibilityService`:** reglas técnicas (socket, RAM, PCIe, watts, longitud de GPU, etc.) y consumo estimado.
* **`PerformanceScoreService`:** índice de rendimiento estimado 0–100.
* **`PcBuilderService`:** orquesta el armado de PC.
* **`WeatherService`:** consume un **servicio externo** (clima de Medellín, API Open-Meteo).
* **Inversión de Dependencias:** interfaz `ReportGenerator` con dos implementaciones concretas (`CsvReportGenerator` y `PdfReportGenerator`), resueltas por el contenedor.

### 5. 🌐 Servicios Web (Proveer / Consumir)
* **Provee** `GET /api/productos` (JSON con enlace directo a cada ficha) → consumido por el equipo siguiente (Grupo 5 - NFT).
* **Consume** el servicio del equipo anterior (Grupo 3 - Joyas) en `/productos-aliados`, con mapeo flexible de campos (es/en).
* **Consume** el servicio externo de clima (Open-Meteo) mostrado en la cabecera.

### 6. 🌍 Internacionalización (2 idiomas)
* Español e inglés con `lang/es/messages.php` y `lang/en/messages.php`.
* Middleware `SetLocale` + selector **ES | EN**. Sin textos "quemados" en las vistas principales (todo con `__()`).

### 7. 🛣️ Rutas Web y API (`routes/web.php`, `routes/api.php`)
* Catálogo, carrito, pedidos, formas de pago, comparador, armador, aliados, reportes e idioma.
* API: `GET /api/productos`.

### 8. 🐳 Docker (despliegue en AWS)
* `Dockerfile` (PHP 8.4 + Apache), `docker-compose.yml`, `docker/entrypoint.sh` y `Dockerrun.aws.json`.
* Guía de despliegue en `docker/README-AWS.md` (App Runner, Elastic Beanstalk o ECS).

### 9. 🧪 Testing & Automatización
* **Pruebas unitarias y de integración (`tests/`):** compatibilidad, comparación, armador, servicio JSON, consumo aliado, idioma e inversión de dependencias.
* **86 pruebas en verde** (239 aserciones): `php artisan test`.

### 10. 📊 Diagramas
* Diagrama de clases (Mermaid) en `docs/diagrama-de-clases.md`.

---

## ⚡ Instrucciones de Ejecución Local

# TechGate

## Installation and Setup

Follow these steps to run TechGate locally after cloning the repository.

### 1. Clone the repository

```bash
git clone https://github.com/kehernand/privado.git
cd privado
```

### 2. Install PHP dependencies

Install all Laravel dependencies using Composer:

```bash
composer install
```

### 3. Configure the environment

Create the `.env` file from the example configuration:

```bash
copy .env.example .env
```

> **Note:** On Linux/macOS, use:
>
> ```bash
> cp .env.example .env
> ```

### 4. Generate the application key

Generate Laravel's application encryption key:

```bash
php artisan key:generate
```

### 5. Clear cached configuration

Clear Laravel's configuration cache:

```bash
php artisan config:clear
```

### 6. Create the SQLite database

TechGate uses SQLite for its database.

Create the database file:

```bash
New-Item database/database.sqlite -ItemType File
```

> **Linux/macOS:**
>
> ```bash
> touch database/database.sqlite
> ```

### 7. Run database migrations

Create the database tables:

```bash
php artisan migrate
```

### 8. Populate the database

Run the database seeders to create the required initial data:

```bash
php artisan db:seed
```

### 9. Clear the application cache

Clear the application cache after configuring the database:

```bash
php artisan cache:clear
```

### 10. Start the development server

Run the Laravel development server:

```bash
php artisan serve
```

The application should now be available at:

```text
http://127.0.0.1:8000
```

Cuentas creadas por el seeder:

| Rol           | Email                  | Contraseña   |
|---------------|------------------------|--------------|
| Administrador | admin@techgate.com     | admin1234    |
| Usuario       | usuario@techgate.com   | usuario1234  |

---

## Complete Setup

For convenience, the complete setup sequence is:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan config:clear
New-Item database/database.sqlite -ItemType File
php artisan migrate
php artisan db:seed
php artisan cache:clear
php artisan serve
```

If you are using **Linux/macOS**, replace the Windows-specific commands with:

```bash
cp .env.example .env
touch database/database.sqlite
```

---

## 🐳 Ejecución con Docker

```bash
docker compose up --build
```

La aplicación quedará disponible en `http://localhost:8080`. Para el despliegue en **AWS** (App Runner, Elastic Beanstalk o ECS), consulta `docker/README-AWS.md`.

---

## Requirements

Before installing TechGate, make sure you have the following installed:

* PHP 8.4 o superior (requerido por Laravel 13)
* Composer
* SQLite
* Git

## Troubleshooting

### Database file does not exist

If Laravel reports:

```text
Database file at path [...] database.sqlite does not exist.
```

Make sure the SQLite database file has been created:

```bash
New-Item database/database.sqlite -ItemType File
```

Then run:

```bash
php artisan migrate
```

### Configuration problems

If changes to `.env` do not seem to take effect, clear the configuration cache:

```bash
php artisan config:clear
```

### Application cache problems

You can clear the application cache with:

```bash
php artisan cache:clear
```
