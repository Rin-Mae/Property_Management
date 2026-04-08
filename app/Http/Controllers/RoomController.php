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
     * Format image URL to include /storage/ prefix (only for uploaded files)
     */
    private function formatImageUrl($imageUrl)
    {
        if (!$imageUrl) {
            return null;
        }
        
        // Don't modify public folder images (standard/deluxe templates)
        if (str_starts_with($imageUrl, 'images/') || str_starts_with($imageUrl, '/images/')) {
            return str_starts_with($imageUrl, '/') ? $imageUrl : '/' . $imageUrl;
        }
        
        // Handle uploaded room images that need /storage/ prefix
        if (str_starts_with($imageUrl, '/storage/')) {
            return $imageUrl;
        } elseif (str_starts_with($imageUrl, '/rooms/')) {
            return '/storage' . $imageUrl;
        } elseif (str_starts_with($imageUrl, 'rooms/')) {
            return '/storage/' . $imageUrl;
        } else {
            return '/storage/' . ltrim($imageUrl, '/');
        }
    }
    /**
     * Display a listing of all rooms
     */
    public function index(Request $request)
    {
        $query = Room::with(['type', 'amenities']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $rooms = $query->orderBy('room_number', 'asc')
            ->paginate(12);

        // Format image URLs in response
        $rooms->getCollection()->transform(function ($room) {
            if ($room->image_url) {
                $room->image_url = $this->formatImageUrl($room->image_url);
            }
            return $room;
        });

        return $rooms;
    }

    /**
     * Show specific room
     */
    public function show(Room $room)
    {
        $room->load(['type', 'amenities']);
        if ($room->image_url) {
            $room->image_url = $this->formatImageUrl($room->image_url);
        }
        return $room;
    }

    /**
     * Store a new room
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:rooms,name',
            'room_number' => 'required|string|unique:rooms,room_number',
            'type_id' => 'required|exists:types,id',
            'capacity' => 'required|integer|min:1|max:10',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:25600',
            'amenities' => 'nullable|array',
            'amenities.*' => 'nullable|exists:amenities,id',
        ], [
            'name.unique' => 'A room with this name already exists. Please choose a different room name.',
            'room_number.unique' => 'Room number #:input already exists. Please choose a different room number.',
            'room_number.required' => 'Room number is required and must be unique.',
            'name.required' => 'Room name is required.',
            'type_id.required' => 'Room type is required.',
            'type_id.exists' => 'The selected room type is invalid.',
            'capacity.required' => 'Room capacity is required.',
            'capacity.integer' => 'Room capacity must be a whole number.',
            'capacity.min' => 'Room capacity must be at least 1 guest.',
            'capacity.max' => 'Room capacity cannot exceed 10 guests.',
            'price.required' => 'Room price is required.',
            'price.numeric' => 'Room price must be a valid number.',
            'price.min' => 'Room price must be greater than or equal to 0.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image may not be greater than 25 megabytes.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'name',
            'room_number',
            'type_id',
            'capacity',
            'price',
            'description',
            'status',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('rooms', 'public');
            $data['image_url'] = '/storage/' . $imagePath;
        }

        $room = Room::create($data);

        if ($request->has('amenities') && is_array($request->input('amenities'))) {
            $amenities = array_filter($request->input('amenities'), fn($id) => !empty($id));
            if (!empty($amenities)) {
                $room->amenities()->sync($amenities);
            }
        }

        $room->load(['type', 'amenities']);
        if ($room->image_url) {
            $room->image_url = $this->formatImageUrl($room->image_url);
        }

        return response()->json([
            'message' => 'Room created successfully',
            'room' => $room
        ], 201);
    }

    /**
     * Update a room
     */
    public function update(Request $request, Room $room)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:rooms,name,' . $room->id,
            'room_number' => 'required|string|unique:rooms,room_number,' . $room->id,
            'type_id' => 'required|exists:types,id',
            'capacity' => 'required|integer|min:1|max:10',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:25600',
            'amenities' => 'nullable|array',
            'amenities.*' => 'nullable|exists:amenities,id',
        ], [
            'room_number.unique' => 'Room number #:input is already in use by another room. Please choose a different room number.',
            'room_number.required' => 'Room number is required.',
            'name.required' => 'Room name is required.',
            'type_id.required' => 'Room type is required.',
            'type_id.exists' => 'The selected room type is invalid.',
            'capacity.required' => 'Room capacity is required.',
            'capacity.integer' => 'Room capacity must be a whole number.',
            'capacity.min' => 'Room capacity must be at least 1 guest.',
            'capacity.max' => 'Room capacity cannot exceed 10 guests.',
            'price.required' => 'Room price is required.',
            'price.numeric' => 'Room price must be a valid number.',
            'price.min' => 'Room price must be greater than or equal to 0.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image may not be greater than 25 megabytes.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'name',
            'room_number',
            'type_id',
            'capacity',
            'price',
            'description',
            'status',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($room->image_url && file_exists(public_path($room->image_url))) {
                unlink(public_path($room->image_url));
            }
            
            $image = $request->file('image');
            $imagePath = $image->store('rooms', 'public');
            $data['image_url'] = '/storage/' . $imagePath;
        }

        $room->update($data);

        if ($request->has('amenities') && is_array($request->input('amenities'))) {
            $amenities = array_filter($request->input('amenities'), fn($id) => !empty($id));
            if (!empty($amenities)) {
                $room->amenities()->sync($amenities);
            } else {
                // Clear all amenities if array is empty
                $room->amenities()->detach();
            }
        }

        $room->load(['type', 'amenities']);
        if ($room->image_url) {
            $room->image_url = $this->formatImageUrl($room->image_url);
        }

        return response()->json([
            'message' => 'Room updated successfully',
            'room' => $room
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

        if ($room->image_url && !str_starts_with($room->image_url, '/')) {
            $room->image_url = '/' . $room->image_url;
        }

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
