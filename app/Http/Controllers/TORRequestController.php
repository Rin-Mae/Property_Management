<?php

namespace App\Http\Controllers;

use App\Models\TORRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TORRequestController extends Controller
{
    /**
     * Show TOR request form
     */
    public function create()
    {
        return view('tor.create');
    }

    /**
     * Store new TOR request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'birthplace' => 'required|string|max:255',
            'birthdate' => 'required|date|before:today',
            'permanent_address' => 'nullable|string|max:500',
            'student_id' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'degree' => 'nullable|string|max:255',
            'year_of_graduation' => 'nullable|integer|min:1900|max:' . date('Y'),
            'purpose' => 'nullable|string|max:500',
        ]);

        $torRequest = TORRequest::create([
            'user_id' => auth()->id(),
            ...$validated,
            'status' => 'pending',
        ]);

        // Log activity
        ActivityLog::log(
            'created',
            'TOR request submitted for ' . $validated['full_name'],
            'TORRequest',
            $torRequest->id
        );

        return response()->json([
            'message' => 'TOR request submitted successfully',
            'request' => $torRequest,
        ], 201);
    }

    /**
     * Get all TOR requests for the authenticated user or all requests for admin
     */
    public function index()
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // If admin, return all requests; otherwise return only user's requests
        if ($user->role === 'admin') {
            $torRequests = TORRequest::withoutTrashed()->orderByDesc('created_at')->get();
        } else {
            $torRequests = TORRequest::where('user_id', $user->id)
                ->withoutTrashed()
                ->orderByDesc('created_at')
                ->get();
        }

        return response()->json($torRequests);
    }

    /**
     * Get a single TOR request
     */
    public function show(TORRequest $torRequest)
    {
        // Check authorization
        if ($torRequest->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($torRequest);
    }

    /**
     * Delete a TOR request (only if pending)
     */
    public function destroy(TORRequest $torRequest)
    {
        // Check authorization
        if ($torRequest->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only allow deletion of pending requests
        if ($torRequest->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete non-pending requests'], 403);
        }

        // Log activity before deletion
        ActivityLog::log(
            'deleted',
            'TOR request deleted for ' . $torRequest->full_name,
            'TORRequest',
            $torRequest->id
        );

        $torRequest->delete();

        return response()->json(['message' => 'TOR request deleted successfully']);
    }

    /**
     * Update a TOR request (status/remarks) -- admin or owner
     */
    public function update(Request $request, TORRequest $torRequest)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Only admins or the owner can update the request
        if ($user->role !== 'admin' && $torRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,approved,rejected,ready_for_pickup',
            'remarks' => 'nullable|string',
        ]);

        $oldStatus = $torRequest->status;
        
        $torRequest->status = $validated['status'];
        if (array_key_exists('remarks', $validated)) {
            $torRequest->remarks = $validated['remarks'];
        }

        // set completed_at for terminal statuses
        if (in_array($validated['status'], ['approved', 'ready_for_pickup'])) {
            $torRequest->completed_at = now();
        } else {
            $torRequest->completed_at = null;
        }

        $torRequest->save();

        // Log activity
        ActivityLog::log(
            'updated',
            'Status changed from ' . $oldStatus . ' to ' . $validated['status'],
            'TORRequest',
            $torRequest->id
        );

        return response()->json($torRequest);
    }
}