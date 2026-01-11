# Módulo del Sistema de Exportación

## Descripción General
El Sistema de Exportación proporciona una forma reutilizable de generar reportes en formatos PDF, Excel y CSV. La Fase 1 se centra en la generación síncrona para conjuntos de datos pequeños a medianos (hasta 100 registros), integrado directamente en el módulo de Usuarios.

## Características
- **Soporte Multi-formato**: PDF, Excel (.xlsx) y CSV.
- **Generación Síncrona**: Descarga instantánea para reportes de hasta 100 registros.
- **Filtrado Inteligente**: Respeta los filtros actuales de búsqueda, rol y estado.
- **Seguridad**: Protegido por el permiso `users.export`.
- **Diseño Profesional**: Plantillas PDF con marca y hojas de Excel formateadas.

## Arquitectura

### Backend
- **Controlador**: `UserExportController` maneja la lógica de exportación.
- **Generador**: La clase `UsersExport` utiliza Generadores de PHP para un procesamiento de datos eficiente en memoria.
- **Librerías**:
  - `barryvdh/laravel-dompdf`: Para la generación de PDF.
  - `spatie/simple-excel`: Para el streaming de alto rendimiento de Excel/CSV.

### Frontend
- **Componente**: `ExportButton.vue` proporciona una interfaz de usuario reutilizable con menú desplegable.
- **Integración**: Integrado en el encabezado de `Index.vue`.

## Uso

### 1. Exportar Usuarios
1. Navega a **Gestión de Usuarios** (`Users Management`).
2. Aplica los filtros deseados (Búsqueda, Rol, Estado).
3. Haz clic en el botón **Export**.
4. Selecciona el formato:
   - **PDF**: Se abre en una nueva pestaña.
   - **Excel/CSV**: Se descarga inmediatamente.

### 2. Agregar Exportación a Otros Módulos
Para agregar funcionalidad de exportación a otro módulo (ej. Productos):

1. **Crear Clase de Exportación**:
   ```php
   class ProductsExport {
       public function generator(): Generator {
           // Yield data rows
       }
   }
   ```

2. **Crear Métodos en el Controlador**:
   Usa `SimpleExcelWriter` para Excel/CSV y `DomPDF` para PDF.

3. **Agregar Botón en Frontend**:
   ```vue
   <ExportButton :filters="filters" baseUrl="/admin/products/export" />
   ```

## Detalles Técnicos

### Generación de PDF
- Utiliza plantillas Blade (`resources/views/exports/users-pdf.blade.php`).
- Estilizado con CSS en línea para mayor compatibilidad.
- Incluye metadatos (hora de generación, usuario, filtros).

### Generación de Excel/CSV
- Utiliza `spatie/simple-excel` para streaming.
- No se crean archivos temporales (transmite directamente al navegador).
- Bajo consumo de memoria gracias a los Generadores.

## Solución de Problemas

### Error "Class not found"
Asegúrate de que `spatie/simple-excel` esté instalado y la extensión `zip` esté habilitada en `php.ini`.

### Problemas de Estilo en PDF
Los generadores de PDF tienen soporte CSS limitado. Usa tablas simples y estilos en línea. Evita Flexbox/Grid.
