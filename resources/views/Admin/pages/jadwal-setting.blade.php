@extends('Admin.layout.master')

@section('page_title', 'Pengaturan Jadwal')

@section('breadcrumb')
    <li class="breadcrumb-item">Akademik</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.jadwal.index') }}">Jadwal</a></li>
    <li class="breadcrumb-item active">Pengaturan</li>
@endsection

@section('content')
<div class="main-content">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
            <i class="feather-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.jadwal.setting.save') }}" method="POST" id="formSetting">
        @csrf
        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="slot_khusus_json" id="slotKhususJson" value="{{ json_encode($setting->slot_khusus ?? []) }}">

        {{-- Header --}}
        <div class="row px-4 pt-3">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size:1.05rem;">
                            <i class="feather-settings me-2 text-primary"></i> Pengaturan Jadwal
                            <span class="badge bg-soft-primary text-primary ms-2">{{ ucfirst($semester) }} {{ $tahunAjaran }}</span>
                        </h5>
                        <a href="{{ route('admin.jadwal.index', ['tahun_ajaran' => $tahunAjaran, 'semester' => $semester]) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="feather-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aturan Jam --}}
        <div class="row px-4 pt-1">
            <div class="col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="feather-clock me-2 text-info"></i> Aturan Jam Pemerintah</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Total JP/Minggu</label>
                                <input type="number" name="total_jam_per_minggu" class="form-control"
                                       value="{{ $setting->total_jam_per_minggu ?? 48 }}" min="1" max="60" id="totalJpm">
                                <small class="text-muted">Standar pemerintah: 48 JP</small>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Tambahan Mulok</label>
                                <input type="number" name="jam_mulok_tambahan" class="form-control"
                                       value="{{ $setting->jam_mulok_tambahan ?? 2 }}" min="0" max="10" id="mulokTambahan">
                                <small class="text-muted">Jam tambahan muatan lokal</small>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info py-2 mb-0" style="font-size:0.85rem;">
                                    <i class="feather-info me-1"></i>
                                    Total Efektif: <strong id="totalEfektif">{{ ($setting->total_jam_per_minggu ?? 48) + ($setting->jam_mulok_tambahan ?? 2) }}</strong> JP/Minggu
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="feather-watch me-2 text-warning"></i> Waktu Pelajaran</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Waktu Mulai</label>
                                <input type="time" name="waktu_mulai" class="form-control" id="waktuMulai"
                                       value="{{ isset($setting->waktu_mulai) ? \Carbon\Carbon::parse($setting->waktu_mulai)->format('H:i') : '07:00' }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Durasi per JP (menit)</label>
                                <input type="number" name="durasi_jam_menit" class="form-control" id="durasiJp"
                                       value="{{ $setting->durasi_jam_menit ?? 45 }}" min="30" max="60">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Distribusi Jam per Hari --}}
        <div class="row px-4 pt-1">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="feather-calendar me-2 text-success"></i> Distribusi Jam per Hari</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            @php
                                $hari = ['senin','selasa','rabu','kamis','jumat'];
                                $hariLabel = ['Senin','Selasa','Rabu','Kamis','Jumat'];
                                $icons = ['☀️','🔥','💧','🌿','🕌'];
                            @endphp
                            @foreach($hari as $idx => $h)
                            <div class="col">
                                <label class="form-label fw-semibold text-center d-block">
                                    {{ $icons[$idx] }} {{ $hariLabel[$idx] }}
                                </label>
                                <input type="number" name="jam_{{ $h }}" class="form-control text-center jam-hari-input"
                                       value="{{ $setting->{'jam_'.$h} ?? 10 }}" min="0" max="14"
                                       data-hari="{{ $h }}" style="font-size:1.1rem; font-weight:600;">
                                <div class="text-center mt-2 text-muted" style="font-size: 0.75rem;">
                                    Pulang: <br><strong class="jam-pulang text-danger fs-6" id="pulang_{{ $h }}">-</strong>
                                </div>
                            </div>
                            @endforeach
                            <div class="col-auto">
                                <div class="text-center">
                                    <span class="d-block fw-semibold text-muted mb-1" style="font-size:0.8rem;">Total</span>
                                    <span id="totalDistribusi" class="badge bg-primary fs-6 px-3 py-2"
                                          style="font-size:1.1rem!important;">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" id="progressDistribusi"
                                     role="progressbar" style="width: 0%">
                                    <span id="progressText" style="font-size:0.8rem;"></span>
                                </div>
                            </div>
                        </div>
                        <div id="distribusiWarning" class="alert alert-warning py-2 mt-2 d-none" style="font-size:0.85rem;">
                            <i class="feather-alert-triangle me-1"></i>
                            <span id="distribusiWarningText"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Slot Khusus --}}
        <div class="row px-4 pt-1">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0"><i class="feather-coffee me-2 text-danger"></i> Slot Khusus (Istirahat, Upacara, dll)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahSlot">
                            <i class="feather-plus me-1"></i> Tambah Slot
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" style="font-size:0.85rem;" id="tabelSlotKhusus">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:120px;">Posisi</th>
                                        <th style="width:100px;">Nomor Jam</th>
                                        <th style="width:100px;">Durasi (menit)</th>
                                        <th>Label</th>
                                        <th style="width:130px;">Hari</th>
                                        <th style="width:60px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="slotKhususBody">
                                    {{-- Filled by JS --}}
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="feather-info me-1"></i>
                            "Sebelum Jam" = slot ditampilkan sebelum jam pelajaran tersebut (cocok untuk Upacara).<br>
                            "Setelah Jam" = slot ditampilkan setelah jam pelajaran tersebut (cocok untuk Istirahat).
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kode Guru & Max Jam --}}
        <div class="row px-4 pt-1">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="feather-users me-2 text-purple" style="color:#7c3aed;"></i>
                            Kode Guru & Batas Jam Mengajar
                            <span class="badge bg-soft-warning text-warning ms-2" style="font-size:0.75rem;">
                                Berlaku untuk {{ ucfirst($semester) }} {{ $tahunAjaran }}
                            </span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" style="font-size:0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>NIP</th>
                                        <th>Nama Guru</th>
                                        <th style="width:100px;" class="text-center">Kode</th>
                                        <th style="width:120px;" class="text-center">Max JP/Minggu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($guruList as $idx => $guru)
                                    <tr>
                                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                        <td>{{ $guru->nip }}</td>
                                        <td class="fw-semibold">{{ $guru->nama }}</td>
                                        <td>
                                            <input type="text" name="kode_guru[{{ $guru->id_guru }}]"
                                                   class="form-control form-control-sm text-center text-uppercase"
                                                   value="{{ $guruKodes[$guru->id_guru] ?? '' }}"
                                                   maxlength="5" placeholder="-"
                                                   style="font-weight:700; letter-spacing:1px;">
                                        </td>
                                        <td>
                                            <input type="number" name="max_jam_guru[{{ $guru->id_guru }}]"
                                                   class="form-control form-control-sm text-center"
                                                   value="{{ $guru->max_jam_per_minggu ?? 24 }}"
                                                   min="1" max="48">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Simpan --}}
        <div class="row px-4 pt-1 pb-4">
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="feather-save me-2"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('modals')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Slot Khusus Init ===
    var slotData = JSON.parse(document.getElementById('slotKhususJson').value || '[]');

    // === Distribusi Jam ===
    function updateDistribusi() {
        var inputs = document.querySelectorAll('.jam-hari-input');
        var total = 0;
        inputs.forEach(function(inp) { total += parseInt(inp.value) || 0; });

        var totalEfektif = (parseInt(document.getElementById('totalJpm').value) || 48) +
                           (parseInt(document.getElementById('mulokTambahan').value) || 0);

        document.getElementById('totalEfektif').textContent = totalEfektif;
        document.getElementById('totalDistribusi').textContent = total;

        var pct = Math.min(100, Math.round(total / totalEfektif * 100));
        var bar = document.getElementById('progressDistribusi');
        bar.style.width = pct + '%';
        document.getElementById('progressText').textContent = total + ' / ' + totalEfektif + ' JP';

        var warn = document.getElementById('distribusiWarning');
        if (total < totalEfektif) {
            warn.classList.remove('d-none');
            bar.className = 'progress-bar bg-warning';
            document.getElementById('distribusiWarningText').textContent =
                'Kurang ' + (totalEfektif - total) + ' JP dari total efektif.';
        } else if (total > totalEfektif) {
            warn.classList.remove('d-none');
            bar.className = 'progress-bar bg-info';
            document.getElementById('distribusiWarningText').textContent =
                'Melebihi ' + (total - totalEfektif) + ' JP dari total efektif (boleh jika sekolah membutuhkan).';
        } else {
            warn.classList.add('d-none');
            bar.className = 'progress-bar bg-success';
        }
        
        hitungJamPulang();
    }

    function hitungJamPulang() {
        var waktuMulai = document.getElementById('waktuMulai').value;
        if(!waktuMulai) waktuMulai = '07:00';
        var durasiJp = parseInt(document.getElementById('durasiJp').value) || 45;
        
        var inputs = document.querySelectorAll('.jam-hari-input');
        inputs.forEach(function(inp) {
            var hari = inp.dataset.hari;
            var jamKe = parseInt(inp.value) || 0;
            
            if (jamKe === 0) {
                var el = document.getElementById('pulang_' + hari);
                if(el) { el.textContent = '-'; el.className = 'jam-pulang text-muted fs-6'; }
                return;
            }
            
            var parts = waktuMulai.split(':');
            var totalMenit = parseInt(parts[0]) * 60 + parseInt(parts[1]);
            
            // Add jam pelajaran
            totalMenit += jamKe * durasiJp;
            
            // Add slot khusus
            slotData.forEach(function(slot) {
                var berlaku = !slot.hari || slot.hari === hari;
                if(berlaku) {
                    if(slot.sebelum_jam && slot.sebelum_jam <= jamKe) totalMenit += parseInt(slot.durasi);
                    else if(slot.setelah_jam && slot.setelah_jam <= jamKe) totalMenit += parseInt(slot.durasi);
                }
            });
            
            var jam = Math.floor(totalMenit / 60);
            var mnt = totalMenit % 60;
            var timeStr = (jam < 10 ? '0' : '') + jam + ':' + (mnt < 10 ? '0' : '') + mnt;
            
            var el = document.getElementById('pulang_' + hari);
            if(el) {
                el.textContent = timeStr;
                if(jam >= 16) el.className = 'jam-pulang text-danger fw-bold fs-6';
                else el.className = 'jam-pulang text-success fw-bold fs-6';
            }
        });
    }

    document.querySelectorAll('.jam-hari-input, #totalJpm, #mulokTambahan, #waktuMulai, #durasiJp').forEach(function(el) {
        el.addEventListener('input', updateDistribusi);
    });
    updateDistribusi();

    // === Slot Khusus ===
    function renderSlotTable() {
        var tbody = document.getElementById('slotKhususBody');
        tbody.innerHTML = '';
        slotData.forEach(function(slot, idx) {
            var posisi = slot.sebelum_jam ? 'sebelum' : 'setelah';
            var nomorJam = slot.sebelum_jam || slot.setelah_jam || 1;
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td><select class="form-select form-select-sm slot-posisi" data-idx="'+idx+'">' +
                    '<option value="sebelum"' + (posisi === 'sebelum' ? ' selected' : '') + '>Sebelum Jam</option>' +
                    '<option value="setelah"' + (posisi === 'setelah' ? ' selected' : '') + '>Setelah Jam</option>' +
                '</select></td>' +
                '<td><input type="number" class="form-control form-control-sm text-center slot-nomor" data-idx="'+idx+'" value="'+nomorJam+'" min="1" max="14"></td>' +
                '<td><input type="number" class="form-control form-control-sm text-center slot-durasi" data-idx="'+idx+'" value="'+(slot.durasi || 15)+'" min="5" max="60"></td>' +
                '<td><input type="text" class="form-control form-control-sm slot-label" data-idx="'+idx+'" value="'+(slot.label || '')+'" placeholder="Istirahat"></td>' +
                '<td><select class="form-select form-select-sm slot-hari" data-idx="'+idx+'">' +
                    '<option value=""' + (!slot.hari ? ' selected' : '') + '>Semua Hari</option>' +
                    '<option value="senin"' + (slot.hari === 'senin' ? ' selected' : '') + '>Senin</option>' +
                    '<option value="selasa"' + (slot.hari === 'selasa' ? ' selected' : '') + '>Selasa</option>' +
                    '<option value="rabu"' + (slot.hari === 'rabu' ? ' selected' : '') + '>Rabu</option>' +
                    '<option value="kamis"' + (slot.hari === 'kamis' ? ' selected' : '') + '>Kamis</option>' +
                    '<option value="jumat"' + (slot.hari === 'jumat' ? ' selected' : '') + '>Jumat</option>' +
                '</select></td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger slot-hapus" data-idx="'+idx+'"><i class="feather-trash-2"></i></button></td>';
            tbody.appendChild(tr);
        });

        // Bind events
        document.querySelectorAll('.slot-posisi, .slot-nomor, .slot-durasi, .slot-label, .slot-hari').forEach(function(el) {
            el.addEventListener('change', function() { syncSlotData(); });
            el.addEventListener('input', function() { syncSlotData(); });
        });
        document.querySelectorAll('.slot-hapus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                slotData.splice(parseInt(this.dataset.idx), 1);
                renderSlotTable();
                syncSlotData();
            });
        });
    }

    function syncSlotData() {
        var rows = document.querySelectorAll('#slotKhususBody tr');
        slotData = [];
        rows.forEach(function(row, idx) {
            var posisi = row.querySelector('.slot-posisi').value;
            var nomor = parseInt(row.querySelector('.slot-nomor').value) || 1;
            var obj = {
                durasi: parseInt(row.querySelector('.slot-durasi').value) || 15,
                label: row.querySelector('.slot-label').value || 'Istirahat',
                hari: row.querySelector('.slot-hari').value || null,
            };
            if (posisi === 'sebelum') obj.sebelum_jam = nomor;
            else obj.setelah_jam = nomor;
            slotData.push(obj);
        });
        document.getElementById('slotKhususJson').value = JSON.stringify(slotData);
        hitungJamPulang();
    }

    document.getElementById('btnTambahSlot').addEventListener('click', function() {
        slotData.push({ setelah_jam: 4, durasi: 15, label: 'Istirahat', hari: null });
        renderSlotTable();
        syncSlotData();
    });

    renderSlotTable();
    syncSlotData();
});
</script>
@endpush
