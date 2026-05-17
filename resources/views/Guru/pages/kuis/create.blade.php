@extends('Guru.layout.master')

@section('page_title', 'Buat Kuis Baru')

@section('breadcrumb')
    <li class="breadcrumb-item">Pokok Ujian</li>
    <li class="breadcrumb-item"><a href="{{ route('guru.kuis.index') }}">Manajemen Kuis</a></li>
    <li class="breadcrumb-item active">Buat Kuis</li>
@endsection

@section('content')
    <div class="main-content">
        <div class="row px-4 pt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Kuis Baru</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('guru.kuis.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Judul Kuis</label>
                                    <input type="text" name="judul_kuis" class="form-control" required placeholder="Contoh: Kuis Evaluasi 1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kelas</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mata Pelajaran</label>
                                    <select name="mapel_id" class="form-select" required>
                                        <option value="">-- Pilih Mapel --</option>
                                        @foreach($mapel as $m)
                                            <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Deskripsi (Opsional)</label>
                                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Informasi tambahan untuk siswa"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipe Ujian</label>
                                    <select name="tipe" class="form-select" required>
                                        <option value="kuis_harian">Kuis Harian</option>
                                        <option value="uts">UTS</option>
                                        <option value="uas">UAS</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Durasi (Menit)</label>
                                    <input type="number" name="durasi_menit" class="form-control" required min="1" value="60">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="draft">Draft (Belum Tampil)</option>
                                        <option value="published">Published (Tampil)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Waktu Mulai</label>
                                    <input type="datetime-local" name="tanggal_mulai" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Waktu Selesai</label>
                                    <input type="datetime-local" name="tanggal_selesai" class="form-control" required>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="acak_soal" id="acak_soal" value="1">
                                        <label class="form-check-label" for="acak_soal">Acak Urutan Soal untuk Siswa</label>
                                    </div>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="tampilkan_nilai" id="tampilkan_nilai" value="1" checked>
                                        <label class="form-check-label" for="tampilkan_nilai">Tampilkan Nilai ke Siswa Setelah Selesai</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5>Daftar Soal</h5>
                                <button type="button" class="btn btn-sm btn-success" id="btnTambahSoal">
                                    <i class="feather-plus me-1"></i> Tambah Soal
                                </button>
                            </div>

                            <div id="soalContainer">
                                <!-- Soal 1 -->
                                <div class="soal-item border p-3 rounded mb-3 position-relative" data-index="0">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 btnHapusSoal" title="Hapus Soal"><i class="feather-trash-2"></i></button>
                                    <h6 class="soal-title mb-3">Soal #1</h6>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Tipe Soal</label>
                                            <select name="soal[0][tipe_soal]" class="form-select tipe-soal-select" data-index="0" required>
                                                <option value="pilihan_ganda">Pilihan Ganda</option>
                                                <option value="essay">Essay</option>
                                            </select>
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label">Pertanyaan</label>
                                            <textarea name="soal[0][pertanyaan]" class="form-control" rows="2" required placeholder="Tuliskan pertanyaan..."></textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Gambar Soal (Opsional)</label>
                                            <input type="file" name="soal[0][gambar]" class="form-control" accept="image/*">
                                            <small class="text-muted">Maksimal 2MB.</small>
                                        </div>
                                        
                                        <div class="col-12 mt-3 pilihan-ganda-area" id="pilihanGandaArea-0">
                                            <label class="form-label mb-2">Pilihan Jawaban</label>
                                            <div class="row g-2 mb-3 align-items-center">
                                                <div class="col-1 text-center fw-bold">A</div>
                                                <div class="col-6"><input type="text" name="soal[0][pilihan][A]" class="form-control" placeholder="Teks Jawaban A"></div>
                                                <div class="col-3"><input type="file" name="soal[0][gambar_pilihan][A]" class="form-control form-control-sm" accept="image/*" title="Gambar A"></div>
                                                <div class="col-2 text-center">
                                                    <input class="form-check-input" type="radio" name="soal[0][kunci_jawaban]" value="A" required> Kunci
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3 align-items-center">
                                                <div class="col-1 text-center fw-bold">B</div>
                                                <div class="col-6"><input type="text" name="soal[0][pilihan][B]" class="form-control" placeholder="Teks Jawaban B"></div>
                                                <div class="col-3"><input type="file" name="soal[0][gambar_pilihan][B]" class="form-control form-control-sm" accept="image/*" title="Gambar B"></div>
                                                <div class="col-2 text-center">
                                                    <input class="form-check-input" type="radio" name="soal[0][kunci_jawaban]" value="B"> Kunci
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3 align-items-center">
                                                <div class="col-1 text-center fw-bold">C</div>
                                                <div class="col-6"><input type="text" name="soal[0][pilihan][C]" class="form-control" placeholder="Teks Jawaban C"></div>
                                                <div class="col-3"><input type="file" name="soal[0][gambar_pilihan][C]" class="form-control form-control-sm" accept="image/*" title="Gambar C"></div>
                                                <div class="col-2 text-center">
                                                    <input class="form-check-input" type="radio" name="soal[0][kunci_jawaban]" value="C"> Kunci
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3 align-items-center">
                                                <div class="col-1 text-center fw-bold">D</div>
                                                <div class="col-6"><input type="text" name="soal[0][pilihan][D]" class="form-control" placeholder="Teks Jawaban D"></div>
                                                <div class="col-3"><input type="file" name="soal[0][gambar_pilihan][D]" class="form-control form-control-sm" accept="image/*" title="Gambar D"></div>
                                                <div class="col-2 text-center">
                                                    <input class="form-check-input" type="radio" name="soal[0][kunci_jawaban]" value="D"> Kunci
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3 align-items-center">
                                                <div class="col-1 text-center fw-bold">E</div>
                                                <div class="col-6"><input type="text" name="soal[0][pilihan][E]" class="form-control" placeholder="Teks E (Opsional)"></div>
                                                <div class="col-3"><input type="file" name="soal[0][gambar_pilihan][E]" class="form-control form-control-sm" accept="image/*" title="Gambar E"></div>
                                                <div class="col-2 text-center">
                                                    <input class="form-check-input" type="radio" name="soal[0][kunci_jawaban]" value="E"> Kunci
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-end">
                                <a href="{{ route('guru.kuis.index') }}" class="btn btn-light me-2">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class="feather-save me-1"></i> Simpan Kuis & Soal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let soalIndex = 1;
        const btnTambahSoal = document.getElementById('btnTambahSoal');
        const soalContainer = document.getElementById('soalContainer');

        btnTambahSoal.addEventListener('click', function() {
            const html = `
                <div class="soal-item border p-3 rounded mb-3 position-relative" data-index="${soalIndex}">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 btnHapusSoal" title="Hapus Soal"><i class="feather-trash-2"></i></button>
                    <h6 class="soal-title mb-3">Soal #${soalIndex + 1}</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipe Soal</label>
                            <select name="soal[${soalIndex}][tipe_soal]" class="form-select tipe-soal-select" data-index="${soalIndex}" required>
                                <option value="pilihan_ganda">Pilihan Ganda</option>
                                <option value="essay">Essay</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Pertanyaan</label>
                            <textarea name="soal[${soalIndex}][pertanyaan]" class="form-control" rows="2" required placeholder="Tuliskan pertanyaan..."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Gambar Soal (Opsional)</label>
                            <input type="file" name="soal[${soalIndex}][gambar]" class="form-control" accept="image/*">
                            <small class="text-muted">Maksimal 2MB.</small>
                        </div>
                        
                        <div class="col-12 mt-3 pilihan-ganda-area" id="pilihanGandaArea-${soalIndex}">
                            <label class="form-label mb-2">Pilihan Jawaban</label>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-1 text-center fw-bold">A</div>
                                <div class="col-6"><input type="text" name="soal[${soalIndex}][pilihan][A]" class="form-control" placeholder="Teks Jawaban A"></div>
                                <div class="col-3"><input type="file" name="soal[${soalIndex}][gambar_pilihan][A]" class="form-control form-control-sm" accept="image/*" title="Gambar A"></div>
                                <div class="col-2 text-center">
                                    <input class="form-check-input" type="radio" name="soal[${soalIndex}][kunci_jawaban]" value="A" required> Kunci
                                </div>
                            </div>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-1 text-center fw-bold">B</div>
                                <div class="col-6"><input type="text" name="soal[${soalIndex}][pilihan][B]" class="form-control" placeholder="Teks Jawaban B"></div>
                                <div class="col-3"><input type="file" name="soal[${soalIndex}][gambar_pilihan][B]" class="form-control form-control-sm" accept="image/*" title="Gambar B"></div>
                                <div class="col-2 text-center">
                                    <input class="form-check-input" type="radio" name="soal[${soalIndex}][kunci_jawaban]" value="B"> Kunci
                                </div>
                            </div>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-1 text-center fw-bold">C</div>
                                <div class="col-6"><input type="text" name="soal[${soalIndex}][pilihan][C]" class="form-control" placeholder="Teks Jawaban C"></div>
                                <div class="col-3"><input type="file" name="soal[${soalIndex}][gambar_pilihan][C]" class="form-control form-control-sm" accept="image/*" title="Gambar C"></div>
                                <div class="col-2 text-center">
                                    <input class="form-check-input" type="radio" name="soal[${soalIndex}][kunci_jawaban]" value="C"> Kunci
                                </div>
                            </div>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-1 text-center fw-bold">D</div>
                                <div class="col-6"><input type="text" name="soal[${soalIndex}][pilihan][D]" class="form-control" placeholder="Teks Jawaban D"></div>
                                <div class="col-3"><input type="file" name="soal[${soalIndex}][gambar_pilihan][D]" class="form-control form-control-sm" accept="image/*" title="Gambar D"></div>
                                <div class="col-2 text-center">
                                    <input class="form-check-input" type="radio" name="soal[${soalIndex}][kunci_jawaban]" value="D"> Kunci
                                </div>
                            </div>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-1 text-center fw-bold">E</div>
                                <div class="col-6"><input type="text" name="soal[${soalIndex}][pilihan][E]" class="form-control" placeholder="Teks E (Opsional)"></div>
                                <div class="col-3"><input type="file" name="soal[${soalIndex}][gambar_pilihan][E]" class="form-control form-control-sm" accept="image/*" title="Gambar E"></div>
                                <div class="col-2 text-center">
                                    <input class="form-check-input" type="radio" name="soal[${soalIndex}][kunci_jawaban]" value="E"> Kunci
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            soalContainer.insertAdjacentHTML('beforeend', html);
            soalIndex++;
            updateSoalNumbers();
        });

        soalContainer.addEventListener('click', function(e) {
            if (e.target.closest('.btnHapusSoal')) {
                const item = e.target.closest('.soal-item');
                if (document.querySelectorAll('.soal-item').length > 1) {
                    item.remove();
                    updateSoalNumbers();
                } else {
                    alert('Minimal harus ada 1 soal.');
                }
            }
        });

        function updateSoalNumbers() {
            const items = document.querySelectorAll('.soal-item');
            items.forEach((item, idx) => {
                item.querySelector('.soal-title').textContent = `Soal #${idx + 1}`;
            });
        }

        soalContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('tipe-soal-select')) {
                const index = e.target.getAttribute('data-index');
                const pilihanArea = document.getElementById(`pilihanGandaArea-${index}`);
                const radioKunci = pilihanArea.querySelectorAll('input[type="radio"]');
                const textPilihan = pilihanArea.querySelectorAll('input[type="text"]');

                if (e.target.value === 'essay') {
                    pilihanArea.style.display = 'none';
                    radioKunci.forEach(r => r.required = false);
                    textPilihan.forEach(r => r.required = false);
                } else {
                    pilihanArea.style.display = 'block';
                    radioKunci.forEach(r => r.required = true);
                    // Require text OR image for multiple choice, let's remove strict text required
                    // because they could only upload image for an option. 
                    // To keep it simple, we don't strictly enforce text input if they use image, but HTML5 validation can't check 'this OR that' easily on input level.
                    // So we make text NOT strictly required if we allow images as an alternative.
                    for (let i = 0; i < textPilihan.length; i++) {
                        if(textPilihan[i]) textPilihan[i].required = false; 
                    }
                }
            }
        });
    });
</script>
@endpush
