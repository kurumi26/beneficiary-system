<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when(filled($request->input('search')), function ($query) use ($request): void {
                $search = (string) $request->input('search');
                $query->where(function ($nested) use ($search): void {
                    $nested->where('action', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(filled($request->input('user_id')), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('activity-logs.index', [
            'logs' => $logs,
            'admins' => User::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->all(),
        ]);
    }
}
