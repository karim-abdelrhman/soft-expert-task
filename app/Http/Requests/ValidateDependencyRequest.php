<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateDependencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        if ($this->filled('dependencies') && is_string($this->dependencies)) {
            $this->merge([
                'dependencies' => array_filter(array_map('intval', explode(',', $this->dependencies)))
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'task_id' => ['required', 'integer', 'exists:tasks,id'],

            'dependencies' => ['required', 'array'],

            'dependencies.*' => [
                'integer',
                'distinct',
                Rule::exists('tasks', 'id'),

                function ($attribute, $value, $fail) {
                    $taskId = $this->task_id;

                    $exists = \DB::table('task_dependencies')
                        ->where('task_id', $taskId)
                        ->where('depends_on', $value)
                        ->exists();

                    if ($exists) {
                        $fail("The dependency {$value} already exists for this task.");
                    }
                },
            ],
        ];
    }
}
