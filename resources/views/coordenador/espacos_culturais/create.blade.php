@extends('console.layout')

@section('title', 'Cadastrar Espaco Cultural')
@section('page.title', 'Cadastrar Espaco Cultural')
@section('topbar.description', 'Cadastre museus e teatros com horarios, midia e configuracao de agendamento no padrao do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-console-topbar-tab">Espacos culturais</a>
  <span class="ui-console-topbar-tab is-active">Novo espaco</span>
@endsection

@section('content')
<div class="ui-console-page">
  <x-dashboard.page-header
    title="Novo espaco cultural"
    subtitle="Cadastre museus e teatros com horarios, midia, localizacao e agendamento sem sair do shell compartilhado."
  >
    <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-btn-secondary">Ver listagem</a>
  </x-dashboard.page-header>

  <form action="{{ route('coordenador.espacos-culturais.store') }}" method="POST" enctype="multipart/form-data" class="mt-5">
    @csrf
    @include('coordenador.espacos_culturais._form')
  </form>
</div>
@endsection
