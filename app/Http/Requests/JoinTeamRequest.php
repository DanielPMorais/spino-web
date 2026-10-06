<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JoinTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:8'],
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'course' => ['required', Rule::in(StoreTeamRequest::campusCourses())],
            'email' => ['required', 'email:rfc', 'ends_with:@aluno.ifsp.edu.br', 'max:255'],
        ];
    }
}
