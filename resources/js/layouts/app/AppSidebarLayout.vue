<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import 'vue-sonner/style.css'
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { usePage } from '@inertiajs/vue3';
import { watch, nextTick } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// Watch for flash messages and display toasts
const page = usePage();

watch(
    () => page.props.flash,
    (flash) => {
        nextTick(() => {
            if (flash?.success) {
                toast.success(flash.success);
            }
            if (flash?.error) {
                toast.error(flash.error);
            }
            if (flash?.warning) {
                toast.warning(flash.warning);
            }
            if (flash?.info) {
                toast.info(flash.info);
            }
        });
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <!-- Confirmation Dialog -->
    <ConfirmDialog />
    
    <!-- Toast Notifications -->
    <Toaster
        position="top-right" 
        :duration="3000"
        closeButton
        richColors
    />
    
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />            
        </AppContent>
    </AppShell>
</template>
