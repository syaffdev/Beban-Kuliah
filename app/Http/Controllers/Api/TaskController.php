<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $validated['title'] = trim($validated['title']);
        $this->validateUniqueTitle($request, (int) $validated['course_id'], $validated['title']);

        $task = $request->user()->tasks()->create($validated)->fresh();

        return response()->json([
            'message' => 'Tugas berhasil dibuat.',
            'data' => $task->load('course:id,name,code'),
        ], 201);
    }

    private function validateUniqueTitle(Request $request, int $courseId, string $title, int $ignoreTaskId = 0): void
    {
        $duplicateExists = Task::query()
            ->where('user_id', $request->user()->id)
            ->where('course_id', $courseId)
            ->where('id', '!=', $ignoreTaskId)
            ->pluck('title')
            ->contains(fn (string $existingTitle) => $this->normalizeTitle($existingTitle) === $this->normalizeTitle($title));

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'title' => 'Judul tugas sudah digunakan untuk mata kuliah ini.',
            ]);
        }
    }

    private function normalizeTitle(string $title): string
    {
        return mb_strtolower(trim($title));
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
        if (array_key_exists('title', $validated)) {
            $validated['title'] = trim($validated['title']);
        }
        $title = $validated['title'] ?? $task->title;
        $courseId = (int) ($validated['course_id'] ?? $task->course_id);
        $this->validateUniqueTitle($request, $courseId, $title, (int) $task->id);
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
