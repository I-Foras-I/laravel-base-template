export interface Role {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
    permissions?: Permission[];
    users_count?: number;
}

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
    roles?: Role[];
}

export interface UserWithRoles extends User {
    roles: Role[];
    permissions: Permission[];
    all_permissions: string[];
}
