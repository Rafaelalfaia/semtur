<?php

namespace App\Http\Requests;

use App\Models\Conteudo\CursoAula;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCursoAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        /** @var \App\Models\Conteudo\CursoAula|null $aula */
        $aula = $this->route('aula');

        return [
            'nome' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:200',
                Rule::unique('curso_aulas', 'slug')->ignore($aula?->id),
            ],
            'descricao' => ['nullable', 'string'],
            'link_acesso' => ['required', 'url', 'max:2048'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(CursoAula::STATUS)],
            'capa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remover_capa' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome' => trim((string) $this->input('nome')),
            'slug' => trim((string) $this->input('slug')) ?: null,
            'descricao' => trim((string) $this->input('descricao')) ?: null,
            'link_acesso' => trim((string) $this->input('link_acesso')),
            'ordem' => $this->filled('ordem') ? (int) $this->input('ordem') : 0,
            'remover_capa' => $this->boolean('remover_capa'),
        ]);
    }
}
