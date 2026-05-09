@extends('Guru.layout.master')

@section('page_title', 'Detail Presensi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.presensi.index') }}">Presensi</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
    <div class="main-content">
        <div class="row px-4 pt-3 justify-content-center">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0" style="border-radius: 1rem; overflow:hidden;">
                    <div class="card-header bg-primary text-white text-center py-4 border-bottom-0">
                        <h4 class="mb-1 text-white fw-bold">{{ $presensi->kelas->nama_kelas }}</h4>
                        <p class="mb-0 text-white-50">{{ $presensi->mapel->nama_mapel }}</p>
                    </div>
                    
                    <div class="card-body p-5 text-center bg-white">
                        <div class="mb-4">
                            @if($presensi->status === 'aktif')
                                <div class="badge bg-success px-3 py-2 fs-6 rounded-pill mb-3">
                                    <i class="feather-activity me-2"></i>Status: Aktif
                                </div>
                            @else
                                <div class="badge bg-secondary px-3 py-2 fs-6 rounded-pill mb-3">
                                    <i class="feather-check-circle me-2"></i>Status: Selesai
                                </div>
                            @endif
                        </div>

                        <div class="qr-container bg-light p-4 rounded-4 mx-auto mb-4" style="width: fit-content; border: 2px dashed #dee2e6;">
                            <!-- QR Code Generate using external API -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($presensi->qr_code) }}" alt="QR Code Presensi" class="img-fluid rounded" style="width: 250px; height: 250px;">
                        </div>

                        <div class="text-muted mb-4">
                            <p class="mb-1"><i class="feather-clock me-2"></i>{{ \Carbon\Carbon::parse($presensi->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($presensi->jam_selesai)->format('H:i') }}</p>
                            <p class="mb-1"><i class="feather-map-pin me-2"></i>Lokasi: {{ $presensi->lokasi->nama_lokasi ?? '-' }}</p>
                            @if($presensi->keterangan)
                                <p class="mb-0"><i class="feather-info me-2"></i>{{ $presensi->keterangan }}</p>
                            @endif
                        </div>

                        <div class="d-flex gap-2 justify-content-center mt-4">
                            <a href="{{ route('guru.presensi.index') }}" class="btn btn-light px-4">Kembali</a>
                            
                            @if($presensi->status === 'aktif')
                                <form action="{{ route('guru.presensi.close', $presensi->id_presensi) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menutup presensi ini? Siswa tidak akan bisa absen lagi.');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger px-4">
                                        <i class="feather-power me-2"></i>Tutup Presensi
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
