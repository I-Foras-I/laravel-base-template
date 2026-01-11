# 🔐 Guía Completa de Autorización con Policies

## 📚 Índice

1. [Introducción](#introducción)
2. [Conceptos Clave](#conceptos-clave)
3. [Flujo de Autorización](#flujo-de-autorización)
4. [Implementación Paso a Paso](#implementación-paso-a-paso)
5. [Ejemplos Prácticos](#ejemplos-prácticos)
6. [Comparación: Policies vs Middleware](#comparación-policies-vs-middleware)
7. [Debugging y Testing](#debugging-y-testing)
8. [Mejores Prácticas](#mejores-prácticas)

---

## 📖 Introducción

### ¿Qué son las Policies?

Las **Policies** son clases que organizan la lógica de autorización alrededor de un modelo o recurso específico. En lugar de verificar roles en middleware, verificamos **permisos específicos** en cada acción.

### ¿Por qué usar Policies?

**Ventajas sobre Middleware de Roles:**
- ✅ **Granularidad**: Control por acción (view, create, edit, delete)
- ✅ **Flexibilidad**: Cambiar permisos sin tocar código
- ✅ **Escalabilidad**: Fácil agregar nuevos roles
- ✅ **Mantenibilidad**: Lógica centralizada
- ✅ **Testing**: Fácil de probar unitariamente

---

## 🎯 Conceptos Clave

### 1. Roles
Grupos de usuarios con responsabilidades similares.

```
super-admin → Acceso total
admin → Gestión general
moderator → Moderación limitada
user → Usuario estándar
```

### 2. Permisos
Acciones específicas que un usuario puede realizar.

```
users.view → Ver usuarios
users.create → Crear usuarios
users.edit → Editar usuarios
users.delete → Eliminar usuarios
```

### 3. Policies
Clases que definen quién puede hacer qué.

```php
// app/Policies/RolePolicy.php
public function viewAny(User $user): bool
{
    return $user->can('roles.view');
}
```

### 4. Relación

```
Usuario → tiene → Roles → tienen → Permisos
                              ↓
                         Policies verifican
```

---

## 🔄 Flujo de Autorización

### Diagrama Completo

```
┌─────────────────────────────────────────────────────────┐
│ 1. Usuario hace Request                                 │
│    GET /admin/roles                                     │
└────────────────┬────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────┐
│ 2. Middleware (Rutas)                                   │
│    ✓ auth → ¿Está autenticado?                         │
│    ✓ verified → ¿Email verificado?                     │
└────────────────┬────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────┐
│ 3. Controller                                           │
│    RoleController@index()                               │
│    $this->authorize('viewAny', Role::class)             │
└────────────────┬────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────┐
│ 4. Laravel busca Policy                                 │
│    AppServiceProvider → Gate::policy(Role, RolePolicy)  │
└────────────────┬────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────┐
│ 5. Policy verifica permiso                              │
│    RolePolicy::viewAny($user)                           │
│    return $user->can('roles.view');                     │
└────────────────┬────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────┐
│ 6. Spatie Permission consulta BD                        │
│    - Obtiene roles del usuario                          │
│    - Obtiene permisos de esos roles                     │
│    - Verifica si 'roles.view' está incluido             │
└────────────────┬────────────────────────────────────────┘
                 ↓
         ┌───────┴────────┐
         ↓                ↓
    ✅ TRUE          ❌ FALSE
    Permitido        403 Unauthorized
```

---

## 🛠️ Implementación Paso a Paso

### Paso 1: Crear la Policy

```bash
php artisan make:policy RolePolicy --model="Spatie\Permission\Models\Role"
```

Esto crea: `app/Policies/RolePolicy.php`

### Paso 2: Definir Métodos de Autorización

```php
<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determina si el usuario puede ver la lista de roles
     */
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    /**
     * Determina si el usuario puede ver un rol específico
     */
    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view');
    }

    /**
     * Determina si el usuario puede crear roles
     */
    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    /**
     * Determina si el usuario puede editar un rol
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.edit');
    }

    /**
     * Determina si el usuario puede eliminar un rol
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->can('roles.delete');
    }
}
```

### Paso 3: Registrar la Policy

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use App\Policies\RolePolicy;

public function boot(): void
{
    Gate::policy(Role::class, RolePolicy::class);
}
```

### Paso 4: Usar en Controllers

```php
// app/Http/Controllers/Admin/RoleController.php
public function index()
{
    // Verifica si el usuario puede ver roles
    $this->authorize('viewAny', Role::class);
    
    // Si llega aquí, tiene permiso
    $roles = Role::all();
    return Inertia::render('admin/roles/Index', ['roles' => $roles]);
}

public function create()
{
    // Verifica si puede crear
    $this->authorize('create', Role::class);
    
    return Inertia::render('admin/roles/Create');
}

public function update(Request $request, Role $role)
{
    // Verifica si puede editar este rol específico
    $this->authorize('update', $role);
    
    $role->update($request->validated());
    return redirect()->route('roles.index');
}
```

### Paso 5: Rutas (Sin middleware de roles)

```php
// routes/web.php
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    // Las policies se encargan de la autorización
    Route::resource('roles', RoleController::class);
});
```

---

## 💡 Ejemplos Prácticos

### Ejemplo 1: Usuario con Permiso

**Usuario**: Juan (rol: admin)
**Permisos del rol admin**: `roles.view`, `roles.create`, `roles.edit`

```php
// Juan intenta ver roles
GET /admin/roles

// Controller
$this->authorize('viewAny', Role::class);

// Policy
return $user->can('roles.view'); // ✅ true

// Resultado: Acceso permitido
```

### Ejemplo 2: Usuario sin Permiso

**Usuario**: María (rol: moderator)
**Permisos del rol moderator**: `users.view`, `users.edit`

```php
// María intenta crear un rol
GET /admin/roles/create

// Controller
$this->authorize('create', Role::class);

// Policy
return $user->can('roles.create'); // ❌ false

// Resultado: 403 Unauthorized
```

### Ejemplo 3: Lógica Condicional en Policy

```php
public function delete(User $user, Role $role): bool
{
    // No puede eliminar si:
    // 1. No tiene el permiso
    if (!$user->can('roles.delete')) {
        return false;
    }
    
    // 2. Es un rol protegido
    if (in_array($role->name, ['super-admin', 'user'])) {
        return false;
    }
    
    return true;
}
```

---

## ⚖️ Comparación: Policies vs Middleware

### Enfoque 1: Middleware de Roles (Tradicional)

```php
// ❌ Menos flexible
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'store']);
    Route::put('roles/{role}', [RoleController::class, 'update']);
    Route::delete('roles/{role}', [RoleController::class, 'destroy']);
});
```

**Problemas:**
- Todo o nada (o eres admin o no entras)
- No permite que un moderator vea pero no edite
- Difícil mantener con muchos roles
- Cambiar permisos requiere modificar código

### Enfoque 2: Policies (Implementado)

```php
// ✅ Más flexible
Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
    // Cada método del controller verifica su permiso específico
});
```

**Ventajas:**
- Control granular por acción
- Cambiar permisos sin tocar código
- Fácil de extender
- Mejor para auditoría

### Tabla Comparativa

| Característica | Middleware | Policies |
|----------------|------------|----------|
| **Granularidad** | Por ruta | Por acción |
| **Flexibilidad** | Baja | Alta |
| **Mantenibilidad** | Media | Alta |
| **Escalabilidad** | Baja | Alta |
| **Testing** | Difícil | Fácil |
| **Auditoría** | Limitada | Completa |
| **Cambios sin código** | ❌ No | ✅ Sí |

---

## 🧪 Debugging y Testing

### Ver Permisos de un Usuario

```bash
php artisan tinker

$user = App\Models\User::find(1);

# Ver roles
$user->getRoleNames();
// Collection: ["admin"]

# Ver permisos
$user->getAllPermissions()->pluck('name');
// Collection: ["users.view", "users.create", "roles.view", ...]

# Verificar permiso específico
$user->can('roles.view');
// true o false
```

### Verificar Policy Manualmente

```bash
php artisan tinker

use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

$user = App\Models\User::find(1);
$role = Role::first();

# Verificar si puede ver roles
Gate::forUser($user)->allows('viewAny', Role::class);
// true o false

# Verificar si puede editar un rol específico
Gate::forUser($user)->allows('update', $role);
// true o false
```

### Testing Unitario

```php
// tests/Feature/RolePolicyTest.php
use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin can view roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin)
        ->get('/admin/roles')
        ->assertOk();
});

test('user cannot view roles', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    
    $this->actingAs($user)
        ->get('/admin/roles')
        ->assertForbidden();
});
```

---

## 🎓 Mejores Prácticas

### 1. Nombrar Permisos Consistentemente

**✅ Bueno** (formato: `recurso.acción`):
```
users.view
users.create
users.edit
users.delete
```

**❌ Malo** (inconsistente):
```
view-users
createUser
edit_user
delete-user
```

### 2. Agrupar Permisos por Módulo

```php
// Seeder
$permissions = [
    // Users module
    'users.view',
    'users.create',
    'users.edit',
    'users.delete',
    
    // Roles module
    'roles.view',
    'roles.create',
    'roles.edit',
    'roles.delete',
];
```

### 3. Usar Policies para Lógica Compleja

```php
public function update(User $user, Role $role): bool
{
    // Verificar permiso base
    if (!$user->can('roles.edit')) {
        return false;
    }
    
    // Lógica adicional: solo super-admin puede editar super-admin
    if ($role->name === 'super-admin' && !$user->hasRole('super-admin')) {
        return false;
    }
    
    return true;
}
```

### 4. Documentar Permisos Requeridos

```php
/**
 * Display a listing of roles.
 * 
 * @requires roles.view
 */
public function index()
{
    $this->authorize('viewAny', Role::class);
    // ...
}
```

### 5. Verificar en Frontend También

```vue
<script setup>
import { usePermissions } from '@/composables/usePermissions';
const { hasPermission } = usePermissions();
</script>

<template>
    <!-- Solo mostrar botón si tiene permiso -->
    <Button v-if="hasPermission('roles.create')">
        Create Role
    </Button>
</template>
```

---

## 🔐 Seguridad en Capas

### Capa 1: Frontend (UX)
```vue
<Button v-if="hasPermission('roles.edit')">Edit</Button>
```
**Propósito**: Mejorar experiencia de usuario

### Capa 2: Rutas (Autenticación)
```php
Route::middleware(['auth', 'verified'])->group(...)
```
**Propósito**: Verificar que está logueado

### Capa 3: Controllers (Autorización)
```php
$this->authorize('update', $role);
```
**Propósito**: Verificar permisos específicos

### Capa 4: Policies (Lógica)
```php
return $user->can('roles.edit');
```
**Propósito**: Definir reglas de negocio

### Capa 5: Base de Datos (Datos)
```
users → model_has_roles → roles → role_has_permissions → permissions
```
**Propósito**: Almacenar configuración

---

## 📊 Matriz de Permisos por Rol

| Permiso | super-admin | admin | moderator | user |
|---------|-------------|-------|-----------|------|
| users.view | ✅ | ✅ | ✅ | ❌ |
| users.create | ✅ | ✅ | ❌ | ❌ |
| users.edit | ✅ | ✅ | ✅ | ❌ |
| users.delete | ✅ | ✅ | ❌ | ❌ |
| roles.view | ✅ | ✅ | ❌ | ❌ |
| roles.create | ✅ | ❌ | ❌ | ❌ |
| roles.edit | ✅ | ✅ | ❌ | ❌ |
| roles.delete | ✅ | ❌ | ❌ | ❌ |
| permissions.view | ✅ | ✅ | ❌ | ❌ |
| permissions.create | ✅ | ❌ | ❌ | ❌ |
| settings.view | ✅ | ✅ | ❌ | ❌ |
| settings.edit | ✅ | ✅ | ❌ | ❌ |
| logs.view | ✅ | ✅ | ✅ | ❌ |

---

## 🚀 Casos de Uso Avanzados

### Caso 1: Permisos Contextuales

```php
// Solo el dueño puede editar su propio perfil
public function update(User $user, User $model): bool
{
    // Super admin puede editar cualquiera
    if ($user->hasRole('super-admin')) {
        return true;
    }
    
    // Admin puede editar usuarios normales
    if ($user->hasRole('admin') && !$model->hasRole('admin')) {
        return true;
    }
    
    // Cualquiera puede editar su propio perfil
    return $user->id === $model->id;
}
```

### Caso 2: Permisos Temporales

```php
// Dar permiso temporal (implementación futura)
$user->givePermissionTo('special.access', now()->addDays(7));
```

### Caso 3: Permisos por Equipo/Organización

```php
public function view(User $user, Project $project): bool
{
    // Verificar si pertenece al mismo equipo
    return $user->team_id === $project->team_id 
        && $user->can('projects.view');
}
```

---

## 📚 Recursos Adicionales

- [Laravel Authorization Docs](https://laravel.com/docs/authorization)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [Policy Best Practices](https://laravel-news.com/authorization-policies)

---

## ✅ Checklist de Implementación

Al implementar autorización con Policies, verifica:

- [ ] Policy creada para cada modelo
- [ ] Métodos de Policy implementados (viewAny, view, create, update, delete)
- [ ] Policy registrada en AppServiceProvider
- [ ] Controllers usan `$this->authorize()`
- [ ] Permisos creados en seeder
- [ ] Permisos asignados a roles
- [ ] Frontend oculta elementos según permisos
- [ ] Tests escritos para policies
- [ ] Documentación actualizada

---

## 🎯 Resumen

**Policies** son la forma moderna y recomendada de manejar autorización en Laravel porque:

1. ✅ **Separan responsabilidades**: Rutas → autenticación, Policies → autorización
2. ✅ **Son flexibles**: Cambiar permisos sin tocar código
3. ✅ **Escalan bien**: Fácil agregar nuevos roles y permisos
4. ✅ **Son testeables**: Lógica aislada y fácil de probar
5. ✅ **Mejoran UX**: Control granular por acción

**Flujo simple**:
```
Request → Middleware (auth) → Controller (authorize) → Policy (can) → DB → ✅/❌
```

---

**Fecha de creación**: 2025-11-23  
**Versión**: 1.0.0  
**Autor**: Equipo de desarrollo
