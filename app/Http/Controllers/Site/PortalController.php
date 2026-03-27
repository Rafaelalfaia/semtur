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
                'eyebrow' => 'Planeje sua viagem',
                'title' => 'Descubra Altamira',
                'description' => 'Ponto de entrada para explorar Altamira por experiências, mapa, cultura, gastronomia e serviços.',
                'lead' => 'Esta seção será o hub principal para quem quer conhecer Altamira com clareza, antes mesmo de entrar no catálogo completo.',
                'cta_label' => 'Explorar catálogo',
                'cta_href' => route('site.explorar'),
                'cards' => [
                    [
                        'title' => 'Explorar atrações e empresas',
                        'text' => 'Acesse o catálogo atual de pontos turísticos, empresas e experiências locais.',
                        'href' => route('site.explorar'),
                        'label' => 'Abrir explorar',
                    ],
                    [
                        'title' => 'Mapa turístico',
                        'text' => 'Veja Altamira em mapa com localização real dos atrativos e empresas.',
                        'href' => route('site.mapa'),
                        'label' => 'Abrir mapa',
                    ],
                    [
                        'title' => 'SEMTUR',
                        'text' => 'Conheça a secretaria e os canais institucionais ligados ao turismo.',
                        'href' => route('site.semtur'),
                        'label' => 'Ver SEMTUR',
                    ],
                ],
            ],

            'roteiros' => [
                'eyebrow' => 'Curadoria turística',
                'title' => 'Roteiros',
                'description' => 'Roteiros por duração da viagem e por perfil de visitante.',
                'lead' => 'Aqui entrarão os roteiros prontos do VisitAltamira, usando dados reais do sistema e curadoria editorial.',
                'cta_label' => 'Ver mapa',
                'cta_href' => route('site.mapa'),
                'cards' => [
                    [
                        'title' => 'Altamira em 1 dia',
                        'text' => 'Roteiro rápido para quem quer conhecer os destaques essenciais.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                    [
                        'title' => 'Natureza e rio',
                        'text' => 'Percurso pensado para visitantes interessados em paisagem, rio e experiências naturais.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                    [
                        'title' => 'Cultura e memória',
                        'text' => 'Roteiro voltado para história, memória e identidade local.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                ],
            ],

            'agenda' => [
                'eyebrow' => 'Programação da cidade',
                'title' => 'Agenda',
                'description' => 'Agenda pública de eventos, temporadas, festivais e programações especiais.',
                'lead' => 'Nesta fase, a página nasce como entrada oficial da agenda. Depois ela será conectada ao módulo completo de eventos.',
                'cta_label' => 'Ver eventos atuais',
                'cta_href' => route('eventos.index'),
                'cards' => [
                    [
                        'title' => 'Eventos em destaque',
                        'text' => 'Área para destaques da semana, programação sazonal e agenda turística.',
                        'href' => route('eventos.index'),
                        'label' => 'Abrir eventos',
                    ],
                    [
                        'title' => 'Programação por período',
                        'text' => 'Depois esta seção terá filtros por data, categoria e gratuidade.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                    [
                        'title' => 'Contato de inscrição',
                        'text' => 'Eventos poderão ter CTA de inscrição, reserva ou contato direto.',
                        'href' => route('site.contato'),
                        'label' => 'Falar com a equipe',
                    ],
                ],
            ],

            'museus' => [
                'eyebrow' => 'Cultura e visitação',
                'title' => 'Museus e Teatros',
                'description' => 'Espaços culturais, horários de visita e futuro agendamento público.',
                'lead' => 'Esta página já entra como porta oficial do módulo que depois receberá cadastro editorial, horários, vagas e agendamento.',
                'cta_label' => 'Ver no mapa',
                'cta_href' => route('site.mapa'),
                'cards' => [
                    [
                        'title' => 'Espaços culturais',
                        'text' => 'Museus, teatros e espaços de visitação cultural de Altamira.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                    [
                        'title' => 'Horários de visita',
                        'text' => 'Cada espaço terá dias, faixas de horário e regras de acesso.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                    [
                        'title' => 'Agendamento',
                        'text' => 'O visitante poderá reservar visita diretamente pelo portal.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                ],
            ],

            'onde_comer' => [
                'eyebrow' => 'Sabores locais',
                'title' => 'Onde comer',
                'description' => 'Página pública dedicada à gastronomia, restaurantes, cafés e culinária local.',
                'lead' => 'Nesta fase ela entra como seção estruturada do portal. Depois poderá puxar filtro real por categoria.',
                'cta_label' => 'Explorar empresas',
                'cta_href' => route('site.explorar'),
                'cards' => [
                    [
                        'title' => 'Restaurantes e lanches',
                        'text' => 'Empresas gastronômicas com presença no catálogo.',
                        'href' => route('site.explorar'),
                        'label' => 'Abrir explorar',
                    ],
                    [
                        'title' => 'Sabores da cidade',
                        'text' => 'Curadoria futura de gastronomia local e experiências alimentares.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                ],
            ],

            'onde_ficar' => [
                'eyebrow' => 'Hospedagem',
                'title' => 'Onde ficar',
                'description' => 'Hotéis, pousadas e opções de hospedagem para planejar a estadia.',
                'lead' => 'Nesta etapa a seção entra como ponto oficial do portal e depois recebe filtros próprios.',
                'cta_label' => 'Ver hospedagens',
                'cta_href' => route('site.explorar'),
                'cards' => [
                    [
                        'title' => 'Hotéis e pousadas',
                        'text' => 'Entradas do catálogo ligadas a hospedagem.',
                        'href' => route('site.explorar'),
                        'label' => 'Abrir explorar',
                    ],
                    [
                        'title' => 'Planejar estadia',
                        'text' => 'Depois esta área poderá destacar localização, faixa de preço e perfil.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                ],
            ],

            'guias' => [
                'eyebrow' => 'Materiais do visitante',
                'title' => 'Guias',
                'description' => 'Central futura de guias, PDFs, materiais sazonais e apoio ao visitante.',
                'lead' => 'Esta seção será importante para concentrar materiais oficiais do destino.',
                'cta_label' => 'Ver informações úteis',
                'cta_href' => route('site.informacoes'),
                'cards' => [
                    [
                        'title' => 'Guia do visitante',
                        'text' => 'Material institucional para orientar quem visita Altamira.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                    [
                        'title' => 'Mapa turístico em PDF',
                        'text' => 'Versões para download, impressão e apoio a eventos.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                ],
            ],

            'informacoes' => [
                'eyebrow' => 'Serviço ao turista',
                'title' => 'Informações úteis',
                'description' => 'Como chegar, contatos úteis, clima, orientações e apoio ao visitante.',
                'lead' => 'Aqui entra a camada prática do portal: menos vitrine e mais serviço público.',
                'cta_label' => 'Entrar em contato',
                'cta_href' => route('site.contato'),
                'cards' => [
                    [
                        'title' => 'Como chegar',
                        'text' => 'Orientações futuras de acesso, deslocamento e localização.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                    [
                        'title' => 'Contatos e apoio',
                        'text' => 'Canais úteis de atendimento ao visitante e suporte institucional.',
                        'href' => route('site.contato'),
                        'label' => 'Ver contato',
                    ],
                    [
                        'title' => 'Clima e planejamento',
                        'text' => 'Dicas para organizar a visita de acordo com período e perfil.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                ],
            ],

            'contato' => [
                'eyebrow' => 'Atendimento',
                'title' => 'Contato',
                'description' => 'Canal oficial de contato do portal VisitAltamira e da SEMTUR.',
                'lead' => 'Nesta fase, a página entra como ponto oficial de atendimento e relacionamento.',
                'cta_label' => 'Ver SEMTUR',
                'cta_href' => route('site.semtur'),
                'cards' => [
                    [
                        'title' => 'Fale com a SEMTUR',
                        'text' => 'Área institucional ligada à secretaria e presença oficial do destino.',
                        'href' => route('site.semtur'),
                        'label' => 'Abrir SEMTUR',
                    ],
                    [
                        'title' => 'Parcerias e informações',
                        'text' => 'Espaço futuro para dúvidas, parcerias e atendimento ao trade.',
                        'href' => '#',
                        'label' => 'Em breve',
                    ],
                ],
            ],
        ];
    }
}
