<?php

namespace App\Http\Controllers\API;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\TaskStoreRequest;
use App\Http\Requests\API\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()->with('assignee');

        // Filter by status (accepts label like 'pending' or integer value)
        if ($request->filled('status')) {
            $statusParam = $request->query('status');

            $statusValue = Status::fromLabel(strtolower($statusParam));

            if ($statusValue !== null) {
                $query->where('status', $statusValue);
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->query('date'));
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
        $data['status'] = Status::fromLabel($data['status']);
        $task->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task,
            'code' => 200
        ]);
    }

    public function show(Task $task)
    {
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
}
