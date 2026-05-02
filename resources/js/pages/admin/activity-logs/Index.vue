<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type BreadcrumbItem, type ActivityLog } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { FileText, User, Calendar, Filter, Search, Eye } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    logs: {
        data: ActivityLog[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        user_id?: number;
        event?: string;
        subject_type?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin/activity-logs' },
    { title: 'Activity Logs', href: '/admin/activity-logs' },
];

const searchQuery = ref(props.filters.search || '');
const selectedEvent = ref(props.filters.event || 'all');
const selectedSubjectType = ref(props.filters.subject_type || 'all');

const applyFilters = () => {
    router.get('/admin/activity-logs', {
        search: searchQuery.value || undefined,
        event: selectedEvent.value !== 'all' ? selectedEvent.value : undefined,
        subject_type: selectedSubjectType.value !== 'all' ? selectedSubjectType.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedEvent.value = 'all';
    selectedSubjectType.value = 'all';
    router.get('/admin/activity-logs', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getEventBadgeVariant = (event: string | null) => {
    switch (event) {
        case 'created':
            return 'default'; // Green
        case 'updated':
            return 'secondary'; // Blue
        case 'deleted':
            return 'destructive'; // Red
        default:
            return 'outline';
    }
};

const getModelName = (subjectType: string | null) => {
    if (!subjectType) return 'Unknown';
    const parts = subjectType.split('\\');
    return parts[parts.length - 1];
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Activity Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Activity Logs</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        View all system activity and user actions
                    </p>
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
                                    placeholder="Search description..."
                                    class="pl-9"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>

                        <!-- Event Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Event</label>
                            <Select v-model="selectedEvent">
                                <SelectTrigger>
                                    <SelectValue placeholder="All events" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All events</SelectItem>
                                    <SelectItem value="created">Created</SelectItem>
                                    <SelectItem value="updated">Updated</SelectItem>
                                    <SelectItem value="deleted">Deleted</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Model Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Model</label>
                            <Select v-model="selectedSubjectType">
                                <SelectTrigger>
                                    <SelectValue placeholder="All models" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All models</SelectItem>
                                    <SelectItem value="App\Models\User">User</SelectItem>
                                    <SelectItem value="Spatie\Permission\Models\Role">Role</SelectItem>
                                    <SelectItem value="Spatie\Permission\Models\Permission">Permission</SelectItem>
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

            <!-- Logs List -->
            <Card>
                <CardHeader>
                    <CardTitle>Activity History</CardTitle>
                    <CardDescription>
                        Total: {{ logs.total }} activities
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4 font-medium">User</th>
                                    <th class="text-left p-4 font-medium">Event</th>
                                    <th class="text-left p-4 font-medium">Model</th>
                                    <th class="text-left p-4 font-medium">Description</th>
                                    <th class="text-left p-4 font-medium">Date</th>
                                    <th class="text-right p-4 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="log in logs.data"
                                    :key="log.id"
                                    class="border-b hover:bg-muted/50 transition-colors"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <User class="h-4 w-4 text-muted-foreground" />
                                            <span class="font-medium">
                                                {{ log.causer?.name || 'System' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge :variant="getEventBadgeVariant(log.event)">
                                            {{ log.event || 'unknown' }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm">{{ getModelName(log.subject_type) }}</span>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm text-muted-foreground">
                                            {{ log.description }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                            <Calendar class="h-4 w-4" />
                                            {{ formatDate(log.created_at) }}
                                        </div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <Link
                                            :href="`/admin/activity-logs/${log.id}`"
                                            class="inline-flex items-center gap-2 text-sm text-primary hover:underline"
                                        >
                                            <Eye class="h-4 w-4" />
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="logs.data.length === 0" class="py-12 text-center">
                            <FileText class="mx-auto h-12 w-12 text-muted-foreground" />
                            <h3 class="mt-4 text-lg font-semibold">No activity logs found</h3>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Try adjusting your filters
                            </p>
                        </div>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4">
                        <Card
                            v-for="log in logs.data"
                            :key="log.id"
                            class="overflow-hidden"
                        >
                            <CardContent class="p-4 space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-2">
                                        <User class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-medium">
                                            {{ log.causer?.name || 'System' }}
                                        </span>
                                    </div>
                                    <Badge :variant="getEventBadgeVariant(log.event)">
                                        {{ log.event }}
                                    </Badge>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm font-medium">
                                        {{ getModelName(log.subject_type) }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ log.description }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t">
                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                        <Calendar class="h-3 w-3" />
                                        {{ formatDate(log.created_at) }}
                                    </div>
                                    <Link
                                        :href="`/admin/activity-logs/${log.id}`"
                                        class="inline-flex items-center gap-1 text-sm text-primary hover:underline"
                                    >
                                        <Eye class="h-4 w-4" />
                                        View
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Empty State Mobile -->
                        <div v-if="logs.data.length === 0" class="py-12 text-center">
                            <FileText class="mx-auto h-12 w-12 text-muted-foreground" />
                            <h3 class="mt-4 text-lg font-semibold">No activity logs found</h3>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Try adjusting your filters
                            </p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="logs.last_page > 1" class="mt-6 flex items-center justify-between border-t pt-4">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ (logs.current_page - 1) * logs.per_page + 1 }} to 
                            {{ Math.min(logs.current_page * logs.per_page, logs.total) }} of 
                            {{ logs.total }} results
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in logs.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md',
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted hover:bg-muted/80',
                                    !link.url && 'opacity-50 cursor-not-allowed',
                                ]"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
