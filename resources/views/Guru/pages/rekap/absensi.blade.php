{{-- resources/views/guru/rekap/absensi.blade.php --}}
@extends('Guru.layout.master')

@section('page_title', 'Rekap Absensi')

@section('breadcrumb')
    <li class="breadcrumb-item">Rekap</li>
    <li class="breadcrumb-item active">Absensi</li>
@endsection

@section('content')
    <div class="main-content">
        <div class="row px-4 pt-3">
            <div class="col-12">
                {{-- Filter Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('guru.rekap.absensi') }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold fs-12 text-muted">Pilih Kelas</label>
                                <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach ($kelasList as $k)
                                        <option value="{{ $k->id_kelas }}"
                                            {{ request('kelas_id') == $k->id_kelas ? 'selected' : '' }}>
                                            {{ $k->tingkat ?? '' }} {{ $k->jurusan->nama_jurusan ?? '' }}
                                            {{ $k->golongan ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold fs-12 text-muted">Bulan</label>
                                <select name="bulan" class="form-select" onchange="this.form.submit()">
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}"
                                            {{ request('bulan', date('m')) == $m ? 'selected' : '' }}>
                                            {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold fs-12 text-muted">Tahun</label>
                                <select name="tahun" class="form-select" onchange="this.form.submit()">
                                    @foreach (range(date('Y') - 2, date('Y') + 1) as $t)
                                        <option value="{{ $t }}"
                                            {{ request('tahun', date('Y')) == $t ? 'selected' : '' }}>{{ $t }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold fs-12 text-muted">Semester</label>
                                <select name="semester" class="form-select" onchange="this.form.submit()">
                                    <option value="ganjil"
                                        {{ request('semester', 'ganjil') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                    <option value="genap"
                                        {{ request('semester', 'ganjil') == 'genap' ? 'selected' : '' }}>Genap</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold fs-12 text-muted">Tahun Ajaran</label>
                                <select name="tahun_ajaran" class="form-select" onchange="this.form.submit()">
                                    <option value="2023/2024"
                                        {{ request('tahun_ajaran', '2024/2025') == '2023/2024' ? 'selected' : '' }}>
                                        2023/2024</option>
                                    <option value="2024/2025"
                                        {{ request('tahun_ajaran', '2024/2025') == '2024/2025' ? 'selected' : '' }}>
                                        2024/2025</option>
                                    <option value="2025/2026"
                                        {{ request('tahun_ajaran', '2024/2025') == '2025/2026' ? 'selected' : '' }}>
                                        2025/2026</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                @if ($kelasId)
                    {{-- Card Statistik --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-white"
                                style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="mb-1 opacity-75">Hadir</p>
                                            <h3 class="mb-0 fw-bold">{{ $statistik->total_hadir }}</h3>
                                        </div>
                                        <i class="feather-check-circle fs-1 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-white"
                                style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="mb-1 opacity-75">Terlambat</p>
                                            <h3 class="mb-0 fw-bold">{{ $statistik->total_terlambat }}</h3>
                                        </div>
                                        <i class="feather-clock fs-1 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-white"
                                style="background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="mb-1 opacity-75">Izin/Sakit/Alpha</p>
                                            <h3 class="mb-0 fw-bold">
                                                {{ $statistik->total_izin + $statistik->total_sakit + $statistik->total_alpha }}
                                            </h3>
                                        </div>
                                        <i class="feather-alert-circle fs-1 opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-white"
                                style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="mb-1 opacity-75">Persentase</p>
                                            <h3 class="mb-0 fw-bold">{{ $statistik->persen_kehadiran }}%</h3>
                                        </div>
                                        <i class="feather-pie-chart fs-1 opacity-50"></i>
                                    </div>
                                    <small>Total {{ $statistik->total_pertemuan }} pertemuan</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Rekap Absensi --}}
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="feather-users me-2 text-primary"></i> Rekap Absensi Per Siswa
                            </h5>
                            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                                <i class="feather-printer me-1"></i> Print
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">No</th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th class="text-center" width="80">Hadir</th>
                                            <th class="text-center" width="80">Terlambat</th>
                                            <th class="text-center" width="80">Izin</th>
                                            <th class="text-center" width="80">Sakit</th>
                                            <th class="text-center" width="80">Alpha</th>
                                            <th class="text-center" width="100">Total Hadir</th>
                                            <th class="text-center" width="120">Kehadiran</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rekapPerSiswa as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $item->siswa->nis ?? '-' }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div
                                                            class="avatar-text avatar-sm rounded-circle bg-soft-primary text-primary">
                                                            {{ strtoupper(substr($item->siswa->nama, 0, 1)) }}
                                                        </div>
                                                        {{ $item->siswa->nama }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-soft-success text-success">{{ $item->hadir }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-soft-warning text-warning">{{ $item->terlambat }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-soft-info text-info">{{ $item->izin }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-soft-secondary text-secondary">{{ $item->sakit }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-soft-danger text-danger">{{ $item->alpha }}</span>
                                                </td>
                                                <td class="text-center fw-bold">{{ $item->total_hadir }} /
                                                    {{ $item->total }}</td>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1 ht-2">
                                                            <div class="progress-bar bg-success" role="progressbar"
                                                                style="width: {{ $item->persen }}%"></div>
                                                        </div>
                                                        <span
                                                            class="fw-semibold {{ $item->persen >= 75 ? 'text-success' : 'text-danger' }}">
                                                            {{ $item->persen }}%
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">
                                                    <i class="feather-inbox d-block mb-2 fs-20"></i>
                                                    Belum ada data absensi
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if (count($rekapPerSiswa) > 0)
                                        <tfoot class="table-light">
                                            <tr class="fw-semibold">
                                                <td colspan="3" class="text-end">Rata-rata Kelas:</td>
                                                <td class="text-center">
                                                    {{ round($statistik->total_hadir / max(count($rekapPerSiswa), 1), 1) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ round($statistik->total_terlambat / max(count($rekapPerSiswa), 1), 1) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ round($statistik->total_izin / max(count($rekapPerSiswa), 1), 1) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ round($statistik->total_sakit / max(count($rekapPerSiswa), 1), 1) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ round($statistik->total_alpha / max(count($rekapPerSiswa), 1), 1) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ round($statistik->total_hadir + $statistik->total_terlambat, 1) }}
                                                </td>
                                                <td class="text-center">{{ $statistik->persen_kehadiran }}%</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="feather-info me-2"></i>
                        Silakan pilih kelas terlebih dahulu untuk melihat rekap absensi.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
