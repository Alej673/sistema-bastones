# Sistema ERP & Cotizador BTO — Taller Arte Titi_Val

Plataforma web integral bajo modelo **Built-to-Order (BTO)** para la gestión operativa y manufactura a medida de bastones institucionales. El sistema centraliza desde el catálogo público de captación y el cotizador reactivo hasta el control estricto de inventario (Kardex) y el módulo de despacho.

---

## Contexto y Reto de Negocio

El taller operaba mediante estimaciones empíricas de insumos (lana, elásticos, bases pre-cortadas), sin trazabilidad de mermas ni control digital de inventario, lo que derivaba en paros imprevistos de producción y presupuestos inexactos.

**Solución desarrollada:**
Un sistema web desacoplado en dos capas:
1. **Capa Pública / BTO:** Catálogo dinámico para clientes que genera prospectos y solicitudes personalizadas sin exponer la lógica de costos ni los márgenes del taller.
2. **Capa ERP Interna:** Panel administrativo con motor de cálculo reactivo, control de inventario en unidades mínimas de consumo (gramos/unidades) y módulo de despacho con tolerancia a fallos.

---

## Stack Tecnológico

- **Backend:** PHP 8.x / Laravel (Arquitectura MVC, Eloquent ORM)
- **Base de Datos:** MySQL (InnoDB con transacciones ACID y Soft Deletes)
- **Frontend:** Blade, JavaScript ES6 modular (compilado con Vite), Bootstrap 5
- **Componentes UI & Asíncronos:** Select2 (búsquedas AJAX), SweetAlert2, Fetch API
- **Documentación & Archivos:** `barryvdh/laravel-dompdf` (PDFs renderizados en RAM), Compressor.js

---

## Decisiones Arquitectónicas Clave

### 1. Desacoplamiento MRP (Planificación de Materiales)
Para evitar la corrupción prematura del inventario, el cotizador y el guardado de pedidos operan como una **reserva matemática**. El descuento físico en bodega solo se ejecuta de forma transaccional (`DB::beginTransaction`) cuando el pedido pasa a estado **"Realizado/Despachado"**.

### 2. Seguridad de Doble Perímetro
Aislamiento total mediante middlewares de Laravel por rol (`admin` / `cliente`). Los clientes registrados acceden a su historial de proformas (*Mis Pedidos*) sin tener acceso a los endpoints internos de costos, inventario ni fórmulas de producción.

### 3. Despacho Resiliente y Deuda de Inventario
El módulo de entrega implementa un algoritmo de búsqueda inteligente (`LIKE` y mapeo por categoría) que tolera variaciones de nomenclatura en insumos. Si el stock físico es insuficiente al despachar, el sistema permite saldos negativos controlados y emite alertas visuales para no frenar la logística de entrega.

### 4. Modularización Frontend (SoC)
El formulario de cotización fue refactorizado de un archivo monolítico a módulos ES6 independientes (`modulo_lana.js`, `modulo_cortinas.js`, `modulo_decoracion.js`, `modulo_diseno.js`) orquestados por un script principal y optimizados con Hot Module Replacement en Vite.

---

## Módulos del Sistema

| Módulo | Responsabilidad Técnica |
| :--- | :--- |
| **Kardex** | Registro continuo de entradas/salidas en unidad mínima. Motor traductor visual (muestra madejas/rollos en UI pero almacena gramos/metros). |
| **Cotizador** | Motor de cálculo reactivo con costeo fraccional, reglas automáticas de mayoreo y blindaje contra *race conditions* (doble clic). |
| **Puente BTO** | Sincronización bidireccional entre solicitudes del catálogo web (`quote_requests`) y órdenes de producción (`pedidos`). |
| **Historial & Despacho** | Vista rápida asíncrona, trazabilidad de estados y generación de Recetas de Compra basadas en déficit real de stock. |
| **Catálogo & Reseñas** | Landing optimizada con carga perezosa de imágenes, sistema de calificación AJAX e integración directa a WhatsApp. |
| **Alertas & KPIs** | Detección de materiales huérfanos y resolución de inventario en 1-clic desde el panel principal. |

---

## Capturas del Sistema

*(Estructura lista para adjuntar imágenes)*

| Vista del Kardex / Inventario | Cotizador y Calculadora Reactiva |
| :---: | :---: |
| ![Kardex](docs/screenshots/kardex.png) | ![Cotizador](docs/screenshots/cotizador.png) |

| Panel de Control & Centro de Alertas | Catálogo Público BTO |
| :---: | :---: |
| ![Dashboard](docs/screenshots/dashboard.png) | ![Catalogo](docs/screenshots/catalogo.png) |

---

## Instalación y Despliegue Local

```bash
# 1. Clonar el repositorio
git clone [https://github.com/Alej673/sistema-bastones.git](https://github.com/Alej673/sistema-bastones.git)
cd sistema-bastones

# 2. Instalar dependencias de PHP y Node
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env y correr migraciones
php artisan migrate --seed

# 5. Compilar assets y levantar servidor
npm run dev
php artisan serve

```
Autor
Alejandro Larco

GitHub

LinkedIn

Proyecto de Integración Curricular (PTIC) — Titulación en Desarrollo de Software.
