@extends('Admin.layout.master')

@section('page_title', 'Bank Lokasi')

@section('breadcrumb')
    <li class="breadcrumb-item">Bank Lokasi</li>
    <li class="breadcrumb-item active">Bank Lokasi</li>
@endsection

@section('content')
    <div class="main-content">

        {{-- Alert Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
                <i class="feather-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
                <i class="feather-alert-circle me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row px-4 pt-3">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="card-title mb-0" style="font-size:1.05rem;">
                            <i class="feather-key me-2 text-primary" style="font-size:1.15rem;"></i> Bank Lokasi
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahLokasi" style="font-size:0.9rem; padding: 0.4rem 0.95rem;">
                            <i class="feather-plus me-1" style="font-size:0.95rem;"></i> Tambah Akun
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="px-3 pt-3 pb-1">
                        <div class="input-group" style="max-width: 380px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="feather-search text-muted" style="font-size:0.95rem;"></i>
                            </span>
                            <input type="text" id="searchAkun" class="form-control border-start-0 ps-0"
                                placeholder="Cari NIP, nama guru..." style="font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabelJurusan" style="font-size:0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Nama Lokasi</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Radius</th>
                                        <th width="150" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabelJurusan">
                                    @foreach ($banklokasis as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-text avatar-sm rounded-circle bg-soft-success text-success fw-bold"
                                                        style="font-size:0.9rem; width:32px; height:32px; line-height:32px; text-align:center;">
                                                        {{ strtoupper(substr($data->nama_lokasi, 0, 1)) }}
                                                    </div>
                                                    <span class="fw-semibold">{{ $data->nama_lokasi }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $data->latitude }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $data->longitude }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $data->radius }} km</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">{{ $data->alamat ?? '-' }}</span>
                                            </td>

                                            <td class="text-center">
                                                {{-- Tombol Edit --}}
                                                <button type="button" class="btn btn-soft-warning btn-edit-jurusan"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditJurusan"
                                                    data-id="{{ $data->id }}"
                                                    data-nama_jurusan="{{ $data->nama_lokasi }}"
                                                    data-deskripsi="{{ $data->latitude }}"
                                                    data-deskripsi="{{ $data->longitude }}"
                                                    data-deskripsi="{{ $data->radius }}"
                                                    data-deskripsi="{{ $data->alamat }}" itle="Edit"
                                                    style="font-size:0.88rem; padding:0.3rem 0.6rem; margin-bottom:2px;">
                                                    <i class="feather-edit-2" style="font-size:0.95rem;"></i>
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <button type="button" class="btn btn-soft-danger btn-hapus-jurusan"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusJurusan"
                                                    data-id="{{ $data->id }}" data-nama="{{ $data->nama_lokasi }}"
                                                    data-deskripsi="{{ $data->latitude }}"
                                                    data-deskripsi="{{ $data->longitude }}"
                                                    data-deskripsi="{{ $data->radius }}"
                                                    data-deskripsi="{{ $data->alamat }}" title="Hapus"
                                                    style="font-size:0.88rem; padding:0.3rem 0.6rem; margin-bottom:2px;">
                                                    <i class="feather-trash-2" style="font-size:0.95rem;"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div id="noResultMsg" class="text-center py-5 text-muted d-none">
                                <i class="feather-search d-block mb-2" style="font-size:2rem;"></i>
                                <span style="font-size:0.9rem;">Data tidak ditemukan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .location-dropdown {
            display: none;
            position: absolute;
            z-index: 1050;
            width: 100%;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            max-height: 220px;
            overflow-y: auto;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15);
        }

        .location-dropdown.show {
            display: block;
        }

        .location-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f3f5;
            font-size: 0.9rem;
        }

        .location-item:hover {
            background-color: #e9ecef;
        }

        .location-item small {
            display: block;
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: 2px;
        }

        #petaLokasi {
            height: 320px;
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            margin-top: 10px;
        }
    </style>
@endpush

@push('modals')
    <div class="modal fade" id="modalTambahLokasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.bank-lokasi.store') }}" method="POST" id="formLokasi">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="feather-map-pin me-2 text-primary"></i> Tambah Lokasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            {{-- Nama Lokasi (diisi manual oleh user) --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama Lokasi <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_lokasi" id="inputNamaLokasi" class="form-control"
                                    placeholder="Contoh: SMKN 1 Tamanan Bondowoso" required>
                                <small class="text-muted">Ketik nama lokasi yang Anda inginkan</small>
                            </div>

                            {{-- Cari Lokasi di Peta --}}
                            <div class="col-md-12 position-relative">
                                <label class="form-label fw-semibold"><i class="feather-search me-1 text-primary"></i>Cari
                                    di Peta</label>
                                <input type="text" id="inputCariLokasi" class="form-control"
                                    placeholder="Ketik area/daerah: Tamanan Bondowoso, lalu pilih atau klik di peta..."
                                    autocomplete="off">
                                <div id="dropdownLokasi" class="location-dropdown"></div>
                                <small class="text-muted">Cari area terdekat, lalu klik tepat di peta untuk titik
                                    presisi</small>
                            </div>

                            {{-- Peta --}}
                            <div class="col-md-12">
                                <div id="petaLokasi"></div>
                                <small class="text-muted mt-1 d-block">📍 Klik langsung di peta untuk menentukan titik
                                    lokasi yang tepat</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" id="inputLat" class="form-control" readonly
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" id="inputLng" class="form-control" readonly
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Radius (meter)</label>
                                <input type="number" name="radius" class="form-control" placeholder="Contoh: 50"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat Lengkap</label>
                                <input type="text" name="alamat" id="inputAlamat" class="form-control"
                                    placeholder="Akan terisi otomatis atau isi manual">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="feather-save me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let map, marker;
            const searchInput = document.getElementById('inputCariLokasi');
            const dropdown = document.getElementById('dropdownLokasi');
            const latInput = document.getElementById('inputLat');
            const lngInput = document.getElementById('inputLng');
            const alamatInput = document.getElementById('inputAlamat');
            const namaInput = document.getElementById('inputNamaLokasi');
            let debounceTimer;

            // 1. Inisialisasi Leaflet (default view: Jawa Timur)
            map = L.map('petaLokasi').setView([-7.9, 113.8], 9);
            L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: 'Map data &copy; Google'
            }).addTo(map);

            // Fix Leaflet saat modal Bootstrap muncul
            document.getElementById('modalTambahLokasi').addEventListener('shown.bs.modal', function() {
                map.invalidateSize();
            });

            // Klik di peta untuk pilih koordinat
            map.on('click', function(e) {
                let lat = e.latlng.lat.toFixed(7);
                let lng = e.latlng.lng.toFixed(7);
                placeMarker(lat, lng, null);
                reverseGeocode(lat, lng);
            });

            // 2. Autocomplete: Gabungan Photon + Nominatim (lebih lengkap)
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                let query = this.value.trim();
                if (query.length < 3) {
                    dropdown.classList.remove('show');
                    dropdown.innerHTML = '';
                    return;
                }
                debounceTimer = setTimeout(() => {
                    // Loading indicator
                    dropdown.innerHTML =
                        '<div class="location-item text-muted"><i class="feather-loader me-2"></i>Mencari lokasi...</div>';
                    dropdown.classList.add('show');

                    // Fetch dari Photon (utama) dan Nominatim (cadangan) secara bersamaan
                    const photonUrl =
                        `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5&lang=id&lat=-7.5&lon=112.0`;
                    const nominatimUrl =
                        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=id&accept-language=id&addressdetails=1`;

                    Promise.allSettled([
                        fetch(photonUrl).then(r => r.json()),
                        fetch(nominatimUrl).then(r => r.json())
                    ]).then(([photonRes, nominatimRes]) => {
                        let results = [];
                        let seenKeys = new Set();

                        // Parse hasil Photon
                        if (photonRes.status === 'fulfilled' && photonRes.value.features) {
                            photonRes.value.features.forEach(f => {
                                let props = f.properties;
                                let coords = f.geometry.coordinates; // [lon, lat]
                                let nama = props.name || '';
                                let parts = [nama, props.street, props.district,
                                    props.city, props.county, props.state, props
                                    .country
                                ].filter(Boolean);
                                let alamat = parts.join(', ');
                                let key =
                                    `${parseFloat(coords[1]).toFixed(4)}_${parseFloat(coords[0]).toFixed(4)}`;

                                if (nama && !seenKeys.has(key)) {
                                    seenKeys.add(key);
                                    results.push({
                                        nama: nama,
                                        alamat: alamat,
                                        lat: coords[1],
                                        lon: coords[0],
                                        source: 'photon'
                                    });
                                }
                            });
                        }

                        // Parse hasil Nominatim (tambahkan yang belum ada)
                        if (nominatimRes.status === 'fulfilled' && Array.isArray(
                                nominatimRes.value)) {
                            nominatimRes.value.forEach(item => {
                                let key =
                                    `${parseFloat(item.lat).toFixed(4)}_${parseFloat(item.lon).toFixed(4)}`;
                                if (!seenKeys.has(key)) {
                                    seenKeys.add(key);
                                    let nama = item.display_name.split(',')[0]
                                    .trim();
                                    results.push({
                                        nama: nama,
                                        alamat: item.display_name,
                                        lat: item.lat,
                                        lon: item.lon,
                                        source: 'nominatim'
                                    });
                                }
                            });
                        }

                        // Tampilkan hasil
                        dropdown.innerHTML = '';
                        if (results.length === 0) {
                            dropdown.innerHTML =
                                '<div class="location-item text-muted"><i class="feather-alert-circle me-2"></i>Lokasi tidak ditemukan, coba kata kunci lain</div>';
                        } else {
                            results.slice(0, 8).forEach(item => {
                                let div = document.createElement('div');
                                div.className = 'location-item';
                                div.innerHTML =
                                    `<strong>${item.nama}</strong><small>${item.alamat}</small>`;
                                div.addEventListener('click', () => {
                                    pilihLokasi(item);
                                });
                                dropdown.appendChild(div);
                            });
                        }
                        dropdown.classList.add('show');
                    });
                }, 400);
            });

            // 3. Fungsi Pilih Lokasi (hanya isi koordinat & alamat, nama lokasi tetap)
            function pilihLokasi(item) {
                searchInput.value = item.nama;
                placeMarker(item.lat, item.lon, item.alamat);
                latInput.value = parseFloat(item.lat).toFixed(7);
                lngInput.value = parseFloat(item.lon).toFixed(7);
                alamatInput.value = item.alamat;
                // Auto-suggest nama jika field nama masih kosong
                if (!namaInput.value.trim()) {
                    namaInput.value = item.nama;
                }
                dropdown.classList.remove('show');
                dropdown.innerHTML = '';
            }

            // 4. Helper: Pasang Marker & Zoom
            function placeMarker(lat, lng, address) {
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map);
                map.setView([lat, lng], 16);
                latInput.value = lat;
                lngInput.value = lng;
                if (address) alamatInput.value = address;
            }

            // 5. Helper: Reverse Geocode (Klik Peta -> Alamat)
            function reverseGeocode(lat, lng) {
                fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=id`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.display_name) {
                            alamatInput.value = data.display_name;
                        }
                    });
            }

            // Tutup dropdown saat klik di luar
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#inputCariLokasi') && !e.target.closest('#dropdownLokasi')) {
                    dropdown.classList.remove('show');
                }
            });

            // Reset form saat modal ditutup
            document.getElementById('modalTambahLokasi').addEventListener('hidden.bs.modal', function() {
                document.getElementById('formLokasi').reset();
                if (marker) map.removeLayer(marker);
                dropdown.innerHTML = '';
                dropdown.classList.remove('show');
            });
        });
    </script>
@endpush
