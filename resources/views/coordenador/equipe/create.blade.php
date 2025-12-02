@extends('console.layout')
@section('title','Novo Membro')
@section('page.title','Equipe — Novo membro')
@section('content')
  @include('coordenador.equipe._form', ['action'=>route('coordenador.equipe.store'),'method'=>'POST','membro'=>null])
@endsection
