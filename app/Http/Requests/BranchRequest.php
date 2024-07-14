<?php

namespace App\Http\Requests;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
{
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
        if ($this->method() == 'PUT' || $this->method() == 'PATCH') {
            $codeIgnore = ','.$this->branch;
        }

        return [
            'code' => 'required|unique:'.(new Branch())->getTable().',code'.$codeIgnore
        ];
    }
}
