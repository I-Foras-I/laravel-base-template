<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { usePermissions } from '@/composables/usePermissions';
import { useConfirm } from '@/composables/useConfirm';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Shield, Users, Edit, Trash2, Plus } from 'lucide-vue-next';

interface Role {
    id: number;
    name: string;
    guard_name: string;
    users_count: number;
    permissions: string[];
    created_at: string;
    updated_at: string;
}

interface Props {
    roles: Role[];
}

const props = defineProps<Props>();
const { hasPermission } = usePermissions();
const { confirm } = useConfirm();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin/roles' },
    { title: 'Roles', href: '/admin/roles' },
];

const isProtectedRole = (roleName: string) => {
    return ['super-admin', 'user'].includes(roleName);
};

const deleteRole = async (role: Role) => {
    if (isProtectedRole(role.name)) {
        await confirm({
            title: 'Cannot Delete Protected Role',
            description: `The role "${role.name}" is protected and cannot be deleted.`,
            confirmText: 'OK',
            cancelText: '',
            variant: 'default',
        });
        return;
    }
    
    const confirmed = await confirm({
        title: 'Delete Role',
        description: `Are you sure you want to delete "${role.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        router.delete(`/admin/roles/${role.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Roles Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Roles Management
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage user roles and their permissions
                    </p>
                </div>
                
                <Link
                    v-if="hasPermission('roles.create')"
                    :href="'/admin/roles/create'"
                >
                    <Button class="gap-2">
                        <Plus class="h-4 w-4" />
                        <span class="hidden sm:inline">Create Role</span>
                        <span class="sm:hidden">New</span>
                    </Button>
                </Link>
            </div>

            <!-- Roles Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card
                    v-for="role in props.roles"
                    :key="role.id"
                    class="relative overflow-hidden transition-all hover:shadow-md dark:hover:shadow-lg"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <Shield class="h-5 w-5 text-primary" />
                                <CardTitle class="text-lg capitalize">
                                    {{ role.name }}
                                </CardTitle>
                            </div>
                            <Badge
                                v-if="isProtectedRole(role.name)"
                                variant="secondary"
                                class="text-xs"
                            >
                                Protected
                            </Badge>
                        </div>
                        <CardDescription class="flex items-center gap-1 text-sm">
                            <Users class="h-3.5 w-3.5" />
                            {{ role.users_count }} {{ role.users_count === 1 ? 'user' : 'users' }}
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="space-y-4">
                        <!-- Permissions -->
                        <div>
                            <p class="text-xs font-medium text-muted-foreground mb-2">
                                Permissions ({{ role.permissions.length }})
                            </p>
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="permission in role.permissions.slice(0, 3)"
                                    :key="permission"
                                    variant="outline"
                                    class="text-xs"
                                >
                                    {{ permission }}
                                </Badge>
                                <Badge
                                    v-if="role.permissions.length > 3"
                                    variant="outline"
                                    class="text-xs"
                                >
                                    +{{ role.permissions.length - 3 }} more
                                </Badge>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2">
                            <Link
                                v-if="hasPermission('roles.edit')"
                                :href="`/admin/roles/${role.id}/edit`"
                                class="flex-1"
                            >
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full gap-2"
                                >
                                    <Edit class="h-3.5 w-3.5" />
                                    Edit
                                </Button>
                            </Link>
                            
                            <Button
                                v-if="hasPermission('roles.delete') && !isProtectedRole(role.name)"
                                variant="outline"
                                size="sm"
                                class="gap-2 text-destructive hover:bg-destructive hover:text-destructive-foreground"
                                @click="deleteRole(role)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                                Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div
                v-if="props.roles.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 p-12 text-center dark:border-sidebar-border"
            >
                <Shield class="h-12 w-12 text-muted-foreground mb-4" />
                <h3 class="text-lg font-semibold mb-2">No roles found</h3>
                <p class="text-sm text-muted-foreground mb-4">
                    Get started by creating your first role
                </p>
                <Link
                    v-if="hasPermission('roles.create')"
                    :href="'/admin/roles/create'"
                >
                    <Button class="gap-2">
                        <Plus class="h-4 w-4" />
                        Create Role
                    </Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
