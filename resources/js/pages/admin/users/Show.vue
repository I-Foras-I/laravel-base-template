<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { usePermissions } from '@/composables/usePermissions';
import { useConfirm } from '@/composables/useConfirm';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, User, Mail, Calendar, Shield, Activity, Pencil, Trash2, CheckCircle, XCircle } from 'lucide-vue-next';

import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    is_active: boolean;
    created_at: string;
    roles: { name: string }[];
    permissions: { name: string }[];
    profile_photo_url: string;
}

interface ActivityLog {
    id: number;
    description: string;
    event: string;
    created_at: string;
    causer?: { name: string };
}

interface Props {
    user: User;
    activities: ActivityLog[];
}

const props = defineProps<Props>();
const { hasPermission } = usePermissions();
const { confirm } = useConfirm();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin/users' },
    { title: 'Users', href: '/admin/users' },
    { title: props.user.name, href: `/admin/users/${props.user.id}` },
];

const deleteUser = async () => {
    const confirmed = await confirm({
        title: 'Delete User',
        description: `Are you sure you want to delete "${props.user.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        router.delete(`/admin/users/${props.user.id}`);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getEventBadgeVariant = (event: string) => {
    switch (event) {
        case 'created': return 'default';
        case 'updated': return 'secondary';
        case 'deleted': return 'destructive';
        default: return 'outline';
    }
};
</script>

<template>
    <Head :title="user.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <Link
                        href="/admin/users"
                        class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground mb-2"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to Users
                    </Link>
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">{{ user.name }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        User Details and Activity
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link v-if="hasPermission('users.edit')" :href="`/admin/users/${user.id}/edit`">
                        <Button variant="outline" class="gap-2">
                            <Pencil class="h-4 w-4" />
                            Edit
                        </Button>
                    </Link>
                    <Button 
                        v-if="hasPermission('users.delete')" 
                        variant="destructive" 
                        class="gap-2"
                        @click="deleteUser"
                    >
                        <Trash2 class="h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- User Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="h-5 w-5" />
                            Personal Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="flex items-center gap-4 border-b pb-6">
                            <Avatar class="h-20 w-20">
                                <AvatarImage :src="user.profile_photo_url" />
                                <AvatarFallback class="text-lg">{{ user.name.substring(0, 2).toUpperCase() }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <h3 class="text-xl font-medium">{{ user.name }}</h3>
                                <p class="text-muted-foreground">{{ user.email }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Full Name</p>
                                <p class="font-medium">{{ user.name }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Email Address</p>
                                <div class="flex items-center gap-2">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <p>{{ user.email }}</p>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Status</p>
                                <Badge :variant="user.is_active ? 'default' : 'secondary'" class="gap-1">
                                    <component :is="user.is_active ? CheckCircle : XCircle" class="h-3 w-3" />
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-muted-foreground">Joined Date</p>
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <p>{{ formatDate(user.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Roles & Permissions -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="h-5 w-5" />
                            Roles & Permissions
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground mb-2">Assigned Roles</p>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="role in user.roles"
                                    :key="role.name"
                                    variant="secondary"
                                    class="capitalize"
                                >
                                    {{ role.name }}
                                </Badge>
                                <p v-if="user.roles.length === 0" class="text-sm text-muted-foreground">
                                    No roles assigned
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Activity -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Activity class="h-5 w-5" />
                            Recent Activity
                        </CardTitle>
                        <CardDescription>
                            Last 5 actions performed by this user
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="activities.length > 0" class="space-y-4">
                            <div
                                v-for="activity in activities"
                                :key="activity.id"
                                class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0"
                            >
                                <div class="space-y-1">
                                    <p class="font-medium">{{ activity.description }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ formatDate(activity.created_at) }}
                                    </p>
                                </div>
                                <Badge :variant="getEventBadgeVariant(activity.event)">
                                    {{ activity.event }}
                                </Badge>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-muted-foreground">
                            No recent activity found
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
