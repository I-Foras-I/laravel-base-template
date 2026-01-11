<?php

namespace App\Exports;

use App\Models\User;
use Generator;

class UsersExport
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Get the generator for the export
     */
    public function generator(): Generator
    {
        $query = User::with('roles')
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($this->filters['role'] ?? null, function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->when(isset($this->filters['status']), function ($query) {
                if ($this->filters['status'] === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->filters['status'] === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->limit(100); // Safety limit

        foreach ($query->cursor() as $user) {
            yield [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Roles' => $user->roles->pluck('name')->implode(', ') ?: 'No roles',
                'Status' => $user->is_active ? 'Active' : 'Inactive',
                'Email Verified' => $user->email_verified_at ? 'Yes' : 'No',
                'Created At' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        }
    }
}
