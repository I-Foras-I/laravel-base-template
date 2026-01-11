<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Shield, Users, Key, FileText } from 'lucide-vue-next';
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import AppLogo from './AppLogo.vue';

const { hasAnyPermission } = usePermissions();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const adminNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];
    
    if (hasAnyPermission(['roles.view', 'permissions.view', 'users.view'])) {
        if (hasAnyPermission(['roles.view'])) {
            items.push({
                title: 'Roles',
                href: '/admin/roles',
                icon: Shield,
            });
        }
        
        if (hasAnyPermission(['permissions.view'])) {
            items.push({
                title: 'Permissions',
                href: '/admin/permissions',
                icon: Key,
            });
        }
        
        if (hasAnyPermission(['users.view'])) {
            items.push({
                title: 'Users',
                href: '/admin/users',
                icon: Users,
            });
        }

        if (hasAnyPermission(['activity-logs.view'])) {
            items.push({
                title: 'Activity Logs',
                href: '/admin/activity-logs',
                icon: FileText,
            });
        }
    }
    
    return items;
});


const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain v-if="adminNavItems.length > 0" :items="adminNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
