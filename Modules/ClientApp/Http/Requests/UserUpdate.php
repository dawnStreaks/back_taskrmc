<?php

namespace Modules\ClientApp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class UserUpdate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $request->id,
            /*'email' => [
                Rule::unique('users')->ignore(\JWTAuth::parseToken()->authenticate()->id, 'id')
            ],*/
            'status' => 'required|in:0,1',
            //'second_name' => 'required|Alpha',
            'last_name' => 'required|Alpha',
            //'user_type' => 'required|integer',
            'subtenant_id' => 'required|integer',
            //'subtenant_user_group_id' => 'required|integer',
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
