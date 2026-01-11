<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Activity::class);

        $logs = Activity::with(['causer', 'subject'])
            ->when($request->user_id, fn($q) => $q->where('causer_id', $request->user_id))
            ->when($request->event, fn($q) => $q->where('event', $request->event))
            ->when($request->subject_type, fn($q) => $q->where('subject_type', $request->subject_type))
            ->when($request->search, function($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/activity-logs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['user_id', 'event', 'subject_type', 'search']),
        ]);
    }

    /**
     * Display the specified activity log.
     */
    public function show(Activity $activity)
    {
        $this->authorize('view', $activity);

        $activity->load(['causer', 'subject']);

        return Inertia::render('admin/activity-logs/Show', [
            'log' => $activity,
        ]);
    }
}
