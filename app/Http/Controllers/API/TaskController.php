<?php

namespace App\Http\Controllers\API;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\TaskStoreRequest;
use App\Http\Requests\API\TaskUpdateRequest;
use App\Http\Requests\ValidateDependencyRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()
            ->with('assignee');

        if(auth()->user()->hasRole('user')){
            $query->where('assignee_id' , auth()->id());
        }

        // Filter by status (accepts label like 'pending' or integer value)
        if ($request->filled('status')) {
            $statusParam = $request->query('status');

            $statusValue = Status::fromLabel(strtolower($statusParam));

            if ($statusValue !== null) {
                $query->where('status', $statusValue);
            }
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date',[$request->query('date_from'), $request->query('date_to')]);
        }

        if (!is_null($request->query('assignee')) && $request->query('assignee') !== '') {
            $query->where('assignee_id', (int)$request->query('assignee'));
        }


        $useOffsetLimit = $request->filled('limit') || $request->filled('offset');

        if ($useOffsetLimit) {
            $limit = (int)$request->query('limit', 15);
            $limit = max(1, min($limit, 100));
            $offset = (int)$request->query('offset', 0);
            $offset = max(0, $offset);

            $total = (clone $query)->count();
            $tasks = $query->skip($offset)->take($limit)->get();

            return response()->json([
                'success' => true,
                'message' => 'Tasks fetched successfully',
                'data' => TaskResource::collection($tasks),
                'meta' => [
                    'total' => $total,
                    'count' => $tasks->count(),
                    'offset' => $offset,
                    'limit' => $limit,
                    'has_more' => ($offset + $tasks->count()) < $total,
                ],
                'code' => 200
            ]);
        } else {
            $perPage = (int)$request->query('per_page', 15);
            $perPage = max(1, min($perPage, 100));
            $page = (int)$request->query('page', 1);
            $page = max(1, $page);

            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Tasks fetched successfully',
                'data' => TaskResource::collection($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
                'code' => 200
            ]);
        }
    }

    public function store(TaskStoreRequest $request)
    {
        $data = $request->validated();

        $data['status'] = Status::fromLabel($data['status']);

        $task = Task::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => TaskResource::make($task->load('assignee')),
            'code' => 201
        ]);
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $data = $request->validated();

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
        if($task->assignee_id !== auth()->id()){
            return response()->json([
                'success' => false,
                'message' => 'Task not assigned to this assignee',
            ]);
        }
        if($task->dependencies->count() > 0) {
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
        $task->delete();
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    public function update_status(Request $request, Task $task)
    {
        if($task->assignee->id !== auth()->id()){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized, You can\'t update this task status',
                'code' => 401
            ]);
        }
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
                        'task' => TaskResource::make($task->load(['assignee','dependencies'])),
                    ]);
                }
            }
        }

        $task->update(['status' => Status::fromLabel($request->status)]);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'task' => TaskResource::make($task->load(['assignee' , 'dependencies'])),
        ]);
    }

    public function add_dependencies(ValidateDependencyRequest $request, Task $task)
    {
        $data = $request->validated();
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

        // 1- before get task we should check if task doesn't have an assignee
        $task = Task::where('id', $data['task_id'])->firstOrFail();

        if($task->assignee_id){
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
