# 🌱 Database Seeders

## Seeders Disponibles

### 1. RolesAndPermissionsSeeder
Crea los roles y permisos base del sistema.

**Roles creados:**
- `super-admin` - Acceso total (16 permisos)
- `admin` - Gestión general (10 permisos)
- `moderator` - Moderación limitada (3 permisos)
- `user` - Usuario estándar (0 permisos)

**Permisos por módulo:**
- Users: view, create, edit, delete
- Roles: view, create, edit, delete
- Permissions: view, create, edit, delete
- Settings: view, edit
- Logs: view, delete

### 2. AdminUserSeeder
Crea o actualiza el usuario administrador de prueba.

**Usuario creado:**
- Email: `admin@example.com`
- Password: `password`
- Rol: `super-admin`
- 2FA: Deshabilitado
- Email verificado: Sí

## Uso

### Ejecutar todos los seeders
```bash
php artisan db:seed
```

Esto ejecutará en orden:
1. `RolesAndPermissionsSeeder`
2. `AdminUserSeeder`

### Ejecutar seeder específico
```bash
# Solo roles y permisos
php artisan db:seed --class=RolesAndPermissionsSeeder

# Solo usuario admin
php artisan db:seed --class=AdminUserSeeder
```

### Refrescar base de datos y seeders
```bash
# ⚠️ CUIDADO: Esto borrará todos los datos
php artisan migrate:fresh --seed
```

## Crear Usuarios de Prueba

### Sin 2FA (recomendado para desarrollo)
```php
User::factory()->withoutTwoFactor()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
]);
```

### Con 2FA (por defecto)
```php
User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
]);
```

### Múltiples usuarios
```php
// 10 usuarios sin 2FA
User::factory(10)->withoutTwoFactor()->create();
```

## Asignar Roles

```php
$user = User::find(1);

// Asignar un rol
$user->assignRole('admin');

// Asignar múltiples roles
$user->assignRole(['admin', 'moderator']);

// Sincronizar roles (reemplaza los existentes)
$user->syncRoles(['admin']);

// Remover rol
$user->removeRole('admin');
```

## Asignar Permisos

```php
$user = User::find(1);

// Asignar permiso directo
$user->givePermissionTo('users.view');

// Asignar múltiples permisos
$user->givePermissionTo(['users.view', 'users.create']);

// Remover permiso
$user->revokePermissionTo('users.view');
```

## Notas Importantes

### 2FA en Usuarios de Prueba
El `UserFactory` por defecto crea usuarios **con 2FA habilitado**. Para desarrollo, es recomendable usar `withoutTwoFactor()`:

```php
// ❌ Creará usuario con 2FA (pedirá código al login)
User::factory()->create();

// ✅ Creará usuario sin 2FA (login directo)
User::factory()->withoutTwoFactor()->create();
```

### Orden de Seeders
Es importante ejecutar `RolesAndPermissionsSeeder` **antes** de `AdminUserSeeder` porque el usuario admin necesita que el rol `super-admin` ya exista.

### Idempotencia
Los seeders están diseñados para ser **idempotentes**:
- `RolesAndPermissionsSeeder`: Crea roles/permisos solo si no existen
- `AdminUserSeeder`: Actualiza el usuario si ya existe, lo crea si no

Esto permite ejecutarlos múltiples veces sin errores.

## Troubleshooting

### Error: "Role does not exist"
```bash
# Ejecutar primero el seeder de roles
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Usuario admin con 2FA habilitado
```bash
# Ejecutar el seeder de admin (deshabilitará 2FA)
php artisan db:seed --class=AdminUserSeeder
```

### Limpiar y empezar de cero
```bash
# Borra todo y vuelve a crear
php artisan migrate:fresh --seed
```
