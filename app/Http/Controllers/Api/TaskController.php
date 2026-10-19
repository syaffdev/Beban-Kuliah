<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()->tasks()->with('course:id,name,code')->latest()->get();

        return response()->json([
            'message' => 'Daftar tugas berhasil diambil.',
            'data' => $tasks,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => [
                'required',
                'integer',
                Rule::exists('courses', 'id')->where('user_id', $request->user()->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['required', 'date'],
            'priority' => [
                'sometimes',
                Rule::in(['low', 'medium', 'high']),
            ],
            'status' => [
                'sometimes',
                Rule::in(['pending', 'in_progress', 'completed']),
            ],
            'estimated_duration_minutes' => [
                'sometimes',
                'integer',
                'min:15',
                'max:480',
            ],
        ]);
        $task = $request->user()->tasks()->create($validated)->fresh();
        return response()->json([
            'message' => 'Tugas berhasil dibuat.',
            'data' => $task->load('course:id,name,code'),
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $task = $request->user()->tasks()->with('course:id,name,code')->findOrFail($id);

        return response()->json([
            'message' => 'Detail tugas berhasil diambil.',
            'data' => $task,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $validated = $request->validate([
            'course_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('courses', 'id')->where('user_id', $request->user()->id),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'deadline' => ['sometimes', 'required', 'date'],
            'priority' => [
                'sometimes',
                Rule::in(['low', 'medium', 'high']),
            ],
            'status' => [
                'sometimes',
                Rule::in(['pending', 'in_progress', 'completed']),
            ],
            'estimated_duration_minutes' => [
                'sometimes',
                'integer',
                'min:15',
                'max:480',
            ],
        ]);
        $task->update($validated);

        return response()->json([
            'message' => 'Tugas berhasil diperbarui.',
            'data' => $task->fresh()->load('course:id,name,code'),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Tugas berhasil dihapus.',
        ]);
    }
}
