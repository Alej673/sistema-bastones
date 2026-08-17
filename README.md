# 🧵 Sistema Integrado ERP y Cotizador BTO — Taller de Bastones

**Built-to-Order (BTO) · Manufactura a medida · 6 módulos integrados**

---

## ¿Qué es esto?

Un sistema web completo que transformó un taller artesanal de bastones para 
cachiporreras en una operación digitalizada. **De "cuentas en una libreta" 
a cotizaciones en tiempo real, control de inventario, y un catálogo público 
que capta clientes sin exponer los márgenes del taller.**

**El problema:** La administradora calculaba mentalmente la cantidad de lana, 
elásticos y bases que necesitaba por pedido. Sin registro, sin trazabilidad, 
con paros de producción por falta de materiales.

**La solución:** Un ERP a medida con cotizador automático, kardex de inventarios, 
y un portal web que permite a los clientes diseñar su bastón y recibir una 
proforma sin que el taller pierda el control de sus costos.

---

## 🚀 Tecnologías

| **Capa** | **Tecnología** |
|----------|----------------|
| Backend | Laravel + Eloquent ORM |
| Base de datos | MySQL (InnoDB, transacciones atómicas) |
| Frontend | Blade + JavaScript ES6 modular (Vite) |
| UI | Bootstrap 5, Select2, SweetAlert2 |
| PDF | barryvdh/laravel-dompdf |
| Seguridad | Laravel Breeze + middleware de doble perímetro |

---

## 🧠 Arquitectura que no es "otro CRUD"

### MRP (Material Requirements Planning)
Los pedidos en estado "pendiente" **reservan matemáticamente** el material. 
El stock físico solo se descuenta al despachar. **Una cotización nunca corrompe 
el inventario real.**

### Doble perímetro de seguridad
- **Perímetro público:** Catálogo, reseñas, solicitudes web (sin costos)
- **Perímetro administrativo:** Kardex, cotizador interno, despacho
- **El motor de costos reales jamás se ejecuta en el navegador del cliente.**

### Deuda de inventario (tolerancia a fallos)
Si falta material al despachar, el sistema permite saldos negativos controlados 
y emite alertas en lugar de bloquear la operación. **Continuidad operativa 
por diseño.**

---

## 📦 Módulos principales

| **Módulo** | **Qué hace** |
|------------|--------------|
| **Kardex** | Trazabilidad de insumos en unidad mínima de consumo (gramos, no madejas). Borrados lógicos para auditoría. |
| **Cotizador automático** | Motor reactivo en JS que aplica costeo fraccional y reglas de mayoreo en milisegundos. |
| **Puente BTO** | Sincroniza solicitudes web con pedidos internos de forma transaccional (estados + precios). |
| **Panel de control** | KPIs en tiempo real + centro de alertas interactivo para resolver incidencias sin salir del dashboard. |
| **Catálogo público** | Vitrinas por categoría con filtros, sistema de reseñas AJAX, y modal de consulta rápida. |
| **Portal del cliente** | "Mis Pedidos": seguimiento y descarga de proformas sin intervención del taller. |

---

## 🎯 Logros técnicos que importan

- **Refactorización de un monolito de +800 líneas** a módulos ES6 independientes (SoC) integrados con Vite.
- **Motor de búsqueda inteligente** en el despacho: tolera variaciones tipográficas y mapea textos a categorías reales.
- **Blindaje contra race conditions:** el botón de cotización se bloquea al recibir respuesta del servidor hasta cerrar el flujo.
- **Transacciones atómicas** entre cotización web y ERP: si falla algo, todo se revierte. Sin pedidos huérfanos.
- **Gestión de deuda de inventario:** saldos negativos controlados que no bloquean la operación.

---

## 📸 Capturas

*(Pendiente: insertar imágenes del Kardex, Cotizador, Panel de Control y Catálogo Público)*

---

## 🔗 Enlaces

- [Repositorio en GitHub](URL_DEL_REPO)
- [Video demo (2 min)](URL_DEL_VIDEO)
- [Landing page del taller](URL_DE_LA_LANDING) *(si está desplegada)*

---

## 📌 Estado actual

**Versión:** 6.0  
**Estado:** Funcional (en producción para el taller)  
**Próximo hito:** Exportación de reportes en Excel y ampliación del panel de inicio.

---

## 👤 Autor

**Alejandro Larco**  
[LinkedIn](URL) · [GitHub](URL) · [Portafolio](URL)

> *Este proyecto fue desarrollado como parte del Plan de Trabajo de Integración Curricular (PTIC) y está documentado con +30 páginas de bitácora técnica.*
