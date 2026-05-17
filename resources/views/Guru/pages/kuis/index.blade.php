@extends('Guru.layout.master')

@section('page_title', 'Manajemen Kuis / Ujian')

@section('breadcrumb')
    <li class="breadcrumb-item">Pokok Ujian</li>
    <li class="breadcrumb-item active">Manajemen Kuis</li>
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
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="card-title mb-0" style="font-size:1.05rem;">
                            <i class="feather-file-text me-2 text-primary" style="font-size:1.15rem;"></i> Daftar Kuis / Ujian
                        </h5>
                        <a href="{{ route('guru.kuis.create') }}" class="btn btn-primary btn-sm">
                            <i class="feather-plus me-1"></i> Buat Kuis Baru
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Judul Kuis</th>
                                        <th>Kelas & Mapel</th>
                                        <th>Tipe</th>
                                        <th>Durasi</th>
                                        <th>Jadwal</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($kuis as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <h6 class="mb-1 text-dark fw-semibold" style="font-size:0.9rem;">{{ $item->judul_kuis }}</h6>
                                            </td>
                                            <td>
                                                <span class="d-block text-dark" style="font-size:0.85rem;">{{ $item->kelas->nama_kelas ?? '-' }}</span>
                                                <span class="text-muted" style="font-size:0.75rem;">{{ $item->mapel->nama_mapel ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-primary text-primary">{{ ucfirst(str_replace('_', ' ', $item->tipe)) }}</span>
                                            </td>
                                            <td>{{ $item->durasi_menit }} Menit</td>
                                            <td>
                                                <span class="d-block" style="font-size:0.8rem;"><i class="feather-calendar me-1"></i> {{ $item->tanggal_mulai->format('d/m/Y H:i') }}</span>
                                                <span class="d-block text-muted" style="font-size:0.75rem;"><i class="feather-arrow-right me-1"></i> {{ $item->tanggal_selesai->format('d/m/Y H:i') }}</span>
                                            </td>
                                            <td>
                                                @if($item->status == 'published')
                                                    <span class="badge bg-success">Published</span>
                                                @elseif($item->status == 'draft')
                                                    <span class="badge bg-secondary">Draft</span>
                                                @else
                                                    <span class="badge bg-danger">Closed</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('guru.kuis.hasil', $item->id_kuis) }}" class="btn btn-sm btn-light" title="Hasil & Jawaban Siswa">
                                                    <i class="feather-users text-success"></i>
                                                </a>
                                                <a href="{{ route('guru.kuis.show', $item->id_kuis) }}" class="btn btn-sm btn-light" title="Detail & Soal">
                                                    <i class="feather-eye text-info"></i>
                                                </a>
                                                <a href="{{ route('guru.kuis.edit', $item->id_kuis) }}" class="btn btn-sm btn-light" title="Edit">
                                                    <i class="feather-edit text-primary"></i>
                                                </a>
                                                <form action="{{ route('guru.kuis.destroy', $item->id_kuis) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kuis ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light" title="Hapus">
                                                        <i class="feather-trash-2 text-danger"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada kuis yang dibuat.</td>
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
