import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface Flash {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    flash: Flash;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles?: string[];
    permissions?: string[];
    profile_photo_url?: string;
}

export interface ActivityLog {
    id: number;
    log_name: string | null;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    event: string | null;
    causer_type: string | null;
    causer_id: number | null;
    properties: {
        attributes?: Record<string, any>;
        old?: Record<string, any>;
    };
    batch_uuid: string | null;
    created_at: string;
    causer?: User;
    subject?: any;
}

export type BreadcrumbItemType = BreadcrumbItem;
