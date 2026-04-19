@extends('Admin.layout.master')

@section('page_title', 'Data Mata Pelajaran')

@section('breadcrumb')
    <li class="breadcrumb-item">Data</li>
    <li class="breadcrumb-item active">Data Mata Pelajaran</li>
@endsection

@section('content')
    <div class="main-content">

        {{-- Alert Success --}}
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
                            <i class="feather-book-open me-2 text-primary" style="font-size:1.15rem;"></i> Data Mata Pelajaran
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahMapel" style="font-size:0.9rem; padding:0.4rem 0.95rem;">
                            <i class="feather-plus me-1" style="font-size:0.95rem;"></i> Tambah Mapel
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="px-3 pt-3 pb-1">
                        <div class="input-group" style="max-width: 380px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="feather-search text-muted" style="font-size:0.95rem;"></i>
                            </span>
                            <input type="text" id="searchMapel" class="form-control border-start-0 ps-0"
                                placeholder="Cari nama mapel, jenis..." style="font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabelMapel" style="font-size:0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Nama Mapel</th>
                                        <th>Jenis</th>
                                        <th>Jurusan</th>
                                        <th>Guru Pengampu</th>
                                        <th width="120" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabelMapel">
                                    @foreach ($mapels as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-text avatar-sm rounded-circle bg-soft-primary text-primary fw-bold"
                                                        style="font-size:0.9rem; width:32px; height:32px; line-height:32px; text-align:center;">
                                                        {{ strtoupper(substr($data->nama_mapel, 0, 1)) }}
                                                    </div>
                                                    <span class="fw-semibold">{{ $data->nama_mapel }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($data->jenis === 'umum')
                                                    <span class="badge bg-soft-info text-info" style="font-size:0.82rem; padding:0.38em 0.65em;">
                                                        Umum
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-warning text-warning" style="font-size:0.82rem; padding:0.38em 0.65em;">
                                                        Jurusan
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $data->jurusan ? $data->jurusan->nama_jurusan : '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                @forelse ($data->gurus as $guru)
                                                    <span class="badge bg-soft-success text-success me-1" style="font-size:0.8rem;">
                                                        {{ $guru->nama }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted" style="font-size:0.85rem;">-</span>
                                                @endforelse
                                            </td>
                                            <td class="text-center">
                                                {{-- Tombol Edit --}}
                                                <button type="button" class="btn btn-soft-warning btn-edit-mapel"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditMapel"
                                                    data-id="{{ $data->id_mapel }}"
                                                    data-nama_mapel="{{ $data->nama_mapel }}"
                                                    data-jenis="{{ $data->jenis }}"
                                                    data-jurusan_id="{{ $data->jurusan_id }}"
                                                    data-guru_ids="{{ $data->gurus->pluck('id_guru')->join(',') }}"
                                                    title="Edit"
                                                    style="font-size:0.88rem; padding:0.3rem 0.6rem; margin-bottom:2px;">
                                                    <i class="feather-edit-2" style="font-size:0.95rem;"></i>
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <button type="button" class="btn btn-soft-danger btn-hapus-mapel"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusMapel"
                                                    data-id="{{ $data->id_mapel }}"
                                                    data-nama="{{ $data->nama_mapel }}" title="Hapus"
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
    {{-- MODAL TAMBAH MAPEL --}}
    <div class="modal fade" id="modalTambahMapel" tabindex="-1" aria-labelledby="modalTambahMapelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.mapel.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahMapelLabel">
                            <i class="feather-plus-circle me-2 text-primary"></i> Tambah Mata Pelajaran
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Mapel <span class="text-danger">*</span></label>
                                <input type="text" name="nama_mapel" class="form-control"
                                    placeholder="cth: Matematika" maxlength="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis <span class="text-danger">*</span></label>
                                <select name="jenis" class="form-select" id="jenisMapelTambah" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="umum">Umum</option>
                                    <option value="jurusan">Jurusan</option>
                                </select>
                            </div>
                            <div class="col-12" id="jurusanWrapTambah" style="display:none;">
                                <label class="form-label fw-semibold">Jurusan</label>
                                <select name="jurusan_id" class="form-select">
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach ($jurusans as $j)
                                        <option value="{{ $j->id_jurusan }}">{{ $j->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Wajib diisi jika jenis adalah Jurusan.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Guru Pengampu</label>
                                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                                    @foreach ($gurus as $g)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="guru_ids[]" value="{{ $g->id_guru }}"
                                                id="guruTambah{{ $g->id_guru }}">
                                            <label class="form-check-label" for="guruTambah{{ $g->id_guru }}">
                                                {{ $g->nama }} <small class="text-muted">({{ $g->nip }})</small>
                                            </label>
                                        </div>
                                    @endforeach
                                    @if ($gurus->isEmpty())
                                        <span class="text-muted" style="font-size:0.9rem;">Belum ada data guru.</span>
                                    @endif
                                </div>
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

    {{-- MODAL EDIT MAPEL --}}
    <div class="modal fade" id="modalEditMapel" tabindex="-1" aria-labelledby="modalEditMapelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formEditMapel" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditMapelLabel">
                            <i class="feather-edit me-2 text-warning"></i> Edit Mata Pelajaran
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Mapel <span class="text-danger">*</span></label>
                                <input type="text" name="nama_mapel" id="editNamaMapel" class="form-control"
                                    maxlength="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis <span class="text-danger">*</span></label>
                                <select name="jenis" class="form-select" id="editJenisMapel" required>
                                    <option value="umum">Umum</option>
                                    <option value="jurusan">Jurusan</option>
                                </select>
                            </div>
                            <div class="col-12" id="jurusanWrapEdit" style="display:none;">
                                <label class="form-label fw-semibold">Jurusan</label>
                                <select name="jurusan_id" id="editJurusanId" class="form-select">
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach ($jurusans as $j)
                                        <option value="{{ $j->id_jurusan }}">{{ $j->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Guru Pengampu</label>
                                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;" id="editGuruList">
                                    @foreach ($gurus as $g)
                                        <div class="form-check">
                                            <input class="form-check-input edit-guru-check" type="checkbox"
                                                name="guru_ids[]" value="{{ $g->id_guru }}"
                                                id="guruEdit{{ $g->id_guru }}">
                                            <label class="form-check-label" for="guruEdit{{ $g->id_guru }}">
                                                {{ $g->nama }} <small class="text-muted">({{ $g->nip }})</small>
                                            </label>
                                        </div>
                                    @endforeach
                                    @if ($gurus->isEmpty())
                                        <span class="text-muted" style="font-size:0.9rem;">Belum ada data guru.</span>
                                    @endif
                                </div>
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

    {{-- MODAL HAPUS MAPEL --}}
    <div class="modal fade" id="modalHapusMapel" tabindex="-1" aria-labelledby="modalHapusMapelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="formHapusMapel" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <div class="avatar-text avatar-lg rounded-circle bg-soft-danger text-danger mx-auto mb-3">
                            <i class="feather-trash-2 fs-24"></i>
                        </div>
                        <h5 class="fw-bold">Hapus Mapel</h5>
                        <p class="text-muted mb-0">
                            Apakah Anda yakin ingin menghapus mapel
                            <strong id="namaHapusMapel"></strong>? Tindakan ini tidak dapat dibatalkan.
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
        // ===== Toggle jurusan field (tambah) =====
        document.getElementById('jenisMapelTambah').addEventListener('change', function () {
            var wrap = document.getElementById('jurusanWrapTambah');
            wrap.style.display = this.value === 'jurusan' ? '' : 'none';
        });

        // ===== Toggle jurusan field (edit) =====
        document.getElementById('editJenisMapel').addEventListener('change', function () {
            var wrap = document.getElementById('jurusanWrapEdit');
            wrap.style.display = this.value === 'jurusan' ? '' : 'none';
        });

        // ===== Edit modal populate =====
        document.querySelectorAll('.btn-edit-mapel').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id         = this.dataset.id;
                var nama       = this.dataset.nama_mapel;
                var jenis      = this.dataset.jenis;
                var jurusanId  = this.dataset.jurusan_id;
                var guruIds    = this.dataset.guru_ids ? this.dataset.guru_ids.split(',') : [];

                document.getElementById('editNamaMapel').value = nama;
                document.getElementById('editJenisMapel').value = jenis;

                // Toggle jurusan
                var jurusanWrap = document.getElementById('jurusanWrapEdit');
                jurusanWrap.style.display = jenis === 'jurusan' ? '' : 'none';

                // Set jurusan select
                var jurusanSelect = document.getElementById('editJurusanId');
                jurusanSelect.value = jurusanId || '';

                // Set guru checkboxes
                document.querySelectorAll('.edit-guru-check').forEach(function (cb) {
                    cb.checked = guruIds.includes(cb.value);
                });

                // Set form action
                document.getElementById('formEditMapel').action = '{{ url('admin/data-mapel') }}/' + id;
            });
        });

        // ===== Hapus modal =====
        document.querySelectorAll('.btn-hapus-mapel').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('namaHapusMapel').textContent = this.dataset.nama;
                document.getElementById('formHapusMapel').action = '{{ url('admin/data-mapel') }}/' + this.dataset.id;
            });
        });

        // ===== Search =====
        document.getElementById('searchMapel').addEventListener('keyup', function () {
            var keyword = this.value.toLowerCase().trim();
            var rows    = document.querySelectorAll('#bodyTabelMapel tr');
            var found   = 0;

            rows.forEach(function (row) {
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
