@extends('layouts.public')
@section('title', 'Gabungan')
@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">
<link rel="stylesheet"
      href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">
@endpush
@section('content')
<div class="toolbar">
    <span id="jumlah"></span>
    <span style="color: #6b7280;">
        Pakai kontrol di kanan atas untuk mengganti peta dasar dan lapisan.
    </span>
</div>
<div id="peta" class="peta"></div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
    const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    });
    const topo = L.tileLayer('https://tile.opentopomap.org/{z}/{x}/{y}.png', {
        maxZoom: 17,
        attribution: 'Data: &copy; OpenStreetMap contributors, SRTM | Gaya: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (CC-BY-SA)',
    });
    const map = L.map('peta', { layers: [osm] }).setView(Peta.pusat, 12);

    Peta.ambilSekolah().then((fitur) => {
        // Satu marker tidak boleh berada di dua grup, jadi dibuat dua kali
        const buatMarker = (f) =>
            L.marker(Peta.latlng(f)).bindPopup(Peta.popup(f));

        const lapisMarker = L.layerGroup(fitur.map(buatMarker));

        const lapisKlaster = L.markerClusterGroup({ chunkedLoading: true });
        lapisKlaster.addLayers(fitur.map(buatMarker));

        const lapisPanas = L.heatLayer(
            fitur.map((f) => [...Peta.latlng(f), 0.35]),
            { radius: 25, blur: 25, maxZoom: 17, max: 1.0 }
        );

        lapisKlaster.addTo(map); // lapisan yang menyala di awal

        L.control.layers(
            { 'OpenStreetMap': osm, 'OpenTopoMap': topo },
            { 'Marker': lapisMarker, 'Klaster': lapisKlaster, 'Peta Panas': lapisPanas },
            { collapsed: false }
        ).addTo(map);

        document.getElementById('jumlah').textContent = fitur.length + ' sekolah';
        if (fitur.length > 0) {
            map.fitBounds(L.latLngBounds(fitur.map(Peta.latlng)).pad(0.1));
        }
    });
</script>
@endpush