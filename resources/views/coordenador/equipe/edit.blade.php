@extends('console.layout')
@section('title','Editar Membro')
@section('page.title','Equipe — Editar membro')
@section('content')
  @include('coordenador.equipe._form', ['action'=>route('coordenador.equipe.update',$membro),'method'=>'PUT','membro'=>$membro])
@endsection
