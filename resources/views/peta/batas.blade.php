@extends('layouts.public')
@section('title', 'Batas Resmi')
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
    <span id="jumlah"></span>
</div>
<div id="peta" class="peta"></div>
<div class="panel" style="right: 12px; bottom: 24px; font-size: 13px;">
    <strong>Jumlah sekolah</strong>
    <div><span style="color: #dc2626;">&#9632;</span> Terbanyak</div>
    <div><span style="color: #ea580c;">&#9632;</span> Banyak</div>
    <div><span style="color: #f59e0b;">&#9632;</span> Sedang</div>
    <div><span style="color: #3b82f6;">&#9632;</span> Sedikit atau nol</div>
</div>
@endsection
@push('scripts')
<script>
    // SESUAIKAN dengan nama kolom pada berkas GeoJSON Anda
    const KOLOM_NAMA = 'NAMOBJ';
    // Saring hanya Banjarmasin; isi kolom dengan null bila berkas sudah hanya Banjarmasin
    const KOTA = { kolom: 'WADMKK', nilai: 'banjarmasin' };

    const map = Peta.buat('peta');
    let lapisan = null;

    // Samakan penulisan nama: huruf kecil, tanpa kata kecamatan, tanpa simbol
    const norm = (t) => String(t ?? '').toLowerCase()
        .replace(/kecamatan/g, '').replace(/[^a-z]/g, '');

    function warna(rasio) {
        if (rasio >= 0.8) return '#dc2626';
        if (rasio >= 0.6) return '#ea580c';
        if (rasio >= 0.4) return '#f59e0b';
        return '#3b82f6';
    }

    async function muat(jenjang = '') {
        try {
            const [batas, sekolah] = await Promise.all([
                fetch("{{ asset('geojson/kecamatan.geojson') }}").then((r) => {
                    if (!r.ok) throw new Error('Berkas batas tidak ditemukan');
                    return r.json();
                }),
                Peta.ambilSekolah(jenjang),
            ]);

            // Hitung sekolah per kecamatan dari data titik
            const hitung = {};
            sekolah.forEach((f) => {
                const k = norm(f.properties.kecamatan);
                hitung[k] = (hitung[k] ?? 0) + 1;
            });
            const maks = Math.max(1, ...Object.values(hitung));

            if (lapisan) map.removeLayer(lapisan);
            lapisan = L.geoJSON(batas, {
                filter: (f) => !KOTA.kolom
                    || norm(f.properties[KOTA.kolom]).includes(KOTA.nilai),
                style: (f) => {
                    const n = hitung[norm(f.properties[KOLOM_NAMA])] ?? 0;
                    const w = n === 0 ? '#9ca3af' : warna(n / maks);
                    return { color: w, fillColor: w, fillOpacity: 0.35, weight: 2 };
                },
                onEachFeature: (f, layer) => {
                    const nama = f.properties[KOLOM_NAMA];
                    const n = hitung[norm(nama)] ?? 0;
                    layer.bindTooltip(Peta.esc(nama), { sticky: true });
                    layer.bindPopup(`<strong>${Peta.esc(nama)}</strong><br>Jumlah sekolah: ${n}`);
                },
            }).addTo(map);

            document.getElementById('jumlah').textContent =
                sekolah.length + ' sekolah';
            if (lapisan.getLayers().length > 0) {
                map.fitBounds(lapisan.getBounds().pad(0.1));
            }
        } catch (e) {
            console.error(e);
            document.getElementById('jumlah').textContent = 'Gagal memuat batas wilayah';
        }
    }

    document.getElementById('filter-jenjang')
        .addEventListener('change', (e) => muat(e.target.value));
    muat();
</script>
@endpush