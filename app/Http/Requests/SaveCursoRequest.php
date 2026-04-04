<?php

namespace App\Http\Requests;

use App\Models\Conteudo\Curso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        /** @var \App\Models\Conteudo\Curso|null $curso */
        $curso = $this->route('curso');

        return [
            'nome' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:200',
                Rule::unique('cursos', 'slug')->ignore($curso?->id),
            ],
            'descricao_curta' => ['nullable', 'string', 'max:255'],
            'publico_alvo' => ['required', 'string', Rule::in(Curso::PUBLICOS_ALVO)],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(Curso::STATUS)],
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
            'publico_alvo' => trim((string) $this->input('publico_alvo')) ?: Curso::PUBLICO_AMBOS,
            'ordem' => $this->filled('ordem') ? (int) $this->input('ordem') : 0,
            'remover_capa' => $this->boolean('remover_capa'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'slug' => 'slug',
            'descricao_curta' => 'descrição curta',
            'ordem' => 'ordem',
            'status' => 'status',
            'capa' => 'capa',
            'remover_capa' => 'remoção da capa',
        ];
    }
}
