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
                            <i class="feather-map-pin me-2 text-primary" style="font-size:1.15rem;"></i> Bank Lokasi
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahLokasi" style="font-size:0.9rem; padding: 0.4rem 0.95rem;">
                            <i class="feather-plus me-1" style="font-size:0.95rem;"></i> Tambah Lokasi
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="px-3 pt-3 pb-1">
                        <div class="input-group" style="max-width: 380px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="feather-search text-muted" style="font-size:0.95rem;"></i>
                            </span>
                            <input type="text" id="searchLokasi" class="form-control border-start-0 ps-0"
                                placeholder="Cari nama lokasi, alamat..." style="font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabelLokasi" style="font-size:0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Nama Lokasi</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Radius</th>
                                        <th>Alamat</th>
                                        <th width="120" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabelLokasi">
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
                                                <span class="badge bg-soft-primary text-primary rounded-pill px-2">{{ $data->radius }} meter</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $data->alamat ?? '-' }}</span>
                                            </td>

                                            <td class="text-center">
                                                {{-- Tombol Edit --}}
                                                <button type="button" class="btn btn-soft-warning btn-edit-lokasi"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditLokasi"
                                                    data-id="{{ $data->id }}"
                                                    data-nama="{{ $data->nama_lokasi }}"
                                                    data-latitude="{{ $data->latitude }}"
                                                    data-longitude="{{ $data->longitude }}"
                                                    data-radius="{{ $data->radius }}"
                                                    data-alamat="{{ $data->alamat }}" title="Edit"
                                                    style="font-size:0.88rem; padding:0.3rem 0.6rem; margin-bottom:2px;">
                                                    <i class="feather-edit-2" style="font-size:0.95rem;"></i>
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <button type="button" class="btn btn-soft-danger btn-hapus-lokasi"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusLokasi"
                                                    data-id="{{ $data->id }}" data-nama="{{ $data->nama_lokasi }}"
                                                    title="Hapus"
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

        .peta-container {
            height: 320px;
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            margin-top: 10px;
        }
    </style>
@endpush

@push('modals')
    {{-- Modal Tambah Lokasi --}}
    <div class="modal fade" id="modalTambahLokasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.bank-lokasi.store') }}" method="POST" id="formTambahLokasi">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="feather-map-pin me-2 text-primary"></i> Tambah Lokasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama Lokasi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_lokasi" id="inputNamaLokasi" class="form-control"
                                    placeholder="Contoh: SMKN 1 Tamanan Bondowoso" required>
                                <small class="text-muted">Ketik nama lokasi yang Anda inginkan</small>
                            </div>

                            <div class="col-md-12 position-relative">
                                <label class="form-label fw-semibold"><i class="feather-search me-1 text-primary"></i>Cari di Peta</label>
                                <input type="text" id="inputCariLokasi" class="form-control"
                                    placeholder="Ketik area/daerah: Tamanan Bondowoso, lalu pilih atau klik di peta..."
                                    autocomplete="off">
                                <div id="dropdownLokasi" class="location-dropdown"></div>
                                <small class="text-muted">Cari area terdekat, lalu klik tepat di peta untuk titik presisi</small>
                            </div>

                            <div class="col-md-12">
                                <div id="petaTambah" class="peta-container"></div>
                                <small class="text-muted mt-1 d-block">📍 Klik langsung di peta untuk menentukan titik lokasi yang tepat</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" id="inputLat" class="form-control" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" id="inputLng" class="form-control" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                                <input type="number" name="radius" class="form-control" placeholder="Contoh: 50" min="1" required>
                                <small class="text-muted">Radius validasi presensi dalam satuan meter</small>
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

    {{-- Modal Edit Lokasi --}}
    <div class="modal fade" id="modalEditLokasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formEditLokasi" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="feather-edit me-2 text-warning"></i> Edit Lokasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama Lokasi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_lokasi" id="editNamaLokasi" class="form-control" required>
                            </div>

                            <div class="col-md-12 position-relative">
                                <label class="form-label fw-semibold"><i class="feather-search me-1 text-primary"></i>Cari di Peta</label>
                                <input type="text" id="editCariLokasi" class="form-control"
                                    placeholder="Cari lokasi baru..." autocomplete="off">
                                <div id="dropdownEditLokasi" class="location-dropdown"></div>
                            </div>

                            <div class="col-md-12">
                                <div id="petaEdit" class="peta-container"></div>
                                <small class="text-muted mt-1 d-block">📍 Klik di peta untuk mengubah titik lokasi</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" id="editLat" class="form-control" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" id="editLng" class="form-control" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                                <input type="number" name="radius" id="editRadius" class="form-control" min="1" required>
                                <small class="text-muted">Radius validasi presensi dalam satuan meter</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat Lengkap</label>
                                <input type="text" name="alamat" id="editAlamat" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="feather-save me-1"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Hapus Lokasi --}}
    <div class="modal fade" id="modalHapusLokasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formHapusLokasi" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="feather-alert-triangle me-2 text-danger"></i> Hapus Lokasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus lokasi <strong id="hapusNamaLokasi"></strong>?</p>
                        <p class="text-muted small mb-0">Data yang sudah dihapus tidak dapat dikembalikan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger"><i class="feather-trash-2 me-1"></i> Hapus</button>
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
            // ========== SEARCH TABEL ==========
            document.getElementById('searchLokasi').addEventListener('input', function() {
                let query = this.value.toLowerCase();
                let rows = document.querySelectorAll('#bodyTabelLokasi tr');
                let found = 0;
                rows.forEach(row => {
                    let text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                    if (text.includes(query)) found++;
                });
                document.getElementById('noResultMsg').classList.toggle('d-none', found > 0);
            });

            // ========== PETA & AUTOCOMPLETE HELPER ==========
            function initMap(containerId, searchId, dropdownId, latId, lngId, alamatId) {
                let map = L.map(containerId).setView([-7.9, 113.8], 9);
                let marker = null;

                L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: 'Map data &copy; Google'
                }).addTo(map);

                // Klik di peta
                map.on('click', function(e) {
                    let lat = e.latlng.lat.toFixed(7);
                    let lng = e.latlng.lng.toFixed(7);
                    setMarker(lat, lng);
                    reverseGeocode(lat, lng, alamatId);
                });

                function setMarker(lat, lng) {
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map);
                    map.setView([lat, lng], 16);
                    document.getElementById(latId).value = lat;
                    document.getElementById(lngId).value = lng;
                }

                function reverseGeocode(lat, lng, alamatField) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=id`)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.display_name) {
                                document.getElementById(alamatField).value = data.display_name;
                            }
                        });
                }

                // Autocomplete
                let debounce;
                const searchEl = document.getElementById(searchId);
                const dropdownEl = document.getElementById(dropdownId);

                if (searchEl) {
                    searchEl.addEventListener('input', function() {
                        clearTimeout(debounce);
                        let q = this.value.trim();
                        if (q.length < 3) { dropdownEl.classList.remove('show'); return; }

                        debounce = setTimeout(() => {
                            dropdownEl.innerHTML = '<div class="location-item text-muted"><i class="feather-loader me-2"></i>Mencari...</div>';
                            dropdownEl.classList.add('show');

                            Promise.allSettled([
                                fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(q)}&limit=5&lang=id&lat=-7.5&lon=112.0`).then(r => r.json()),
                                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=5&countrycodes=id&accept-language=id`).then(r => r.json())
                            ]).then(([pRes, nRes]) => {
                                let results = [], seen = new Set();

                                if (pRes.status === 'fulfilled' && pRes.value.features) {
                                    pRes.value.features.forEach(f => {
                                        let p = f.properties, c = f.geometry.coordinates;
                                        let nama = p.name || '';
                                        let alamat = [nama, p.street, p.district, p.city, p.county, p.state].filter(Boolean).join(', ');
                                        let key = `${parseFloat(c[1]).toFixed(4)}_${parseFloat(c[0]).toFixed(4)}`;
                                        if (nama && !seen.has(key)) { seen.add(key); results.push({nama, alamat, lat:c[1], lon:c[0]}); }
                                    });
                                }
                                if (nRes.status === 'fulfilled' && Array.isArray(nRes.value)) {
                                    nRes.value.forEach(i => {
                                        let key = `${parseFloat(i.lat).toFixed(4)}_${parseFloat(i.lon).toFixed(4)}`;
                                        if (!seen.has(key)) { seen.add(key); results.push({nama: i.display_name.split(',')[0].trim(), alamat: i.display_name, lat: i.lat, lon: i.lon}); }
                                    });
                                }

                                dropdownEl.innerHTML = '';
                                if (!results.length) {
                                    dropdownEl.innerHTML = '<div class="location-item text-muted">Tidak ditemukan</div>';
                                } else {
                                    results.slice(0, 8).forEach(item => {
                                        let div = document.createElement('div');
                                        div.className = 'location-item';
                                        div.innerHTML = `<strong>${item.nama}</strong><small>${item.alamat}</small>`;
                                        div.addEventListener('click', () => {
                                            setMarker(item.lat, item.lon);
                                            document.getElementById(alamatId).value = item.alamat;
                                            searchEl.value = item.nama;
                                            dropdownEl.classList.remove('show');
                                        });
                                        dropdownEl.appendChild(div);
                                    });
                                }
                                dropdownEl.classList.add('show');
                            });
                        }, 400);
                    });
                }

                return { map, setMarker, getMarker: () => marker };
            }

            // ========== MODAL TAMBAH ==========
            let tambahMap = null;
            document.getElementById('modalTambahLokasi').addEventListener('shown.bs.modal', function() {
                if (!tambahMap) {
                    tambahMap = initMap('petaTambah', 'inputCariLokasi', 'dropdownLokasi', 'inputLat', 'inputLng', 'inputAlamat');
                }
                tambahMap.map.invalidateSize();
            });
            document.getElementById('modalTambahLokasi').addEventListener('hidden.bs.modal', function() {
                document.getElementById('formTambahLokasi').reset();
            });

            // ========== MODAL EDIT ==========
            let editMap = null;
            document.querySelectorAll('.btn-edit-lokasi').forEach(btn => {
                btn.addEventListener('click', function() {
                    let id = this.dataset.id;
                    document.getElementById('formEditLokasi').action = `/admin/bank-lokasi/${id}`;
                    document.getElementById('editNamaLokasi').value = this.dataset.nama;
                    document.getElementById('editLat').value = this.dataset.latitude;
                    document.getElementById('editLng').value = this.dataset.longitude;
                    document.getElementById('editRadius').value = this.dataset.radius;
                    document.getElementById('editAlamat').value = this.dataset.alamat || '';
                });
            });
            document.getElementById('modalEditLokasi').addEventListener('shown.bs.modal', function() {
                if (!editMap) {
                    editMap = initMap('petaEdit', 'editCariLokasi', 'dropdownEditLokasi', 'editLat', 'editLng', 'editAlamat');
                }
                editMap.map.invalidateSize();
                let lat = document.getElementById('editLat').value;
                let lng = document.getElementById('editLng').value;
                if (lat && lng) {
                    editMap.setMarker(lat, lng);
                }
            });

            // ========== MODAL HAPUS ==========
            document.querySelectorAll('.btn-hapus-lokasi').forEach(btn => {
                btn.addEventListener('click', function() {
                    let id = this.dataset.id;
                    document.getElementById('formHapusLokasi').action = `/admin/bank-lokasi/${id}`;
                    document.getElementById('hapusNamaLokasi').textContent = this.dataset.nama;
                });
            });

            // Tutup dropdown saat klik di luar
            document.addEventListener('click', function(e) {
                document.querySelectorAll('.location-dropdown').forEach(dd => {
                    if (!e.target.closest('.position-relative')) dd.classList.remove('show');
                });
            });
        });
    </script>
@endpush
