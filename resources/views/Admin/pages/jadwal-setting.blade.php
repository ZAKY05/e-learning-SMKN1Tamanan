@extends('Admin.layout.master')

@section('page_title', 'Pengaturan Jadwal')

@section('breadcrumb')
    <li class="breadcrumb-item">Akademik</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.jadwal.index') }}">Jadwal</a></li>
    <li class="breadcrumb-item active">Pengaturan</li>
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

    <form action="{{ route('admin.jadwal.setting.save') }}" method="POST" id="formSetting">
        @csrf
        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
        <input type="hidden" name="semester" value="{{ $semester }}">

        {{-- Header --}}
        <div class="row px-4 pt-3">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size:1.05rem;">
                            <i class="feather-settings me-2 text-primary"></i> Pengaturan Jadwal
                            <span class="badge bg-soft-primary text-primary ms-2">{{ ucfirst($semester) }} {{ $tahunAjaran }}</span>
                        </h5>
                        <a href="{{ route('admin.jadwal.index', ['tahun_ajaran' => $tahunAjaran, 'semester' => $semester]) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="feather-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Waktu Pelajaran --}}
        <div class="row px-4 pt-1">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="feather-watch me-2 text-warning"></i> Waktu Pelajaran</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Waktu Mulai</label>
                                <input type="time" name="waktu_mulai" class="form-control" id="waktuMulai"
                                       value="{{ isset($setting->waktu_mulai) ? \Carbon\Carbon::parse($setting->waktu_mulai)->format('H:i') : '07:00' }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Durasi per JP (menit)</label>
                                <input type="number" name="durasi_jam_menit" class="form-control" id="durasiJp"
                                       value="{{ $setting->durasi_jam_menit ?? 45 }}" min="30" max="60">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Simpan --}}
        <div class="row px-4 pt-1 pb-4">
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="feather-save me-2"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
