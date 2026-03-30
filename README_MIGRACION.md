# Plugin Barcode para GLPI 11
## Migrado a GLPI 11.x por Dawing S.A.S.

---

## ¿Qué hace este plugin?

| Función | Descripción |
|---|---|
| **Barcodes lineales** | Genera Code39, Code128, EAN13, INT25, Postnet, UPC-A a partir del número de inventario (`otherserial`) del activo |
| **QR codes** | Genera QR codes con contenido configurable: número de serie, inventario, ID, UUID, nombre, URL y fecha |
| **Exportación PDF** | Grilla de códigos exportada a PDF con layout automático (múltiples por página) |
| **Acción masiva** | Disponible desde cualquier listado de activos GLPI: "Print barcodes" y "Print QRcodes" |
| **Configuración por tipo** | Márgenes, tamaño de página, orientación, dimensiones máximas de código, tamaño de texto |
| **Logo corporativo** | Permite subir un logo PNG que aparece sobre cada código en el PDF |

---

## Cambios realizados para compatibilidad con GLPI 11

### setup.php
- Versión mínima/máxima actualizada a `11.0.0` / `11.0.99`
- Eliminado `$PLUGIN_HOOKS['csrf_compliant']` (deprecado y eliminado en GLPI 11)
- Eliminado `Plugin::getWebDir()` (deprecado en GLPI 11) → reemplazado por rutas absolutas `/plugins/barcode/...`

### hook.php
- `$DB->query(...) or die(...)` → reemplazado por `$DB->doQueryOrDie(...)` (API GLPI 11)
- Bug heredado: `PRIMARY KEY (ID)` (mayúscula) → corregido a `PRIMARY KEY (id)` (minúscula, compatible con MariaDB estricto)
- `$DB->request('tabla', $cond)` → actualizado a sintaxis con array estructurado

### inc/barcode.class.php / inc/qrcode.class.php
- `Plugin::getWebDir()` → rutas `/plugins/barcode/front/...`
- `count()` sobre valores que pueden ser null → protegido con `is_array()`
- `key($ma->items)` → comparación mejorada con `is_a()` para detectar tipos ITIL

### inc/config.class.php
- `array_keys($obj->find(...))[0]` → `array_key_first($obj->find(...))` (PHP 7.3+, disponible en PHP 8.x)

### front/*.php
- Validación de seguridad mejorada en `send.php` (solo permite archivos PDF propios del plugin)
- `document.send.php`: validación de MIME type y restricción solo a `barcode/logo.png`
- `checkItemByInv.php`: validación de que el itemtype es subclase de `CommonDBTM`
- Confirmaciones con `Session::checkRight()` en todos los controladores

### composer.json
- `platform.php` actualizado de `7.4.0` a `8.1.0`
- Añadido autoload `classmap` para el directorio `inc/`

---

## Requisitos del servidor

| Componente | Versión mínima |
|---|---|
| GLPI | 11.0.0 |
| PHP | 8.1 |
| Extensiones PHP | `gd`, `mbstring`, `xml`, `curl` |
| Composer | 2.x |

---

## Instalación

### Paso 1 – Copiar el plugin

```bash
# Desde el directorio raíz de GLPI
cp -r /ruta/a/barcode /var/www/glpi/plugins/barcode
# O usando marketplace
cp -r /ruta/a/barcode /var/www/glpi/marketplace/barcode
```

### Paso 2 – Instalar dependencias con Composer

```bash
cd /var/www/glpi/plugins/barcode
composer install --no-dev --optimize-autoloader
```

> **Nota:** Si el servidor no tiene acceso a internet, ejecuta Composer en tu equipo local  
> y sube la carpeta `vendor/` completa al servidor.

### Paso 3 – Verificar permisos

```bash
# El usuario del servidor web (apache/nginx/www-data) debe poder escribir en:
chown -R www-data:www-data /var/www/glpi/plugins/barcode
chmod -R 755 /var/www/glpi/plugins/barcode

# Y también en el directorio de documentos del plugin:
mkdir -p /var/www/glpi/files/_plugins/barcode
chown -R www-data:www-data /var/www/glpi/files/_plugins/barcode
chmod -R 750 /var/www/glpi/files/_plugins/barcode
```

### Paso 4 – Instalar en GLPI

1. Entrar a GLPI como super-administrador
2. Ir a **Configuración → Plugins** (o **Setup → Plugins**)
3. Localizar **Barcode** en la lista
4. Hacer clic en **Instalar** (ícono de engranaje)
5. Hacer clic en **Activar**

### Paso 5 – Configurar derechos

1. Ir a **Administración → Perfiles**
2. Editar los perfiles que necesiten usar el plugin
3. En la pestaña **Barcode** configurar:
   - `Manage configuration` (UPDATE) → para administradores
   - `Generation of barcode` (CREATE) → para técnicos

---

## Uso

### Generación manual

Ir a **Herramientas → Barcode** y escribir el código a generar.

### Generación desde activos (acción masiva)

1. Ir a cualquier listado de activos (Equipos, Monitores, Impresoras, etc.)
2. Seleccionar uno o más activos con los checkboxes
3. En la barra de acciones masivas: **Barcode – Print barcodes** o **Barcode – Print QRcodes**
4. Configurar opciones (tipo, tamaño, orientación, campos a incluir en QR)
5. Hacer clic en **Create** → aparecerá un enlace para descargar el PDF

### Logo corporativo

1. Ir a **Configuración → Plugins → Barcode**
2. En la sección **Company logo**, subir un archivo PNG
3. El logo aparecerá sobre cada código en los PDFs generados

---

## Estructura de archivos

```
barcode/
├── setup.php                  ← Bootstrap e inicialización de hooks
├── hook.php                   ← Funciones de instalación/desinstalación/acciones masivas
├── barcode.xml                ← Metadatos del plugin
├── composer.json              ← Dependencias PHP
├── inc/
│   ├── barcode.class.php      ← Clase principal: generación de códigos lineales + PDF
│   ├── qrcode.class.php       ← Clase QR code
│   ├── config.class.php       ← Clase de configuración general
│   ├── config_type.class.php  ← Clase de configuración por tipo
│   └── profile.class.php      ← Clase de gestión de perfiles/derechos
├── front/
│   ├── barcode.php            ← Listado/formulario de generación manual
│   ├── barcode.form.php       ← Procesador POST de generación manual
│   ├── config.php             ← Página de configuración
│   ├── config.form.php        ← Procesador POST de configuración general
│   ├── config_type.form.php   ← Procesador POST de configuración por tipo
│   ├── send.php               ← Descarga del PDF generado
│   ├── document.send.php      ← Sirve el logo de la empresa
│   └── checkItemByInv.php     ← Búsqueda de activo por número de inventario
├── locales/                   ← Archivos de traducción (.po/.mo)
├── patch/
│   └── fix-pear-image-barcode.patch  ← Patch de compatibilidad PHP 8.x para PEAR Image_Barcode
└── vendor/                    ← Dependencias Composer (generadas por composer install)
```

---

## Notas de compatibilidad

- Esta versión (3.0.0) es **exclusivamente compatible con GLPI 11.0.x**
- Para GLPI 10.0.x usar la versión 2.7.1 del plugin original
- Las tablas de base de datos son compatibles hacia atrás (mismo esquema)
- Los archivos PDF e imágenes generados previamente en `files/_plugins/barcode/` siguen siendo válidos

---

## Soporte

Para soporte técnico relacionado con esta migración:
- **Dawing S.A.S.** – https://dawing.com.co
- soporte@dawing.com.co

Para el plugin original:
- https://github.com/pluginsGLPI/barcode
