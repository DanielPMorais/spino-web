<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'team_name' => ['required', 'string', 'min:3', 'max:100'],
            'category' => ['required', 'in:civil,general'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'enrollment' => ['required', 'string', 'max:30'],
        ];
    }
}
