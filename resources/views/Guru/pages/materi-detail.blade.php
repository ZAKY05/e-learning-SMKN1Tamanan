@extends('Guru.layout.master')

@section('page_title', 'Materi - ' . ($kelas->nama_kelas ?? ''))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.materi.index') }}">Upload Materi</a></li>
    <li class="breadcrumb-item active">{{ $kelas->nama_kelas }} - {{ $mapel->nama_mapel }}</li>
@endsection

@section('content')
    <div class="main-content">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
                <i class="feather-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Header Info --}}
        <div class="row px-4 pt-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #5b5fc7 0%, #8b5cf6 100%);">
                    <div class="card-body p-4 text-white">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text rounded-3"
                                style="width:56px; height:56px; min-width:56px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;">
                                <i class="feather-book text-white" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-white">{{ $kelas->nama_kelas }}</h5>
                                <p class="mb-0 opacity-75" style="font-size:0.9rem;">
                                    <i class="feather-bookmark me-1"></i> {{ $mapel->nama_mapel }}
                                </p>
                            </div>
                            <div class="ms-auto text-end">
                                <div class="fw-bold" style="font-size:1.8rem;">{{ $materiList->count() }}<span style="font-size:1rem;">/15</span></div>
                                <small class="opacity-75">Minggu terisi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cards Minggu 1-15 --}}
        <div class="row px-4 pt-2 g-3">
            @foreach ($mingguList as $minggu)
                @php
                    $materi = $materiList->get($minggu);
                    $hasMateri = $materi !== null;
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card border h-100 shadow-sm" style="transition: all 0.2s ease;"
                        onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.1)';"
                        onmouseout="this.style.transform=''; this.style.boxShadow='';">
                        <div class="card-header d-flex align-items-center justify-content-between py-2 px-3"
                            style="background: {{ $hasMateri ? 'linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%)' : 'linear-gradient(135deg, #fff3e0 0%, #fce4ec 100%)' }};">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:32px; height:32px; background:{{ $hasMateri ? '#4caf50' : '#ff9800' }};">
                                    @if ($hasMateri)
                                        <i class="feather-check text-white" style="font-size:0.85rem;"></i>
                                    @else
                                        <span class="text-white fw-bold" style="font-size:0.8rem;">{{ $minggu }}</span>
                                    @endif
                                </div>
                                <span class="fw-bold" style="font-size:0.9rem; color:#333;">Minggu {{ $minggu }}</span>
                            </div>
                            @if ($hasMateri)
                                <span class="badge bg-success" style="font-size:0.72rem;">Terisi</span>
                            @else
                                <span class="badge bg-warning text-dark" style="font-size:0.72rem;">Kosong</span>
                            @endif
                        </div>
                        <div class="card-body p-3">
                            @if ($hasMateri)
                                <h6 class="fw-semibold mb-1" style="font-size:0.88rem;">{{ $materi->judul_materi }}</h6>
                                @if ($materi->deskripsi)
                                    <p class="text-muted mb-2" style="font-size:0.8rem; line-height:1.4;">
                                        {{ Str::limit($materi->deskripsi, 80) }}
                                    </p>
                                @endif
                                @if ($materi->file_name)
                                    <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" style="background:#f5f5f5;">
                                        <i class="feather-file text-primary" style="font-size:0.9rem;"></i>
                                        <span style="font-size:0.78rem;" class="text-truncate">{{ $materi->file_name }}</span>
                                        <a href="{{ asset('storage/' . $materi->file_path) }}" target="_blank"
                                            class="ms-auto badge bg-primary text-white text-decoration-none" style="font-size:0.7rem;">
                                            <i class="feather-download" style="font-size:0.65rem;"></i> Unduh
                                        </a>
                                    </div>
                                @endif
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <small class="text-muted" style="font-size:0.72rem;">
                                        <i class="feather-calendar me-1"></i>{{ $materi->tanggal_upload }}
                                    </small>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-soft-warning btn-sm btn-edit-materi"
                                            data-bs-toggle="modal" data-bs-target="#modalEditMateri"
                                            data-id="{{ $materi->id_materi }}"
                                            data-judul="{{ $materi->judul_materi }}"
                                            data-deskripsi="{{ $materi->deskripsi }}"
                                            data-semester="{{ $materi->semester }}"
                                            style="font-size:0.75rem; padding:0.2rem 0.5rem;">
                                            <i class="feather-edit-2" style="font-size:0.75rem;"></i>
                                        </button>
                                        <button type="button" class="btn btn-soft-danger btn-sm btn-hapus-materi"
                                            data-bs-toggle="modal" data-bs-target="#modalHapusMateri"
                                            data-id="{{ $materi->id_materi }}"
                                            data-minggu="{{ $minggu }}"
                                            style="font-size:0.75rem; padding:0.2rem 0.5rem;">
                                            <i class="feather-trash-2" style="font-size:0.75rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="feather-plus-circle d-block mb-2 text-muted" style="font-size:1.8rem;"></i>
                                    <p class="text-muted mb-2" style="font-size:0.82rem;">Belum ada materi</p>
                                    <button type="button" class="btn btn-primary btn-sm btn-tambah-materi"
                                        data-bs-toggle="modal" data-bs-target="#modalTambahMateri"
                                        data-minggu="{{ $minggu }}"
                                        style="font-size:0.82rem; padding:0.3rem 0.8rem;">
                                        <i class="feather-upload me-1" style="font-size:0.8rem;"></i> Upload Materi
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('modals')
    {{-- MODAL TAMBAH MATERI --}}
    <div class="modal fade" id="modalTambahMateri" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('guru.materi.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id_kelas }}">
                    <input type="hidden" name="mapel_id" value="{{ $mapel->id_mapel }}">
                    <input type="hidden" name="minggu_ke" id="tambahMingguKe" value="">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="feather-upload me-2 text-primary"></i> Upload Materi - <span id="tambahMingguLabel"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Judul Materi <span class="text-danger">*</span></label>
                                <input type="text" name="judul_materi" class="form-control" placeholder="cth: Pengenalan Dasar..." required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                <select name="semester" class="form-select" required>
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi materi (opsional)"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">File Materi</label>
                                <input type="file" name="file_materi" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.png">
                                <small class="text-muted">Format: PDF, DOC, PPT, XLS, ZIP, JPG, PNG. Max 10MB</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-upload me-1"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT MATERI --}}
    <div class="modal fade" id="modalEditMateri" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formEditMateri" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="feather-edit me-2 text-warning"></i> Edit Materi
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Judul Materi <span class="text-danger">*</span></label>
                                <input type="text" name="judul_materi" id="editJudul" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                <select name="semester" id="editSemester" class="form-select" required>
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi</label>
                                <textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Ganti File (opsional)</label>
                                <input type="file" name="file_materi" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.png">
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
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

    {{-- MODAL HAPUS MATERI --}}
    <div class="modal fade" id="modalHapusMateri" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="formHapusMateri" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <div class="avatar-text avatar-lg rounded-circle bg-soft-danger text-danger mx-auto mb-3">
                            <i class="feather-trash-2 fs-24"></i>
                        </div>
                        <h5 class="fw-bold">Hapus Materi</h5>
                        <p class="text-muted mb-0">
                            Hapus materi <strong id="hapusMingguLabel"></strong>?
                            File yang diupload juga akan dihapus.
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tambah materi — set minggu_ke
            document.querySelectorAll('.btn-tambah-materi').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var minggu = this.dataset.minggu;
                    document.getElementById('tambahMingguKe').value = minggu;
                    document.getElementById('tambahMingguLabel').textContent = 'Minggu ' + minggu;
                });
            });

            // Edit materi
            document.querySelectorAll('.btn-edit-materi').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('editJudul').value = this.dataset.judul;
                    document.getElementById('editDeskripsi').value = this.dataset.deskripsi || '';
                    document.getElementById('editSemester').value = this.dataset.semester;
                    document.getElementById('formEditMateri').action = '{{ url("guru/materi") }}/' + this.dataset.id;
                });
            });

            // Hapus materi
            document.querySelectorAll('.btn-hapus-materi').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('hapusMingguLabel').textContent = 'Minggu ' + this.dataset.minggu;
                    document.getElementById('formHapusMateri').action = '{{ url("guru/materi") }}/' + this.dataset.id;
                });
            });
        });
    </script>
@endpush
