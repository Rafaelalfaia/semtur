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
                'eyebrow' => ui_text('ui.portal_pages.descubra.eyebrow'),
                'title' => ui_text('ui.portal_pages.descubra.title'),
                'description' => ui_text('ui.portal_pages.descubra.description'),
                'lead' => ui_text('ui.portal_pages.descubra.lead'),
                'cta_label' => ui_text('ui.portal_pages.descubra.cta_label'),
                'cta_href' => localized_route('site.explorar'),
                'cards' => [
                    [
                        'title' => ui_text('ui.portal_pages.descubra.cards.explore.title'),
                        'text' => ui_text('ui.portal_pages.descubra.cards.explore.text'),
                        'href' => localized_route('site.explorar'),
                        'label' => ui_text('ui.portal_pages.common.open_explore'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.descubra.cards.map.title'),
                        'text' => ui_text('ui.portal_pages.descubra.cards.map.text'),
                        'href' => localized_route('site.mapa'),
                        'label' => ui_text('ui.portal_pages.common.open_map'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.descubra.cards.semtur.title'),
                        'text' => ui_text('ui.portal_pages.descubra.cards.semtur.text'),
                        'href' => localized_route('site.semtur'),
                        'label' => ui_text('ui.portal_pages.common.see_semtur'),
                    ],
                ],
            ],

            'roteiros' => [
                'eyebrow' => ui_text('ui.portal_pages.roteiros.eyebrow'),
                'title' => ui_text('ui.portal_pages.roteiros.title'),
                'description' => ui_text('ui.portal_pages.roteiros.description'),
                'lead' => ui_text('ui.portal_pages.roteiros.lead'),
                'cta_label' => ui_text('ui.portal_pages.roteiros.cta_label'),
                'cta_href' => localized_route('site.mapa'),
                'cards' => [
                    [
                        'title' => ui_text('ui.portal_pages.roteiros.cards.one_day.title'),
                        'text' => ui_text('ui.portal_pages.roteiros.cards.one_day.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.roteiros.cards.nature.title'),
                        'text' => ui_text('ui.portal_pages.roteiros.cards.nature.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.roteiros.cards.culture.title'),
                        'text' => ui_text('ui.portal_pages.roteiros.cards.culture.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'agenda' => [
                'eyebrow' => ui_text('ui.portal_pages.agenda.eyebrow'),
                'title' => ui_text('ui.agenda.title'),
                'description' => ui_text('ui.portal_pages.agenda.description'),
                'lead' => ui_text('ui.portal_pages.agenda.lead'),
                'cta_label' => ui_text('ui.portal_pages.agenda.cta_label'),
                'cta_href' => localized_route('eventos.index'),
                'cards' => [
                    [
                        'title' => ui_text('ui.portal_pages.agenda.cards.highlights.title'),
                        'text' => ui_text('ui.portal_pages.agenda.cards.highlights.text'),
                        'href' => localized_route('eventos.index'),
                        'label' => ui_text('ui.portal_pages.common.open_events'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.agenda.cards.period.title'),
                        'text' => ui_text('ui.portal_pages.agenda.cards.period.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.agenda.cards.signup.title'),
                        'text' => ui_text('ui.portal_pages.agenda.cards.signup.text'),
                        'href' => localized_route('site.contato'),
                        'label' => ui_text('ui.portal_pages.common.talk_to_team'),
                    ],
                ],
            ],

            'museus' => [
                'eyebrow' => ui_text('ui.portal_pages.museus.eyebrow'),
                'title' => ui_text('ui.portal_pages.museus.title'),
                'description' => ui_text('ui.portal_pages.museus.description'),
                'lead' => ui_text('ui.portal_pages.museus.lead'),
                'cta_label' => ui_text('ui.portal_pages.museus.cta_label'),
                'cta_href' => localized_route('site.mapa'),
                'cards' => [
                    [
                        'title' => ui_text('ui.portal_pages.museus.cards.spaces.title'),
                        'text' => ui_text('ui.portal_pages.museus.cards.spaces.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.museus.cards.hours.title'),
                        'text' => ui_text('ui.portal_pages.museus.cards.hours.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.museus.cards.booking.title'),
                        'text' => ui_text('ui.portal_pages.museus.cards.booking.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'onde_comer' => [
                'eyebrow' => ui_text('ui.portal_pages.onde_comer.eyebrow'),
                'title' => ui_text('ui.portal_pages.onde_comer.title'),
                'description' => ui_text('ui.portal_pages.onde_comer.description'),
                'lead' => ui_text('ui.portal_pages.onde_comer.lead'),
                'cta_label' => ui_text('ui.portal_pages.onde_comer.cta_label'),
                'cta_href' => localized_route('site.explorar'),
                'cards' => [
                    [
                        'title' => ui_text('ui.portal_pages.onde_comer.cards.restaurants.title'),
                        'text' => ui_text('ui.portal_pages.onde_comer.cards.restaurants.text'),
                        'href' => localized_route('site.explorar'),
                        'label' => ui_text('ui.portal_pages.common.open_explore'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.onde_comer.cards.flavors.title'),
                        'text' => ui_text('ui.portal_pages.onde_comer.cards.flavors.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'onde_ficar' => [
                'eyebrow' => ui_text('ui.portal_pages.onde_ficar.eyebrow'),
                'title' => ui_text('ui.portal_pages.onde_ficar.title'),
                'description' => ui_text('ui.portal_pages.onde_ficar.description'),
                'lead' => ui_text('ui.portal_pages.onde_ficar.lead'),
                'cta_label' => ui_text('ui.portal_pages.onde_ficar.cta_label'),
                'cta_href' => localized_route('site.explorar'),
                'cards' => [
                    [
                        'title' => ui_text('ui.portal_pages.onde_ficar.cards.hotels.title'),
                        'text' => ui_text('ui.portal_pages.onde_ficar.cards.hotels.text'),
                        'href' => localized_route('site.explorar'),
                        'label' => ui_text('ui.portal_pages.common.open_explore'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.onde_ficar.cards.stay.title'),
                        'text' => ui_text('ui.portal_pages.onde_ficar.cards.stay.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'guias' => [
                'eyebrow' => ui_text('ui.portal_pages.guias.eyebrow'),
                'title' => ui_text('ui.portal_pages.guias.title'),
                'description' => ui_text('ui.portal_pages.guias.description'),
                'lead' => ui_text('ui.portal_pages.guias.lead'),
                'cta_label' => ui_text('ui.portal_pages.guias.cta_label'),
                'cta_href' => localized_route('site.informacoes'),
                'cards' => [
                    [
                        'title' => ui_text('ui.portal_pages.guias.cards.visitor_guide.title'),
                        'text' => ui_text('ui.portal_pages.guias.cards.visitor_guide.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.guias.cards.pdf_map.title'),
                        'text' => ui_text('ui.portal_pages.guias.cards.pdf_map.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'informacoes' => [
                'eyebrow' => ui_text('ui.portal_pages.informacoes.eyebrow'),
                'title' => ui_text('ui.portal_pages.informacoes.title'),
                'description' => ui_text('ui.portal_pages.informacoes.description'),
                'lead' => ui_text('ui.portal_pages.informacoes.lead'),
                'cta_label' => ui_text('ui.portal_pages.informacoes.cta_label'),
                'cta_href' => localized_route('site.contato'),
                'cards' => [
                    [
                        'title' => ui_text('ui.portal_pages.informacoes.cards.arrival.title'),
                        'text' => ui_text('ui.portal_pages.informacoes.cards.arrival.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.informacoes.cards.support.title'),
                        'text' => ui_text('ui.portal_pages.informacoes.cards.support.text'),
                        'href' => localized_route('site.contato'),
                        'label' => ui_text('ui.portal_pages.common.see_contact'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.informacoes.cards.weather.title'),
                        'text' => ui_text('ui.portal_pages.informacoes.cards.weather.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],

            'contato' => [
                'eyebrow' => ui_text('ui.portal_pages.contato.eyebrow'),
                'title' => ui_text('ui.portal_pages.contato.title'),
                'description' => ui_text('ui.portal_pages.contato.description'),
                'lead' => ui_text('ui.portal_pages.contato.lead'),
                'cta_label' => ui_text('ui.portal_pages.contato.cta_label'),
                'cta_href' => localized_route('site.semtur'),
                'cards' => [
                    [
                        'title' => ui_text('ui.portal_pages.contato.cards.semtur.title'),
                        'text' => ui_text('ui.portal_pages.contato.cards.semtur.text'),
                        'href' => localized_route('site.semtur'),
                        'label' => ui_text('ui.portal_pages.common.see_semtur'),
                    ],
                    [
                        'title' => ui_text('ui.portal_pages.contato.cards.partners.title'),
                        'text' => ui_text('ui.portal_pages.contato.cards.partners.text'),
                        'href' => '#',
                        'label' => ui_text('ui.portal_pages.common.coming_soon'),
                    ],
                ],
            ],
        ];
    }
}

