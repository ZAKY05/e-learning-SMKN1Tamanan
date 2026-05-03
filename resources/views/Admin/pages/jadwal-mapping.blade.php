@extends('Admin.layout.master')

@section('page_title', 'Mapping Singkatan Import')

@section('breadcrumb')
    <li class="breadcrumb-item">Akademik</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.jadwal.index') }}">Jadwal</a></li>
    <li class="breadcrumb-item active">Mapping Singkatan</li>
@endsection

@section('content')
<div class="main-content">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
            <i class="feather-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row px-4 pt-3">
        {{-- INFO --}}
        <div class="col-12 mb-3">
            <div class="alert alert-info" style="font-size:0.9rem;">
                <i class="feather-info me-2"></i>
                <strong>Mapping Singkatan ASC</strong> — Halaman ini digunakan untuk mencocokkan singkatan nama Kelas dan Mapel yang ada di file Excel ASC TimeTables dengan data Master di sistem E-Learning.
                Contoh: Singkatan <code>PIOK</code> di ASC → dipetakan ke Mapel <strong>Penjaskes</strong> di sistem.
            </div>
        </div>

        {{-- MAPPING MAPEL --}}
        <div class="col-lg-6 mb-4">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title mb-0" style="font-size:1rem;">
                        <i class="feather-book me-2 text-primary"></i> Mapping Mata Pelajaran
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Form Tambah --}}
                    <form action="{{ route('admin.jadwal.mapping.save') }}" method="POST" class="row g-2 mb-3 align-items-end">
                        @csrf
                        <input type="hidden" name="tipe" value="mapel">
                        <div class="col-4">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Singkatan ASC</label>
                            <input type="text" name="singkatan" class="form-control form-control-sm" placeholder="cth: PIOK" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Mapel di Sistem</label>
                            <select name="target_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih --</option>
                                @foreach($allMapel as $m)
                                    <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="feather-plus me-1"></i> Tambah
                            </button>
                        </div>
                    </form>

                    {{-- Tabel --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle text-center" style="font-size:0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Singkatan ASC</th>
                                    <th>Mapel di Sistem</th>
                                    <th style="width:60px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mapelMappings as $mm)
                                <tr>
                                    <td><code>{{ $mm->singkatan }}</code></td>
                                    <td>{{ $mm->mapel->nama_mapel ?? '⚠️ Mapel Dihapus' }}</td>
                                    <td>
                                        <form action="{{ route('admin.jadwal.mapping.delete', $mm->id) }}" method="POST" onsubmit="return confirm('Hapus mapping ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger py-0 px-1"><i class="feather-trash-2" style="font-size:0.8rem;"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-muted py-3">Belum ada mapping mapel.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAPPING KELAS --}}
        <div class="col-lg-6 mb-4">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title mb-0" style="font-size:1rem;">
                        <i class="feather-layers me-2 text-success"></i> Mapping Kelas
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Form Tambah --}}
                    <form action="{{ route('admin.jadwal.mapping.save') }}" method="POST" class="row g-2 mb-3 align-items-end">
                        @csrf
                        <input type="hidden" name="tipe" value="kelas">
                        <div class="col-4">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Singkatan ASC</label>
                            <input type="text" name="singkatan" class="form-control form-control-sm" placeholder="cth: X-ELKA 1" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Kelas di Sistem</label>
                            <select name="target_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih --</option>
                                @foreach($allKelas as $k)
                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="feather-plus me-1"></i> Tambah
                            </button>
                        </div>
                    </form>

                    {{-- Tabel --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle text-center" style="font-size:0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Singkatan ASC</th>
                                    <th>Kelas di Sistem</th>
                                    <th style="width:60px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kelasMappings as $km)
                                <tr>
                                    <td><code>{{ $km->singkatan }}</code></td>
                                    <td>{{ $km->kelas->nama_kelas ?? '⚠️ Kelas Dihapus' }}</td>
                                    <td>
                                        <form action="{{ route('admin.jadwal.mapping.delete', $km->id) }}" method="POST" onsubmit="return confirm('Hapus mapping ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger py-0 px-1"><i class="feather-trash-2" style="font-size:0.8rem;"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-muted py-3">Belum ada mapping kelas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Kembali --}}
        <div class="col-12 mb-4">
            <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary">
                <i class="feather-arrow-left me-1"></i> Kembali ke Jadwal
            </a>
        </div>
    </div>
</div>
@endsection
