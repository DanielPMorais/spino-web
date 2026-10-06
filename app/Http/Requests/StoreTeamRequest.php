<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'team_name' => ['required', 'string', 'min:3', 'max:100', Rule::unique('teams', 'name')],
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'course' => ['required', Rule::in(self::campusCourses())],
            'email' => ['required', 'email:rfc', 'ends_with:@aluno.ifsp.edu.br', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('team_name'))) {
            $this->merge(['team_name' => Str::squish($this->input('team_name'))]);
        }
    }

    public static function campusCourses(): array
    {
        return ['Técnico em Edificações', 'Bacharelado em Engenharia Civil'];
    }
}
