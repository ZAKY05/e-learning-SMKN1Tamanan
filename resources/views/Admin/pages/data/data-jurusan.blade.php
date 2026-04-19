@extends('Admin.layout.master')

@section('page_title', 'Data Jurusan')

@section('breadcrumb')
    <li class="breadcrumb-item">Akademik</li>
    <li class="breadcrumb-item active">Data Jurusan</li>
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
                            <i class="feather-book me-2 text-primary" style="font-size:1.15rem;"></i> Data Jurusan
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahJurusan" style="font-size:0.9rem; padding:0.4rem 0.95rem;">
                            <i class="feather-plus me-1" style="font-size:0.95rem;"></i> Tambah Jurusan
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="px-3 pt-3 pb-1">
                        <div class="input-group" style="max-width: 380px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="feather-search text-muted" style="font-size:0.95rem;"></i>
                            </span>
                            <input type="text" id="searchJurusan" class="form-control border-start-0 ps-0"
                                placeholder="Cari nama jurusan, deskripsi..." style="font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabelJurusan" style="font-size:0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Nama Jurusan</th>
                                        <th>Deskripsi</th>
                                        <th>Jumlah Kelas</th>
                                        <th>Jumlah Siswa</th>
                                        <th width="150" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabelJurusan">
                                    @foreach ($jurusans as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-text avatar-sm rounded-circle bg-soft-success text-success fw-bold"
                                                        style="font-size:0.9rem; width:32px; height:32px; line-height:32px; text-align:center;">
                                                        {{ strtoupper(substr($data->nama_jurusan, 0, 1)) }}
                                                    </div>
                                                    <span class="fw-semibold">{{ $data->nama_jurusan }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $data->deskripsi ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-primary text-primary"
                                                    style="font-size:0.82rem; padding:0.38em 0.65em;">
                                                    {{ $data->kelas_count }} Kelas
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-info text-info"
                                                    style="font-size:0.82rem; padding:0.38em 0.65em;">
                                                    {{ $data->students_count }} Siswa
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                {{-- Tombol Edit --}}
                                                <button type="button" class="btn btn-soft-warning btn-edit-jurusan"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditJurusan"
                                                    data-id="{{ $data->id_jurusan }}"
                                                    data-nama_jurusan="{{ $data->nama_jurusan }}"
                                                    data-deskripsi="{{ $data->deskripsi }}" title="Edit"
                                                    style="font-size:0.88rem; padding:0.3rem 0.6rem; margin-bottom:2px;">
                                                    <i class="feather-edit-2" style="font-size:0.95rem;"></i>
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <button type="button" class="btn btn-soft-danger btn-hapus-jurusan"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusJurusan"
                                                    data-id="{{ $data->id_jurusan }}"
                                                    data-nama="{{ $data->nama_jurusan }}" title="Hapus"
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
    {{-- MODAL TAMBAH JURUSAN --}}
    <div class="modal fade" id="modalTambahJurusan" tabindex="-1" aria-labelledby="modalTambahJurusanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.jurusan.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahJurusanLabel">
                            <i class="feather-plus-circle me-2 text-primary"></i> Tambah Jurusan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jurusan" class="form-control"
                                placeholder="cth: Teknik Komputer dan Jaringan" maxlength="50" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi singkat jurusan (opsional)"
                                maxlength="255"></textarea>
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

    {{-- MODAL EDIT JURUSAN --}}
    <div class="modal fade" id="modalEditJurusan" tabindex="-1" aria-labelledby="modalEditJurusanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formEditJurusan" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditJurusanLabel">
                            <i class="feather-edit me-2 text-warning"></i> Edit Jurusan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jurusan" id="editNamaJurusan" class="form-control"
                                maxlength="50" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="3" maxlength="255"></textarea>
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

    {{-- MODAL HAPUS JURUSAN --}}
    <div class="modal fade" id="modalHapusJurusan" tabindex="-1" aria-labelledby="modalHapusJurusanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="formHapusJurusan" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <div class="avatar-text avatar-lg rounded-circle bg-soft-danger text-danger mx-auto mb-3">
                            <i class="feather-trash-2 fs-24"></i>
                        </div>
                        <h5 class="fw-bold">Hapus Jurusan</h5>
                        <p class="text-muted mb-0">
                            Apakah Anda yakin ingin menghapus jurusan
                            <strong id="namaHapusJurusan"></strong>?
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
        document.querySelectorAll('.btn-edit-jurusan').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                document.getElementById('editNamaJurusan').value = this.dataset.nama_jurusan;
                document.getElementById('editDeskripsi').value = this.dataset.deskripsi || '';
                document.getElementById('formEditJurusan').action = '{{ url('admin/data-jurusan') }}/' + id;
            });
        });

        // Hapus modal
        document.querySelectorAll('.btn-hapus-jurusan').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('namaHapusJurusan').textContent = this.dataset.nama;
                document.getElementById('formHapusJurusan').action = '{{ url('admin/data-jurusan') }}/' + this.dataset.id;
            });
        });

        // Search
        document.getElementById('searchJurusan').addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('#bodyTabelJurusan tr');
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
