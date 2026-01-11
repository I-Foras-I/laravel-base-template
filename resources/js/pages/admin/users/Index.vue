<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermissions } from '@/composables/usePermissions';
import { useConfirm } from '@/composables/useConfirm';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Users as UsersIcon, Shield, Mail, Calendar, Filter, Search, Plus, MoreHorizontal, Pencil, Trash2, CheckCircle, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import ExportButton from '@/components/ExportButton.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    is_active: boolean;
    roles: string[];
    profile_photo_url: string;
    created_at: string;
}

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    roles: string[];
    filters: {
        search?: string;
        role?: string;
        status?: string;
    };
}

const props = defineProps<Props>();
const { hasPermission } = usePermissions();
const { confirm } = useConfirm();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin/users' },
    { title: 'Users', href: '/admin/users' },
];

const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || 'all');
const selectedStatus = ref(props.filters.status || 'all');

const applyFilters = () => {
    router.get('/admin/users', {
        search: searchQuery.value || undefined,
        role: selectedRole.value !== 'all' ? selectedRole.value : undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedRole.value = 'all';
    selectedStatus.value = 'all';
    router.get('/admin/users', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteUser = async (user: User) => {
    const confirmed = await confirm({
        title: 'Delete User',
        description: `Are you sure you want to delete "${user.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        router.delete(`/admin/users/${user.id}`, {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Users Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Users Management
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage users, roles, and permissions
                    </p>
                </div>
                <div class="flex gap-2">
                    <ExportButton 
                        v-if="hasPermission('users.export')" 
                        :filters="{ search: searchQuery, role: selectedRole, status: selectedStatus }"
                    />
                    <Link v-if="hasPermission('users.create')" href="/admin/users/create">
                        <Button class="w-full sm:w-auto gap-2">
                            <Plus class="h-4 w-4" />
                            Create User
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="h-5 w-5" />
                        Filters
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Search -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Search</label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Name or email..."
                                    class="pl-9"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>

                        <!-- Role Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Role</label>
                            <Select v-model="selectedRole">
                                <SelectTrigger>
                                    <SelectValue placeholder="All roles" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All roles</SelectItem>
                                    <SelectItem 
                                        v-for="role in roles" 
                                        :key="role" 
                                        :value="role"
                                        class="capitalize"
                                    >
                                        {{ role }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Status Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Status</label>
                            <Select v-model="selectedStatus">
                                <SelectTrigger>
                                    <SelectValue placeholder="All status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All status</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-end gap-2">
                            <Button @click="applyFilters" class="flex-1">
                                Apply
                            </Button>
                            <Button @click="clearFilters" variant="outline">
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Users List -->
            <Card>
                <CardHeader>
                    <CardTitle>Users List</CardTitle>
                    <CardDescription>
                        Total: {{ users.total }} users
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4 font-medium">User</th>
                                    <th class="text-left p-4 font-medium">Roles</th>
                                    <th class="text-left p-4 font-medium">Status</th>
                                    <th class="text-left p-4 font-medium">Joined</th>
                                    <th class="text-right p-4 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="border-b hover:bg-muted/50 transition-colors"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <Avatar>
                                                <AvatarImage :src="user.profile_photo_url" />
                                                <AvatarFallback>{{ user.name.substring(0, 2).toUpperCase() }}</AvatarFallback>
                                            </Avatar>
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ user.name }}</span>
                                                <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge
                                                v-for="role in user.roles"
                                                :key="role"
                                                variant="secondary"
                                                class="text-xs capitalize"
                                            >
                                                {{ role }}
                                            </Badge>
                                            <span v-if="user.roles.length === 0" class="text-sm text-muted-foreground">No roles</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge :variant="user.is_active ? 'default' : 'secondary'" class="gap-1">
                                            <component :is="user.is_active ? CheckCircle : XCircle" class="h-3 w-3" />
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                            <Calendar class="h-4 w-4" />
                                            {{ formatDate(user.created_at) }}
                                        </div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                <DropdownMenuItem as-child>
                                                    <Link :href="`/admin/users/${user.id}`" class="flex items-center cursor-pointer">
                                                        <UsersIcon class="mr-2 h-4 w-4" />
                                                        View Details
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('users.edit')" as-child>
                                                    <Link :href="`/admin/users/${user.id}/edit`" class="flex items-center cursor-pointer">
                                                        <Pencil class="mr-2 h-4 w-4" />
                                                        Edit User
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem 
                                                    v-if="hasPermission('users.delete')" 
                                                    class="text-destructive focus:text-destructive cursor-pointer"
                                                    @click="deleteUser(user)"
                                                >
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Delete User
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4">
                        <Card
                            v-for="user in users.data"
                            :key="user.id"
                            class="overflow-hidden"
                        >
                            <CardContent class="p-4 space-y-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <Avatar>
                                            <AvatarImage :src="user.profile_photo_url" />
                                            <AvatarFallback>{{ user.name.substring(0, 2).toUpperCase() }}</AvatarFallback>
                                        </Avatar>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ user.name }}</span>
                                            <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                                        </div>
                                    </div>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="-mt-2 -mr-2">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/admin/users/${user.id}`" class="flex items-center">
                                                    <UsersIcon class="mr-2 h-4 w-4" />
                                                    View Details
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-if="hasPermission('users.edit')" as-child>
                                                <Link :href="`/admin/users/${user.id}/edit`" class="flex items-center">
                                                    <Pencil class="mr-2 h-4 w-4" />
                                                    Edit User
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem 
                                                v-if="hasPermission('users.delete')" 
                                                class="text-destructive"
                                                @click="deleteUser(user)"
                                            >
                                                <Trash2 class="mr-2 h-4 w-4" />
                                                Delete User
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="role in user.roles"
                                        :key="role"
                                        variant="secondary"
                                        class="text-xs capitalize"
                                    >
                                        {{ role }}
                                    </Badge>
                                    <Badge :variant="user.is_active ? 'default' : 'secondary'" class="gap-1">
                                        <component :is="user.is_active ? CheckCircle : XCircle" class="h-3 w-3" />
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </div>

                                <div class="text-xs text-muted-foreground pt-2 border-t">
                                    Joined {{ formatDate(user.created_at) }}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.last_page > 1" class="mt-6 flex items-center justify-between border-t pt-4">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ (users.current_page - 1) * users.per_page + 1 }} to 
                            {{ Math.min(users.current_page * users.per_page, users.total) }} of 
                            {{ users.total }} results
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in users.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md',
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted hover:bg-muted/80',
                                    !link.url && 'opacity-50 cursor-not-allowed',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
