<?php

namespace App\Http\Requests\LandingPageInfo;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class ListLandingPageInfo extends FormRequest
{
    use ValidateJsonResponse;

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        $validate = [
            'landing_page_id' => 'nullable|integer',
            'id' => 'nullable|integer',
        ];

        return $validate;
    }
}
