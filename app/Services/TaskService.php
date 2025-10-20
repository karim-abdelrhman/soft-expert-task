<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Task;
use App\Models\TaskDependency;
use Symfony\Component\HttpFoundation\Response;

class TaskService
{
    public function assignUser(Task $task, int $assigneeId): Task
    {
        $task->update(['assignee_id' => $assigneeId]);
        return $task->load('assignee');
    }

    public function addDependencies(Task $task, array $dependencyIds): Task
    {
        $task->dependencies()->syncWithoutDetaching($dependencyIds);
        return $task->load('dependencies');
    }

    public function updateStatus(Task $task, string $status): Task
    {
        if ($status == 'completed') {
            if ($this->hasUncompletedDependencies($task, $status)) {
                throw new \Exception('You have to finish task dependencies before update this task.');
            }
        }
        $task->update(['status' => $status]);
        return $task->load(['assignee', 'dependencies']);
    }

    private function hasUncompletedDependencies(Task $task, string $status): bool
    {
        return $task->dependencies
            ->contains(fn($dep) => $dep->status !== 'completed');
    }
}
