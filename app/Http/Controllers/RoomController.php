<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Type;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Display a listing of all rooms
     */
    public function index()
    {
        return Room::with(['type', 'amenities'])
            ->orderBy('room_number', 'asc')
            ->paginate(12);
    }

    /**
     * Show specific room
     */
    public function show(Room $room)
    {
        return $room->load(['type', 'amenities']);
    }

    /**
     * Store a new room
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'room_number' => 'required|string|unique:rooms,room_number',
            'type_id' => 'required|exists:types,id',
            'capacity' => 'required|integer|min:1|max:10',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance',
            'image_url' => 'nullable|url',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $room = Room::create($request->only([
            'name',
            'room_number',
            'type_id',
            'capacity',
            'price',
            'description',
            'status',
            'image_url',
        ]));

        if ($request->has('amenities')) {
            $room->amenities()->sync($request->input('amenities'));
        }

        return response()->json([
            'message' => 'Room created successfully',
            'room' => $room->load(['type', 'amenities'])
        ], 201);
    }

    /**
     * Update a room
     */
    public function update(Request $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'room_number' => 'required|string|unique:rooms,room_number,' . $room->id,
            'type_id' => 'required|exists:types,id',
            'capacity' => 'required|integer|min:1|max:10',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance',
            'image_url' => 'nullable|url',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $room->update($request->only([
            'name',
            'room_number',
            'type_id',
            'capacity',
            'price',
            'description',
            'status',
            'image_url',
        ]));

        if ($request->has('amenities')) {
            $room->amenities()->sync($request->input('amenities'));
        }

        return response()->json([
            'message' => 'Room updated successfully',
            'room' => $room->load(['type', 'amenities'])
        ]);
    }

    /**
     * Delete a room
     */
    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully'
        ]);
    }

    /**
     * Update room status
     */
    public function updateStatus(Request $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $room->update(['status' => $request->input('status')]);

        return response()->json([
            'message' => 'Room status updated successfully',
            'room' => $room
        ]);
    }

    /**
     * Get all room types (for dropdown)
     */
    public function getTypes()
    {
        return Type::all();
    }

    /**
     * Get all amenities (for checkboxes)
     */
    public function getAmenities()
    {
        return Amenity::all();
    }
}
