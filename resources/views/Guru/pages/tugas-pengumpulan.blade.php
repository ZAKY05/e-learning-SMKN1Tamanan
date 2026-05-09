@extends('Guru.layout.master')

@section('page_title', 'Pengumpulan Tugas')

@section('breadcrumb')
    <li class="breadcrumb-item">Pembelajaran</li>
    <li class="breadcrumb-item"><a href="{{ route('guru.tugas.index') }}">Pengumpulan Tugas</a></li>
    <li class="breadcrumb-item active">Monitoring Jawaban</li>
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

        <div class="row px-4 pt-3">
            <div class="col-12 mb-4">
                <div class="card bg-primary-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-text avatar-lg bg-primary text-white rounded-circle me-3">
                                <i class="feather-file-text fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold text-primary">{{ $tugas->judul_tugas }}</h5>
                                <p class="mb-0 text-muted">
                                    Kelas: <strong>{{ $tugas->kelas->nama_kelas ?? '-' }}</strong> | 
                                    Mapel: <strong>{{ $tugas->mapel->nama_mapel ?? '-' }}</strong> |
                                    Materi: <strong>Minggu {{ $tugas->materi->minggu_ke ?? '-' }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Daftar Pengumpulan Siswa</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="50">No</th>
                                        <th>Nama Siswa</th>
                                        <th>Waktu Pengumpulan</th>
                                        <th>File Jawaban</th>
                                        <th>Status</th>
                                        <th>Nilai</th>
                                        <th class="text-center" width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($siswaKelas as $index => $siswa)
                                        @php
                                            $pengumpulan = $pengumpulanList->get($siswa->id_siswa);
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $siswa->nama }}</div>
                                                <small class="text-muted">{{ $siswa->nis }}</small>
                                            </td>
                                            <td>
                                                @if($pengumpulan)
                                                    {{ \Carbon\Carbon::parse($pengumpulan->tanggal_pengumpulan)->format('d M Y, H:i') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pengumpulan && $pengumpulan->file_path)
                                                    <a href="{{ asset('storage/' . $pengumpulan->file_path) }}" target="_blank" class="badge bg-soft-info text-info text-decoration-none">
                                                        <i class="feather-download"></i> Lihat Jawaban
                                                    </a>
                                                    @if($pengumpulan->jawaban)
                                                        <div class="mt-1 small text-muted"><i class="feather-message-square"></i> "{{ Str::limit($pengumpulan->jawaban, 30) }}"</div>
                                                    @endif
                                                @elseif($pengumpulan && $pengumpulan->jawaban)
                                                    <div class="small"><i class="feather-message-square text-muted"></i> "{{ $pengumpulan->jawaban }}"</div>
                                                @else
                                                    <span class="text-muted">Belum mengumpulkan</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pengumpulan)
                                                    @if($pengumpulan->status == 'dikumpulkan')
                                                        <span class="badge bg-soft-success text-success">Tepat Waktu</span>
                                                    @elseif($pengumpulan->status == 'terlambat')
                                                        <span class="badge bg-soft-warning text-warning">Terlambat</span>
                                                    @elseif($pengumpulan->status == 'dinilai')
                                                        <span class="badge bg-soft-primary text-primary">Dinilai</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">Kosong</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pengumpulan && $pengumpulan->nilai !== null)
                                                    <span class="fw-bold fs-16">{{ $pengumpulan->nilai }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($pengumpulan)
                                                    <button class="btn btn-sm btn-primary btn-nilai"
                                                        data-bs-toggle="modal" data-bs-target="#modalNilai"
                                                        data-id="{{ $pengumpulan->id_pengumpulan }}"
                                                        data-nama="{{ $siswa->nama }}"
                                                        data-nilai="{{ $pengumpulan->nilai }}"
                                                        data-catatan="{{ $pengumpulan->catatan_guru }}">
                                                        <i class="feather-check-square me-1"></i> Nilai
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-light" disabled>
                                                        <i class="feather-x"></i> Belum ada
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    {{-- MODAL PENILAIAN --}}
    <div class="modal fade" id="modalNilai" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formNilai" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="feather-award me-2 text-primary"></i> Beri Nilai Tugas
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3 text-muted">Siswa: <strong id="namaSiswa" class="text-dark"></strong></p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nilai (0 - 100) <span class="text-danger">*</span></label>
                            <input type="number" name="nilai" id="inputNilai" class="form-control" min="0" max="100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan Guru (opsional)</label>
                            <textarea name="catatan_guru" id="inputCatatan" class="form-control" rows="3" placeholder="Berikan feedback atau catatan tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-save me-1"></i> Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-nilai').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('namaSiswa').textContent = this.dataset.nama;
                    document.getElementById('inputNilai').value = this.dataset.nilai;
                    document.getElementById('inputCatatan').value = this.dataset.catatan;
                    document.getElementById('formNilai').action = '{{ url("guru/pengumpulan") }}/' + this.dataset.id + '/nilai';
                });
            });
        });
    </script>
@endpush
