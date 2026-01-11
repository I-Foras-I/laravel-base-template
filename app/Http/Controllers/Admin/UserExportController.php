<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;

class UserExportController extends Controller
{
    /**
     * Export users to PDF
     */
    public function exportPdf(Request $request)
    {
        $this->authorize('export', User::class);

        $filters = $request->only(['search', 'role', 'status']);

        $users = User::with('roles')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($filters['role'] ?? null, function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                if ($filters['status'] === 'active') {
                    $query->where('is_active', true);
                } elseif ($filters['status'] === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->limit(100)
            ->get();

        $pdf = Pdf::loadView('exports.users-pdf', [
            'users' => $users,
            'filters' => $filters,
            'generatedAt' => now(),
            'generatedBy' => auth()->user()->name,
        ]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');

        // Stream PDF to browser (opens in new tab)
        return $pdf->stream('users-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export users to Excel
     */
    public function exportExcel(Request $request)
    {
        $this->authorize('export', User::class);

        $filters = $request->only(['search', 'role', 'status']);
        $export = new UsersExport($filters);

        $filename = 'users-report-' . now()->format('Y-m-d') . '.xlsx';

        return SimpleExcelWriter::streamDownload($filename)
            ->addRows($export->generator())
            ->toBrowser();
    }

    /**
     * Export users to CSV
     */
    public function exportCsv(Request $request)
    {
        $this->authorize('export', User::class);

        $filters = $request->only(['search', 'role', 'status']);
        $export = new UsersExport($filters);

        $filename = 'users-report-' . now()->format('Y-m-d') . '.csv';

        return SimpleExcelWriter::streamDownload($filename)
            ->addRows($export->generator())
            ->toBrowser();
    }
}
