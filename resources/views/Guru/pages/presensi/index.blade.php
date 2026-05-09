@extends('Guru.layout.master')

@section('page_title', 'Presensi Kelas')

@section('breadcrumb')
    <li class="breadcrumb-item">Pembelajaran</li>
    <li class="breadcrumb-item active">Presensi</li>
@endsection

@section('content')
    <div class="main-content">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
                <i class="feather-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
                <i class="feather-alert-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row px-4 pt-3">
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="card-title mb-0"><i class="feather-plus-circle text-primary me-2"></i>Buka Presensi Baru</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('guru.presensi.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Kelas & Mapel <span class="text-danger">*</span></label>
                                <select name="kelas_mapel" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Kelas & Mapel --</option>
                                    @foreach($kelasMapel as $key => $item)
                                        <option value="{{ $key }}">{{ $item['kelas']->nama_kelas }} - {{ $item['mapel']->nama_mapel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                                <select name="lokasi_id" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Lokasi --</option>
                                    @foreach($lokasi as $lok)
                                        <option value="{{ $lok->id }}">{{ $lok->nama_lokasi }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Lokasi tempat siswa melakukan presensi</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_mulai" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_selesai" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Pertemuan Ke-1"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100"><i class="feather-play me-2"></i>Buka Presensi</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="card-title mb-0"><i class="feather-list text-primary me-2"></i>Presensi Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        @if($presensiHariIni->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="feather-inbox d-block mb-2" style="font-size:3rem; color:#d1d5db;"></i>
                                <p>Belum ada presensi yang dibuka hari ini.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Kelas</th>
                                            <th>Mapel</th>
                                            <th>Status</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($presensiHariIni as $presensi)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($presensi->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($presensi->jam_selesai)->format('H:i') }}</div>
                                                    <small class="text-muted">{{ $presensi->lokasi->nama_lokasi ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-soft-primary text-primary">{{ $presensi->kelas->nama_kelas }}</span>
                                                </td>
                                                <td>{{ $presensi->mapel->nama_mapel }}</td>
                                                <td>
                                                    @if($presensi->status === 'aktif')
                                                        <span class="badge bg-success"><i class="feather-activity me-1"></i>Aktif</span>
                                                    @else
                                                        <span class="badge bg-secondary">Selesai</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('guru.presensi.show', $presensi->id_presensi) }}" class="btn btn-sm btn-light border-0 shadow-sm" title="Lihat QR & Detail">
                                                        <i class="feather-eye text-primary"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
