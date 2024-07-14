<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\ProcessNullValidate;
use App\Http\Requests\ValidateJsonResponse;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    use ValidateJsonResponse;

    use ProcessNullValidate;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function myValidate()
    {
        $codeIgnore = '';

        if ($this->method() == 'PUT' || $this->method() == 'PATCH') {
            $codeIgnore = ','.$this->department;
        }

        return [
            'code' => 'string|required|unique:'.(new Department())->getTable().',code'.$codeIgnore,
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'quota' => 'integer|required',
            'is_active' => 'integer|in:1,0',
            'is_delete' => 'integer|in:1,0',
            'branch_id' => 'integer',
            'branch_name' => 'string|unique',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->processBeforeUpdate($this->myValidate());
    }
}
