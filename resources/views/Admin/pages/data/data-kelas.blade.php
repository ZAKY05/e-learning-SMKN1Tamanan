@extends('Admin.layout.master')

@section('page_title', 'Data Kelas')

@section('breadcrumb')
    <li class="breadcrumb-item">Data</li>
    <li class="breadcrumb-item active">Data Kelas</li>
@endsection

@section('content')
    <div class="main-content">

        {{-- Alert --}}
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
                            <i class="feather-grid me-2 text-primary" style="font-size:1.15rem;"></i> Data Kelas
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahKelas" style="font-size:0.9rem; padding:0.4rem 0.95rem;">
                            <i class="feather-plus me-1" style="font-size:0.95rem;"></i> Tambah Kelas
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="px-3 pt-3 pb-1">
                        <div class="input-group" style="max-width: 380px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="feather-search text-muted" style="font-size:0.95rem;"></i>
                            </span>
                            <input type="text" id="searchKelas" class="form-control border-start-0 ps-0"
                                placeholder="Cari tingkat, jurusan, golongan..." style="font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabelKelas" style="font-size:0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Tingkat</th>
                                        <th>Jurusan</th>
                                        <th>Golongan</th>
                                        <th>Nama Kelas</th>
                                        <th>Jumlah Siswa</th>
                                        <th width="150" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabelKelas">
                                    @foreach ($kelas as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="badge bg-soft-primary text-primary"
                                                    style="font-size:0.82rem; padding:0.38em 0.65em;">
                                                    Kelas {{ $data->tingkat }}
                                                </span>
                                            </td>
                                            <td>{{ $data->jurusan ? $data->jurusan->nama_jurusan : '-' }}</td>
                                            <td>{{ $data->golongan }}</td>
                                            <td><span class="fw-semibold">{{ $data->nama_kelas }}</span></td>
                                            <td>
                                                <span class="badge bg-soft-info text-info"
                                                    style="font-size:0.82rem; padding:0.38em 0.65em;">
                                                    {{ $data->students->count() }} Siswa
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-soft-warning btn-edit-kelas"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditKelas"
                                                    data-id="{{ $data->id_kelas }}" data-tingkat="{{ $data->tingkat }}"
                                                    data-jurusan_id="{{ $data->jurusan_id }}"
                                                    data-golongan="{{ $data->golongan }}" title="Edit"
                                                    style="font-size:0.88rem; padding:0.3rem 0.6rem; margin-bottom:2px;">
                                                    <i class="feather-edit-2" style="font-size:0.95rem;"></i>
                                                </button>

                                                <button type="button" class="btn btn-soft-danger btn-hapus-kelas"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusKelas"
                                                    data-id="{{ $data->id_kelas }}" data-nama="{{ $data->nama_kelas }}"
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
    {{-- MODAL TAMBAH KELAS --}}
    <div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-labelledby="modalTambahKelasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.kelas.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahKelasLabel">
                            <i class="feather-plus-circle me-2 text-primary"></i> Tambah Kelas
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tingkat <span class="text-danger">*</span></label>
                            <input type="number" name="tingkat" class="form-control" placeholder="cth: 10"
                                min="1" max="13" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jurusan <span class="text-danger">*</span></label>
                            <select name="jurusan_id" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Jurusan --</option>
                                @foreach ($jurusans as $data)
                                    <option value="{{ $data->id_jurusan }}">{{ $data->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Golongan <span class="text-danger">*</span></label>
                            <input type="number" name="golongan" class="form-control" placeholder="cth: 1"
                                min="1" required>
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

    {{-- MODAL EDIT KELAS --}}
    <div class="modal fade" id="modalEditKelas" tabindex="-1" aria-labelledby="modalEditKelasLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formEditKelas" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditKelasLabel">
                            <i class="feather-edit me-2 text-warning"></i> Edit Kelas
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tingkat <span class="text-danger">*</span></label>
                            <input type="number" name="tingkat" id="editTingkat" class="form-control" min="1"
                                max="13" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jurusan <span class="text-danger">*</span></label>
                            <select name="jurusan_id" id="editJurusanIdKelas" class="form-select" required>
                                <option value="" disabled>-- Pilih Jurusan --</option>
                                @foreach ($jurusans as $data)
                                    <option value="{{ $data->id_jurusan }}">{{ $data->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Golongan <span class="text-danger">*</span></label>
                            <input type="number" name="golongan" id="editGolongan" class="form-control" min="1"
                                required>
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

    {{-- MODAL HAPUS KELAS --}}
    <div class="modal fade" id="modalHapusKelas" tabindex="-1" aria-labelledby="modalHapusKelasLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="formHapusKelas" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <div class="avatar-text avatar-lg rounded-circle bg-soft-danger text-danger mx-auto mb-3">
                            <i class="feather-trash-2 fs-24"></i>
                        </div>
                        <h5 class="fw-bold">Hapus Kelas</h5>
                        <p class="text-muted mb-0">
                            Apakah Anda yakin ingin menghapus kelas
                            <strong id="namaHapusKelas"></strong>?
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
        document.querySelectorAll('.btn-edit-kelas').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('editTingkat').value = this.dataset.tingkat;
                document.getElementById('editJurusanIdKelas').value = this.dataset.jurusan_id;
                document.getElementById('editGolongan').value = this.dataset.golongan;
                document.getElementById('formEditKelas').action = '{{ url('admin/data-kelas') }}/' + this.dataset.id;
            });
        });

        // Hapus modal
        document.querySelectorAll('.btn-hapus-kelas').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('namaHapusKelas').textContent = this.dataset.nama;
                document.getElementById('formHapusKelas').action = '{{ url('admin/data-kelas') }}/' + this.dataset.id;
            });
        });

        // Search
        document.getElementById('searchKelas').addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('#bodyTabelKelas tr');
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
