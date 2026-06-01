{{-- resources/views/guru/rekap/nilai.blade.php --}}
@extends('Guru.layout.master')

@section('page_title', 'Rekap Nilai')

@section('breadcrumb')
    <li class="breadcrumb-item">Rekap</li>
    <li class="breadcrumb-item active">Nilai</li>
@endsection

@section('content')
    <div class="main-content">
        <div class="row px-4 pt-3">
            <div class="col-12">
                {{-- Filter Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('guru.rekap.nilai') }}" class="row g-3 align-items-end">
                            <div class="col-md-4">
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
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label class="form-label fw-semibold fs-12 text-muted">&nbsp;</label>
                                <div>
                                    <a href="{{ route('guru.rekap.nilai') }}" class="btn btn-light w-100">
                                        <i class="feather-refresh-ccw me-1"></i> Reset
                                    </a>
                                </div>
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
                                    <p class="mb-1 opacity-75">Rata-rata Tugas</p>
                                    <h3 class="mb-0 fw-bold">{{ number_format($statistik->rata_tugas, 1) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm text-white"
                                style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                                <div class="card-body p-3 text-center">
                                    <p class="mb-1 opacity-75">Nilai Akhir</p>
                                    <h3 class="mb-0 fw-bold">{{ number_format($statistik->rata_akhir, 1) }}</h3>
                                    <small>{{ $statistik->siswa_tuntas }} Tuntas / {{ $statistik->siswa_belum_tuntas }}
                                        Belum</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm text-white"
                                style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">
                                <div class="card-body p-3 text-center">
                                    <p class="mb-1 opacity-75">Range Nilai</p>
                                    <h3 class="mb-0 fw-bold">{{ number_format($statistik->nilai_terendah, 1) }} -
                                        {{ number_format($statistik->nilai_tertinggi, 1) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Rekap Nilai --}}
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="feather-award me-2 text-primary"></i> Rekap Nilai Per Siswa
                            </h5>
                            <a href="{{ route('guru.rekap.nilai.export', request()->query()) }}" class="btn btn-sm btn-success">
                                <i class="feather-download me-1"></i> Export Excel
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">No</th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th class="text-center">Jml Tugas</th>
                                            <th class="text-center">Dinilai</th>
                                            <th class="text-center">Rata-rata Tugas</th>
                                            <th class="text-center">Nilai Akhir</th>
                                            <th class="text-center">Predikat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rekapPerSiswa as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $item->siswa->nis ?? '-' }}</td>
                                                <td>{{ $item->siswa->nama }}</td>
                                                <td class="text-center">{{ $item->jumlah_tugas }}</td>
                                                <td class="text-center">{{ $item->jumlah_dinilai }}</td>
                                                <td class="text-center">{{ $item->tugas }}</td>
                                                <td class="text-center">
                                                    <span
                                                        class="fw-bold fs-16
                                                    @if ($item->akhir >= 75) text-success
                                                    @elseif($item->akhir >= 60) text-warning
                                                    @else text-danger @endif">
                                                        {{ $item->akhir }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge
                                                    @if ($item->predikat == 'A') bg-success
                                                    @elseif($item->predikat == 'B') bg-primary
                                                    @elseif($item->predikat == 'C') bg-warning
                                                    @else bg-danger @endif">
                                                        {{ $item->predikat }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="feather-inbox d-block mb-2 fs-20"></i>
                                                    Belum ada data nilai
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if (count($rekapPerSiswa) > 0)
                                        <tfoot class="table-light">
                                            <tr class="fw-semibold">
                                                <td colspan="5" class="text-end">Rata-rata Kelas:</td>
                                                <td class="text-center">{{ number_format($statistik->rata_tugas, 1) }}</td>
                                                <td class="text-center">{{ number_format($statistik->rata_akhir, 1) }}</td>
                                                <td class="text-center"></td>
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
