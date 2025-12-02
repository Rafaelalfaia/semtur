@if ($errors->any())
  <div class="mb-4 rounded-xl border border-red-500/30 bg-red-900/20 px-4 py-3 text-red-200">
    <div class="font-semibold mb-1">Corrija os erros abaixo:</div>
    <ul class="list-disc pl-5 space-y-0.5">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if (session('ok'))
  <div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-900/20 px-4 py-3 text-emerald-200">
    {{ session('ok') }}
  </div>
@endif
