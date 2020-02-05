<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Validator;

class ProductFormRequest 
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function Validador($data)
    {
        $validator = Validator::make($data , $this->rules(), $this->messages());
       
        if ($validator->fails()) {
            return response()->json(formatMessage(400, $validator->messages()), 400);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'            => 'required|min:2|max:120',
            'price'           => 'required|min:1|regex:/^[0-9]{1,9}(,[0-9]{3})*(\.[0-9]+)*$/',
            'description'     => 'required|min:2|max:255',
            'color_variation' => 'max:1|in:Y,N,',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(){
        return [
            'price.regex'        => 'The price format is invalid.' ,
            'color_variation.in' => 'The selected color variation is invalid. Only Y or N is allowed.' 
        ];
    }
}
