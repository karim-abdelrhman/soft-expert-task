<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $table = 'tasks';
    protected $fillable = ['title' , 'description' , 'assignee_id' , 'date', 'status'];
    protected $casts = [
        'assignee_id' => 'integer',
        'date' => 'date',
        'status' => Status::class
    ];


    public function assignee() : BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function dependencies()
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'task_id',
            'depends_on'
        );
    }
}
