<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type ActivityLog } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, User, FileText, Code } from 'lucide-vue-next';

interface Props {
    log: ActivityLog;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin/activity-logs' },
    { title: 'Activity Logs', href: '/admin/activity-logs' },
    { title: `Log #${props.log.id}`, href: `/admin/activity-logs/${props.log.id}` },
];

const getEventBadgeVariant = (event: string | null) => {
    switch (event) {
        case 'created':
            return 'default';
        case 'updated':
            return 'secondary';
        case 'deleted':
            return 'destructive';
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

const hasChanges = () => {
    return props.log.properties?.old || props.log.properties?.attributes;
};

const getChangedFields = () => {
    const old = props.log.properties?.old || {};
    const attributes = props.log.properties?.attributes || {};
    const allKeys = new Set([...Object.keys(old), ...Object.keys(attributes)]);
    
    return Array.from(allKeys).map(key => ({
        field: key,
        old: old[key],
        new: attributes[key],
        changed: JSON.stringify(old[key]) !== JSON.stringify(attributes[key]),
    }));
};

const formatValue = (value: any): string => {
    if (value === null || value === undefined) return 'null';
    if (typeof value === 'object') return JSON.stringify(value, null, 2);
    if (typeof value === 'boolean') return value ? 'true' : 'false';
    return String(value);
};
</script>

<template>
    <Head :title="`Activity Log #${log.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <Link
                        href="/admin/activity-logs"
                        class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground mb-2"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to Activity Logs
                    </Link>
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Activity Log Details</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Log ID: #{{ log.id }}
                    </p>
                </div>
            </div>

            <!-- Main Info Card -->
            <Card>
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <div>
                            <CardTitle>{{ log.description }}</CardTitle>
                            <CardDescription class="mt-2">
                                {{ formatDate(log.created_at) }}
                            </CardDescription>
                        </div>
                        <Badge :variant="getEventBadgeVariant(log.event)">
                            {{ log.event || 'unknown' }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- User Info -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm font-medium">
                                <User class="h-4 w-4 text-muted-foreground" />
                                Performed By
                            </div>
                            <div class="pl-6">
                                <p class="font-medium">{{ log.causer?.name || 'System' }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ log.causer?.email || 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm font-medium">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                Model
                            </div>
                            <div class="pl-6">
                                <p class="font-medium">{{ getModelName(log.subject_type) }}</p>
                                <p class="text-sm text-muted-foreground">
                                    ID: {{ log.subject_id || 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Metadata -->
                    <div class="border-t pt-4">
                        <h3 class="text-sm font-medium mb-3">Metadata</h3>
                        <div class="grid gap-3 sm:grid-cols-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Log Name:</span>
                                <span class="font-medium">{{ log.log_name || 'default' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Batch UUID:</span>
                                <span class="font-mono text-xs">{{ log.batch_uuid || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Changes Card (only for updates) -->
            <Card v-if="hasChanges()">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Code class="h-5 w-5" />
                        Changes
                    </CardTitle>
                    <CardDescription>
                        Fields that were modified in this action
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="change in getChangedFields()"
                            :key="change.field"
                            class="border rounded-lg p-4"
                            :class="change.changed ? 'bg-muted/50' : ''"
                        >
                            <div class="font-medium mb-3 flex items-center gap-2">
                                {{ change.field }}
                                <Badge v-if="change.changed" variant="outline" class="text-xs">
                                    Modified
                                </Badge>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <!-- Old Value -->
                                <div>
                                    <div class="text-xs font-medium text-muted-foreground mb-2">
                                        Old Value
                                    </div>
                                    <div class="bg-destructive/10 border border-destructive/20 rounded p-3">
                                        <pre class="text-xs font-mono whitespace-pre-wrap break-all">{{ formatValue(change.old) }}</pre>
                                    </div>
                                </div>

                                <!-- New Value -->
                                <div>
                                    <div class="text-xs font-medium text-muted-foreground mb-2">
                                        New Value
                                    </div>
                                    <div class="bg-primary/10 border border-primary/20 rounded p-3">
                                        <pre class="text-xs font-mono whitespace-pre-wrap break-all">{{ formatValue(change.new) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Properties Card (for created/deleted) -->
            <Card v-if="!hasChanges() && log.properties?.attributes">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Code class="h-5 w-5" />
                        Properties
                    </CardTitle>
                    <CardDescription>
                        Data associated with this action
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="bg-muted rounded-lg p-4">
                        <pre class="text-sm font-mono whitespace-pre-wrap break-all">{{ JSON.stringify(log.properties.attributes, null, 2) }}</pre>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex justify-between">
                <Link href="/admin/activity-logs">
                    <Button variant="outline">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to List
                    </Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
