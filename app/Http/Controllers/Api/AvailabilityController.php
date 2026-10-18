<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $availabilities = $request->user()->weeklyAvailabilities()->orderBy('day_of_week')->orderBy('start_time')->get();

        return response()->json([
            'message' => 'Weekly Availability Retrivied Successfully',
            'data' => $availabilities,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $availability = $request->user()->weeklyAvailabilities()->create($validated);

        return response()->json([
            'message' => 'Weekly availability created successfully',
            'data' => $availability,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);
        $availability = $request->user()->weeklyAvailabilities()->findOrFail($id);
        $availability->update($validated);
        return response()->json([
            'message' => 'Weekly availability updated successfully.',
            'data' => $availability->fresh(),
        ], 200);
    }

    public function destroy(Request $request, int $id)
    {
        $availability = $request->user()->weeklyAvailabilities()->findOrFail($id);
        $availability->delete();

        return response()->json([
            'message' => 'Weekly availability deleted successfully.',
        ]);
    }
}
