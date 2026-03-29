<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\EventoEdicao;

class PortalController extends Controller
{
    public function roteiros()
    {
        return $this->page('roteiros');
    }

    public function agenda()
    {
        return $this->page('agenda');
    }

    public function museus()
    {
        return $this->page('museus');
    }

    public function ondeComer()
    {
        return $this->page('onde_comer');
    }

    public function ondeFicar()
    {
        return $this->page('onde_ficar');
    }

    public function guias()
    {
        return $this->page('guias');
    }

    public function informacoes()
    {
        return $this->page('informacoes');
    }

    public function contato()
    {
        return $this->page('contato');
    }

    private function page(string $key)
    {
        $pages = $this->pages();

        abort_unless(isset($pages[$key]), 404);

        $payload = [
            'page' => $pages[$key],
        ];

        if ($key === 'agenda') {
            $payload['agendaEvents'] = $this->agendaPreviewEvents();
        }

        return view('site.portal.index', $payload);
    }

    private function agendaPreviewEvents()
    {
        return Evento::query()
            ->select('eventos.*')
            ->addSelect(['ano_max' => EventoEdicao::selectRaw('MAX(ano)')
                ->whereColumn('evento_id', 'eventos.id')
                ->where('status', 'publicado')])
            ->whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('evento_edicoes')
                    ->whereColumn('evento_edicoes.evento_id', 'eventos.id')
                    ->where('evento_edicoes.status', 'publicado');
            })
            ->orderByDesc('ano_max')
            ->with(['edicoes' => fn ($query) => $query->where('status', 'publicado')->orderByDesc('ano')])
            ->limit(6)
            ->get();
    }

    private function pages(): array
    {
        return [
            'descubra' => [
                'eyebrow' => __('ui.portal_pages.descubra.eyebrow'),
                'title' => __('ui.portal_pages.descubra.title'),
                'description' => __('ui.portal_pages.descubra.description'),
                'lead' => __('ui.portal_pages.descubra.lead'),
                'cta_label' => __('ui.portal_pages.descubra.cta_label'),
                'cta_href' => localized_route('site.explorar'),
                'cards' => [
                    [
                        'title' => __('ui.portal_pages.descubra.cards.explore.title'),
                        'text' => __('ui.portal_pages.descubra.cards.explore.text'),
                        'href' => localized_route('site.explorar'),
                        'label' => __('ui.portal_pages.common.open_explore'),
                    ],
                    [
                        'title' => __('ui.portal_pages.descubra.cards.map.title'),
                        'text' => __('ui.portal_pages.descubra.cards.map.text'),
                        'href' => localized_route('site.mapa'),
                        'label' => __('ui.portal_pages.common.open_map'),
                    ],
                    [
                        'title' => __('ui.portal_pages.descubra.cards.semtur.title'),
                        'text' => __('ui.portal_pages.descubra.cards.semtur.text'),
                        'href' => localized_route('site.semtur'),
                        'label' => __('ui.portal_pages.common.see_semtur'),
                    ],
                ],
            ],

            'roteiros' => [
                'eyebrow' => __('ui.portal_pages.roteiros.eyebrow'),
                'title' => __('ui.portal_pages.roteiros.title'),
                'description' => __('ui.portal_pages.roteiros.description'),
                'lead' => __('ui.portal_pages.roteiros.lead'),
                'cta_label' => __('ui.portal_pages.roteiros.cta_label'),
                'cta_href' => localized_route('site.mapa'),
                'cards' => [
                    [
                        'title' => __('ui.portal_pages.roteiros.cards.one_day.title'),
                        'text' => __('ui.portal_pages.roteiros.cards.one_day.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => __('ui.portal_pages.roteiros.cards.nature.title'),
                        'text' => __('ui.portal_pages.roteiros.cards.nature.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => __('ui.portal_pages.roteiros.cards.culture.title'),
                        'text' => __('ui.portal_pages.roteiros.cards.culture.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'agenda' => [
                'eyebrow' => __('ui.portal_pages.agenda.eyebrow'),
                'title' => __('ui.agenda.title'),
                'description' => __('ui.portal_pages.agenda.description'),
                'lead' => __('ui.portal_pages.agenda.lead'),
                'cta_label' => __('ui.portal_pages.agenda.cta_label'),
                'cta_href' => localized_route('eventos.index'),
                'cards' => [
                    [
                        'title' => __('ui.portal_pages.agenda.cards.highlights.title'),
                        'text' => __('ui.portal_pages.agenda.cards.highlights.text'),
                        'href' => localized_route('eventos.index'),
                        'label' => __('ui.portal_pages.common.open_events'),
                    ],
                    [
                        'title' => __('ui.portal_pages.agenda.cards.period.title'),
                        'text' => __('ui.portal_pages.agenda.cards.period.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => __('ui.portal_pages.agenda.cards.signup.title'),
                        'text' => __('ui.portal_pages.agenda.cards.signup.text'),
                        'href' => localized_route('site.contato'),
                        'label' => __('ui.portal_pages.common.talk_to_team'),
                    ],
                ],
            ],

            'museus' => [
                'eyebrow' => __('ui.portal_pages.museus.eyebrow'),
                'title' => __('ui.portal_pages.museus.title'),
                'description' => __('ui.portal_pages.museus.description'),
                'lead' => __('ui.portal_pages.museus.lead'),
                'cta_label' => __('ui.portal_pages.museus.cta_label'),
                'cta_href' => localized_route('site.mapa'),
                'cards' => [
                    [
                        'title' => __('ui.portal_pages.museus.cards.spaces.title'),
                        'text' => __('ui.portal_pages.museus.cards.spaces.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => __('ui.portal_pages.museus.cards.hours.title'),
                        'text' => __('ui.portal_pages.museus.cards.hours.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => __('ui.portal_pages.museus.cards.booking.title'),
                        'text' => __('ui.portal_pages.museus.cards.booking.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'onde_comer' => [
                'eyebrow' => __('ui.portal_pages.onde_comer.eyebrow'),
                'title' => __('ui.portal_pages.onde_comer.title'),
                'description' => __('ui.portal_pages.onde_comer.description'),
                'lead' => __('ui.portal_pages.onde_comer.lead'),
                'cta_label' => __('ui.portal_pages.onde_comer.cta_label'),
                'cta_href' => localized_route('site.explorar'),
                'cards' => [
                    [
                        'title' => __('ui.portal_pages.onde_comer.cards.restaurants.title'),
                        'text' => __('ui.portal_pages.onde_comer.cards.restaurants.text'),
                        'href' => localized_route('site.explorar'),
                        'label' => __('ui.portal_pages.common.open_explore'),
                    ],
                    [
                        'title' => __('ui.portal_pages.onde_comer.cards.flavors.title'),
                        'text' => __('ui.portal_pages.onde_comer.cards.flavors.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'onde_ficar' => [
                'eyebrow' => __('ui.portal_pages.onde_ficar.eyebrow'),
                'title' => __('ui.portal_pages.onde_ficar.title'),
                'description' => __('ui.portal_pages.onde_ficar.description'),
                'lead' => __('ui.portal_pages.onde_ficar.lead'),
                'cta_label' => __('ui.portal_pages.onde_ficar.cta_label'),
                'cta_href' => localized_route('site.explorar'),
                'cards' => [
                    [
                        'title' => __('ui.portal_pages.onde_ficar.cards.hotels.title'),
                        'text' => __('ui.portal_pages.onde_ficar.cards.hotels.text'),
                        'href' => localized_route('site.explorar'),
                        'label' => __('ui.portal_pages.common.open_explore'),
                    ],
                    [
                        'title' => __('ui.portal_pages.onde_ficar.cards.stay.title'),
                        'text' => __('ui.portal_pages.onde_ficar.cards.stay.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'guias' => [
                'eyebrow' => __('ui.portal_pages.guias.eyebrow'),
                'title' => __('ui.portal_pages.guias.title'),
                'description' => __('ui.portal_pages.guias.description'),
                'lead' => __('ui.portal_pages.guias.lead'),
                'cta_label' => __('ui.portal_pages.guias.cta_label'),
                'cta_href' => localized_route('site.informacoes'),
                'cards' => [
                    [
                        'title' => __('ui.portal_pages.guias.cards.visitor_guide.title'),
                        'text' => __('ui.portal_pages.guias.cards.visitor_guide.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => __('ui.portal_pages.guias.cards.pdf_map.title'),
                        'text' => __('ui.portal_pages.guias.cards.pdf_map.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'informacoes' => [
                'eyebrow' => __('ui.portal_pages.informacoes.eyebrow'),
                'title' => __('ui.portal_pages.informacoes.title'),
                'description' => __('ui.portal_pages.informacoes.description'),
                'lead' => __('ui.portal_pages.informacoes.lead'),
                'cta_label' => __('ui.portal_pages.informacoes.cta_label'),
                'cta_href' => localized_route('site.contato'),
                'cards' => [
                    [
                        'title' => __('ui.portal_pages.informacoes.cards.arrival.title'),
                        'text' => __('ui.portal_pages.informacoes.cards.arrival.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => __('ui.portal_pages.informacoes.cards.support.title'),
                        'text' => __('ui.portal_pages.informacoes.cards.support.text'),
                        'href' => localized_route('site.contato'),
                        'label' => __('ui.portal_pages.common.see_contact'),
                    ],
                    [
                        'title' => __('ui.portal_pages.informacoes.cards.weather.title'),
                        'text' => __('ui.portal_pages.informacoes.cards.weather.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'contato' => [
                'eyebrow' => __('ui.portal_pages.contato.eyebrow'),
                'title' => __('ui.portal_pages.contato.title'),
                'description' => __('ui.portal_pages.contato.description'),
                'lead' => __('ui.portal_pages.contato.lead'),
                'cta_label' => __('ui.portal_pages.contato.cta_label'),
                'cta_href' => localized_route('site.semtur'),
                'cards' => [
                    [
                        'title' => __('ui.portal_pages.contato.cards.semtur.title'),
                        'text' => __('ui.portal_pages.contato.cards.semtur.text'),
                        'href' => localized_route('site.semtur'),
                        'label' => __('ui.portal_pages.common.see_semtur'),
                    ],
                    [
                        'title' => __('ui.portal_pages.contato.cards.partners.title'),
                        'text' => __('ui.portal_pages.contato.cards.partners.text'),
                        'href' => '#',
                        'label' => __('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],
        ];
    }
}
