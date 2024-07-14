<?php

namespace App\Http\Requests\ActiveCode;

use App\Http\Requests\ValidateJsonResponse;
use App\Models\LandingPage;
use Illuminate\Foundation\Http\FormRequest;

class ActiveCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    use ValidateJsonResponse;
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'active_code' => 'required|array|max:2000',
            'active_code.*.landing_page_id' => 'required|integer|exists:'.(new LandingPage())->getTable().',id',
            'active_code.*.code' => 'string|required',
            'active_code.*.product_id' => 'string|required',
            'active_code.*.used' => 'in:no',
        ];
    }
}
