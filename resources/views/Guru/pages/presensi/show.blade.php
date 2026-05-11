@extends('Guru.layout.master')

@section('page_title', 'Detail Presensi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.presensi.index') }}">Presensi</a></li>
    <li class="breadcrumb-item active">Detail</li>
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
            {{-- Card Info & QR Code --}}
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0" style="border-radius: 1rem; overflow:hidden;">
                    <div class="card-header text-white text-center py-4 border-bottom-0" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                        <h4 class="mb-1 text-white fw-bold">{{ $presensi->kelas->nama_kelas }}</h4>
                        <p class="mb-0 text-white-50">{{ $presensi->mapel->nama_mapel }}</p>
                    </div>
                    
                    <div class="card-body p-4 text-center bg-white">
                        {{-- Status Badge --}}
                        <div class="mb-3">
                            @if($faseWaktu === 'normal')
                                <div class="badge bg-success px-3 py-2 fs-6 rounded-pill">
                                    <i class="feather-activity me-2"></i>Presensi Aktif
                                </div>
                            @elseif($faseWaktu === 'terlambat')
                                <div class="badge px-3 py-2 fs-6 rounded-pill" style="background: linear-gradient(135deg, #f6c23e, #e67e22); color: #fff;">
                                    <i class="feather-clock me-2"></i>Masa Toleransi Terlambat
                                </div>
                                @if($sisaWaktuTerlambat !== null)
                                    <div class="mt-2">
                                        <small class="text-warning fw-bold">
                                            <i class="feather-alert-triangle me-1"></i>Sisa waktu: {{ $sisaWaktuTerlambat }} menit lagi
                                        </small>
                                    </div>
                                @endif
                            @elseif($faseWaktu === 'expired')
                                <div class="badge bg-danger px-3 py-2 fs-6 rounded-pill">
                                    <i class="feather-alert-triangle me-2"></i>Waktu Habis - Tindak Lanjut Diperlukan
                                </div>
                            @else
                                <div class="badge bg-secondary px-3 py-2 fs-6 rounded-pill">
                                    <i class="feather-check-circle me-2"></i>Presensi Selesai
                                </div>
                            @endif
                        </div>

                        {{-- QR Code --}}
                        @if($faseWaktu === 'normal' || $faseWaktu === 'terlambat')
                            <div class="qr-container bg-light p-4 rounded-4 mx-auto mb-4" style="width: fit-content; border: 2px dashed #dee2e6;">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($presensi->qr_code) }}" alt="QR Code Presensi" class="img-fluid rounded" style="width: 220px; height: 220px;">
                            </div>
                        @endif

                        {{-- Info Waktu --}}
                        <div class="text-muted mb-3">
                            <p class="mb-1"><i class="feather-clock me-2"></i><strong>Jam Presensi:</strong> {{ \Carbon\Carbon::parse($presensi->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($presensi->jam_selesai)->format('H:i') }}</p>
                            <p class="mb-1"><i class="feather-clock me-2 text-warning"></i><strong>Batas Terlambat:</strong> {{ $batasTerlambat->format('H:i') }}</p>
                            <p class="mb-1"><i class="feather-map-pin me-2"></i><strong>Lokasi:</strong> {{ $presensi->lokasi->nama_lokasi ?? '-' }}</p>
                            @if($presensi->keterangan)
                                <p class="mb-0"><i class="feather-info me-2"></i>{{ $presensi->keterangan }}</p>
                            @endif
                        </div>

                        {{-- Statistik Kehadiran --}}
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 rounded-3 text-center" style="background: #e8f5e9;">
                                    <div class="fw-bold text-success fs-4">{{ $totalHadir }}</div>
                                    <small class="text-muted">Hadir</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 text-center" style="background: #fff3e0;">
                                    <div class="fw-bold text-warning fs-4">{{ $totalTerlambat }}</div>
                                    <small class="text-muted">Terlambat</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 text-center" style="background: #fce4ec;">
                                    <div class="fw-bold text-danger fs-4">{{ $totalBelumAbsen + $totalAlpha }}</div>
                                    <small class="text-muted">Belum Absen</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 text-center" style="background: #e3f2fd;">
                                    <div class="fw-bold text-info fs-4">{{ $totalSakit }}</div>
                                    <small class="text-muted">Sakit</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 text-center" style="background: #f3e5f5;">
                                    <div class="fw-bold" style="color:#7b1fa2; font-size:1.5rem;">{{ $totalIzin }}</div>
                                    <small class="text-muted">Izin</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 text-center" style="background: #f5f5f5;">
                                    <div class="fw-bold text-dark fs-4">{{ $totalSiswa }}</div>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2 justify-content-center mt-3">
                            <a href="{{ route('guru.presensi.index') }}" class="btn btn-light px-4"><i class="feather-arrow-left me-1"></i>Kembali</a>
                            
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

            {{-- Daftar Siswa --}}
            <div class="col-lg-7 mb-4">
                {{-- Siswa Belum Absen (ditampilkan saat toleransi/expired/selesai) --}}
                @if($siswaBelumAbsen->count() > 0)
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem; overflow:hidden;">
                        <div class="card-header border-bottom-0 pt-4 pb-3 px-4" style="background: linear-gradient(135deg, #fff3e0 0%, #fce4ec 100%);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 text-danger">
                                        <i class="feather-alert-circle me-2"></i>Siswa Belum Absen
                                        <span class="badge bg-danger rounded-pill ms-2">{{ $siswaBelumAbsen->count() }}</span>
                                    </h5>
                                    <small class="text-muted">Tentukan status kehadiran siswa yang belum melakukan presensi</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            @if($faseWaktu === 'terlambat')
                                <div class="alert alert-warning d-flex align-items-center mb-3 rounded-3" role="alert" style="border: none; background: linear-gradient(135deg, #fff8e1, #fff3e0);">
                                    <i class="feather-clock me-3 fs-4"></i>
                                    <div>
                                        <strong>Masa Toleransi Terlambat Aktif</strong><br>
                                        <small>Siswa masih bisa scan QR dan akan dicatat sebagai <span class="badge bg-warning text-dark">Terlambat</span>. Sisa waktu: <strong>{{ $sisaWaktuTerlambat }} menit</strong>.</small>
                                    </div>
                                </div>
                            @elseif($faseWaktu === 'expired' || $faseWaktu === 'selesai')
                                <div class="alert alert-danger d-flex align-items-center mb-3 rounded-3" role="alert" style="border: none; background: linear-gradient(135deg, #ffebee, #fce4ec);">
                                    <i class="feather-alert-triangle me-3 fs-4"></i>
                                    <div>
                                        <strong>Waktu Presensi Telah Berakhir</strong><br>
                                        <small>Silakan tentukan status kehadiran untuk siswa yang belum absen di bawah ini.</small>
                                    </div>
                                </div>
                            @endif

                            <form action="{{ route('guru.presensi.updateStatus', $presensi->id_presensi) }}" method="POST" id="formUpdateStatus">
                                @csrf
                                
                                {{-- Quick Action Buttons --}}
                                <div class="d-flex gap-2 mb-3 flex-wrap">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="setAllStatus('alpha')">
                                        <i class="feather-x-circle me-1"></i>Semua Alpha
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="setAllStatus('sakit')">
                                        <i class="feather-thermometer me-1"></i>Semua Sakit
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setAllStatus('izin')">
                                        <i class="feather-file-text me-1"></i>Semua Izin
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr class="table-light">
                                                <th style="width: 40px;">No</th>
                                                <th>Nama Siswa</th>
                                                <th>NIS</th>
                                                <th style="width: 160px;">Status</th>
                                                <th style="width: 180px;">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($siswaBelumAbsen as $index => $siswa)
                                                <tr>
                                                    <td class="text-muted">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2" style="width: 36px; height: 36px; background: linear-gradient(135deg, #e74c3c, #c0392b); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.8rem;">
                                                                {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold">{{ $siswa->nama }}</div>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="siswa[{{ $index }}][id_siswa]" value="{{ $siswa->id_siswa }}">
                                                    </td>
                                                    <td><small class="text-muted">{{ $siswa->nis }}</small></td>
                                                    <td>
                                                        <select name="siswa[{{ $index }}][status]" class="form-select form-select-sm status-select" data-index="{{ $index }}">
                                                            <option value="alpha" selected>Alpha</option>
                                                            <option value="sakit">Sakit</option>
                                                            <option value="izin">Izin</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="siswa[{{ $index }}][keterangan]" class="form-control form-control-sm" placeholder="Opsional..." maxlength="255">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3 text-end">
                                    <button type="submit" class="btn btn-primary px-4" onclick="return confirm('Simpan status kehadiran untuk {{ $siswaBelumAbsen->count() }} siswa?')">
                                        <i class="feather-save me-2"></i>Simpan Status Kehadiran
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Siswa Sudah Absen --}}
                <div class="card shadow-sm border-0" style="border-radius: 1rem; overflow:hidden;">
                    <div class="card-header border-bottom-0 pt-4 pb-3 px-4" style="background: linear-gradient(135deg, #e8f5e9 0%, #e3f2fd 100%);">
                        <h5 class="card-title mb-1 text-success">
                            <i class="feather-check-circle me-2"></i>Daftar Kehadiran
                            <span class="badge bg-success rounded-pill ms-2">{{ $siswaSudahAbsen->count() }}</span>
                        </h5>
                        <small class="text-muted">Siswa yang sudah tercatat kehadirannya</small>
                    </div>
                    <div class="card-body p-4">
                        @if($siswaSudahAbsen->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="table-light">
                                            <th style="width: 40px;">No</th>
                                            <th>Nama Siswa</th>
                                            <th>Waktu</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                            @if($presensi->status === 'aktif')
                                                <th class="text-end">Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($siswaSudahAbsen as $index => $detail)
                                            <tr>
                                                <td class="text-muted">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @php
                                                            $avatarColors = [
                                                                'hadir' => 'linear-gradient(135deg, #27ae60, #2ecc71)',
                                                                'terlambat' => 'linear-gradient(135deg, #f39c12, #e67e22)',
                                                                'sakit' => 'linear-gradient(135deg, #3498db, #2980b9)',
                                                                'izin' => 'linear-gradient(135deg, #9b59b6, #8e44ad)',
                                                                'alpha' => 'linear-gradient(135deg, #e74c3c, #c0392b)',
                                                            ];
                                                            $color = $avatarColors[$detail->status_kehadiran] ?? $avatarColors['alpha'];
                                                        @endphp
                                                        <div class="avatar avatar-sm me-2" style="width: 36px; height: 36px; background: {{ $color }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.8rem;">
                                                            {{ strtoupper(substr($detail->siswa->nama ?? '?', 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $detail->siswa->nama ?? '-' }}</div>
                                                            <small class="text-muted">{{ $detail->siswa->nis ?? '-' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($detail->waktu_presensi)
                                                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($detail->waktu_presensi)->format('H:i:s') }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @switch($detail->status_kehadiran)
                                                        @case('hadir')
                                                            <span class="badge rounded-pill" style="background: linear-gradient(135deg, #27ae60, #2ecc71); padding: 6px 12px;">
                                                                <i class="feather-check me-1"></i>Hadir
                                                            </span>
                                                            @break
                                                        @case('terlambat')
                                                            <span class="badge rounded-pill" style="background: linear-gradient(135deg, #f39c12, #e67e22); padding: 6px 12px;">
                                                                <i class="feather-clock me-1"></i>Terlambat
                                                            </span>
                                                            @break
                                                        @case('sakit')
                                                            <span class="badge rounded-pill" style="background: linear-gradient(135deg, #3498db, #2980b9); padding: 6px 12px;">
                                                                <i class="feather-thermometer me-1"></i>Sakit
                                                            </span>
                                                            @break
                                                        @case('izin')
                                                            <span class="badge rounded-pill" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); padding: 6px 12px;">
                                                                <i class="feather-file-text me-1"></i>Izin
                                                            </span>
                                                            @break
                                                        @case('alpha')
                                                            <span class="badge rounded-pill" style="background: linear-gradient(135deg, #e74c3c, #c0392b); padding: 6px 12px;">
                                                                <i class="feather-x-circle me-1"></i>Alpha
                                                            </span>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $detail->keterangan ?? '-' }}</small>
                                                </td>
                                                @if($presensi->status === 'aktif')
                                                    <td class="text-end">
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-light border-0 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="feather-more-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                                @foreach(['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha'] as $statusKey => $statusLabel)
                                                                    @if($detail->status_kehadiran !== $statusKey)
                                                                        <li>
                                                                            <form action="{{ route('guru.presensi.updateStatusSingle', [$presensi->id_presensi, $detail->siswa_id]) }}" method="POST">
                                                                                @csrf
                                                                                <input type="hidden" name="status" value="{{ $statusKey }}">
                                                                                <button type="submit" class="dropdown-item">
                                                                                    <i class="feather-{{ $statusKey === 'hadir' ? 'check' : ($statusKey === 'terlambat' ? 'clock' : ($statusKey === 'sakit' ? 'thermometer' : ($statusKey === 'izin' ? 'file-text' : 'x-circle'))) }} me-2"></i>
                                                                                    Ubah ke {{ $statusLabel }}
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="feather-users d-block mb-2" style="font-size:3rem; color:#d1d5db;"></i>
                                <p>Belum ada siswa yang melakukan presensi.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function setAllStatus(status) {
        document.querySelectorAll('.status-select').forEach(function(select) {
            select.value = status;
        });
    }

    // Auto-refresh halaman setiap 30 detik jika presensi masih aktif
    @if($faseWaktu === 'normal' || $faseWaktu === 'terlambat')
        setTimeout(function() {
            location.reload();
        }, 30000);
    @endif
</script>
@endpush
