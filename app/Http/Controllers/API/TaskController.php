<?php

namespace App\Http\Controllers\API;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\AssignTaskRequest;
use App\Http\Requests\API\TaskStoreRequest;
use App\Http\Requests\API\TaskUpdateRequest;
use App\Http\Requests\API\ValidateDependencyRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Services\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(
        private TaskService $service
    ){}

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

        return TaskCollection::make($query->paginate($request->filled('per_page')
            ? $request->query('per_page')
            : 10)
        );
    }

    public function store(TaskStoreRequest $request)
    {
        $data = $request->validated();

        Gate::authorize('create', Task::class);

        $task = Task::create($data);

        return $this->successResponse(
            data: TaskResource::make($task->load('assignee')),
            message: 'Task created successfully',
            status: Response::HTTP_CREATED
        );
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $data = $request->validated();

        Gate::authorize('update', $task);

        $task->update($data);

        return $this->successResponse(
            data: TaskResource::make($task->load('assignee')),
            message: 'Task updated successfully',
            status: Response::HTTP_OK
        );
    }

    public function show(Task $task)
    {
        Gate::authorize('view', $task);

        return $this->successResponse(
            data: TaskResource::make($task->load(['assignee' , 'dependencies'])),
            message: 'Task retrieved successfully',
            status: Response::HTTP_OK
        );
    }

    public function destroy(Request $request, Task $task)
    {
        Gate::authorize('delete', $task);
        $task->delete();
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    public function update_status(Request $request, Task $task)
    {
        try{
            $task = $this->service->updateStatus($task, $request->status);
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage());
        }

        return $this->successResponse(
            data: TaskResource::make($task->load(['assignee', 'dependencies'])),
            message: 'Task updated successfully',
            status: Response::HTTP_OK
        );
    }

    public function add_dependencies(ValidateDependencyRequest $request, Task $task)
    {
        $data = $request->validated();

        $task = $this->service->addDependencies($task, $data['dependencies']);

        return $this->successResponse(
            data: TaskResource::make($task),
            message: 'Dependencies added successfully',
            status: Response::HTTP_OK
        );
    }

    public function assign_task_to_user(AssignTaskRequest $request, Task $task)
    {
        $data = $request->validated();

        if($task->haveAssignee()){
            return $this->errorResponse(
                message: 'Task already assigned to user',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $task = $this->service->assignUser($task, $data['user_id']);

        return $this->successResponse(
            data: TaskResource::make($task),
            message: 'Task assigned successfully',
            status: Response::HTTP_OK
        );
    }
}
