@extends('Guru.layout.master')

@section('page_title', 'Detail Kuis & Soal')

@section('breadcrumb')
    <li class="breadcrumb-item">Pokok Ujian</li>
    <li class="breadcrumb-item"><a href="{{ route('guru.kuis.index') }}">Manajemen Kuis</a></li>
    <li class="breadcrumb-item active">Detail Kuis</li>
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
            <div class="col-xl-4 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Kuis</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="fw-bold text-dark mb-1">{{ $kuis->judul_kuis }}</h5>
                        <p class="text-muted mb-3">{{ $kuis->kelas->nama_kelas ?? '-' }} - {{ $kuis->mapel->nama_mapel ?? '-' }}</p>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tipe</span>
                            <span class="fw-medium text-dark">{{ ucfirst(str_replace('_', ' ', $kuis->tipe)) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Durasi</span>
                            <span class="fw-medium text-dark">{{ $kuis->durasi_menit }} Menit</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Jadwal Mulai</span>
                            <span class="fw-medium text-dark">{{ $kuis->tanggal_mulai->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Jadwal Selesai</span>
                            <span class="fw-medium text-dark">{{ $kuis->tanggal_selesai->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Status</span>
                            @if($kuis->status == 'published')
                                <span class="badge bg-success">Published</span>
                            @elseif($kuis->status == 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @else
                                <span class="badge bg-danger">Closed</span>
                            @endif
                        </div>
                        
                        <hr>
                        <p class="text-muted small mb-0">{{ $kuis->deskripsi }}</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Daftar Soal</h5>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSoalModal">
                            <i class="feather-plus me-1"></i> Tambah Soal
                        </button>
                    </div>
                    <div class="card-body">
                        @forelse($kuis->soalKuis as $index => $soal)
                            <div class="border rounded p-3 mb-3 position-relative">
                                <div class="d-flex justify-content-between mb-2">
                                    <h6 class="fw-bold mb-0">Soal #{{ $soal->nomor_urut }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <form action="{{ route('guru.kuis.soal.destroy', [$kuis->id_kuis, $soal->id_soal]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus soal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-danger p-0" title="Hapus"><i class="feather-trash-2"></i></button>
                                        </form>
                                    </div>
                                </div>
                                @if($soal->gambar)
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/' . $soal->gambar) }}" alt="Gambar Soal" class="img-fluid rounded" style="max-height: 200px;">
                                    </div>
                                @endif
                                <p class="text-dark mb-3">{{ $soal->pertanyaan }}</p>
                                
                                @if($soal->tipe_soal == 'pilihan_ganda')
                                    <div class="row g-2">
                                        @foreach($soal->pilihanJawaban as $pilihan)
                                            <div class="col-md-6">
                                                <div class="p-2 border rounded {{ $pilihan->is_correct ? 'border-success bg-soft-success' : 'bg-light' }} d-flex flex-column">
                                                    <div>
                                                        <span class="fw-bold me-2">{{ $pilihan->pilihan }}.</span>
                                                        <span>{{ $pilihan->isi_pilihan }}</span>
                                                        @if($pilihan->is_correct)
                                                            <i class="feather-check-circle text-success float-end mt-1"></i>
                                                        @endif
                                                    </div>
                                                    @if($pilihan->gambar_pilihan)
                                                        <div class="mt-2 text-center">
                                                            <img src="{{ asset('storage/' . $pilihan->gambar_pilihan) }}" class="img-fluid rounded" style="max-height: 100px;">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <p class="mb-0">Belum ada soal untuk kuis ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Soal -->
    <div class="modal fade" id="tambahSoalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Soal Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('guru.kuis.soal.store', $kuis->id_kuis) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipe Soal</label>
                                <select name="tipe_soal" id="tipeSoalSelect" class="form-select" required>
                                    <option value="pilihan_ganda">Pilihan Ganda</option>
                                    <option value="essay">Essay</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nomor Urut</label>
                                <input type="number" name="nomor_urut" class="form-control" value="{{ $kuis->soalKuis->count() + 1 }}" required min="1">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Pertanyaan</label>
                                <textarea name="pertanyaan" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Gambar Soal (Opsional)</label>
                                <input type="file" name="gambar" class="form-control" accept="image/*">
                                <small class="text-muted">Maksimal ukuran file 2MB.</small>
                            </div>

                            <!-- Pilihan Ganda Area -->
                            <div class="col-12" id="pilihanGandaArea">
                                <hr>
                                <h6 class="mb-3">Pilihan Jawaban (Pilihan Ganda)</h6>
                                
                                <div class="row g-2 mb-3 align-items-center">
                                    <div class="col-1 text-center fw-bold">A</div>
                                    <div class="col-6"><input type="text" name="pilihan[A]" class="form-control" placeholder="Teks Jawaban A"></div>
                                    <div class="col-3"><input type="file" name="gambar_pilihan[A]" class="form-control form-control-sm" accept="image/*" title="Gambar A"></div>
                                    <div class="col-2 text-center">
                                        <input class="form-check-input" type="radio" name="kunci_jawaban" value="A" required> Kunci
                                    </div>
                                </div>
                                
                                <div class="row g-2 mb-3 align-items-center">
                                    <div class="col-1 text-center fw-bold">B</div>
                                    <div class="col-6"><input type="text" name="pilihan[B]" class="form-control" placeholder="Teks Jawaban B"></div>
                                    <div class="col-3"><input type="file" name="gambar_pilihan[B]" class="form-control form-control-sm" accept="image/*" title="Gambar B"></div>
                                    <div class="col-2 text-center">
                                        <input class="form-check-input" type="radio" name="kunci_jawaban" value="B"> Kunci
                                    </div>
                                </div>

                                <div class="row g-2 mb-3 align-items-center">
                                    <div class="col-1 text-center fw-bold">C</div>
                                    <div class="col-6"><input type="text" name="pilihan[C]" class="form-control" placeholder="Teks Jawaban C"></div>
                                    <div class="col-3"><input type="file" name="gambar_pilihan[C]" class="form-control form-control-sm" accept="image/*" title="Gambar C"></div>
                                    <div class="col-2 text-center">
                                        <input class="form-check-input" type="radio" name="kunci_jawaban" value="C"> Kunci
                                    </div>
                                </div>

                                <div class="row g-2 mb-3 align-items-center">
                                    <div class="col-1 text-center fw-bold">D</div>
                                    <div class="col-6"><input type="text" name="pilihan[D]" class="form-control" placeholder="Teks Jawaban D"></div>
                                    <div class="col-3"><input type="file" name="gambar_pilihan[D]" class="form-control form-control-sm" accept="image/*" title="Gambar D"></div>
                                    <div class="col-2 text-center">
                                        <input class="form-check-input" type="radio" name="kunci_jawaban" value="D"> Kunci
                                    </div>
                                </div>
                                
                                <div class="row g-2 mb-3 align-items-center">
                                    <div class="col-1 text-center fw-bold">E</div>
                                    <div class="col-6"><input type="text" name="pilihan[E]" class="form-control" placeholder="Teks Jawaban E (Opsional)"></div>
                                    <div class="col-3"><input type="file" name="gambar_pilihan[E]" class="form-control form-control-sm" accept="image/*" title="Gambar E"></div>
                                    <div class="col-2 text-center">
                                        <input class="form-check-input" type="radio" name="kunci_jawaban" value="E"> Kunci
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Soal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipeSoalSelect = document.getElementById('tipeSoalSelect');
        const pilihanGandaArea = document.getElementById('pilihanGandaArea');
        const radioKunci = document.querySelectorAll('input[name="kunci_jawaban"]');
        const inputPilihan = document.querySelectorAll('input[name^="pilihan"]');

        tipeSoalSelect.addEventListener('change', function() {
            if (this.value === 'essay') {
                pilihanGandaArea.style.display = 'none';
                radioKunci.forEach(r => r.required = false);
            } else {
                pilihanGandaArea.style.display = 'block';
                radioKunci.forEach(r => r.required = true);
            }
        });
    });
</script>
@endpush
