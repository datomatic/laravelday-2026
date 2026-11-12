<?php

namespace App\Http\Requests;

use App\Enums\CompanyRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreWorkshopBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|ValidationRule|Enum>>
     */
    public function rules(): array
    {
        return [
            'company_role' => ['required', Rule::enum(CompanyRole::class)],
            'company_name' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'company_role' => 'ruolo in azienda',
            'company_name' => 'nome azienda',
        ];
    }
}
