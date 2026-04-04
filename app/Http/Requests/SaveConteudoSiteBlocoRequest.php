<?php

namespace App\Http\Requests;

use App\Models\Conteudo\ConteudoSiteBloco;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveConteudoSiteBlocoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['Admin', 'Coordenador']);
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:12', 'exists:idiomas,codigo'],
            'rotulo' => ['nullable', 'string', 'max:160'],
            'tipo' => ['nullable', 'string', 'max:40'],
            'regiao' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(ConteudoSiteBloco::STATUS)],

            'eyebrow' => ['nullable', 'string', 'max:180'],
            'titulo' => ['nullable', 'string', 'max:180'],
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'lead' => ['nullable', 'string'],
            'conteudo' => ['nullable', 'string'],
            'cta_label' => ['nullable', 'string', 'max:180'],
            'cta_href' => ['nullable', 'string', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:255'],

            'media_slot' => ['nullable', 'string', 'max:60'],
            'media' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remover_media' => ['nullable', 'boolean'],
            'hero' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remover_hero' => ['nullable', 'boolean'],
        ];
    }
}
