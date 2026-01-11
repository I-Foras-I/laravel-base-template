# User Management Module

## 📋 Tabla de Contenidos
- [Descripción General](#descripción-general)
- [Características](#características)
- [Arquitectura](#arquitectura)
- [Rutas y Endpoints](#rutas-y-endpoints)
- [Base de Datos](#base-de-datos)
- [Permisos y Seguridad](#permisos-y-seguridad)
- [Componentes Frontend](#componentes-frontend)
- [Gestión de Avatares](#gestión-de-avatares)
- [Uso y Ejemplos](#uso-y-ejemplos)
- [Troubleshooting](#troubleshooting)

---

## Descripción General

El **User Management Module** es un sistema completo de administración de usuarios que permite crear, editar, eliminar y visualizar usuarios del sistema. Incluye gestión de roles, permisos, estados de activación y avatares de usuario.

### Stack Tecnológico
- **Backend**: Laravel 11+, PHP 8.2+
- **Frontend**: Vue 3, TypeScript, Inertia.js
- **UI**: Tailwind CSS, shadcn-vue
- **Permisos**: Spatie Laravel Permission
- **Activity Logs**: Spatie Laravel Activitylog
- **Storage**: Laravel Storage (compatible con Local y Cloud S3/R2)

---

## Características

### 1. Listado de Usuarios (Index)
- **Búsqueda en tiempo real** por nombre o email
- **Filtros avanzados**:
  - Por rol (Admin, Editor, Viewer, etc.)
  - Por estado (Activo/Inactivo)
- **Paginación** (10 usuarios por página)
- **Vista responsive**:
  - Desktop: Tabla completa
  - Mobile: Cards adaptativas
- **Acciones rápidas**: Ver detalles, Editar, Eliminar

### 2. Crear Usuario (Create)
- Formulario completo con validación
- Campos:
  - Nombre
  - Email (único)
  - Contraseña (mínimo 8 caracteres)
  - Confirmación de contraseña
  - Avatar (opcional, max 2MB)
  - Roles (múltiples selecciones)
  - Estado activo/inactivo
- Validación en tiempo real
- Feedback visual de errores

### 3. Editar Usuario (Edit)
- Actualización de información personal
- Cambio de avatar (elimina el anterior automáticamente)
- Gestión de roles
- Activación/desactivación de cuenta
- **Nota**: No permite cambiar contraseña (se maneja por separado)

### 4. Ver Detalles (Show)
- Información completa del usuario
- Avatar grande
- Roles y permisos asignados
- **Actividad reciente**: Últimas 5 acciones del usuario
- Acciones rápidas: Editar, Eliminar

### 5. Eliminar Usuario (Delete)
- Confirmación obligatoria con diálogo
- **Protecciones de seguridad**:
  - No puedes eliminarte a ti mismo
  - Super-admins no pueden ser eliminados por no-super-admins
- Eliminación automática del avatar

### 6. Gestión de Avatares
- Upload de imágenes (JPG, PNG, GIF, WebP)
- Tamaño máximo: 2MB
- Previsualización antes de guardar
- Almacenamiento optimizado
- Compatible con Cloud Storage (S3/R2)
- Fallback a iniciales si no hay avatar

---

## Arquitectura

### Backend

#### Controlador Principal
**`App\Http\Controllers\Admin\UserController`**

```php
// Métodos disponibles
index()    // Lista de usuarios con filtros
create()   // Formulario de creación
store()    // Guardar nuevo usuario
show()     // Ver detalles
edit()     // Formulario de edición
update()   // Actualizar usuario
destroy()  // Eliminar usuario
```

#### Requests de Validación
- **`StoreUserRequest`**: Validación para crear usuarios
  - name: required, string, max:255
  - email: required, email, unique
  - password: required, min:8, confirmed
  - roles: array, exists:roles,name
  - is_active: boolean
  - photo: nullable, image, max:2048

- **`UpdateUserRequest`**: Validación para actualizar usuarios
  - name: required, string, max:255
  - email: required, email, unique (excepto el usuario actual)
  - roles: array, exists:roles,name
  - is_active: boolean
  - photo: nullable, image, max:2048

#### Modelo User
**`App\Models\User`**

```php
// Traits utilizados
use HasFactory, Notifiable, TwoFactorAuthenticatable;
use HasRoles;           // Spatie Permission
use LogsActivity;       // Spatie Activitylog
use CausesActivity;     // Spatie Activitylog

// Campos fillable
protected $fillable = [
    'name',
    'email',
    'password',
    'is_active',
    'profile_photo_path',
];

// Accessor
public function getProfilePhotoUrlAttribute(): string
{
    return $this->profile_photo_path
        ? Storage::disk('public')->url($this->profile_photo_path)
        : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
}
```

#### Policy
**`App\Policies\UserPolicy`**

```php
viewAny()  // Requiere: users.view
view()     // Requiere: users.view
create()   // Requiere: users.create
update()   // Requiere: users.edit
delete()   // Requiere: users.delete + protecciones especiales
```

### Frontend

#### Páginas Vue
1. **`Index.vue`** - Lista de usuarios
   - Búsqueda y filtros
   - Tabla responsive
   - Paginación
   - Avatares en lista

2. **`Create.vue`** - Crear usuario
   - Formulario completo
   - Upload de avatar
   - Selección de roles

3. **`Edit.vue`** - Editar usuario
   - Formulario pre-poblado
   - Cambio de avatar
   - Gestión de roles

4. **`Show.vue`** - Detalles del usuario
   - Avatar grande
   - Información completa
   - Activity logs

#### Componentes Reutilizables
- **`ImageUpload.vue`**: Componente de upload con preview
  - Drag & drop
  - Preview inmediato
  - Botón de cancelar
  - Validación de tipo y tamaño

---

## Rutas y Endpoints

### Rutas Web (Inertia)
```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
});
```

### Endpoints Disponibles

| Método | URI | Nombre | Acción |
|--------|-----|--------|--------|
| GET | `/admin/users` | admin.users.index | Lista de usuarios |
| GET | `/admin/users/create` | admin.users.create | Formulario de creación |
| POST | `/admin/users` | admin.users.store | Guardar nuevo usuario |
| GET | `/admin/users/{user}` | admin.users.show | Ver detalles |
| GET | `/admin/users/{user}/edit` | admin.users.edit | Formulario de edición |
| PUT/PATCH | `/admin/users/{user}` | admin.users.update | Actualizar usuario |
| DELETE | `/admin/users/{user}` | admin.users.destroy | Eliminar usuario |

---

## Base de Datos

### Tabla: `users`

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    profile_photo_path VARCHAR(2048) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Relaciones

#### Con Spatie Permission
```php
// Roles del usuario
$user->roles; // Collection de roles

// Permisos del usuario (directos + de roles)
$user->permissions; // Collection de permisos

// Verificar permisos
$user->hasPermissionTo('users.edit');
$user->hasRole('admin');
```

#### Con Activity Log
```php
// Actividades causadas por el usuario
$user->actions(); // Relación

// Actividades sobre el usuario
$user->activities(); // Relación (de LogsActivity)
```

---

## Permisos y Seguridad

### Permisos Requeridos

| Permiso | Descripción |
|---------|-------------|
| `users.view` | Ver lista y detalles de usuarios |
| `users.create` | Crear nuevos usuarios |
| `users.edit` | Editar usuarios existentes |
| `users.delete` | Eliminar usuarios |

### Reglas de Seguridad

1. **Auto-protección**: Un usuario no puede eliminarse a sí mismo
2. **Protección de Super-Admin**: Los super-admins solo pueden ser eliminados por otros super-admins
3. **Autorización en cada acción**: Todas las acciones pasan por `UserPolicy`
4. **Validación de datos**: Requests dedicados para validación
5. **CSRF Protection**: Automático en formularios Laravel

### Configuración de Permisos

```php
// database/seeders/RolesAndPermissionsSeeder.php

// Crear permisos
Permission::create(['name' => 'users.view']);
Permission::create(['name' => 'users.create']);
Permission::create(['name' => 'users.edit']);
Permission::create(['name' => 'users.delete']);

// Asignar a roles
$adminRole->givePermissionTo([
    'users.view',
    'users.create',
    'users.edit',
    'users.delete',
]);
```

---

## Componentes Frontend

### ImageUpload.vue

Componente reutilizable para upload de imágenes con preview.

**Props:**
```typescript
interface Props {
    modelValue?: File | null;
    currentImageUrl?: string;
    defaultInitials?: string;
}
```

**Eventos:**
```typescript
emit('update:modelValue', file: File | null)
```

**Uso:**
```vue
<ImageUpload 
    v-model="form.photo" 
    :current-image-url="user.profile_photo_url"
    :default-initials="user.name.substring(0, 2).toUpperCase()"
/>
```

### Composables Utilizados

#### usePermissions
```typescript
const { hasPermission, hasAnyPermission } = usePermissions();

if (hasPermission('users.edit')) {
    // Mostrar botón de editar
}
```

#### useConfirm
```typescript
const { confirm } = useConfirm();

const confirmed = await confirm({
    title: 'Delete User',
    description: 'Are you sure?',
    confirmText: 'Delete',
    variant: 'destructive',
});
```

---

## Gestión de Avatares

### Arquitectura de Storage

#### Local (Desarrollo)
```
storage/
  app/
    public/
      avatars/
        abc123.jpg
        def456.png
```

Acceso público vía symlink:
```bash
php artisan storage:link
```

URL generada: `http://localhost/storage/avatars/abc123.jpg`

#### Cloud (Producción - S3/R2)

**Configuración en `.env`:**
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your-bucket
AWS_ENDPOINT=https://account.r2.cloudflarestorage.com
AWS_URL=https://your-cdn-url.com
```

URL generada: `https://your-cdn-url.com/avatars/abc123.jpg`

### Flujo de Upload

1. **Usuario selecciona imagen** → Preview inmediato en frontend
2. **Submit del formulario** → Envío como `FormData` (no JSON)
3. **Validación** → Max 2MB, tipos: jpg, png, gif, webp
4. **Storage** → `$request->file('photo')->store('avatars', 'public')`
5. **Base de datos** → Guarda ruta relativa: `avatars/abc123.jpg`
6. **Accessor** → Genera URL completa según disco configurado

### Flujo de Reemplazo

1. **Usuario sube nueva imagen**
2. **Backend detecta** → `$user->profile_photo_path` existe
3. **Elimina anterior** → `Storage::disk('public')->delete($oldPath)`
4. **Guarda nueva** → `store('avatars', 'public')`
5. **Actualiza BD** → Nueva ruta

### Flujo de Eliminación

1. **Usuario es eliminado**
2. **UserController::destroy()** verifica si tiene avatar
3. **Elimina archivo** → `Storage::disk('public')->delete($path)`
4. **Elimina registro** → `$user->delete()`

### Fallback de Avatar

Si el usuario no tiene avatar, se usa **UI Avatars**:
```
https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF
```

Genera iniciales automáticamente con colores personalizados.

---

## Uso y Ejemplos

### Crear un Usuario Programáticamente

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password123'),
    'is_active' => true,
]);

// Asignar roles
$user->assignRole('editor');

// O múltiples roles
$user->assignRole(['editor', 'viewer']);
```

### Actualizar Avatar Programáticamente

```php
use Illuminate\Support\Facades\Storage;

// Desde un archivo subido
$path = $request->file('photo')->store('avatars', 'public');
$user->update(['profile_photo_path' => $path]);

// Eliminar avatar
if ($user->profile_photo_path) {
    Storage::disk('public')->delete($user->profile_photo_path);
    $user->update(['profile_photo_path' => null]);
}
```

### Verificar Permisos en Blade/Vue

**En Blade:**
```blade
@can('users.edit', $user)
    <a href="{{ route('admin.users.edit', $user) }}">Edit</a>
@endcan
```

**En Vue:**
```vue
<template>
    <Button v-if="hasPermission('users.edit')" @click="editUser">
        Edit User
    </Button>
</template>

<script setup>
import { usePermissions } from '@/composables/usePermissions';
const { hasPermission } = usePermissions();
</script>
```

### Filtrar Usuarios

```php
// En el controlador
$users = User::with('roles')
    ->when($request->search, function ($query, $search) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
    })
    ->when($request->role, function ($query, $role) {
        $query->whereHas('roles', fn($q) => $q->where('name', $role));
    })
    ->when($request->status !== null, function ($query) use ($request) {
        $query->where('is_active', $request->status === 'active');
    })
    ->paginate(10);
```

---

## Troubleshooting

### Problema: Los checkboxes no guardan el valor

**Síntoma**: Al crear/editar usuario, `is_active` o `roles` no se guardan.

**Causa**: `shadcn-vue` Checkbox usa `modelValue` en lugar de `checked`.

**Solución**:
```vue
<!-- ❌ Incorrecto -->
<Checkbox v-model="form.is_active" />

<!-- ✅ Correcto -->
<Checkbox
    :modelValue="form.is_active"
    @update:modelValue="form.is_active = $event as boolean"
/>
```

### Problema: Avatar no se muestra

**Verificaciones**:
1. ¿Existe el symlink? → `php artisan storage:link`
2. ¿El archivo existe? → Verifica `storage/app/public/avatars/`
3. ¿La URL es correcta? → Inspecciona `$user->profile_photo_url`
4. ¿Permisos del servidor? → `chmod -R 775 storage`

### Problema: Error al subir archivo grande

**Síntoma**: Error 413 o timeout al subir imagen.

**Solución**: Ajustar límites en `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

Y en `.htaccess` (si usas Apache):
```apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

### Problema: No puedo eliminar un usuario

**Verificaciones**:
1. ¿Tienes el permiso `users.delete`?
2. ¿Estás intentando eliminarte a ti mismo? (No permitido)
3. ¿El usuario es super-admin y tú no? (No permitido)

### Problema: FormData no envía archivos

**Causa**: Inertia requiere configuración especial para archivos.

**Solución en Edit.vue**:
```typescript
// ❌ Incorrecto
form.put(`/admin/users/${user.id}`);

// ✅ Correcto (usar POST con _method)
const form = useForm({
    _method: 'put',
    name: user.name,
    photo: null,
    // ...
});

form.post(`/admin/users/${user.id}`);
```

---

## Activity Logging

Todas las acciones sobre usuarios se registran automáticamente:

```php
// En User.php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'email', 'profile_photo_path'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}
```

**Ver logs de un usuario**:
```php
// Acciones realizadas POR el usuario
$user->actions()->latest()->get();

// Cambios realizados EN el usuario
$user->activities()->latest()->get();
```

---

## Testing

### Ejemplo de Test Unitario

```php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('user can be created with avatar', function () {
    $user = User::factory()->create([
        'profile_photo_path' => 'avatars/test.jpg',
    ]);

    expect($user->profile_photo_url)
        ->toContain('avatars/test.jpg');
});

test('user cannot delete themselves', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect()
        ->assertSessionHas('error');
});
```

---

## Mejoras Futuras

- [ ] Cambio de contraseña desde el perfil
- [ ] Verificación de email
- [ ] Two-Factor Authentication UI
- [ ] Exportar usuarios a CSV/Excel
- [ ] Importar usuarios desde CSV
- [ ] Historial completo de cambios
- [ ] Soft deletes para usuarios
- [ ] Búsqueda avanzada con múltiples criterios

---

## Referencias

- [Laravel Documentation](https://laravel.com/docs)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Spatie Activitylog](https://spatie.be/docs/laravel-activitylog)
- [Inertia.js](https://inertiajs.com)
- [shadcn-vue](https://www.shadcn-vue.com)
