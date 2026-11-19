@extends('layouts.public')
@section('title', 'Peta Sekolah')
@section('content')
<div class="toolbar">
    <label>Jenjang
        <select id="filter-jenjang">
            <option value="">Semua jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>
    </label>
    <label>Status
        <select id="filter-status">
            <option value="">Semua status</option>
            <option value="Negeri">Negeri</option>
            <option value="Swasta">Swasta</option>
        </select>
    </label>
    <span id="jumlah"></span>
    <button id="unduh" type="button">Unduh GeoJSON</button>
</div>
<div id="peta" class="peta"></div>
@endsection

@push('scripts')
<script>
    const map = Peta.buat('peta');
    const lapisan = L.layerGroup().addTo(map);
    let tampil = [];

    async function muat() {
        const jenjang = document.getElementById('filter-jenjang').value;
        const status = document.getElementById('filter-status').value;
        try {
            tampil = await Peta.ambilSekolah(jenjang, status);
            lapisan.clearLayers();
            tampil.forEach((f) => {
                L.marker(Peta.latlng(f)).bindPopup(Peta.popup(f)).addTo(lapisan);
            });
            document.getElementById('jumlah').textContent =
                tampil.length + ' sekolah';
            if (tampil.length > 0) {
                map.fitBounds(L.latLngBounds(tampil.map(Peta.latlng)).pad(0.1));
            }
        } catch (e) {
            document.getElementById('jumlah').textContent = 'Gagal memuat data';
        }
    }

    ['filter-jenjang', 'filter-status'].forEach((id) => {
        document.getElementById(id).addEventListener('change', muat);
    });

    // Latihan 7: unduh data yang sedang tampil sebagai berkas GeoJSON
    document.getElementById('unduh').addEventListener('click', () => {
        const jenjang = document.getElementById('filter-jenjang').value || 'semua';
        const status = document.getElementById('filter-status').value || 'semua';
        const isi = JSON.stringify(
            { type: 'FeatureCollection', features: tampil }, null, 2
        );
        const blob = new Blob([isi], { type: 'application/geo+json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `sekolah-${jenjang}-${status}.geojson`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }); 

    muat();
</script>
@endpush