# 🔐 Sistema de Roles y Permisos - Documentación

## 📋 Descripción General

Se ha implementado un **sistema completo de roles y permisos** utilizando **Spatie Laravel Permission**, totalmente administrable desde la interfaz de usuario con soporte para:

- ✅ Diseño responsive (mobile-first)
- ✅ Dark mode
- ✅ TypeScript
- ✅ Inertia.js
- ✅ Vue 3 Composition API

---

## 🏗️ Arquitectura

### Backend (Laravel)

#### Paquete Principal
- **spatie/laravel-permission v6.23.0**

#### Controllers Creados
```
app/Http/Controllers/Admin/
├── RoleController.php          # CRUD completo de roles
├── PermissionController.php    # CRUD de permisos
└── UserController.php          # Asignación de roles a usuarios
```

#### Policies
```
app/Policies/
├── RolePolicy.php              # Autorización basada en permisos
├── PermissionPolicy.php        # Autorización basada en permisos
└── UserPolicy.php              # Autorización basada en permisos
```

#### Rutas
```php
// routes/web.php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::post('permissions', [PermissionController::class, 'store']);
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy']);
    Route::get('users', [UserController::class, 'index']);
    Route::post('users/{user}/roles', [UserController::class, 'assignRoles']);
});
```

### Frontend (Vue + TypeScript)

#### Composables
```typescript
// resources/js/composables/usePermissions.ts
export function usePermissions() {
    return {
        hasRole,
        hasPermission,
        hasAnyRole,
        hasAllRoles,
        hasAnyPermission,
        hasAllPermissions,
        isSuperAdmin,
        isAdmin,
    };
}
```

#### Páginas Creadas
```
resources/js/pages/admin/
├── roles/
│   ├── Index.vue               # Lista de roles (cards)
│   ├── Create.vue              # Crear rol con permisos
│   └── Edit.vue                # Editar rol y permisos
├── permissions/
│   └── Index.vue               # Lista y creación de permisos
└── users/
    └── Index.vue               # Lista usuarios y asignación de roles
```

#### Navegación
- Sección "Admin" en el sidebar (solo visible con permisos)
- Enlaces dinámicos basados en permisos del usuario

---

## 🎯 Roles y Permisos Base

### Roles Predefinidos

| Rol | Protegido | Descripción |
|-----|-----------|-------------|
| `super-admin` | ✅ Sí | Acceso total al sistema |
| `admin` | ❌ No | Gestión general (sin crear super-admins) |
| `moderator` | ❌ No | Moderación limitada |
| `user` | ✅ Sí | Usuario estándar (sin permisos especiales) |

### Permisos por Módulo

#### Users (Usuarios)
- `users.view` - Ver usuarios
- `users.create` - Crear usuarios
- `users.edit` - Editar usuarios
- `users.delete` - Eliminar usuarios

#### Roles
- `roles.view` - Ver roles
- `roles.create` - Crear roles
- `roles.edit` - Editar roles
- `roles.delete` - Eliminar roles

#### Permissions (Permisos)
- `permissions.view` - Ver permisos
- `permissions.create` - Crear permisos
- `permissions.edit` - Editar permisos
- `permissions.delete` - Eliminar permisos

#### Settings (Configuración)
- `settings.view` - Ver configuración
- `settings.edit` - Editar configuración

#### Logs
- `logs.view` - Ver logs del sistema
- `logs.delete` - Eliminar logs

---

## 🚀 Uso

### Usuario de Prueba

Se ha creado un usuario administrador para testing:

```
Email: admin@example.com
Password: password (por defecto de factory)
Rol: super-admin
Permisos: Todos (16 permisos)
```

### Acceder al Panel de Administración

1. Iniciar sesión con el usuario admin
2. En el sidebar, verás la sección "Admin" con:
   - **Roles** - Gestionar roles y permisos
   - **Permissions** - Crear/eliminar permisos
   - **Users** - Asignar roles a usuarios

### Crear un Nuevo Rol

1. Ir a **Admin > Roles**
2. Click en "Create Role"
3. Ingresar nombre del rol (ej: `editor`)
4. Seleccionar permisos por módulo
5. Guardar

### Asignar Roles a Usuarios

1. Ir a **Admin > Users**
2. Click en "Manage Roles" en la tarjeta del usuario
3. Seleccionar/deseleccionar roles
4. Guardar cambios

### Crear Nuevos Permisos

1. Ir a **Admin > Permissions**
2. Click en "Create Permission"
3. Ingresar nombre en formato `module.action` (ej: `posts.publish`)
4. Crear

---

## 💻 Uso en Código

### Backend (Laravel)

#### Verificar Permisos en Controllers
```php
public function index()
{
    $this->authorize('viewAny', Role::class);
    // ...
}
```

#### Verificar en Blade/Rutas
```php
// Middleware
Route::middleware(['permission:users.view'])->group(function () {
    // ...
});

// En código
if ($user->can('users.edit')) {
    // ...
}
```

### Frontend (Vue)

#### Usar el Composable
```vue
<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';

const { hasPermission, hasRole, isSuperAdmin } = usePermissions();
</script>

<template>
    <Button v-if="hasPermission('users.create')">
        Create User
    </Button>
    
    <div v-if="hasRole('admin')">
        Admin Panel
    </div>
    
    <div v-if="isSuperAdmin">
        Super Admin Only
    </div>
</template>
```

#### Verificar Múltiples Permisos
```typescript
// Cualquiera de estos permisos
if (hasAnyPermission(['users.view', 'users.edit'])) {
    // ...
}

// Todos estos permisos
if (hasAllPermissions(['users.view', 'users.edit'])) {
    // ...
}
```

---

## 🎨 Características de UI

### Diseño Responsive

Todas las páginas están optimizadas para:
- 📱 **Mobile** (< 640px) - Layout vertical, botones full-width
- 📱 **Tablet** (640px - 1024px) - Grid 2 columnas
- 💻 **Desktop** (> 1024px) - Grid 3-4 columnas

### Dark Mode

- Soporte completo para tema oscuro
- Colores adaptativos usando variables CSS
- Transiciones suaves entre temas

### Componentes Utilizados

- **shadcn-vue**: Card, Button, Badge, Input, Checkbox, Dialog
- **lucide-vue-next**: Iconos modernos
- **Tailwind CSS v4**: Estilos utility-first

---

## 🔒 Seguridad

### Protecciones Implementadas

1. **Roles Protegidos**: `super-admin` y `user` no se pueden eliminar
2. **Autorización en Controllers**: Todas las acciones verifican permisos
3. **Policies**: Lógica de autorización centralizada
4. **Middleware**: Rutas protegidas por autenticación
5. **UI Condicional**: Botones/enlaces solo visibles con permisos

### Jerarquía de Permisos

```
Super Admin (todos los permisos)
    ↓
Admin (gestión general)
    ↓
Moderator (moderación limitada)
    ↓
User (sin permisos especiales)
```

---

## 📊 Datos Compartidos con Inertia

El middleware `HandleInertiaRequests` comparte automáticamente:

```typescript
{
    auth: {
        user: {
            id: number,
            name: string,
            email: string,
            roles: string[],           // ['super-admin']
            permissions: string[],     // ['users.view', 'users.create', ...]
        }
    }
}
```

Esto permite verificar permisos en cualquier componente Vue sin hacer peticiones adicionales.

---

## 🧪 Testing

### Verificar Roles y Permisos

```bash
# Entrar a Tinker
php artisan tinker

# Ver todos los roles
Spatie\Permission\Models\Role::all(['id', 'name']);

# Ver permisos de un rol
$role = Spatie\Permission\Models\Role::findByName('admin');
$role->permissions->pluck('name');

# Ver roles de un usuario
$user = App\Models\User::find(1);
$user->getRoleNames();

# Ver permisos de un usuario
$user->getAllPermissions()->pluck('name');
```

### Asignar Roles Manualmente

```bash
php artisan tinker

$user = App\Models\User::find(1);
$user->assignRole('admin');
$user->givePermissionTo('users.view');
```

---

## 📝 Próximos Pasos Sugeridos

1. **Tests Automatizados**
   - Tests Pest para policies
   - Tests de integración para controllers
   - Tests de componentes Vue

2. **Mejoras de UI**
   - Búsqueda y filtros en listados
   - Paginación para grandes cantidades de datos
   - Exportar roles/permisos a CSV

3. **Funcionalidades Adicionales**
   - Historial de cambios de roles
   - Permisos temporales (con fecha de expiración)
   - Grupos de permisos
   - Roles heredados

4. **Optimizaciones**
   - Cache de permisos
   - Eager loading en queries
   - Índices en base de datos

---

## 🐛 Troubleshooting

### El usuario no ve la sección Admin

**Solución**: Verificar que el usuario tenga al menos un permiso de visualización:
```bash
php artisan tinker
$user = App\Models\User::find(1);
$user->givePermissionTo('roles.view');
```

### Error "Policy not found"

**Solución**: Limpiar cache de Laravel:
```bash
php artisan optimize:clear
```

### Permisos no se actualizan

**Solución**: Spatie cachea permisos. Limpiar cache:
```bash
php artisan cache:forget spatie.permission.cache
```

---

## 📚 Recursos

- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [Laravel Authorization](https://laravel.com/docs/authorization)
- [Inertia.js](https://inertiajs.com/)
- [shadcn-vue](https://www.shadcn-vue.com/)

---

**Implementado**: 2025-11-22  
**Versión**: 1.0.0  
**Stack**: Laravel 12 + Vue 3 + TypeScript + Inertia.js + Spatie Permission
