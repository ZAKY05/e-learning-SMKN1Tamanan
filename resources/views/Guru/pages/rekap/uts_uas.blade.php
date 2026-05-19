{{-- resources/views/guru/rekap/uts_uas.blade.php --}}
@extends('Guru.layout.master')

@section('page_title', 'Rekap ' . strtoupper($jenis))

@section('breadcrumb')
    <li class="breadcrumb-item">Rekap</li>
    <li class="breadcrumb-item active">{{ strtoupper($jenis) }}</li>
@endsection

@section('content')
    <div class="main-content">
        <div class="row px-4 pt-3">
            <div class="col-12">
                {{-- Filter Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('guru.rekap.uts_uas') }}" class="row g-3 align-items-end">
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
                            <div class="col-md-3">
                                <label class="form-label fw-semibold fs-12 text-muted">Mata Pelajaran</label>
                                <select name="mapel_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Pilih Mapel --</option>
                                    @foreach ($mapelList as $m)
                                        <option value="{{ $m->id_mapel }}"
                                            {{ request('mapel_id') == $m->id_mapel ? 'selected' : '' }}>
                                            {{ $m->nama_mapel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold fs-12 text-muted">Jenis</label>
                                <select name="jenis" class="form-select" onchange="this.form.submit()">
                                    <option value="uts" {{ $jenis == 'uts' ? 'selected' : '' }}>UTS</option>
                                    <option value="uas" {{ $jenis == 'uas' ? 'selected' : '' }}>UAS</option>
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
                            <div class="col-md-2">
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

                @if ($kelasId && $mapelId)
                    {{-- Card Statistik --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm text-white"
                                style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);">
                                <div class="card-body p-3 text-center">
                                    <p class="mb-1 opacity-75">Rata-rata</p>
                                    <h3 class="mb-0 fw-bold">{{ number_format($statistik->rata_nilai, 1) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm text-white"
                                style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                                <div class="card-body p-3 text-center">
                                    <p class="mb-1 opacity-75">Nilai Tertinggi</p>
                                    <h3 class="mb-0 fw-bold">{{ number_format($statistik->tertinggi, 1) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm text-white"
                                style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">
                                <div class="card-body p-3 text-center">
                                    <p class="mb-1 opacity-75">Nilai Terendah</p>
                                    <h3 class="mb-0 fw-bold">{{ number_format($statistik->terendah, 1) }}</h3>
                                    <small>{{ $statistik->jumlah_siswa }} siswa dinilai</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Rekap UTS/UAS --}}
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="feather-file-text me-2 text-primary"></i> Daftar Nilai {{ strtoupper($jenis) }}
                                Per Siswa
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
                                            <th class="text-center" width="150">Nilai</th>
                                            <th class="text-center" width="150">Status</th>
                                            <th class="text-center">Waktu Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rekapPerSiswa as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $item->siswa->nis ?? '-' }}</td>
                                                <td>{{ $item->siswa->nama }}</td>
                                                <td class="text-center">
                                                    @if ($item->nilai !== null)
                                                        <span
                                                            class="fw-bold fs-16
                                                        @if ($item->nilai >= 75) text-success
                                                        @elseif($item->nilai >= 60) text-warning
                                                        @else text-danger @endif">
                                                            {{ number_format($item->nilai, 1) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($item->nilai !== null)
                                                        <span class="badge bg-soft-success text-success">Selesai</span>
                                                    @else
                                                        <span class="badge bg-soft-danger text-danger">Belum
                                                            Mengerjakan</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($item->waktu_selesai)
                                                        {{ \Carbon\Carbon::parse($item->waktu_selesai)->translatedFormat('d M Y, H:i') }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="feather-inbox d-block mb-2 fs-20"></i>
                                                    Belum ada data {{ strtoupper($jenis) }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if (count($rekapPerSiswa) > 0)
                                        <tfoot class="table-light">
                                            <tr class="fw-semibold">
                                                <td colspan="3" class="text-end">Rata-rata Kelas:</td>
                                                <td class="text-center">{{ number_format($statistik->rata_nilai, 1) }}</td>
                                                <td colspan="2" class="text-center"></td>
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
                        Silakan pilih kelas dan mata pelajaran terlebih dahulu.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
