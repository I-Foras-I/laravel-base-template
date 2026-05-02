<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import ImageUpload from '@/components/ImageUpload.vue';

interface User {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    roles: { name: string }[];
    profile_photo_url: string;
}

interface Props {
    user: User;
    roles: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin/users' },
    { title: 'Users', href: '/admin/users' },
    { title: 'Edit User', href: `/admin/users/${props.user.id}/edit` },
];

const form = useForm({
    _method: 'put',
    name: props.user.name,
    email: props.user.email,
    roles: props.user.roles.map(r => r.name),
    is_active: Boolean(props.user.is_active),
    photo: null as File | null,
});

const toggleRole = (role: string) => {
    const index = form.roles.indexOf(role);
    if (index === -1) {
        form.roles.push(role);
    } else {
        form.roles.splice(index, 1);
    }
};

const submit = () => {
    form.post(`/admin/users/${props.user.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

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
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Edit User</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Update user information and roles
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- User Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>User Details</CardTitle>
                        <CardDescription>
                            Update the user's personal information
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label>Profile Photo</Label>
                            <ImageUpload 
                                v-model="form.photo" 
                                :current-image-url="user.profile_photo_url"
                                :default-initials="user.name.substring(0, 2).toUpperCase()"
                            />
                            <p v-if="form.errors.photo" class="text-sm text-destructive">
                                {{ form.errors.photo }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="John Doe"
                                :class="{ 'border-destructive': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="john@example.com"
                                :class="{ 'border-destructive': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="text-sm text-destructive">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="flex items-center space-x-2 pt-2">
                            <Checkbox
                                id="is_active"
                                :modelValue="form.is_active"
                                @update:modelValue="form.is_active = $event as boolean"
                            />
                            <Label
                                for="is_active"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                Active Account
                            </Label>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Inactive users cannot log in to the system.
                        </p>
                    </CardContent>
                </Card>

                <!-- Roles -->
                <Card>
                    <CardHeader>
                        <CardTitle>Roles</CardTitle>
                        <CardDescription>
                            Assign roles to define user permissions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="role in roles"
                                :key="role"
                                class="flex items-start space-x-3 space-y-0 rounded-md border p-4"
                            >
                                <Checkbox
                                    :id="`role-${role}`"
                                    :modelValue="form.roles.includes(role)"
                                    @update:modelValue="toggleRole(role)"
                                />
                                <div class="space-y-1 leading-none">
                                    <Label :for="`role-${role}`" class="capitalize">
                                        {{ role }}
                                    </Label>
                                    <p class="text-sm text-muted-foreground">
                                        Grants permissions associated with the {{ role }} role.
                                    </p>
                                </div>
                            </div>
                            <p v-if="form.errors.roles" class="text-sm text-destructive">
                                {{ form.errors.roles }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-4">
                <Link href="/admin/users">
                    <Button variant="outline">Cancel</Button>
                </Link>
                <Button @click="submit" :disabled="form.processing" class="gap-2">
                    <Save class="h-4 w-4" />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
