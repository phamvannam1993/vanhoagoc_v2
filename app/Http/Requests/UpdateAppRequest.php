<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppRequest extends BaseModuleRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $appId = (int)request()->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:5',
            ],
        ];
    }
}
