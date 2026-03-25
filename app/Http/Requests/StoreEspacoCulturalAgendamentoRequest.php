<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEspacoCulturalAgendamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'telefone' => preg_replace('/\D+/', '', (string) $this->input('telefone')) ?: null,
            'email' => $this->filled('email') ? strtolower((string) $this->input('email')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'data_visita' => ['required', 'date'],
            'espaco_cultural_horario_id' => ['required', 'integer'],
            'nome' => ['required', 'string', 'max:160'],
            'telefone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:160'],
            'qtd_visitantes' => ['required', 'integer', 'min:1', 'max:999'],
            'observacao_visitante' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
