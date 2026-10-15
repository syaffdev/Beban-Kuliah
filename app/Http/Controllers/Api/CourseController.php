<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = $request->user()->courses()->latest()->get();
        return response()->json([
            'message' => 'Daftar mata kuliah berhasil diambil.',
            'data' => $courses,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('courses', 'code')->where('user_id', $request->user()->id),
            ],
            'credits' => ['required', 'integer', 'min:1', 'max:6'],
        ]);
        $course = $request->user()->courses()->create($validated);
        return response()->json([
            'message' => 'Mata kuliah berhasil dibuat.',
            'data' => $course,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $course = $request->user()->courses()->findOrFail($id);
        return response()->json([
            'message' => 'Detail mata kuliah berhasil diambil.',
            'data' => $course,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse {
        $course = $request->user()->courses()->findOrFail($id);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('courses', 'code')->where('user_id', $request->user()->id)->ignore($course->id),
            ],
            'credits' => ['sometimes', 'required', 'integer', 'min:1', 'max:6'],
        ]);
        $course->update($validated);
        return response()->json([
            'message' => 'Mata kuliah berhasil diperbarui.',
            'data' => $course->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse {
        $course = $request->user()->courses()->findOrFail($id);
        $course->delete();
        return response()->json([
            'message' => 'Mata kuliah berhasil dihapus.',
        ]);
    }
}

