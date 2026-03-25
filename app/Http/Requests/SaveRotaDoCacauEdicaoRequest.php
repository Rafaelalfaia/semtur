<?php

namespace App\Http\Requests;

use App\Models\RotaDoCacau;
use App\Models\RotaDoCacauEdicao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveRotaDoCacauEdicaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug'));
        $publishedAt = trim((string) $this->input('published_at'));

        $this->merge([
            'remover_capa' => $this->boolean('remover_capa'),
            'slug' => $slug !== '' ? Str::slug($slug) : null,
            'ordem' => (int) ($this->input('ordem', 0) ?: 0),
            'published_at' => $publishedAt !== '' ? $publishedAt : null,
        ]);
    }

    public function rules(): array
    {
        $edicao = $this->route('edicao');
        $rota = $this->route('rotaDoCacau');
        $rotaId = $rota?->id ?? $edicao?->rota_do_cacau_id;
        $edicaoId = $edicao?->id;

        return [
            'ano' => [
                'required',
                'integer',
                'min:1900',
                'max:2100',
                Rule::unique('rota_do_cacau_edicoes', 'ano')
                    ->where(fn ($q) => $q->where('rota_do_cacau_id', $rotaId))
                    ->ignore($edicaoId),
            ],
            'titulo' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:200',
                Rule::unique('rota_do_cacau_edicoes', 'slug')
                    ->where(fn ($q) => $q->where('rota_do_cacau_id', $rotaId))
                    ->ignore($edicaoId),
            ],
            'descricao' => ['required', 'string'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(RotaDoCacau::STATUS)],
            'published_at' => ['nullable', 'date'],

            'capa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remover_capa' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ((string) $this->input('status') !== RotaDoCacauEdicao::STATUS_PUBLICADO) {
                return;
            }

            $edicao = $this->route('edicao');
            $hasExistingCover = $edicao
                && filled($edicao->capa_path)
                && !$this->boolean('remover_capa');

            if (!$hasExistingCover && !$this->hasFile('capa')) {
                $validator->errors()->add('capa', 'Para publicar, envie uma imagem de capa.');
            }
        });
    }
}
