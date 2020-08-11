<?php

namespace Modules\ClientApp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriorityTypeStore extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //"parent_id" => "integer|exists:SubTenant,idSubTenant|nullable",
            "TypeCodeMin" => "required",
            "TypeCodeMax" => "required",
            "PRCType" => "required|unique:TaskPriorityType"
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
