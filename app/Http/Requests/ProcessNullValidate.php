<?php

namespace App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

trait ProcessNullValidate
{
    public function processBeforeUpdate($validates)
    {

        if (Str::upper($this->method()) == 'POST') {
            return $validates;
        }

        $data = $this->all();

        foreach ($validates as $key => $validate) {
            if (!isset($data[$key]) || (isset($data[$key]) && is_null($data[$key]))) {
                unset($validates[$key]);
            }
        }
        $checkPathSchoolTour = ($this->getPathInfo() == '/api/v2/school-tour');
        $checkPathEasyIelts = ($this->getPathInfo() == '/api/v2/flt-easy-ielts');

        if (!empty($checkPathSchoolTour)){
            $validates['agentCode'] = 'required|integer';
        }
        if (!empty($checkPathEasyIelts)){
            $validates['email'] = 'required||string|email';
        }
        return $validates;
    }
}
