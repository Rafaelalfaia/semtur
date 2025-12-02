@extends('layouts.app')
@section('title','Mapa')
@section('content')
<div class="max-w-6xl mx-auto p-4">
  <div id="map" class="w-full h-[60vh] rounded-xl border"></div>
</div>
@endsection

@push('scripts')
<script type="module">
  import { Loader } from '@googlemaps/js-api-loader'

  const loader = new Loader({
    apiKey: '{{ config('services.google.maps_key') }}',
    version: 'weekly',
    libraries: ['places']
  })

  loader.load().then(() => {
    const map = new google.maps.Map(document.getElementById('map'), {
      center: { lat: -3.2034, lng: -52.2060 }, // Altamira aprox
      zoom: 12,
      mapId: 'SEMTUR_MAP'
    })
  })
</script>
@endpush
