<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->isManager()) {
            return true;
        }

        return $task->assignee_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->isManager()
            ? Response::allow()
            : Response::deny('You do not have permission to create tasks.' , 403);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): Response
    {
        return $user->isManager()
            ? Response::allow()
            : Response::deny('You do not have permission to update tasks.' , 403);
    }


    public function updateStatus(User $user, Task $task): Response
    {
        // Users can only update status of tasks assigned to them
        return $task->assignee_id === $user->id
            ? Response::allow()
            : Response::deny('You can only update tasks that assigned to you.' , 403);
    }

    /**
     * Determine that manager can only delete the task.
     */
    public function delete(User $user, Task $task): Response
    {
        return $user->isManager()
            ? Response::allow()
            : Response::deny('You do not have permission to delete tasks.' , 403);
    }

    public function assignTask(User $user , Task $task): Response
    {
        return $user->isManager()
            ? Response::allow()
            : Response::deny('You do not have permission to assign tasks.' , 403);
    }

    public function addDependencies(User $user, Task $task): Response
    {
        return $user->isManager()
            ? Response::allow()
            : Response::deny('You do not have permission to add dependencies.' , 403);
    }
}
