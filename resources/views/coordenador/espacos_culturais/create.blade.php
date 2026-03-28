@extends('console.layout')

@section('title', 'Cadastrar Espaço Cultural')
@section('page.title', 'Cadastrar Espaço Cultural')
@section('topbar.description', 'Cadastre museus e teatros com horários, mídia e configuração de agendamento no padrão do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-console-topbar-tab">Espaços culturais</a>
  <span class="ui-console-topbar-tab is-active">Novo espaço</span>
@endsection

@section('content')
<div class="ui-console-page">
  <x-dashboard.page-header
    title="Novo espaço cultural"
    subtitle="Cadastre museus e teatros com horários, mídia, localização e agendamento sem sair do shell compartilhado."
  >
    <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-btn-secondary">Ver listagem</a>
  </x-dashboard.page-header>

  <form action="{{ route('coordenador.espacos-culturais.store') }}" method="POST" enctype="multipart/form-data" class="mt-5">
    @csrf
    @include('coordenador.espacos_culturais._form')
  </form>
</div>
@endsection
