# AGENTS.md

## Objetivo
Este repositório contém um projeto Laravel já funcional. Em tarefas de manutenção, refinamento visual e evolução do painel, o agente deve preservar a estabilidade do sistema, respeitar a arquitetura existente e evitar mudanças desnecessárias em regras de negócio.

## Princípios Obrigatórios

### 1. Preservar regras de negócio
- Não alterar regras de negócio sem solicitação explícita.
- Não mudar comportamento funcional apenas para acomodar refactor visual.
- Não alterar contratos de dados, permissões, policies, validações, queries ou fluxos de navegação em tarefas visuais.

### 2. Evitar mudanças em controllers e rotas durante refactors visuais
- Em refactors de UI, priorizar alterações em Blade, componentes, estilos e estrutura visual.
- Não modificar controllers, requests, middlewares, policies ou rotas, exceto quando o usuário pedir explicitamente.
- Se uma melhoria visual parecer exigir mudança backend, pausar e sinalizar antes de prosseguir.

### 3. Preferir componentes reutilizáveis
- Sempre que houver repetição visual, preferir extrair ou reutilizar componentes Blade.
- Antes de criar novo componente, verificar se já existe estrutura compatível no projeto.
- Reaproveitar padrões já existentes de cards, headers, tabelas, badges, botões e blocos de dashboard.
- Evitar criar variantes paralelas sem necessidade clara.

### 4. Concentrar temas em tokens semânticos
- Toda preparação para temas deve ser centralizada em tokens semânticos no CSS principal.
- Usar variáveis de cor, superfície, borda, sombra, raio, texto e estados.
- Não espalhar cores fixas diretamente nas páginas Blade.
- Evitar hardcode visual em views quando o valor puder viver como token ou classe utilitária semântica.

### 5. Não duplicar layouts
- Não criar layout paralelo se já houver uma base aproveitável.
- Consolidar shells administrativos em uma estrutura única sempre que possível.
- Ao evoluir sidebar, topbar ou frame do painel, preferir fortalecer o layout principal existente em vez de introduzir outro.

### 6. Trabalhar com diffs pequenos e auditáveis
- Fazer mudanças em etapas pequenas.
- Priorizar diffs limitados, reversíveis e fáceis de revisar.
- Em tarefas maiores, dividir o trabalho por fases: shell, componentes, páginas específicas, acabamento.
- Não misturar refactor estrutural com mudança funcional no mesmo passo.

### 7. Sempre propor validação
- Toda alteração proposta deve vir acompanhada de uma forma clara de validação.
- Sempre listar:
  - arquivos alterados;
  - risco da alteração;
  - como validar;
  - o que não foi alterado.
- Ao final de cada etapa, resumir impactos e próximos passos possíveis.

### 8. Respeitar a arquitetura Laravel do projeto
- Manter a separação de responsabilidades entre rotas, controllers, requests, models, policies, Blade e assets.
- Views devem continuar responsáveis por apresentação.
- Não mover lógica de negócio para Blade.
- Não introduzir acoplamentos desnecessários entre camadas.
- Seguir convenções existentes do projeto antes de propor novas abstrações.

### 9. Evitar hardcode visual nas páginas
- Não inserir cores, sombras, raios, espaçamentos ou estados repetidos diretamente em múltiplas páginas.
- Preferir classes reutilizáveis e tokens centralizados.
- Se uma página exigir tratamento especial, ainda assim tentar resolver com abstrações consistentes.

## Ordem Preferencial em Tarefas Visuais
1. Ler os arquivos existentes envolvidos.
2. Mapear layout principal, componentes reutilizáveis e CSS base.
3. Explicar o plano antes de editar.
4. Alterar primeiro a base compartilhada de layout/tokens.
5. Ajustar componentes reutilizáveis.
6. Só depois adaptar páginas específicas.
7. Validar visualmente e tecnicamente sem alterar comportamento funcional.

## O que evitar
- Alterar controllers sem necessidade.
- Alterar rotas em refactors visuais.
- Duplicar sidebar/topbar/layout.
- Introduzir estilos hardcoded em várias páginas.
- Fazer refactors grandes sem plano e sem validação.
- Trocar estrutura existente por outra paralela apenas por preferência estética.

## Saída esperada do agente em cada etapa
Antes de editar, o agente deve informar:
- arquivos que pretende tocar;
- risco de cada alteração;
- como validar;
- o que não será alterado.

Ao final de cada etapa, o agente deve resumir:
- o que foi alterado;
- impactos;
- possíveis próximos passos.
