@extends('Guru.layout.master')

@section('page_title', 'Upload Materi')

@section('breadcrumb')
    <li class="breadcrumb-item">Pembelajaran</li>
    <li class="breadcrumb-item active">Upload Materi</li>
@endsection

@section('content')
    <div class="main-content">

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
                <i class="feather-alert-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row px-4 pt-3">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="card-title mb-0" style="font-size:1.05rem;">
                            <i class="feather-book-open me-2 text-primary" style="font-size:1.15rem;"></i> Kelas Yang Anda Ajar
                        </h5>
                        {{-- Search --}}
                        <div class="input-group" style="max-width: 320px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="feather-search text-muted" style="font-size:0.95rem;"></i>
                            </span>
                            <input type="text" id="searchKelas" class="form-control border-start-0 ps-0"
                                placeholder="Cari kelas atau mapel..." style="font-size:0.9rem;"
                                value="{{ $search }}">
                        </div>
                    </div>
                    <div class="card-body">
                        @if (empty($kelasMapel))
                            <div class="text-center py-5 text-muted">
                                <i class="feather-inbox d-block mb-2" style="font-size:2.5rem;"></i>
                                <p style="font-size:0.95rem;">Belum ada kelas yang Anda ajar.</p>
                                <p style="font-size:0.85rem;">Hubungi admin untuk generate jadwal mengajar.</p>
                            </div>
                        @else
                            <div class="row g-3" id="kelasContainer">
                                @foreach ($kelasMapel as $item)
                                    @php
                                        $kelas = $item['kelas'];
                                        $mapel = $item['mapel'];
                                        $materiCount = \App\Models\Materi::where('guru_id', $guru->id_guru)
                                            ->where('kelas_id', $kelas->id_kelas)
                                            ->where('mapel_id', $mapel->id_mapel)
                                            ->count();
                                    @endphp
                                    <div class="col-md-6 col-lg-4 kelas-card-item"
                                        data-search="{{ strtolower($kelas->nama_kelas . ' ' . $mapel->nama_mapel) }}">
                                        <a href="{{ route('guru.materi.show', [$kelas->id_kelas, $mapel->id_mapel]) }}"
                                            class="text-decoration-none">
                                            <div class="card border h-100 shadow-sm"
                                                style="transition: all 0.2s ease; cursor:pointer;"
                                                onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(91,95,199,0.15)';"
                                                onmouseout="this.style.transform=''; this.style.boxShadow='';">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-start gap-3">
                                                        <div class="avatar-text rounded-3"
                                                            style="width:48px; height:48px; min-width:48px; background: linear-gradient(135deg, #5b5fc7 0%, #8b5cf6 100%); display:flex; align-items:center; justify-content:center;">
                                                            <i class="feather-book text-white" style="font-size:1.2rem;"></i>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <h6 class="fw-bold text-dark mb-1" style="font-size:0.95rem;">
                                                                {{ $kelas->nama_kelas }}
                                                            </h6>
                                                            <p class="text-muted mb-2" style="font-size:0.82rem;">
                                                                <i class="feather-bookmark me-1" style="font-size:0.75rem;"></i>
                                                                {{ $mapel->nama_mapel }}
                                                            </p>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge bg-soft-primary text-primary" style="font-size:0.75rem;">
                                                                    <i class="feather-file-text me-1"></i> {{ $materiCount }}/15 Minggu
                                                                </span>
                                                                @if ($materiCount >= 15)
                                                                    <span class="badge bg-soft-success text-success" style="font-size:0.75rem;">Lengkap</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <i class="feather-chevron-right text-muted" style="font-size:1.1rem;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <div id="noResultMsg" class="text-center py-5 text-muted d-none">
                                <i class="feather-search d-block mb-2" style="font-size:2rem;"></i>
                                <span style="font-size:0.9rem;">Kelas tidak ditemukan</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('searchKelas');
            if (!searchInput) return;

            searchInput.addEventListener('keyup', function () {
                var keyword = this.value.toLowerCase().trim();
                var cards = document.querySelectorAll('.kelas-card-item');
                var found = 0;

                cards.forEach(function (card) {
                    var text = card.getAttribute('data-search');
                    if (text.includes(keyword)) {
                        card.style.display = '';
                        found++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                var noMsg = document.getElementById('noResultMsg');
                if (found === 0 && keyword !== '') {
                    noMsg.classList.remove('d-none');
                } else {
                    noMsg.classList.add('d-none');
                }
            });
        });
    </script>
@endpush
