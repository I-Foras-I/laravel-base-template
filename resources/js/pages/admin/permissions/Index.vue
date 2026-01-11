<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { usePermissions } from '@/composables/usePermissions';
import { useConfirm } from '@/composables/useConfirm';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Key, Plus, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
    roles: string[];
    created_at: string;
    updated_at: string;
}

interface Props {
    permissions: Permission[];
}

const props = defineProps<Props>();
const { hasPermission } = usePermissions();
const { confirm } = useConfirm();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin/permissions' },
    { title: 'Permissions', href: '/admin/permissions' },
];

const showCreateForm = ref(false);
const form = useForm({
    name: '',
});

const createPermission = () => {
    form.post('/admin/permissions', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showCreateForm.value = false;
        },
    });
};

const deletePermission = async (permission: Permission) => {
    const confirmed = await confirm({
        title: 'Delete Permission',
        description: `Are you sure you want to delete "${permission.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        router.delete(`/admin/permissions/${permission.id}`, {
            preserveScroll: true,
        });
    }
};

// Group permissions by module - REACTIVE
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

</script>

<template>
    <Head title="Permissions Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Permissions Management
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage system permissions
                    </p>
                </div>
                
                <Button
                    v-if="hasPermission('permissions.create')"
                    @click="showCreateForm = !showCreateForm"
                    class="gap-2"
                >
                    <Plus class="h-4 w-4" />
                    <span class="hidden sm:inline">Create Permission</span>
                    <span class="sm:hidden">New</span>
                </Button>
            </div>

            <!-- Create Form -->
            <Card v-if="showCreateForm" class="border-primary/50">
                <CardHeader>
                    <CardTitle>Create New Permission</CardTitle>
                    <CardDescription>
                        Add a new permission to the system
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="createPermission" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="permission-name">Permission Name</Label>
                            <Input
                                id="permission-name"
                                v-model="form.name"
                                placeholder="e.g., posts.create, comments.delete"
                                :class="{ 'border-destructive': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Use format: module.action (e.g., users.view, posts.edit)
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="gap-2"
                            >
                                <Plus class="h-4 w-4" />
                                {{ form.processing ? 'Creating...' : 'Create' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="showCreateForm = false"
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Permissions by Module -->
            <div class="space-y-6">
                <Card
                    v-for="(permissions, module) in groupedPermissions"
                    :key="module"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 capitalize">
                            <Key class="h-5 w-5 text-primary" />
                            {{ module }}
                        </CardTitle>
                        <CardDescription>
                            {{ permissions.length }} {{ permissions.length === 1 ? 'permission' : 'permissions' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="permission in permissions"
                                :key="permission.id"
                                class="flex flex-col gap-3 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium">{{ permission.name }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="role in permission.roles"
                                            :key="role"
                                            variant="secondary"
                                            class="text-xs"
                                        >
                                            {{ role }}
                                        </Badge>
                                        <span
                                            v-if="permission.roles.length === 0"
                                            class="text-xs text-muted-foreground"
                                        >
                                            No roles assigned
                                        </span>
                                    </div>
                                </div>
                                
                                <Button
                                    v-if="hasPermission('permissions.delete')"
                                    variant="outline"
                                    size="sm"
                                    class="gap-2 text-destructive hover:bg-destructive hover:text-destructive-foreground sm:w-auto"
                                    @click="deletePermission(permission)"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div
                v-if="props.permissions.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 p-12 text-center dark:border-sidebar-border"
            >
                <Key class="h-12 w-12 text-muted-foreground mb-4" />
                <h3 class="text-lg font-semibold mb-2">No permissions found</h3>
                <p class="text-sm text-muted-foreground mb-4">
                    Get started by creating your first permission
                </p>
                <Button
                    v-if="hasPermission('permissions.create')"
                    @click="showCreateForm = true"
                    class="gap-2"
                >
                    <Plus class="h-4 w-4" />
                    Create Permission
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
