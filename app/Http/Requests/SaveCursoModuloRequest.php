<?php

namespace App\Http\Requests;

use App\Models\Conteudo\CursoModulo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCursoModuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        /** @var \App\Models\Conteudo\CursoModulo|null $modulo */
        $modulo = $this->route('modulo');

        return [
            'nome' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:200',
                Rule::unique('curso_modulos', 'slug')->ignore($modulo?->id),
            ],
            'descricao_curta' => ['nullable', 'string', 'max:255'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(CursoModulo::STATUS)],
            'capa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remover_capa' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome' => trim((string) $this->input('nome')),
            'slug' => trim((string) $this->input('slug')) ?: null,
            'descricao_curta' => trim((string) $this->input('descricao_curta')) ?: null,
            'ordem' => $this->filled('ordem') ? (int) $this->input('ordem') : 0,
            'remover_capa' => $this->boolean('remover_capa'),
        ]);
    }
}
