<?php

namespace App\Http\Requests;

use App\Models\RotaDoCacau;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveRotaDoCacauRequest extends FormRequest
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
            'remover_foto_perfil' => $this->boolean('remover_foto_perfil'),
            'remover_foto_capa' => $this->boolean('remover_foto_capa'),
            'slug' => $slug !== '' ? Str::slug($slug) : null,
            'ordem' => (int) ($this->input('ordem', 0) ?: 0),
            'published_at' => $publishedAt !== '' ? $publishedAt : null,
        ]);
    }

    public function rules(): array
    {
        $id = optional($this->route('rotaDoCacau'))->id;

        return [
            'titulo' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('rota_do_cacau', 'slug')->ignore($id)],
            'descricao' => ['required', 'string'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(RotaDoCacau::STATUS)],
            'published_at' => ['nullable', 'date'],

            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'foto_capa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remover_foto_perfil' => ['nullable', 'boolean'],
            'remover_foto_capa' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ((string) $this->input('status') !== RotaDoCacau::STATUS_PUBLICADO) {
                return;
            }

            $rota = $this->route('rotaDoCacau');

            $hasExistingProfile = $rota
                && filled($rota->foto_perfil_path)
                && !$this->boolean('remover_foto_perfil');
            $hasExistingCover = $rota
                && filled($rota->foto_capa_path)
                && !$this->boolean('remover_foto_capa');

            if (!$hasExistingProfile && !$this->hasFile('foto_perfil')) {
                $validator->errors()->add('foto_perfil', 'Para publicar, envie uma foto de perfil.');
            }

            if (!$hasExistingCover && !$this->hasFile('foto_capa')) {
                $validator->errors()->add('foto_capa', 'Para publicar, envie uma foto de capa.');
            }
        });
    }
}
