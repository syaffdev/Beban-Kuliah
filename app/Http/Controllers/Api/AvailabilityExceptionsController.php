<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AvailabilityExceptionsController extends Controller
{
    public function index(Request $request)
    {
        $exceptions = $request->user()
            ->availabilityExceptions()
            ->orderBy('date')
            ->get();

        return response()->json([
            'message' => 'Availability exceptions retrieved successfully.',
            'data' => $exceptions,
        ], 200);
    }

    public function store(Request $request)
    {
        $rules = [
            'date' => ['required', 'date_format:Y-m-d'],
            'type' => ['required', 'in:unavailable,extra_available'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
        if ($request->input('type') === 'extra_available') {
            $rules['start_time'] = ['required', 'date_format:H:i'];
            $rules['end_time'] = [
                'required',
                'date_format:H:i',
                'after:start_time',
            ];
        } else {
            $rules['start_time'] = ['nullable', 'date_format:H:i'];
            $rules['end_time'] = ['nullable', 'date_format:H:i'];
        }
        $validated = $request->validate($rules);
        $exception = $request->user()->availabilityExceptions()->create($validated);

        return response()->json([
            'message' => 'Availability exception created successfully.',
            'data' => $exception,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $exception = $request->user()->availabilityExceptions()->findOrFail($id);

        $rules = [
            'date' => ['required', 'date_format:Y-m-d'],
            'type' => ['required', 'in:unavailable,extra_available'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];

        if ($request->input('type') === 'extra_available') {
            $rules['start_time'] = ['required', 'date_format:H:i'];
            $rules['end_time'] = [
                'required',
                'date_format:H:i',
                'after:start_time',
            ];
        } else {
            $rules['start_time'] = ['nullable', 'date_format:H:i'];
            $rules['end_time'] = ['nullable', 'date_format:H:i'];
        }

        $validated = $request->validate($rules);

        $exception->update($validated);

        return response()->json([
            'message' => 'Availability exception updated successfully.',
            'data' => $exception->fresh(),
        ], 200);
    }

    public function destroy(Request $request, int $id)
    {
        $exception = $request->user()->availabilityExceptions()->findOrFail($id);
        $exception->delete();

        return response()->json([
            'message' => 'Availability exception deleted successfully.',
        ], 200);
    }
}
