<?php

namespace Modules\ClientApp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubTenantStore extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "parent_id" => "integer|exists:subtenant,id|nullable",
            "name" => "required|max:255",
            "description" => "required",
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
