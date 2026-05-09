@extends('Guru.layout.master')

@section('page_title', 'Monitoring Pengumpulan Tugas')

@section('breadcrumb')
    <li class="breadcrumb-item">Pembelajaran</li>
    <li class="breadcrumb-item active">Pengumpulan Tugas</li>
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
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="feather-clipboard text-primary me-2"></i>Daftar Tugas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="50">No</th>
                                        <th>Kelas & Mapel</th>
                                        <th>Judul Tugas</th>
                                        <th>Materi (Minggu Ke)</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th class="text-center" width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tugasList as $index => $tugas)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $tugas->kelas->nama_kelas ?? '-' }}</div>
                                                <small class="text-muted">{{ $tugas->mapel->nama_mapel ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $tugas->judul_tugas }}</div>
                                                @if($tugas->file_name)
                                                    <a href="{{ asset('storage/' . $tugas->file_path) }}" target="_blank" class="badge bg-soft-info text-info text-decoration-none mt-1">
                                                        <i class="feather-paperclip"></i> Lampiran
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                @if($tugas->materi)
                                                    Minggu {{ $tugas->materi->minggu_ke }} - {{ \Illuminate\Support\Str::limit($tugas->materi->judul_materi, 30) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($tugas->tanggal_deadline)->format('d M Y, H:i') }}
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-success text-success">{{ ucfirst($tugas->status) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('guru.tugas.pengumpulan', $tugas->id_tugas) }}" 
                                                    class="btn btn-sm btn-primary w-100" title="Monitoring Pengumpulan">
                                                    <i class="feather-users me-1"></i> Monitoring
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada tugas yang diupload melalui halaman materi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
