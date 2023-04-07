<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_status_id' => 'sometimes|nullable|exists:order_statuses,id',
            'products' => 'sometimes|nullable|array',
            'address' => 'sometimes|nullable|array',
            'amount' => 'required|numeric|min:1',
            'delivery_fee' => 'nullable|numeric|min:0',
        ];
    }
}
