<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Shield, Save, X } from 'lucide-vue-next';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface Props {
    permissions: Permission[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin/roles' },
    { title: 'Roles', href: '/admin/roles' },
    { title: 'Create', href: '/admin/roles/create' },
];

// Group permissions by module
const groupedPermissions = props.permissions.reduce((acc, permission) => {
    const [module] = permission.name.split('.');
    if (!acc[module]) {
        acc[module] = [];
    }
    acc[module].push(permission);
    return acc;
}, {} as Record<string, Permission[]>);

const togglePermission = (permissionName: string) => {
    const index = form.permissions.indexOf(permissionName);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permissionName);
    }
};

const toggleModule = (module: string) => {
    const modulePermissions = groupedPermissions[module].map(p => p.name);
    const allSelected = modulePermissions.every(p => form.permissions.includes(p));
    
    if (allSelected) {
        form.permissions = form.permissions.filter(p => !modulePermissions.includes(p));
    } else {
        modulePermissions.forEach(p => {
            if (!form.permissions.includes(p)) {
                form.permissions.push(p);
            }
        });
    }
};

const isModuleSelected = (module: string) => {
    const modulePermissions = groupedPermissions[module].map(p => p.name);
    return modulePermissions.every(p => form.permissions.includes(p));
};

const submit = () => {
    form.post('/admin/roles', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Role" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Create Role
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Create a new role and assign permissions
                    </p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Role Details Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="h-5 w-5" />
                            Role Details
                        </CardTitle>
                        <CardDescription>
                            Basic information about the role
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Role Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="e.g., editor, manager"
                                :class="{ 'border-destructive': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Permissions Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Permissions</CardTitle>
                        <CardDescription>
                            Select permissions for this role
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            <div
                                v-for="(permissions, module) in groupedPermissions"
                                :key="module"
                                class="space-y-3"
                            >
                                <!-- Module Header -->
                                <div class="flex items-center gap-2 border-b pb-2">
                                    <Checkbox
                                        :id="`module-${module}`"
                                        :modelValue="isModuleSelected(module)"
                                        @update:modelValue="toggleModule(module)"
                                    />
                                    <Label
                                        :for="`module-${module}`"
                                        class="text-base font-semibold capitalize cursor-pointer"
                                    >
                                        {{ module }}
                                    </Label>
                                </div>

                                <!-- Module Permissions -->
                                <div class="grid gap-3 pl-6 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="permission in permissions"
                                        :key="permission.id"
                                        class="flex items-center gap-2"
                                    >
                                        <Checkbox
                                            :id="`permission-${permission.id}`"
                                            :modelValue="form.permissions.includes(permission.name)"
                                            @update:modelValue="togglePermission(permission.name)"
                                        />
                                        <Label
                                            :for="`permission-${permission.id}`"
                                            class="text-sm cursor-pointer"
                                        >
                                            {{ permission.name.split('.')[1] }}
                                        </Label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <Button
                        type="button"
                        variant="outline"
                        @click="router.visit('/admin/roles')"
                        class="gap-2"
                    >
                        <X class="h-4 w-4" />
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="gap-2"
                    >
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Creating...' : 'Create Role' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
