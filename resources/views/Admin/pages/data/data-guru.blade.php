@extends('Admin.layout.master')

@section('page_title', 'Data Guru')

@section('breadcrumb')
    <li class="breadcrumb-item">Data</li>
    <li class="breadcrumb-item active">Data Guru</li>
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
                            <i class="feather-users me-2 text-primary" style="font-size:1.15rem;"></i> Data Guru
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahGuru" style="font-size:0.9rem; padding: 0.4rem 0.95rem;">
                            <i class="feather-plus me-1" style="font-size:0.95rem;"></i> Tambah Guru
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="px-3 pt-3 pb-1">
                        <div class="input-group" style="max-width: 380px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="feather-search text-muted" style="font-size:0.95rem;"></i>
                            </span>
                            <input type="text" id="searchGuru" class="form-control border-start-0 ps-0"
                                placeholder="Cari NIP, nama, alamat..." style="font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabelGuru" style="font-size:0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Foto</th>
                                        <th>NIP</th>
                                        <th>Nama Guru</th>
                                        <th>No. Telp</th>
                                        <th>Alamat</th>
                                        <th>Mapel Diampu</th>
                                        <th width="150" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabelGuru">
                                    @foreach ($gurus as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($data->foto_profil)
                                                    <img src="{{ asset($data->foto_profil) }}" alt="Foto"
                                                        class="rounded-circle"
                                                        style="width:36px; height:36px; object-fit:cover;">
                                                @else
                                                    <div class="avatar-text avatar-sm rounded-circle bg-soft-primary text-primary fw-bold"
                                                        style="font-size:0.9rem; width:36px; height:36px; line-height:36px; text-align:center;">
                                                        {{ strtoupper(substr($data->nama, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td><span class="fw-semibold">{{ $data->nip }}</span></td>
                                            <td>{{ $data->nama }}</td>
                                            <td>{{ $data->no_telp ?? '-' }}</td>
                                            <td>{{ $data->alamat ? \Illuminate\Support\Str::limit($data->alamat, 30) : '-' }}
                                            </td>
                                            <td>
                                                @forelse ($data->mapels as $mapel)
                                                    <span class="badge bg-soft-primary text-primary me-1 mb-1" style="font-size:0.8rem;">
                                                        {{ $mapel->nama_mapel }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted" style="font-size:0.85rem;">-</span>
                                                @endforelse
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-soft-warning btn-edit-guru"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditGuru"
                                                    data-id="{{ $data->id_guru }}" data-nip="{{ $data->nip }}"
                                                    data-nama="{{ $data->nama }}"
                                                    data-no_telp="{{ $data->no_telp }}" data-alamat="{{ $data->alamat }}"
                                                    data-mapel_ids="{{ $data->mapels->pluck('id_mapel')->join(',') }}"
                                                    title="Edit"
                                                    style="font-size:0.88rem; padding:0.3rem 0.6rem; margin-bottom:2px;">
                                                    <i class="feather-edit-2" style="font-size:0.95rem;"></i>
                                                </button>

                                                <button type="button" class="btn btn-soft-danger btn-hapus-guru"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusGuru"
                                                    data-id="{{ $data->id_guru }}" data-nama="{{ $data->nama }}"
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

@push('modals')
    {{-- MODAL TAMBAH GURU --}}
    <div class="modal fade" id="modalTambahGuru" tabindex="-1" aria-labelledby="modalTambahGuruLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.guru.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahGuruLabel">
                            <i class="feather-user-plus me-2 text-primary"></i> Tambah Data Guru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP"
                                maxlength="20" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Guru <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama guru"
                                maxlength="50" required>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">No. Telp</label>
                                <input type="text" name="no_telp" class="form-control" placeholder="08xxxxxxxxxx"
                                    maxlength="15">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan alamat"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto Profil</label>
                            <input type="file" name="foto_profil" class="form-control"
                                accept="image/jpg,image/jpeg,image/png">
                            <small class="text-muted">Format: JPG, JPEG, PNG. Maks: 2MB</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mata Pelajaran (Opsional)</label>
                            <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                @foreach ($mapels as $m)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            name="mapel_ids[]" value="{{ $m->id_mapel }}"
                                            id="mapelTambah{{ $m->id_mapel }}">
                                        <label class="form-check-label" for="mapelTambah{{ $m->id_mapel }}">
                                            {{ $m->nama_mapel }} <small class="text-muted">({{ $m->jenis }})</small>
                                        </label>
                                    </div>
                                @endforeach
                                @if ($mapels->isEmpty())
                                    <span class="text-muted" style="font-size:0.9rem;">Belum ada data mapel.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT GURU --}}
    <div class="modal fade" id="modalEditGuru" tabindex="-1" aria-labelledby="modalEditGuruLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formEditGuru" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditGuruLabel">
                            <i class="feather-edit me-2 text-warning"></i> Edit Data Guru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" id="editNip" class="form-control" maxlength="20"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Guru <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="editNama" class="form-control" maxlength="50"
                                required>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">No. Telp</label>
                                <input type="text" name="no_telp" id="editNoTelp" class="form-control"
                                    maxlength="15">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" id="editAlamat" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto Profil</label>
                            <input type="file" name="foto_profil" class="form-control"
                                accept="image/jpg,image/jpeg,image/png">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mata Pelajaran (Opsional)</label>
                            <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                @foreach ($mapels as $m)
                                    <div class="form-check">
                                        <input class="form-check-input edit-mapel-check" type="checkbox"
                                            name="mapel_ids[]" value="{{ $m->id_mapel }}"
                                            id="mapelEdit{{ $m->id_mapel }}">
                                        <label class="form-check-label" for="mapelEdit{{ $m->id_mapel }}">
                                            {{ $m->nama_mapel }} <small class="text-muted">({{ $m->jenis }})</small>
                                        </label>
                                    </div>
                                @endforeach
                                @if ($mapels->isEmpty())
                                    <span class="text-muted" style="font-size:0.9rem;">Belum ada data mapel.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="feather-save me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL HAPUS GURU --}}
    <div class="modal fade" id="modalHapusGuru" tabindex="-1" aria-labelledby="modalHapusGuruLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="formHapusGuru" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <div class="avatar-text avatar-lg rounded-circle bg-soft-danger text-danger mx-auto mb-3">
                            <i class="feather-trash-2 fs-24"></i>
                        </div>
                        <h5 class="fw-bold">Hapus Data Guru</h5>
                        <p class="text-muted mb-0">
                            Apakah Anda yakin ingin menghapus data
                            <strong id="namaHapusGuru"></strong>?
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="feather-trash me-1"></i> Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        // Edit modal
        document.querySelectorAll('.btn-edit-guru').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                var mapelIds = this.dataset.mapel_ids ? this.dataset.mapel_ids.split(',') : [];
                
                document.getElementById('editNip').value = this.dataset.nip;
                document.getElementById('editNama').value = this.dataset.nama;
                document.getElementById('editNoTelp').value = this.dataset.no_telp || '';
                document.getElementById('editAlamat').value = this.dataset.alamat || '';
                
                // Set checkboxes
                document.querySelectorAll('.edit-mapel-check').forEach(function (cb) {
                    cb.checked = mapelIds.includes(cb.value);
                });

                document.getElementById('formEditGuru').action = '{{ url('admin/data-guru') }}/' + id;
            });
        });

        // Hapus modal
        document.querySelectorAll('.btn-hapus-guru').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('namaHapusGuru').textContent = this.dataset.nama;
                document.getElementById('formHapusGuru').action = '{{ url('admin/data-guru') }}/' + this.dataset.id;
            });
        });

        // Search
        document.getElementById('searchGuru').addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('#bodyTabelGuru tr');
            var found = 0;

            rows.forEach(function(row) {
                if (row.id === 'emptyRow') return;
                var text = row.textContent.toLowerCase();
                if (text.includes(keyword)) {
                    row.style.display = '';
                    found++;
                } else {
                    row.style.display = 'none';
                }
            });

            var noMsg = document.getElementById('noResultMsg');
            if (found === 0 && keyword !== '') {
                noMsg.classList.remove('d-none');
            } else {
                noMsg.classList.add('d-none');
            }
        });
    </script>
@endpush
