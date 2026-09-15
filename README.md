# 🚀 TechGate - Core Domain & Interface (Sprint 1)

## 📌 Descripción del Proyecto
Monolito web desarrollado en **Laravel 13** para la gestión de productos, categorías y evaluación de compatibilidad de componentes en **TechGate** (tienda de tecnología).

---

## 🛠️ Cambios e Implementaciones en este Push

### 1. 🗄️ Modelos y Base de Datos (Eloquent & Migraciones)
* **Entidades Principales:** Creados los modelos y migraciones correspondientes para:
  * `Category`: Gestión de categorías de productos.
  * `Product`: Relacionado directamente con su categoría (`belongsTo`).
  * `ProductComparator`: Lógica para comparación de características.
  * `ComponentCompatibility`: Reglas de validación y compatibilidad entre hardware/componentes.

### 2. 🎮 Controladores Web (MVC Monolítico)
* **`ProductController`**: Métodos para listado (`index`), renderizado del formulario (`create`) y almacenamiento (`store`).
* **`CategoryController`**: Métodos para gestión de categorías (`index`, `create`, `store`).
* **`ProductComparatorController` & `ComponentCompatibilityController`**: Controladores de soporte para el dominio core.

### 3. 🎨 Interfaz de Usuario (Vistas con Blade & Bootstrap 5)
* **Plantilla Base (`resources/views/layouts/app.blade.php`)**: Layout principal con barra de navegación integrada mediante CDN de Bootstrap 5.
* **Módulo de Productos (`resources/views/products/`)**:
  * `index.blade.php`: Tabla de catálogo de productos.
  * `create.blade.php`: Formulario de registro con manejo de errores y selección dinámica de categorías.
* **Módulo de Categorías (`resources/views/categories/`)**:
  * `index.blade.php`: Listado de categorías registradas.
  * `create.blade.php`: Formulario de ingreso de nuevas categorías.

### 4. 🛣️ Rutas Web (`routes/web.php`)
* Configuración de rutas resource para `products` y `categories`.
* Redirección básica de la raíz (`/`) al catálogo principal.

### 5. 🧪 Testing & Automatización
* **Pruebas Unitarias (`tests/Unit/ProductTest.php`)**:
  * Validación de la reducción de stock y manejo de stock insuficiente.
* **Pruebas de Integración (`tests/Feature/ProductWebTest.php`)**:
  * Verificación del flujo de creación de productos a través del formulario web HTTP.
* **Ajustes en `tests/Feature/ExampleTest.php`**:
  * Adaptación para validar la redirección correcta de la ruta principal.

---

## ⚡ Instrucciones de Ejecución Local

1. **Ejecutar las migraciones:**
   ```bash
   php artisan migrate
2. **Ejecutar la suite de pruebas:**

   ```bash
   php artisan test
3. **levantar el servidor local:**
   ```bash
   php artisan serve
Las URLS son:   
Catálogo: http://127.0.0.1:8000/products

Categorías: http://127.0.0.1:8000/categories
