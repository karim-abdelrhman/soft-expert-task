<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $table = 'tasks';
    protected $fillable = ['title' , 'description' , 'assignee_id' , 'date', 'status'];
    protected $casts = [
        'assignee_id' => 'integer',
        'date' => 'date',
        'status' => Status::class
    ];

//    public function status(): Attribute
//    {
//        return Attribute::make(
//            get: fn ($value) => Status::labels()[$value] ?? null,
//            set: fn ($value) => Status::fromLabel($value)
//        );
//    }

    public function assignee() : BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
