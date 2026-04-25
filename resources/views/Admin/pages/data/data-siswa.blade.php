@extends('Admin.layout.master')

@section('page_title', 'Data Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item">Data</li>
    <li class="breadcrumb-item active">Data Siswa</li>
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
                            <i class="feather-users me-2 text-primary" style="font-size:1.15rem;"></i> Data Siswa
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahSiswa" style="font-size:0.9rem; padding: 0.4rem 0.95rem;">
                            <i class="feather-plus me-1" style="font-size:0.95rem;"></i> Tambah Siswa
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="px-3 pt-3 pb-1">
                        <div class="input-group" style="max-width: 380px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="feather-search text-muted" style="font-size:0.95rem;"></i>
                            </span>
                            <input type="text" id="searchSiswa" class="form-control border-start-0 ps-0"
                                placeholder="Cari NIS, nama, kelas, jurusan..." style="font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabelSiswa" style="font-size:0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Foto</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Jurusan</th>
                                        <th width="150" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabelSiswa">
                                    @foreach ($pelajar as $data)
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
                                            <td><span class="fw-semibold">{{ $data->nis }}</span></td>
                                            <td>{{ $data->nama }}</td>
                                            <td>
                                                @if ($data->kelas)
                                                    <span class="badge bg-soft-info text-info"
                                                        style="font-size:0.82rem; padding:0.38em 0.65em;">
                                                        {{ $data->kelas->nama_kelas }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-success text-success"
                                                    style="font-size:0.82rem; padding:0.38em 0.65em;">
                                                    {{ $data->jurusan ? $data->jurusan->nama_jurusan : '-' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-soft-warning btn-edit-siswa"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditSiswa"
                                                    data-id="{{ $data->id_siswa }}" data-nis="{{ $data->nis }}"
                                                    data-nama="{{ $data->nama }}"
                                                    data-jurusan_id="{{ $data->jurusan_id }}"
                                                    data-kelas_id="{{ $data->kelas_id }}" title="Edit"
                                                    style="font-size:0.88rem; padding:0.3rem 0.6rem; margin-bottom:2px;">
                                                    <i class="feather-edit-2" style="font-size:0.95rem;"></i>
                                                </button>

                                                <button type="button" class="btn btn-soft-danger btn-hapus-siswa"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusSiswa"
                                                    data-id="{{ $data->id_siswa }}" data-nama="{{ $data->nama }}"
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
    {{-- MODAL TAMBAH SISWA --}}
    <div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-labelledby="modalTambahSiswaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.siswa.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahSiswaLabel">
                            <i class="feather-user-plus me-2 text-primary"></i> Tambah Data Siswa
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control" placeholder="Masukkan NIS"
                                maxlength="15" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Siswa <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama siswa"
                                maxlength="30" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold">Kelas</label>
                                <select name="kelas_id" class="form-select">
                                    <option value="" selected>-- Pilih Kelas --</option>
                                    @foreach ($kelas as $data)
                                        <option value="{{ $data->id_kelas }}">{{ $data->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold">Jurusan</label>
                                <select name="jurusan_id" class="form-select">
                                    <option value="" selected>-- Pilih Jurusan --</option>
                                    @foreach ($jurusans as $data)
                                        <option value="{{ $data->id_jurusan }}">{{ $data->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto Profil</label>
                            <input type="file" name="foto_profil" class="form-control"
                                accept="image/jpg,image/jpeg,image/png">
                            <small class="text-muted">Format: JPG, JPEG, PNG. Maks: 2MB</small>
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

    {{-- MODAL EDIT SISWA --}}
    <div class="modal fade" id="modalEditSiswa" tabindex="-1" aria-labelledby="modalEditSiswaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formEditSiswa" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditSiswaLabel">
                            <i class="feather-edit me-2 text-warning"></i> Edit Data Siswa
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" id="editNis" class="form-control" maxlength="15"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Siswa <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="editNama" class="form-control" maxlength="30"
                                required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold">Kelas</label>
                                <select name="kelas_id" id="editKelasId" class="form-select">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach ($kelas as $data)
                                        <option value="{{ $data->id_kelas }}">{{ $data->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold">Jurusan</label>
                                <select name="jurusan_id" id="editJurusanId" class="form-select">
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach ($jurusans as $data)
                                        <option value="{{ $data->id_jurusan }}">{{ $data->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto Profil</label>
                            <input type="file" name="foto_profil" class="form-control"
                                accept="image/jpg,image/jpeg,image/png">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah foto</small>
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

    {{-- MODAL HAPUS SISWA --}}
    <div class="modal fade" id="modalHapusSiswa" tabindex="-1" aria-labelledby="modalHapusSiswaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="formHapusSiswa" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <div class="avatar-text avatar-lg rounded-circle bg-soft-danger text-danger mx-auto mb-3">
                            <i class="feather-trash-2 fs-24"></i>
                        </div>
                        <h5 class="fw-bold">Hapus Data Siswa</h5>
                        <p class="text-muted mb-0">
                            Apakah Anda yakin ingin menghapus data
                            <strong id="namaHapus"></strong>?
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
        document.querySelectorAll('.btn-edit-siswa').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                document.getElementById('editNis').value = this.dataset.nis;
                document.getElementById('editNama').value = this.dataset.nama;
                document.getElementById('editJurusanId').value = this.dataset.jurusan_id || '';
                document.getElementById('editKelasId').value = this.dataset.kelas_id || '';
                document.getElementById('formEditSiswa').action = '{{ url('admin/data-siswa') }}/' + id;
            });
        });

        // Hapus modal
        document.querySelectorAll('.btn-hapus-siswa').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('namaHapus').textContent = this.dataset.nama;
                document.getElementById('formHapusSiswa').action = '{{ url('admin/data-siswa') }}/' + this.dataset.id;
            });
        });

        // Search
        document.getElementById('searchSiswa').addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('#bodyTabelSiswa tr');
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