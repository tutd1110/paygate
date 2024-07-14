<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class DepartmentFilterRequest extends FormRequest
{
    use ValidateJsonResponse;
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
    public function rules()
    {
        $validate = [
            'id' => 'nullable|integer',
            'code' => 'nullable|string',
            'name' => 'nullable|string',
            'branch_id' => 'nullable|integer',
            'is_active' => 'nullable|integer|in:0,1',
            'limit' => 'nullable|integer',
            'order' => 'nullable|in:id,code,name,description,quota,is_active,is_delete,branch_id,branch_name,created_by,updated_by',
            'direction' => 'nullable|in:asc,desc'
        ];

        $canArrayKey = [
            'id',
            'code',
            'name',
            'branch_id',
            'is_active',
            'limit',
            'order',
            'direction',
        ];

        $data = $this->all();

        foreach ($canArrayKey as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                $validate[$key.'.*'] = $validate[$key];
                $validate[$key] = 'array';
            }
        }

        return $validate;
    }
}
