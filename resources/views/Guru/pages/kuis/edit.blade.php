@extends('Guru.layout.master')

@section('page_title', 'Edit Kuis')

@section('breadcrumb')
    <li class="breadcrumb-item">Pokok Ujian</li>
    <li class="breadcrumb-item"><a href="{{ route('guru.kuis.index') }}">Manajemen Kuis</a></li>
    <li class="breadcrumb-item active">Edit Kuis</li>
@endsection

@section('content')
    <div class="main-content">
        <div class="row px-4 pt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Edit Kuis</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('guru.kuis.update', $kuis->id_kuis) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Judul Kuis</label>
                                    <input type="text" name="judul_kuis" class="form-control" required value="{{ $kuis->judul_kuis }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kelas</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ $kuis->kelas_id == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mata Pelajaran</label>
                                    <select name="mapel_id" class="form-select" required>
                                        <option value="">-- Pilih Mapel --</option>
                                        @foreach($mapel as $m)
                                            <option value="{{ $m->id_mapel }}" {{ $kuis->mapel_id == $m->id_mapel ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Deskripsi (Opsional)</label>
                                    <textarea name="deskripsi" class="form-control" rows="3">{{ $kuis->deskripsi }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipe Ujian</label>
                                    <select name="tipe" class="form-select" required>
                                        <option value="kuis_harian" {{ $kuis->tipe == 'kuis_harian' ? 'selected' : '' }}>Kuis Harian</option>
                                        <option value="uts" {{ $kuis->tipe == 'uts' ? 'selected' : '' }}>UTS</option>
                                        <option value="uas" {{ $kuis->tipe == 'uas' ? 'selected' : '' }}>UAS</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Durasi (Menit)</label>
                                    <input type="number" name="durasi_menit" class="form-control" required min="1" value="{{ $kuis->durasi_menit }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="draft" {{ $kuis->status == 'draft' ? 'selected' : '' }}>Draft (Belum Tampil)</option>
                                        <option value="published" {{ $kuis->status == 'published' ? 'selected' : '' }}>Published (Tampil)</option>
                                        <option value="closed" {{ $kuis->status == 'closed' ? 'selected' : '' }}>Closed (Tutup)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Waktu Mulai</label>
                                    <input type="datetime-local" name="tanggal_mulai" class="form-control" required value="{{ $kuis->tanggal_mulai->format('Y-m-d\TH:i') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Waktu Selesai</label>
                                    <input type="datetime-local" name="tanggal_selesai" class="form-control" required value="{{ $kuis->tanggal_selesai->format('Y-m-d\TH:i') }}">
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="acak_soal" id="acak_soal" value="1" {{ $kuis->acak_soal ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acak_soal">Acak Urutan Soal untuk Siswa</label>
                                    </div>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="tampilkan_nilai" id="tampilkan_nilai" value="1" {{ $kuis->tampilkan_nilai ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tampilkan_nilai">Tampilkan Nilai ke Siswa Setelah Selesai</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <a href="{{ route('guru.kuis.index') }}" class="btn btn-light me-2">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class="feather-save me-1"></i> Update Kuis</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
