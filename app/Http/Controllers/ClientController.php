<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of all clients
     */
    public function index()
    {
        return Client::orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Show specific client
     */
    public function show(Client $client)
    {
        return $client;
    }

    /**
     * Store a new client
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Client::create($request->all());

        return response()->json([
            'message' => 'Client created successfully',
            'client' => $client
        ], 201);
    }

    /**
     * Update a client
     */
    public function update(Request $request, Client $client)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client->update($request->all());

        return response()->json([
            'message' => 'Client updated successfully',
            'client' => $client
        ]);
    }

    /**
     * Delete a client (soft delete)
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json([
            'message' => 'Client deleted successfully'
        ]);
    }

    /**
     * Restore a deleted client
     */
    public function restore($id)
    {
        $client = Client::withTrashed()->findOrFail($id);
        $client->restore();

        return response()->json([
            'message' => 'Client restored successfully',
            'client' => $client
        ]);
    }
}
