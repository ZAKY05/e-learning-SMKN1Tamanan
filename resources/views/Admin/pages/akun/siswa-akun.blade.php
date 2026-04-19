@extends('Admin.layout.master')

@section('page_title', 'Akun Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item">Akun</li>
    <li class="breadcrumb-item active">Akun Siswa</li>
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
                            <i class="feather-key me-2 text-primary" style="font-size:1.15rem;"></i> Manajemen Akun Siswa
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahAkun" style="font-size:0.9rem; padding: 0.4rem 0.95rem;">
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
                                placeholder="Cari NIS, nama siswa..." style="font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabelAkunSiswa" style="font-size:0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Nama Siswa</th>
                                        <th>NIS</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                        <th width="150" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabelAkun">
                                    @foreach ($akuns as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-text avatar-sm rounded-circle bg-soft-primary text-primary fw-bold"
                                                        style="font-size:0.9rem; width:36px; height:36px; line-height:36px; text-align:center;">
                                                        {{ strtoupper(substr($data->name, 0, 1)) }}
                                                    </div>
                                                    <span class="fw-semibold">{{ $data->name }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $data->nis }}</td>
                                            <td><code>{{ $data->email }}</code></td>
                                            <td>
                                                <span class="password-mask" id="pwd-{{ $data->id }}">••••••••</span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-soft-warning btn-edit-akun"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditAkun"
                                                    data-id="{{ $data->id }}" data-name="{{ $data->name }}"
                                                    data-nis="{{ $data->nis }}" data-email="{{ $data->email }}"
                                                    title="Edit Akun"
                                                    style="font-size:0.88rem; padding:0.3rem 0.6rem; margin-bottom:2px;">
                                                    <i class="feather-edit-2" style="font-size:0.95rem;"></i>
                                                </button>

                                                <button type="button" class="btn btn-soft-danger btn-hapus-akun"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusAkun"
                                                    data-id="{{ $data->id }}" data-name="{{ $data->name }}"
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
    {{-- MODAL TAMBAH AKUN SISWA --}}
    <div class="modal fade" id="modalTambahAkun" tabindex="-1" aria-labelledby="modalTambahAkunLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.akun-siswa.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahAkunLabel">
                            <i class="feather-user-plus me-2 text-primary"></i> Tambah Akun Siswa
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control" placeholder="Masukkan NIS siswa"
                                maxlength="15" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Siswa <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Masukkan nama siswa"
                                maxlength="255" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="Masukkan email siswa"
                                required>
                            <small class="text-muted">Email akan digunakan untuk login</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="inputPasswordTambah" class="form-control"
                                    placeholder="Masukkan password" minlength="6" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="inputPasswordTambah">
                                    <i class="feather-eye" style="font-size:0.95rem;"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimal 6 karakter</small>
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

    {{-- MODAL EDIT AKUN SISWA --}}
    <div class="modal fade" id="modalEditAkun" tabindex="-1" aria-labelledby="modalEditAkunLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formEditAkun" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditAkunLabel">
                            <i class="feather-edit me-2 text-warning"></i> Edit Akun Siswa
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" id="editNisSiswa" class="form-control" maxlength="15" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Siswa <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editNamaSiswa" class="form-control" maxlength="255" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="editEmailSiswa" class="form-control"
                                placeholder="Masukkan email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="inputPasswordEdit" class="form-control"
                                    placeholder="Kosongkan jika tidak ingin mengubah" minlength="6">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="inputPasswordEdit">
                                    <i class="feather-eye" style="font-size:0.95rem;"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimal 6 karakter, kosongkan jika tidak diubah</small>
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

    {{-- MODAL HAPUS AKUN SISWA --}}
    <div class="modal fade" id="modalHapusAkun" tabindex="-1" aria-labelledby="modalHapusAkunLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="formHapusAkun" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <div class="avatar-text avatar-lg rounded-circle bg-soft-danger text-danger mx-auto mb-3">
                            <i class="feather-trash-2 fs-24"></i>
                        </div>
                        <h5 class="fw-bold">Hapus Akun Siswa</h5>
                        <p class="text-muted mb-0">
                            Apakah Anda yakin ingin menghapus akun
                            <strong id="namaHapusAkun"></strong>?
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
        document.querySelectorAll('.btn-edit-akun').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                document.getElementById('editNamaSiswa').value = this.dataset.name;
                document.getElementById('editNisSiswa').value = this.dataset.nis;
                document.getElementById('editEmailSiswa').value = this.dataset.email;
                document.getElementById('inputPasswordEdit').value = '';
                document.getElementById('formEditAkun').action = '{{ url('admin/akun-siswa') }}/' + id;
            });
        });

        // Hapus modal
        document.querySelectorAll('.btn-hapus-akun').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('namaHapusAkun').textContent = this.dataset.name;
                document.getElementById('formHapusAkun').action = '{{ url('admin/akun-siswa') }}/' + this
                    .dataset.id;
            });
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var target = document.getElementById(this.dataset.target);
                var icon = this.querySelector('i');
                if (target.type === 'password') {
                    target.type = 'text';
                    icon.className = 'feather-eye-off';
                } else {
                    target.type = 'password';
                    icon.className = 'feather-eye';
                }
            });
        });

        // Search
        document.getElementById('searchAkun').addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('#bodyTabelAkun tr');
            var found = 0;

            rows.forEach(function(row) {
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
