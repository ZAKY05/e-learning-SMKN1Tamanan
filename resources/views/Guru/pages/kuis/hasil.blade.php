@extends('Guru.layout.master')

@section('page_title', 'Hasil Kuis Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item">Pokok Ujian</li>
    <li class="breadcrumb-item"><a href="{{ route('guru.kuis.index') }}">Manajemen Kuis</a></li>
    <li class="breadcrumb-item active">Hasil Kuis</li>
@endsection

@section('content')
    <div class="main-content">
        <div class="row px-4 pt-3">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="feather-users me-2 text-primary"></i> Hasil Kuis: {{ $kuis->judul_kuis }}
                            </h5>
                            <p class="text-muted mb-0" style="font-size:0.85rem;">{{ $kuis->kelas->nama_kelas ?? '-' }} - {{ $kuis->mapel->nama_mapel ?? '-' }}</p>
                        </div>
                        <a href="{{ route('guru.kuis.index') }}" class="btn btn-light btn-sm">
                            <i class="feather-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">No</th>
                                        <th>Nama Siswa</th>
                                        <th>Waktu Submit</th>
                                        <th>Benar/Salah/Kosong</th>
                                        <th>Nilai Akhir</th>
                                        <th>Status Penilaian</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($hasil as $index => $item)
                                        <tr>
                                            <td class="ps-4">{{ $index + 1 }}</td>
                                            <td>
                                                <h6 class="mb-1 text-dark fw-semibold" style="font-size:0.9rem;">{{ $item->siswa->name ?? 'Siswa Tidak Ditemukan' }}</h6>
                                            </td>
                                            <td>
                                                @if($item->waktu_selesai)
                                                    {{ $item->waktu_selesai->format('d/m/Y H:i') }}
                                                @else
                                                    <span class="text-warning">Belum Selesai</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-success text-success" title="Benar">{{ $item->jumlah_benar }}</span>
                                                <span class="badge bg-soft-danger text-danger" title="Salah">{{ $item->jumlah_salah }}</span>
                                                <span class="badge bg-soft-secondary text-secondary" title="Tidak Dijawab">{{ $item->tidak_dijawab }}</span>
                                            </td>
                                            <td>
                                                <h6 class="mb-0 {{ $item->nilai >= 75 ? 'text-success' : 'text-danger' }}">{{ $item->nilai ?? '0' }}</h6>
                                            </td>
                                            <td>
                                                @if($item->status == 'dinilai')
                                                    <span class="badge bg-success">Sudah Dinilai</span>
                                                @elseif($item->status == 'selesai')
                                                    <span class="badge bg-warning">Menunggu Koreksi Essay</span>
                                                @else
                                                    <span class="badge bg-secondary">Sedang Mengerjakan</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($item->status != 'sedang_mengerjakan')
                                                    <a href="{{ route('guru.kuis.hasil.review', [$kuis->id_kuis, $item->id_hasil]) }}" class="btn btn-sm btn-primary" title="Review Jawaban & Nilai Essay">
                                                        <i class="feather-check-square me-1"></i> Review & Nilai
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada siswa yang mengerjakan kuis ini.</td>
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
