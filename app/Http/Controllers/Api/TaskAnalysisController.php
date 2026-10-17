<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskAnalysisController extends Controller
{
    public function workload(Request $request)
    {
        $user = $request->user();

        $tasks = Task::where('user_id', $user->id);

        return response()->json([
            'message' => 'Analisis beban kuliah berhasil.',
            'data' => [
                'total_courses' => Course::where('user_id', $user->id)->count(),

                'pending_tasks' => (clone $tasks)
                    ->where('status', 'pending')
                    ->count(),

                'in_progress_tasks' => (clone $tasks)
                    ->where('status', 'in_progress')
                    ->count(),

                'completed_tasks' => (clone $tasks)
                    ->where('status', 'completed')
                    ->count(),

                'overdue_tasks' => (clone $tasks)
                    ->where('status', '!=', 'completed')
                    ->where('deadline', '<', now())
                    ->count(),
            ],
        ]);
    }
}
