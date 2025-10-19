<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    protected $table = 'task_dependencies';
    protected $fillable = ['task_id', 'depends_on'];


}
