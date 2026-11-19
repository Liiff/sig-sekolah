@extends('layouts.public')
@section('title', 'Per Tampilan')
@section('content')
<div class="toolbar">
    <span id="jumlah"></span>
    <span style="color: #6b7280;">
        Geser atau perbesar peta; hanya sekolah di area yang terlihat yang diambil dari server.
    </span>
</div>
<div id="peta" class="peta"></div>
@endsection
@push('scripts')
<script>
    const map = Peta.buat('peta', 13);
    const lapisan = L.layerGroup().addTo(map);
    let tunda;
    let versi = 0;

    async function muat() {
        const saya = ++versi;
        try {
            const fitur = await Peta.ambilSekolah('', '', map.getBounds());
            if (saya !== versi) return; // abaikan respons yang sudah usang
            lapisan.clearLayers();
            fitur.forEach((f) => {
                L.marker(Peta.latlng(f)).bindPopup(Peta.popup(f)).addTo(lapisan);
            });
            document.getElementById('jumlah').textContent =
                fitur.length + ' sekolah di area peta';
        } catch (e) {
            document.getElementById('jumlah').textContent = 'Gagal memuat data';
        }
    }

    map.on('moveend', () => {
        clearTimeout(tunda);
        tunda = setTimeout(muat, 300);
    });
    muat();
</script>
@endpush