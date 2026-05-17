@extends('Guru.layout.master')

@section('page_title', 'Review Jawaban Siswa')

@section('breadcrumb')
    <li class="breadcrumb-item">Pokok Ujian</li>
    <li class="breadcrumb-item"><a href="{{ route('guru.kuis.index') }}">Manajemen Kuis</a></li>
    <li class="breadcrumb-item"><a href="{{ route('guru.kuis.hasil', $kuis->id_kuis) }}">Hasil Kuis</a></li>
    <li class="breadcrumb-item active">Review Jawaban</li>
@endsection

@section('content')
    <div class="main-content">
        <div class="row px-4 pt-3">
            <div class="col-xl-4 col-lg-5">
                <div class="card position-sticky" style="top: 20px;">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Pengerjaan</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="avatar-text avatar-lg rounded-circle bg-soft-primary text-primary mx-auto mb-3">
                                <i class="feather-user" style="font-size: 1.5rem;"></i>
                            </div>
                            <h5 class="mb-1">{{ $hasil->siswa->name }}</h5>
                            <p class="text-muted">{{ $hasil->siswa->kelas->nama_kelas ?? '' }}</p>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Kuis</span>
                            <span class="fw-medium text-dark text-end">{{ $kuis->judul_kuis }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Waktu Submit</span>
                            <span class="fw-medium text-dark">{{ $hasil->waktu_selesai ? $hasil->waktu_selesai->format('d M Y, H:i') : '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Status</span>
                            @if($hasil->status == 'dinilai')
                                <span class="badge bg-success">Sudah Dinilai</span>
                            @else
                                <span class="badge bg-warning">Menunggu Koreksi</span>
                            @endif
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <p class="text-muted mb-1">Nilai Akhir</p>
                            <h2 class="mb-0 text-primary" id="nilaiAkhirDisplay">{{ $hasil->nilai ?? '0' }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Review Jawaban</h5>
                    </div>
                    <div class="card-body">
                        @foreach($hasil->jawabanSiswa as $jawaban)
                            <div class="border rounded p-3 mb-4 {{ $jawaban->soal->tipe_soal == 'pilihan_ganda' ? ($jawaban->is_correct ? 'border-success' : 'border-danger') : 'border-info' }}">
                                <div class="d-flex justify-content-between mb-2">
                                    <h6 class="fw-bold mb-0">Soal #{{ $jawaban->soal->nomor_urut }} 
                                        @if($jawaban->soal->tipe_soal == 'essay')
                                            <span class="badge bg-info ms-2">Essay</span>
                                        @endif
                                    </h6>
                                    <div>
                                        @if($jawaban->soal->tipe_soal == 'pilihan_ganda')
                                            @if($jawaban->is_correct)
                                                <span class="badge bg-success"><i class="feather-check"></i> Benar</span>
                                            @else
                                                <span class="badge bg-danger"><i class="feather-x"></i> Salah</span>
                                            @endif
                                        @else
                                            @if($jawaban->is_correct === true)
                                                <span class="badge bg-success"><i class="feather-check"></i> Benar</span>
                                            @elseif($jawaban->is_correct === false)
                                                <span class="badge bg-danger"><i class="feather-x"></i> Salah</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                
                                @if($jawaban->soal->gambar)
                                    <img src="{{ asset('storage/' . $jawaban->soal->gambar) }}" class="img-fluid rounded mb-2" style="max-height:150px;">
                                @endif
                                <p class="text-dark mb-3">{{ $jawaban->soal->pertanyaan }}</p>
                                
                                <!-- Jawaban Siswa -->
                                <div class="bg-light p-3 rounded mb-3">
                                    <h6 class="text-muted mb-2" style="font-size: 0.8rem;">JAWABAN SISWA:</h6>
                                    @if($jawaban->soal->tipe_soal == 'pilihan_ganda')
                                        <p class="mb-0 fw-medium {{ $jawaban->is_correct ? 'text-success' : 'text-danger' }}">
                                            @if($jawaban->pilihan)
                                                {{ $jawaban->pilihan->pilihan }}. {{ $jawaban->pilihan->isi_pilihan }}
                                            @else
                                                <span class="text-muted fst-italic">Tidak Dijawab</span>
                                            @endif
                                        </p>
                                    @else
                                        <p class="mb-0 fw-medium text-dark">{{ $jawaban->jawaban_essay ?: 'Tidak dijawab' }}</p>
                                    @endif
                                </div>

                                <!-- Form Penilaian Essay -->
                                @if($jawaban->soal->tipe_soal == 'essay')
                                    <hr>
                                    <form class="form-nilai-essay" onsubmit="submitNilaiEssay(event, this)">
                                        @csrf
                                        <input type="hidden" name="jawaban_id" value="{{ $jawaban->id_jawaban }}">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Penilaian</label>
                                                <select name="is_correct" class="form-select" required>
                                                    <option value="">-- Pilih --</option>
                                                    <option value="1" {{ $jawaban->is_correct === true ? 'selected' : '' }}>Benar</option>
                                                    <option value="0" {{ $jawaban->is_correct === false ? 'selected' : '' }}>Salah</option>
                                                </select>
                                            </div>
                                            <div class="col-md-9">
                                                <label class="form-label">Catatan / Feedback (Opsional)</label>
                                                <input type="text" name="catatan" class="form-control" value="{{ $jawaban->catatan_guru }}" placeholder="Misal: Jawaban kurang tepat di bagian akhir">
                                            </div>
                                            <div class="col-12 text-end mt-2">
                                                <button type="submit" class="btn btn-sm btn-primary btn-simpan-nilai">
                                                    <i class="feather-save me-1"></i> Simpan Penilaian
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function submitNilaiEssay(event, formElement) {
        event.preventDefault();
        
        const btn = formElement.querySelector('.btn-simpan-nilai');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        btn.disabled = true;

        const formData = new FormData(formElement);
        
        fetch("{{ route('guru.kuis.hasil.nilai_essay') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Update the overall score
                document.getElementById('nilaiAkhirDisplay').textContent = data.nilai_akhir;
                
                // Show success UI briefly
                btn.innerHTML = '<i class="feather-check me-1"></i> Tersimpan!';
                btn.classList.replace('btn-primary', 'btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.replace('btn-success', 'btn-primary');
                    btn.disabled = false;
                }, 2000);
            } else {
                alert(data.message || 'Terjadi kesalahan');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghubungi server.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endpush
