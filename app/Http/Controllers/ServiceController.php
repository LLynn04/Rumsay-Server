<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index(Request $request)
    {
        $query = Service::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter active services for users
        if ($request->user() && $request->user()->isUser()) {
            $query->active();
        }

        $services = $query->orderBy('name')->get();

        return response()->json([
            'message' => 'Services retrieved successfully',
            'data' => $services
        ]);
    }

    /**
     * Store a newly created service (Admin only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1', // in minutes
            'category' => 'required|string|max:100',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $serviceData = $request->only(['name', 'description', 'price', 'duration', 'category']);
        $serviceData['is_active'] = $request->get('is_active', true);

        // Handle image upload or URL
        if ($request->hasFile('image')) {
            // File upload
            $path = $request->file('image')->store('services', 'public');
            $serviceData['image'] = $path;
        }
        elseif ($request->filled('image_url')) {
            $serviceData['image'] = $request->input('image_url');
        }

        $service = Service::create($serviceData);

        return response()->json([
            'message' => 'Service created successfully',
            'data' => $service
        ]);
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        return response()->json([
            'message' => 'Service retrieved successfully',
            'data' => $service
        ]);
    }

    /**
     * Update the specified service (Admin only).
     */
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'duration' => 'sometimes|integer|min:1',
            'category' => 'sometimes|string|max:100',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $updateData = $request->only(['name', 'description', 'price', 'duration', 'category', 'is_active']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image from storage if it exists
            if ($service->image && Storage::disk('public')->exists($service->image)) {
                Storage::disk('public')->delete($service->image);
            }

            $path = $request->file('image')->store('services', 'public');
            $updateData['image'] = 'storage/' . $path;
        }
        // Handle image URL import if no new file was uploaded
        elseif ($request->filled('image_url')) {
            $updateData['image'] = $request->input('image_url');
        }

        $service->update($updateData);

        return response()->json([
            'message' => 'Service updated successfully',
            'data' => $service
        ]);
    }


    /**
     * Remove the specified service (Admin only).
     */
    public function destroy(Service $service)
    {
        // Delete associated image only if it's a file path (not URL)
        if ($service->image && !filter_var($service->image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully'
        ]);
    }
}
