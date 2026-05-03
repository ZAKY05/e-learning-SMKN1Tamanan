@extends('Admin.layout.master')

@section('page_title', 'Kode Guru Jadwal')

@section('breadcrumb')
    <li class="breadcrumb-item">Akademik</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.jadwal.index') }}">Jadwal</a></li>
    <li class="breadcrumb-item active">Kode Guru</li>
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
        {{-- INFO --}}
        <div class="col-12 mb-3">
            <div class="alert alert-info" style="font-size:0.9rem;">
                <i class="feather-info me-2"></i>
                <strong>Kode Guru</strong> — Setiap guru memiliki kode unik (misal: <code>A</code>, <code>B</code>, <code>AA</code>) yang digunakan di jadwal ASC TimeTables. Kode ini <strong>bisa berubah setiap semester</strong>. Pastikan kode di sini sesuai dengan kode yang tertera di file jadwal Excel ASC Anda.
            </div>
        </div>

        {{-- Filter Tahun & Semester --}}
        <div class="col-12 mb-3">
            <div class="card stretch stretch-full">
                <div class="card-body py-3">
                    <form action="{{ route('admin.jadwal.guru-kode') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" class="form-control form-control-sm" value="{{ $tahunAjaran }}" placeholder="2025/2026">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Semester</label>
                            <select name="semester" class="form-select form-select-sm">
                                <option value="ganjil" {{ $semester === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="genap" {{ $semester === 'genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="feather-filter me-1"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Form Tambah + Tabel --}}
        <div class="col-lg-8 mb-4">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title mb-0" style="font-size:1rem;">
                        <i class="feather-user-check me-2 text-primary"></i> 
                        Daftar Kode Guru 
                        <span class="badge bg-soft-primary text-primary ms-2">{{ ucfirst($semester) }} {{ $tahunAjaran }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Form Tambah --}}
                    <form action="{{ route('admin.jadwal.guru-kode.save') }}" method="POST" class="row g-2 mb-3 align-items-end">
                        @csrf
                        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                        <input type="hidden" name="semester" value="{{ $semester }}">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Kode</label>
                            <input type="text" name="kode" class="form-control form-control-sm text-center fw-bold" 
                                   placeholder="A" maxlength="5" required style="text-transform:uppercase; font-size:1.1rem;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Nama Guru</label>
                            <select name="guru_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($allGuru as $g)
                                    <option value="{{ $g->id_guru }}">{{ $g->nama }} (NIP: {{ $g->nip ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="feather-plus me-1"></i> Tambah Kode
                            </button>
                        </div>
                    </form>

                    <hr>

                    {{-- Tabel --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle text-center" style="font-size:0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:80px;">Kode</th>
                                    <th>Nama Guru</th>
                                    <th>NIP</th>
                                    <th style="width:70px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($guruKodes as $gk)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary" style="font-size:1rem; padding:5px 12px;">{{ $gk->kode }}</span>
                                    </td>
                                    <td class="text-start">{{ $gk->guru->nama ?? '⚠️ Guru Dihapus' }}</td>
                                    <td>{{ $gk->guru->nip ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('admin.jadwal.guru-kode.delete', $gk->id) }}" method="POST" 
                                              onsubmit="return confirm('Hapus kode {{ $gk->kode }} ?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger py-0 px-2">
                                                <i class="feather-trash-2" style="font-size:0.85rem;"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-muted py-4">
                                        <i class="feather-inbox d-block mb-2" style="font-size:2rem;"></i>
                                        Belum ada kode guru untuk semester ini.<br>
                                        <small>Silakan tambahkan kode guru di form di atas.</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted">Total: {{ $guruKodes->count() }} kode guru terdaftar.</small>
                </div>
            </div>
        </div>

        {{-- Panduan --}}
        <div class="col-lg-4 mb-4">
            <div class="card stretch stretch-full border-warning">
                <div class="card-header bg-soft-warning">
                    <h6 class="card-title mb-0"><i class="feather-help-circle me-1"></i> Panduan</h6>
                </div>
                <div class="card-body" style="font-size:0.85rem;">
                    <p><strong>Apa itu Kode Guru?</strong></p>
                    <p>Di jadwal ASC TimeTables, setiap guru direpresentasikan dengan huruf kode singkat, contoh:</p>
                    <ul>
                        <li><code>A</code> = Pak Ahmad</li>
                        <li><code>B</code> = Bu Budi</li>
                        <li><code>AA</code> = Pak Anton</li>
                    </ul>
                    <p>Kode ini dipakai saat Anda meng-<em>import</em> file jadwal Excel dari ASC. Sistem akan mencocokkan kode huruf di Excel dengan nama guru di database.</p>
                    <hr>
                    <p><strong>⚠️ Penting:</strong> Kode guru bisa berbeda setiap semester. Pastikan Anda memilih <strong>Tahun Ajaran</strong> dan <strong>Semester</strong> yang benar di atas sebelum menambahkan kode.</p>
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
