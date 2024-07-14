<?php

namespace App\Http\Requests\Campaign;

use App\Http\Requests\ProcessNullValidate;
use App\Http\Requests\ValidateJsonResponse;
use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
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
            $codeIgnore = ','.$this->campaign;
        }

        return [
            'department_id' => 'integer|required',
            'code' => 'string|required|unique:'.(new Campaign())->getTable().',code'.$codeIgnore,
            'name' => 'string|required',
            'description' => 'string|nullable',
            'start_date' => 'date_format:Y-m-d H:i:s',
            'adverting_budget' => 'numeric|nullable',
            'amount_spent' => 'numeric|nullable',
            'is_active' => 'nullable|integer|in:1,0',
            'is_delete' => 'nullable|integer|in:1,0',
            'created_by' => 'integer|nullable',
            'updated_by' => 'integer|nullable',
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

