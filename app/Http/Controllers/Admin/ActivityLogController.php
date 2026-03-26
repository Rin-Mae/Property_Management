<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Get activity logs for admin only
     */
    public function getActivityLogs()
    {
        // Ensure user is admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $activityLogs = ActivityLog::with('user')
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_name' => $log->user->first_name . ' ' . $log->user->last_name,
                    'action' => $log->action,
                    'description' => $log->description,
                    'model' => $log->model,
                    'created_at' => $log->created_at->format('M d, Y H:i A'),
                ];
            });

        return response()->json([
            'activity_logs' => $activityLogs,
        ]);
    }
}
