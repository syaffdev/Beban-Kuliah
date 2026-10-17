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

    public function deadlineRisk(Request $request)
    {
        $user = $request->user();

        $tasks = Task::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->orderBy('deadline')
            ->get();

        $data = $tasks->map(function ($task) {
            $daysLeft = (int) now()
                ->startOfDay()
                ->diffInDays($task->deadline->copy()->startOfDay(), false);

            if ($daysLeft < 0) {
                $risk = 'overdue';
            } elseif ($daysLeft <= 2) {
                $risk = 'high';
            } else {
                $risk = 'low';
            }

            return [
                'id' => $task->id,
                'title' => $task->title,
                'deadline' => $task->deadline,
                'status' => $task->status,
                'days_left' => $daysLeft,
                'risk' => $risk,
            ];
        });

        return response()->json([
            'message' => 'Analisis risiko deadline berhasil.',
            'data' => $data,
        ]);
    }
}
