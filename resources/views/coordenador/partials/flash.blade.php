@if ($errors->any())
  <div class="ui-alert ui-alert-danger mb-4">
    <div class="font-semibold mb-1">Corrija os erros abaixo:</div>
    <ul class="list-disc pl-5 space-y-0.5">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if (session('ok'))
  <div class="ui-alert ui-alert-success mb-4">
    {{ session('ok') }}
  </div>
@endif
