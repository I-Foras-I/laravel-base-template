# ⚠️ Gotchas y Consideraciones Técnicas

Este documento contiene notas importantes sobre peculiaridades, errores comunes y mejores prácticas específicas del stack tecnológico utilizado en este proyecto.

---

## 🎯 Vue 3 + TypeScript + shadcn-vue

### Checkbox Component - modelValue vs checked

**Problema**: El componente `Checkbox` de shadcn-vue **NO** usa la prop estándar `:checked` de HTML.

**❌ Incorrecto** (No funcionará):
```vue
<Checkbox
    :checked="isSelected"
    @update:checked="handleChange"
/>
```

**✅ Correcto** (Usar `modelValue`):
```vue
<Checkbox
    :modelValue="isSelected"
    @update:modelValue="handleChange"
/>
```

**Razón**: shadcn-vue sigue el patrón de Vue 3 `v-model` que usa `modelValue` como prop y `update:modelValue` como evento.

**Alternativa con v-model**:
```vue
<script setup>
const isSelected = ref(false);
</script>

<template>
    <Checkbox v-model="isSelected" />
</template>
```

```

---

### Datos Derivados de Props - Usar `computed()` para Reactividad

**Problema**: Cuando creas una variable derivada de `props` usando operaciones como `reduce()`, `map()`, `filter()`, etc., los cambios en los props **no se reflejan** en la variable derivada.

**Síntoma**: Crear/eliminar un item en Inertia → El backend actualiza correctamente → Los props cambian → Pero la UI no se actualiza hasta recargar manualmente (F5).

**Causa**: Las variables `const` se calculan **una sola vez** cuando el componente se monta. No son reactivas a cambios en props.

**❌ Incorrecto** (No reactivo):
```vue
<script setup lang="ts">
interface Permission {
    id: number;
    name: string;
}

const props = defineProps<{
    permissions: Permission[];
}>();

// ❌ Esto se calcula UNA SOLA VEZ al montar el componente
const groupedPermissions = props.permissions.reduce((acc, permission) => {
    const [module] = permission.name.split('.');
    if (!acc[module]) {
        acc[module] = [];
    }
    acc[module].push(permission);
    return acc;
}, {} as Record<string, Permission[]>);
// Si props.permissions cambia, groupedPermissions NO se actualiza
</script>

<template>
    <div v-for="(permissions, module) in groupedPermissions">
        <!-- No se actualiza cuando props.permissions cambia -->
    </div>
</template>
```

**✅ Correcto** (Reactivo con `computed()`):
```vue
<script setup lang="ts">
import { computed } from 'vue';

interface Permission {
    id: number;
    name: string;
}

const props = defineProps<{
    permissions: Permission[];
}>();

// ✅ computed() se recalcula automáticamente cuando props.permissions cambia
const groupedPermissions = computed(() => {
    return props.permissions.reduce((acc, permission) => {
        const [module] = permission.name.split('.');
        if (!acc[module]) {
            acc[module] = [];
        }
        acc[module].push(permission);
        return acc;
    }, {} as Record<string, Permission[]>);
});
// Ahora cuando props.permissions cambia, groupedPermissions se actualiza
</script>

<template>
    <div v-for="(permissions, module) in groupedPermissions">
        <!-- Se actualiza automáticamente ✅ -->
    </div>
</template>
```

**Regla General**: 

Si una variable depende de `props` o `ref/reactive`, **siempre usa `computed()`**:

```vue
// ❌ NO reactivo
const filtered = props.items.filter(item => item.active);
const sorted = props.items.sort((a, b) => a.name.localeCompare(b.name));
const mapped = props.items.map(item => ({ ...item, label: item.name }));

// ✅ Reactivo
const filtered = computed(() => props.items.filter(item => item.active));
const sorted = computed(() => props.items.sort((a, b) => a.name.localeCompare(b.name)));
const mapped = computed(() => props.items.map(item => ({ ...item, label: item.name })));
```

**Nota**: En el template, accede a computed properties **sin paréntesis**:
```vue
<div v-for="item in filtered">  <!-- ✅ Correcto -->
<div v-for="item in filtered()"> <!-- ❌ Incorrecto -->
```

---


### Instalación de Componentes - Usar shadcn-vue CLI

**Problema**: Instalar librerías de UI directamente con `npm install` en lugar de usar el CLI de shadcn-vue puede causar problemas de estilos y configuración.

**Síntoma**: Componente funciona pero sin estilos, o estilos rotos/inconsistentes con el resto de la aplicación.

**❌ Incorrecto** (Instalación directa):
```bash
npm install vue-sonner
```

Problemas:
- No integra automáticamente con los estilos de shadcn-vue
- Requiere configuración manual de CSS
- No usa las variables de tema del proyecto
- Puede tener conflictos de estilos

**✅ Correcto** (Usar CLI de shadcn-vue):
```bash
npx shadcn-vue@latest add sonner
```

Ventajas:
- ✅ Crea el componente en `components/ui/sonner/`
- ✅ Integra automáticamente con el tema
- ✅ Usa las variables CSS del proyecto
- ✅ Incluye TypeScript types
- ✅ Dark mode automático
- ✅ Estilos consistentes

**Regla**: Siempre verifica primero si shadcn-vue tiene el componente disponible en https://www.shadcn-vue.com/docs/components/

**Nota sobre vue-sonner**: El componente de shadcn-vue usa `vue-sonner` internamente, por eso necesitas importar los estilos base:

```vue
<script setup>
import 'vue-sonner/style.css' // Estilos base necesarios
import { Toaster } from '@/components/ui/sonner'; // Wrapper de shadcn
import { toast } from 'vue-sonner'; // Funciones toast
</script>
```

---


## 🔐 Laravel 11+ Authorization

### Controller Base - AuthorizesRequests Trait

**Problema**: En Laravel 11+, el trait `AuthorizesRequests` ya no se incluye por defecto en el Controller base.

**Síntoma**: Error `Call to undefined method authorize()`

**✅ Solución**:
```php
// app/Http/Controllers/Controller.php
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;
}
```

---

### Policy Registration para Modelos de Paquetes

**Problema**: Laravel no auto-descubre Policies para modelos de paquetes externos (como Spatie Permission).

**Síntoma**: Error `403 This action is unauthorized` incluso con permisos correctos.

**✅ Solución**: Registrar manualmente en `AppServiceProvider`:
```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Policies\RolePolicy;
use App\Policies\PermissionPolicy;

public function boot(): void
{
    Gate::policy(Role::class, RolePolicy::class);
    Gate::policy(Permission::class, PermissionPolicy::class);
}
```

---

## 🏭 User Factory - Two-Factor Authentication

### 2FA Habilitado por Defecto

**Problema**: El `UserFactory` crea usuarios con 2FA ya configurado por defecto.

**Síntoma**: Al hacer login, pide código de autenticación que no tienes.

**❌ Problema**:
```php
User::factory()->create(); // Crea usuario con 2FA
```

**✅ Solución 1** - Usar método `withoutTwoFactor()`:
```php
User::factory()->withoutTwoFactor()->create([
    'email' => 'test@example.com',
]);
```

**✅ Solución 2** - Sobrescribir campos manualmente:
```php
User::factory()->create([
    'email' => 'test@example.com',
    'two_factor_secret' => null,
    'two_factor_recovery_codes' => null,
    'two_factor_confirmed_at' => null,
]);
```

**Recomendación**: Para desarrollo, siempre usar `withoutTwoFactor()` a menos que específicamente necesites probar 2FA.

---

## 📦 Spatie Laravel Permission

### Cache de Permisos

**Problema**: Los cambios en permisos no se reflejan inmediatamente.

**Razón**: Spatie cachea los permisos para mejor rendimiento.

**✅ Solución**:
```bash
# Limpiar cache de permisos
php artisan cache:forget spatie.permission.cache

# O limpiar todo el cache
php artisan optimize:clear
```

---

### Verificación de Permisos en Blade vs Controllers

**En Controllers** (usar policies):
```php
$this->authorize('viewAny', Role::class);
```

**En Blade** (usar directivas):
```blade
@can('roles.view')
    <!-- contenido -->
@endcan
```

**En Vue** (usar composable):
```vue
<script setup>
import { usePermissions } from '@/composables/usePermissions';
const { hasPermission } = usePermissions();
</script>

<template>
    <div v-if="hasPermission('roles.view')">
        <!-- contenido -->
    </div>
</template>
```

---

## 🎨 Tailwind CSS v4

### Cambios en Border Color

**Problema**: En Tailwind v4, el color de borde por defecto cambió a `currentColor`.

**Solución ya implementada** en `resources/css/app.css`:
```css
@layer base {
    *,
    ::after,
    ::before,
    ::backdrop,
    ::file-selector-button {
        border-color: var(--color-gray-200, currentColor);
    }
}
```

---

## 🔄 Inertia.js

### Shared Data vs Props

**Shared Data** (disponible en todas las páginas):
```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return [
        'auth' => [
            'user' => $request->user(),
        ],
    ];
}
```

**Props** (específicos de una página):
```php
// Controller
return Inertia::render('Page', [
    'roles' => $roles,
]);
```

**Acceso en Vue**:
```vue
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

// Shared data
const page = usePage();
const user = page.props.auth.user;

// Props de la página
defineProps<{
    roles: Role[];
}>();
</script>
```

---

## 🐛 Debugging Tips

### Ver Datos Compartidos de Inertia

```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
console.log('All props:', page.props);
console.log('User:', page.props.auth.user);
console.log('Roles:', page.props.auth.user?.roles);
</script>
```

### Ver Permisos del Usuario Actual

```bash
php artisan tinker

$user = App\Models\User::find(1);
$user->getRoleNames();
$user->getAllPermissions()->pluck('name');
```

### Verificar Policies

```bash
php artisan tinker

$user = App\Models\User::find(1);
$role = Spatie\Permission\Models\Role::first();

# Verificar si puede ver roles
Gate::forUser($user)->allows('viewAny', Spatie\Permission\Models\Role::class);
```

---

## 📝 Mejores Prácticas

### 1. Siempre Usar TypeScript Types

**❌ Evitar**:
```vue
<script setup>
const props = defineProps(['roles']);
</script>
```

**✅ Preferir**:
```vue
<script setup lang="ts">
interface Role {
    id: number;
    name: string;
}

const props = defineProps<{
    roles: Role[];
}>();
</script>
```

### 2. Composables para Lógica Reutilizable

**❌ Evitar** duplicar lógica:
```vue
<script setup>
const user = usePage().props.auth.user;
const hasRole = (role: string) => user?.roles?.includes(role);
</script>
```

**✅ Preferir** usar composables:
```vue
<script setup>
import { usePermissions } from '@/composables/usePermissions';
const { hasRole } = usePermissions();
</script>
```

### 3. Validación en Backend Y Frontend

**Backend** (siempre requerido):
```php
$this->authorize('update', $role);
```

**Frontend** (UX):
```vue
<Button v-if="hasPermission('roles.edit')">
    Edit
</Button>
```

---

## 🔍 Recursos Útiles

- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [shadcn-vue Components](https://www.shadcn-vue.com/docs/components)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [Inertia.js Guide](https://inertiajs.com/)
- [Laravel 11 Release Notes](https://laravel.com/docs/11.x/releases)

---

## 🛣️ Laravel Routing

### Route Naming con Prefijos de Grupo

**Problema**: Cuando defines rutas dentro de un grupo con `prefix()` y `name()`, debes incluir el prefijo completo del grupo al hacer `redirect()->route()`.

**Síntoma**: Error `Route [permissions.index] not defined`

**Código problemático**:
```php
// routes/web.php
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('permissions', [PermissionController::class, 'index'])
        ->name('permissions.index');
    //      ↑ Nombre completo será: admin.permissions.index
});

// Controller
return redirect()->route('permissions.index'); // ❌ Error: Route not found
```

**✅ Solución**: Incluir el prefijo del grupo
```php
// Controller
return redirect()->route('admin.permissions.index'); // ✅ Correcto
```

**Explicación**: 

Cuando usas `name('admin.')` en un grupo de rutas, Laravel **concatena** ese prefijo con todos los nombres de ruta dentro del grupo:

```php
Route::name('admin.')->group(function () {
    Route::get('roles', ...)->name('roles.index');
    // Nombre real: admin.roles.index
    
    Route::get('users', ...)->name('users.index');
    // Nombre real: admin.users.index
    
    Route::resource('permissions', PermissionController::class);
    // Nombres reales: 
    // - admin.permissions.index
    // - admin.permissions.create
    // - admin.permissions.store
    // - etc.
});
```

**Tip**: Usa `php artisan route:list` para ver todos los nombres de ruta registrados.

---

**Última actualización**: 2025-11-24  
**Mantenedor**: Equipo de desarrollo

> 💡 **Nota**: Si encuentras más gotchas o peculiaridades, agrégalas a este documento para ayudar a futuros desarrolladores.
