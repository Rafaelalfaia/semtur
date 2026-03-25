<?php

namespace App\Http\Requests;

use App\Models\Conteudo\BannerDestaque;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBannerDestaqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'target_blank' => $this->boolean('target_blank'),
            'autoplay' => $this->boolean('autoplay', true),
            'loop' => $this->boolean('loop', true),
            'muted' => $this->boolean('muted', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['nullable', 'string', 'max:160'],
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'target_blank' => ['sometimes', 'boolean'],
            'cor_fundo' => ['nullable', 'string', 'max:20'],
            'overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],

            'status' => ['nullable', 'string', Rule::in([
                BannerDestaque::STATUS_PUBLICADO,
                BannerDestaque::STATUS_RASCUNHO,
                BannerDestaque::STATUS_ARQUIVADO,
            ])],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'inicio_publicacao' => ['nullable', 'date'],
            'fim_publicacao' => ['nullable', 'date', 'after_or_equal:inicio_publicacao'],

            'media_type' => ['required', Rule::in([BannerDestaque::MEDIA_IMAGE, BannerDestaque::MEDIA_VIDEO])],
            'hero_variant' => ['nullable', 'string', 'max:40'],
            'preload_mode' => ['nullable', Rule::in(['none', 'metadata', 'auto'])],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'autoplay' => ['sometimes', 'boolean'],
            'loop' => ['sometimes', 'boolean'],
            'muted' => ['sometimes', 'boolean'],

            'imagem_desktop' => ['nullable', 'image', 'max:6144'],
            'imagem_mobile' => ['nullable', 'image', 'max:6144'],
            'crop_imagem_desktop' => ['nullable', 'string'],
            'crop_imagem_mobile' => ['nullable', 'string'],
            'pos_desktop' => ['nullable', 'string'],
            'pos_mobile' => ['nullable', 'string'],

            'video_desktop' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime', 'max:51200'],
            'video_mobile' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime', 'max:51200'],
            'poster_desktop' => ['nullable', 'image', 'max:6144'],
            'poster_mobile' => ['nullable', 'image', 'max:6144'],
            'fallback_image_desktop' => ['nullable', 'image', 'max:6144'],
            'fallback_image_mobile' => ['nullable', 'image', 'max:6144'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $banner = $this->route('banner');
            $isVideo = $this->input('media_type') === BannerDestaque::MEDIA_VIDEO;

            if (! $isVideo) {
                return;
            }

            $hasVideo = $this->hasFile('video_desktop')
                || $this->hasFile('video_mobile')
                || filled($banner?->video_desktop_path)
                || filled($banner?->video_mobile_path);

            if (! $hasVideo) {
                $validator->errors()->add('video_desktop', 'Envie pelo menos um vídeo para o hero de abertura.');
            }

            $hasPosterOrFallback = $this->hasFile('poster_desktop')
                || $this->hasFile('poster_mobile')
                || $this->hasFile('fallback_image_desktop')
                || $this->hasFile('fallback_image_mobile')
                || filled($banner?->poster_desktop_path)
                || filled($banner?->poster_mobile_path)
                || filled($banner?->fallback_image_desktop_path)
                || filled($banner?->fallback_image_mobile_path)
                || filled($banner?->imagem_desktop_path)
                || filled($banner?->imagem_mobile_path);

            if (! $hasPosterOrFallback) {
                $validator->errors()->add('poster_desktop', 'Defina um poster ou imagem de fallback para o modo vídeo.');
            }
        });
    }
}
