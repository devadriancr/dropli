<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExitScanRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exitCode' => ['required', 'string'],
            'orderNumber' => ['required', 'string'],
            'sequence' => ['required', 'string'],
            'quantity' => ['required', 'numeric', 'min:1'],
        ];
    }
}
