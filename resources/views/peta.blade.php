<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Peta Sekolah</title>
    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <style>
        html, body { height: 100%; margin: 0;
                     font-family: system-ui, sans-serif; }
        header { height: 56px; box-sizing: border-box; padding: 0 16px;
                 display: flex; align-items: center; gap: 16px;
                 background: #1e3a8a; color: #fff; }
        header h1 { font-size: 18px; margin: 0; }
        header a { color: #bfdbfe; margin-left: auto; }
        #map { height: calc(100% - 56px); }
    </style>
</head>
<body>
    <header>
        <h1>Peta Sekolah</h1>
        <select id="filter-jenjang">
            <option value="">Semua jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>
        <select id="filter-status">
            <option value="">Semua status</option>
            <option value="Negeri">Negeri</option>
            <option value="Swasta">Swasta</option>
        </select>
        <input type="text" id="cari" list="daftar-nama"
            placeholder="Cari nama sekolah, tekan Enter">
        <datalist id="daftar-nama"></datalist>
        <span id="jumlah"></span>
        @auth
            <a href="{{ route('dashboard') }}">
                Dashboard
            </a>
        @else
            <a href="{{ route('login') }}">
                Masuk
            </a>
        @endauth
    </header>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>

        const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        const topo = L.tileLayer('https://tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: 'Map data: &copy; OpenStreetMap contributors, SRTM | Style: &copy; OpenTopoMap (CC-BY-SA)'
        });

        const map = L.map('map', { layers: [osm] }).setView([-3.3194, 114.5908], 12);

        L.control.layers({
            'OpenStreetMap': osm,
            'OpenTopoMap': topo
        }).addTo(map);

        // Mencegah teks dari database dibaca sebagai HTML (XSS)
        function esc(teks) {
            const d = document.createElement('div');
            d.textContent = teks ?? '';
            return d.innerHTML;
        }

        const warna = { SD: '#dc2626', SMP: '#2563eb', SMA: '#16a34a', SMK: '#d97706' };

        function ikon(jenjang) {
            return L.divIcon({
                className: '',
                html: `<div style="background:${warna[jenjang] || '#555'};width:18px;height:18px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 4px rgba(0,0,0,.6)"></div>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9],
                popupAnchor: [0, -10]
            });
        }
        const indeksNama = {};
        const klaster = L.markerClusterGroup().addTo(map);

        const lapisan = L.geoJSON(null, {
            pointToLayer: (fitur, latlng) =>
                L.marker(latlng, { icon: ikon(fitur.properties.jenjang) }),
            onEachFeature: (fitur, layer) => {
                const p = fitur.properties;
                const [lng, lat] = fitur.geometry.coordinates;
                layer.bindPopup(`
                    <strong>${esc(p.nama)}</strong><br>
                    NPSN: ${esc(p.npsn)}<br>
                    ${esc(p.jenjang)} - ${esc(p.status)}<br>
                    ${esc(p.alamat)}, ${esc(p.kecamatan)}<br>
                    Telp: ${esc(p.telepon) || '-'}<br>
                    Kepala sekolah: ${esc(p.kepala_sekolah) || '-'}<br>
                    Akreditasi: ${esc(p.akreditasi) || '-'}<br>
                    Siswa: ${p.jumlah_siswa ?? '-'} | Guru: ${p.jumlah_guru ?? '-'}<br>
                    <small>Koordinat: ${lat}, ${lng}</small>
                `);
                indeksNama[p.nama.toLowerCase()] = layer;
            }
        });

        document.getElementById('cari').addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            const kunci = e.target.value.trim().toLowerCase();

            let marker = indeksNama[kunci];
            if (!marker) {
                const cocok = Object.keys(indeksNama).find((n) => n.includes(kunci));
                marker = cocok ? indeksNama[cocok] : null;
            }
            if (!marker) { alert('Sekolah tidak ditemukan.'); return; }

            klaster.zoomToShowLayer(marker, () => marker.openPopup());
        });

        async function muatData() {
            const params = new URLSearchParams({
                jenjang: document.getElementById('filter-jenjang').value,
                status: document.getElementById('filter-status').value
            });
            const respon = await fetch(`{{ route('peta.data') }}?${params}`);
            const data = await respon.json();

            // 1. Kosongkan semua data lama
            Object.keys(indeksNama).forEach((k) => delete indeksNama[k]); 
            klaster.clearLayers();                                         
            lapisan.clearLayers();

            // 2. Isi data baru
            lapisan.addData(data);
            klaster.addLayer(lapisan);                                     

            // 3. Perbarui tampilan
            document.getElementById('jumlah').textContent =
                data.features.length + ' sekolah';

            document.getElementById('daftar-nama').innerHTML = data.features   
                .map((f) => `<option value="${esc(f.properties.nama)}">`).join('');

            if (data.features.length > 0) {
                map.fitBounds(klaster.getBounds().pad(0.1));               
            }
        }

        document.getElementById('filter-jenjang').addEventListener('change', muatData);
        document.getElementById('filter-status').addEventListener('change', muatData);

        muatData();
    </script>
</body>
</html>