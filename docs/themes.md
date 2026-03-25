# Modulo de Temas

## Objetivo
O modulo de temas governa identidade visual institucional. Ele nao cadastra conteudo editorial do coordenador.

Tema inclui:
- tokens visuais globais
- assets institucionais
- shell do console
- apresentacao de auth/login
- apresentacao do site publico
- vigencia, fallback, escopo e regras administrativas

Tema nao inclui:
- banners, banner principal, categorias, pontos, empresas, roteiros, paginas e modulos editoriais
- regras de publicacao de conteudo
- navegacao funcional ou permissoes editoriais fora do proprio modulo

## Escopos
Escopos suportados:
- `global`: vale para console, auth e site
- `console`: vale para Admin, Coordenador e Tecnico
- `site`: vale para o site publico
- `auth`: vale para login e telas de autenticacao

Nesta fase, `application_scopes` continua sendo resolvido via `config_json` para preservar compatibilidade sem exigir coluna nova.

## Ciclo de Vida
Status suportados:
- `rascunho`
- `disponivel`
- `arquivado`

Regras:
- preview e sempre local a sessao do Admin
- ativacao manual promove rascunho para disponivel
- tema arquivado nao pode ser preview nem ativo
- vigencia real respeita `starts_at` e `ends_at`
- tema ativo expirado cai para fallback seguro

## Ordem de Resolucao
Para cada contexto (`console`, `site`, `auth`), o resolver segue:
1. preview valido da sessao do Admin
2. tema ativo manual valido para o contexto
3. tema default valido para o contexto
4. primeiro tema disponivel valido para o contexto
5. fallback seguro da base CSS e dos assets padrao

## Tokens Oficiais
O contrato oficial fica centralizado em `App\\Models\\Theme::TOKEN_DEFINITIONS`.

Categorias principais:
- fundo e frame: `ui-app-*`, `ui-frame-*`, `ui-shell-*`
- sidebar e topbar: `ui-sidebar-*`, `ui-topbar-*`
- superficies, bordas e textos: `ui-surface*`, `ui-border*`, `ui-text*`
- identidade e estados: `ui-primary*`, `ui-success*`, `ui-warning*`, `ui-danger*`
- hero: `ui-hero-*`
- sombra, raio e espacamento: `ui-shadow-*`, `ui-radius-*`, `ui-spacing-*`

Tokens ausentes nao quebram o sistema. O CSS base continua fornecendo fallback seguro.

## Assets Oficiais
Assets suportados:
- `logo`
- `login_background`
- `hero_image`

Assets ausentes caem para `App\\Models\\Theme::DEFAULT_ASSETS`.

## Config JSON
O contrato oficial de `config_json` suporta apenas:
- `shell.variant`
- `shell.density`
- `site.variant`
- `site.hero_variant`
- `auth.variant`
- `auth.layout`
- `flags.use_gradient_hero`
- `flags.use_glass_topbar`
- `flags.emphasize_brand`
- `notes.internal`

Qualquer chave fora desse contrato e rejeitada na validacao administrativa.

## Auditoria
Rastreabilidade do modulo:
- `themes.created_by`
- `themes.updated_by`
- `theme_activity_logs`

Acoes logadas:
- `created`
- `updated`
- `previewed`
- `activated`
- `archived`

## Permissoes
Permissoes do modulo:
- `themes.view`
- `themes.create`
- `themes.edit`
- `themes.preview`
- `themes.activate`
- `themes.archive`

Admin continua sendo o dono do tema.

## Checklist de Validacao
- criar tema novo com escopo `global`
- criar tema com escopo `site` e verificar fallback no console
- ativar tema em rascunho e confirmar promocao para `disponivel`
- testar preview sem contaminar outra sessao/perfil
- testar tema expirado com queda para default
- remover asset e validar fallback oficial
- enviar `config_json` invalido e confirmar bloqueio


## Seguranca de Execucao
- nao usar migrate:fresh`r
- nao usar migrate:refresh`r
- nao rodar testes destrutivos enquanto .env.testing nao estiver isolado do banco restaurado

