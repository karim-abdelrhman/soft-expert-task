<?php

namespace App\Http\Controllers\API;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\TaskStoreRequest;
use App\Http\Requests\API\TaskUpdateRequest;
use App\Http\Requests\ValidateDependencyRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()
            ->with('assignee')
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', Status::fromLabel($request->status));
            })
            ->when($request->filled('assignee'), function ($q) use ($request) {
                $q->where('assignee_id', $request->assignee);
            })
            ->when($request->filled('date_from') && $request->filled('date_to'), function ($q) use ($request) {
                $q->whereBetween('date', [$request->date_from, $request->date_to]);
            });

        if (!$request->user()->isManager()) {
            $query->where('assignee_id', $request->user()->id);
        }

        return TaskCollection::make($query->paginate($request->filled('per_page') ? $request->query('per_page') : 10));
    }

    public function store(TaskStoreRequest $request)
    {
        $data = $request->validated();

        Gate::authorize('create', Task::class);

        $data['status'] = Status::fromLabel($data['status']);

        $task = Task::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => TaskResource::make($task->load('assignee')),
        ], Response::HTTP_CREATED);
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $data = $request->validated();

        Gate::authorize('update', $task);

        $task->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => TaskResource::make($task->load('assignee')),
            'code' => 200
        ]);
    }

    public function show(Task $task)
    {
        if ($task->assignee_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Task not assigned to this assignee',
            ]);
        }
        if ($task->dependencies->count() > 0) {
            $task->load('dependencies');
        }
        return response()->json([
            'success' => true,
            'message' => 'Task fetched successfully',
            'data' => TaskResource::make($task->load('assignee')),
            'code' => 200
        ]);
    }

    public function destroy(Request $request, Task $task)
    {
        Gate::authorize('destroy', $task);
        $task->delete();
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    public function update_status(Request $request, Task $task)
    {
        Gate::authorize('updateStatus', $task);

        /**
         * so some validation before update status
         * 1- check if this task has dependencies
         * 2- check if all dependencies completed
         */
        if ($task->dependencies->count() > 0 && $request->status == 'completed') {
            foreach ($task->dependencies as $dependency) {
                if ($dependency->status->value !== Status::fromLabel('completed')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have to finish task dependencies before update this task',
                        'task' => TaskResource::make($task->load(['assignee', 'dependencies'])),
                    ]);
                }
            }
        }

        $task->update(['status' => Status::fromLabel($request->status)]);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'task' => TaskResource::make($task->load(['assignee', 'dependencies'])),
        ]);
    }

    public function add_dependencies(ValidateDependencyRequest $request, Task $task)
    {
        $data = $request->validated();

        Gate::authorize('addDependencies', $task);

        foreach ($data['dependencies'] as $dependency) {
            TaskDependency::create([
                'task_id' => $task->id,
                'depends_on' => $dependency,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Task dependencies fetched successfully',
            'data' => TaskResource::make($task->load('dependencies')),
        ]);
    }

    public function assign_task_to_user(Request $request)
    {
        $data = $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $task = Task::find($data['task_id']);

        Gate::authorize('assignTask', $task);

        // 1- before get task we should check if task doesn't have an assignee
        $task = Task::where('id', $data['task_id'])->firstOrFail();

        if ($task->assignee_id) {
            return response()->json([
                'success' => false,
                'message' => 'Task already assigned to user',
                'data' => TaskResource::make($task->load('assignee')),
            ]);
        }

        $task->update([
            'assignee_id' => $data['user_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task assigned successfully',
            'data' => TaskResource::make($task->load('assignee')),
        ]);
    }
}
