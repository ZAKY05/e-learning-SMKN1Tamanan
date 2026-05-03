@extends('Admin.layout.master')

@section('page_title', 'Jadwal Mengajar')

@section('breadcrumb')
    <li class="breadcrumb-item">Akademik</li>
    <li class="breadcrumb-item active">Jadwal Mengajar</li>
@endsection

@section('content')
    <div class="main-content">

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
                <i class="feather-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('warnings') && count(session('warnings')) > 0)
            <div class="alert alert-warning alert-dismissible fade show mx-4 mt-3" role="alert">
                <i class="feather-alert-triangle me-2"></i> <strong>Peringatan:</strong>
                <ul class="mb-0 mt-1">
                    @foreach (session('warnings') as $w)
                        <li style="font-size:0.85rem;">{{ $w }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
                <i class="feather-alert-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ===== CARD: Action & Filter ===== --}}
        <div class="row px-4 pt-3">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size:1.05rem;">
                            <i class="feather-calendar me-2 text-primary" style="font-size:1.15rem;"></i>
                            Jadwal Mengajar <span class="badge bg-soft-primary text-primary ms-2">{{ ucfirst($semester) }} {{ $tahunAjaran }}</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.jadwal.guru-kode', ['tahun_ajaran' => $tahunAjaran, 'semester' => $semester]) }}"
                               class="btn btn-sm btn-outline-success">
                                <i class="feather-user-check me-1"></i> Kode Guru
                            </a>
                            <a href="{{ route('admin.jadwal.mapping') }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="feather-link me-1"></i> Mapping Singkatan
                            </a>
                            <a href="{{ route('admin.jadwal.setting', ['tahun_ajaran' => $tahunAjaran, 'semester' => $semester]) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="feather-settings me-1"></i> Pengaturan Jadwal
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.jadwal.index') }}" method="GET" class="row g-3 align-items-end" id="formFilter">
                            {{-- Tahun Ajaran --}}
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tahun Ajaran</label>
                                <input type="text" name="tahun_ajaran" class="form-control" placeholder="2025/2026"
                                       value="{{ $tahunAjaran }}" style="font-size:0.9rem;">
                            </div>
                            {{-- Semester --}}
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Semester</label>
                                <select name="semester" class="form-select" style="font-size:0.9rem;">
                                    <option value="ganjil" {{ $semester === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                    <option value="genap" {{ $semester === 'genap' ? 'selected' : '' }}>Genap</option>
                                </select>
                            </div>
                            {{-- Tombol --}}
                            <div class="col-md-3 d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-primary btn-sm" style="font-size:0.9rem; padding:0.4rem 0.95rem;">
                                    <i class="feather-filter me-1"></i> Tampilkan
                                </button>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerate" style="font-size:0.9rem; padding:0.4rem 0.95rem;">
                                    <i class="feather-zap me-1"></i> Generate
                                </button>
                                <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#modalImport" style="font-size:0.9rem; padding:0.4rem 0.95rem;">
                                    <i class="feather-upload me-1"></i> Import Excel
                                </button>
                                @if ($hasJadwal)
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalReset" style="font-size:0.9rem; padding:0.4rem 0.95rem;">
                                    <i class="feather-refresh-cw me-1"></i> Reset
                                </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TABS HARI ===== --}}
        <div class="row px-4 pt-1">
            <div class="col-12">
                <ul class="nav nav-pills nav-justified mb-3 gap-2" id="pills-tab" role="tablist">
                    @foreach($hariList as $h)
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('admin.jadwal.index', ['tahun_ajaran' => $tahunAjaran, 'semester' => $semester, 'hari' => $h]) }}"
                           class="nav-link {{ $hariAktif === $h ? 'active shadow-sm' : 'bg-white text-muted border' }}"
                           style="border-radius: 8px; font-weight: 600; {{ $hariAktif === $h ? 'background: linear-gradient(to right, #4e73df, #224abe); color: white!important;' : '' }}">
                            {{ ucfirst($h) }}
                            @if($hariAktif === $h)
                                <span class="badge bg-white text-primary ms-2">{{ $jamHariIni }} JP</span>
                            @endif
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- ===== GRID JADWAL MIRIP PDF ===== --}}
        <div class="row px-4">
            <div class="col-12">
                <div class="card stretch stretch-full border-top-0" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                    <div class="card-body p-0">
                        @if ($jamHariIni == 0)
                            <div class="text-center py-5 text-muted">
                                <i class="feather-calendar d-block mb-2" style="font-size:2.5rem;"></i>
                                <p style="font-size:0.95rem;">Belum ada pengaturan jam untuk hari <strong>{{ ucfirst($hariAktif) }}</strong>.</p>
                                <a href="{{ route('admin.jadwal.setting', ['tahun_ajaran' => $tahunAjaran, 'semester' => $semester]) }}" class="btn btn-sm btn-outline-primary mt-2">Atur Sekarang</a>
                            </div>
                        @else
                            <div class="table-responsive" style="max-height: 70vh;">
                                <table class="table table-bordered align-middle text-center mb-0" style="font-size:0.8rem; min-width: 1200px;">
                                    <thead class="table-light sticky-top" style="z-index: 10;">
                                        {{-- Baris Header: Hari --}}
                                        <tr>
                                            <th rowspan="3" style="width: 120px; vertical-align: middle; background: #f8f9fc;">
                                                <strong>Hari</strong><br><br>
                                                <strong>Kelas \ Jam</strong>
                                            </th>
                                            <th colspan="{{ count($timeSlots) }}" style="background: #eaecf4; font-size: 1rem; padding: 10px;">
                                                {{ strtoupper($hariAktif) }}
                                            </th>
                                        </tr>
                                        {{-- Baris Header: Jam Ke --}}
                                        <tr>
                                            @foreach($timeSlots as $slot)
                                                @if($slot['type'] === 'khusus')
                                                    <th rowspan="2" style="background: #e3e6f0; width: 60px; vertical-align: middle;">
                                                        <div style="writing-mode: vertical-rl; transform: rotate(180deg); margin: 0 auto; white-space: nowrap; font-weight: bold; letter-spacing: 2px;">
                                                            {{ strtoupper($slot['label']) }}
                                                        </div>
                                                    </th>
                                                @else
                                                    <th style="width: 80px; background: #f8f9fc;">{{ $slot['jam_ke'] }}</th>
                                                @endif
                                            @endforeach
                                        </tr>
                                        {{-- Baris Header: Waktu --}}
                                        <tr>
                                            @foreach($timeSlots as $slot)
                                                @if($slot['type'] === 'reguler')
                                                    <th style="font-size: 0.7rem; background: #f8f9fc; font-weight: normal; padding: 4px;">
                                                        {{ $slot['waktu_mulai'] }}<br>s/d<br>{{ $slot['waktu_selesai'] }}
                                                    </th>
                                                @endif
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($kelasList as $kelas)
                                            @if($kelas->is_pkl)
                                                {{-- Jika kelas PKL, tampilkan 1 baris khusus merged --}}
                                                <tr>
                                                    <td class="fw-bold" style="background: #fdf5e6; text-align: left; padding-left: 10px;">
                                                        {{ $kelas->nama_kelas }}
                                                    </td>
                                                    <td colspan="{{ count($timeSlots) }}" style="background: #ffe4b5; color: #b8860b; font-weight: 800; font-size: 1.1rem; letter-spacing: 3px;">
                                                        PRAKTIK KERJA LAPANGAN (PKL)
                                                    </td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td class="fw-bold" style="background: #f8f9fc; text-align: left; padding-left: 10px; border-right: 2px solid #e3e6f0;">
                                                        {{ $kelas->nama_kelas }}
                                                    </td>
                                                    @foreach($timeSlots as $slot)
                                                        @if($slot['type'] === 'khusus')
                                                            {{-- Kolom khusus (istirahat dll) di-merge secara vertikal oleh CSS/table --}}
                                                            <td style="background: #f1f3f9;"></td>
                                                        @else
                                                            @php
                                                                $jd = $jadwalMatrix[$kelas->id_kelas][$slot['jam_ke']] ?? null;
                                                                $bgColor = '#ffffff';
                                                                $text = '-';
                                                                $tooltip = 'Kosong';

                                                                if ($jd) {
                                                                    $bgColor = $mapelColors[$jd->mapel_id] ?? '#e8f0fe';
                                                                    $kodeMapel = $jd->mapel->kode_mapel ?? $jd->mapel->nama_mapel;
                                                                    $kodeGuru = '';

                                                                    if ($jd->mapel->isProduktif()) {
                                                                        // Jika mapel produktif, tampilkan nama mapel saja (tanpa kode guru jika belum diset)
                                                                        $text = '<strong>' . $kodeMapel . '</strong>';
                                                                        if ($jd->guru_id) {
                                                                            $kGuru = $guruKodes[$jd->guru_id] ?? '?';
                                                                            $text .= ' <br><small>(' . $kGuru . ')</small>';
                                                                        }
                                                                    } else {
                                                                        // Mapel umum: MAPEL / KODE_GURU
                                                                        $kGuru = $jd->guru_id ? ($guruKodes[$jd->guru_id] ?? '?') : '?';
                                                                        $text = '<strong>' . $kodeMapel . '</strong> / ' . $kGuru;
                                                                    }

                                                                    $namaGuru = $jd->guru->nama ?? 'Belum ditentukan';
                                                                    $tooltip = $jd->mapel->nama_mapel . ' - ' . $namaGuru;
                                                                }
                                                            @endphp
                                                            <td class="slot-cell p-1 align-middle"
                                                                style="background: {{ $bgColor }}; cursor: pointer; transition: 0.2s; border: 1px solid #e3e6f0;"
                                                                title="{{ $tooltip }}"
                                                                data-kelas="{{ $kelas->id_kelas }}"
                                                                data-kelas-nama="{{ $kelas->nama_kelas }}"
                                                                data-jam="{{ $slot['jam_ke'] }}"
                                                                data-mapel="{{ $jd->mapel_id ?? '' }}"
                                                                data-guru="{{ $jd->guru_id ?? '' }}"
                                                                onclick="openSlotModal(this)">
                                                                {!! $text !!}
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    {{-- MODAL GENERATE --}}
    <div class="modal fade" id="modalGenerate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form action="{{ route('admin.jadwal.generate') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                    <input type="hidden" name="semester" value="{{ $semester }}">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <div class="avatar-text avatar-lg rounded-circle bg-soft-success text-success mx-auto mb-3">
                            <i class="feather-zap fs-24"></i>
                        </div>
                        <h5 class="fw-bold">Generate Jadwal</h5>
                        <p class="text-muted mb-0" style="font-size:0.9rem;">
                            Generate jadwal otomatis untuk <strong>{{ ucfirst($semester) }} {{ $tahunAjaran }}</strong>?<br>
                            Pastikan <strong>Pengaturan Jadwal</strong> (Jam per hari & Kode Guru) sudah benar.
                            @if ($hasJadwal)
                                <br><span class="text-danger mt-2 d-block" style="font-size:0.82rem;">⚠ Jadwal lama akan ditimpa!</span>
                            @endif
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success px-4">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Import --}}
    <div class="modal fade" id="modalImport" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.jadwal.import.excel') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                    <input type="hidden" name="semester" value="{{ $semester }}">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="feather-upload me-2 text-info"></i> Import Jadwal Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning mb-3" style="font-size:0.85rem;">
                            <i class="feather-alert-triangle me-1"></i> 
                            <strong>Peringatan:</strong> Mengimpor jadwal akan <strong>MENGHAPUS</strong> dan menggantikan semua jadwal yang sudah ada untuk semester ini!
                        </div>
                        
                        <div class="mb-4 text-center">
                            <p class="text-muted mb-2" style="font-size:0.9rem;">Belum punya file dengan format yang sesuai?</p>
                            <a href="{{ route('admin.jadwal.import.template') }}" class="btn btn-outline-success btn-sm">
                                <i class="feather-download me-1"></i> Download Template Excel
                            </a>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih File Excel (.xlsx / .xls)</label>
                            <input type="file" name="file_excel" class="form-control" accept=".xlsx,.xls" required>
                            <small class="text-muted">Maksimal ukuran file: 5MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="feather-upload me-1"></i> Import Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL RESET --}}
    <div class="modal fade" id="modalReset" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form action="{{ route('admin.jadwal.reset') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                    <input type="hidden" name="semester" value="{{ $semester }}">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <div class="avatar-text avatar-lg rounded-circle bg-soft-danger text-danger mx-auto mb-3">
                            <i class="feather-trash-2 fs-24"></i>
                        </div>
                        <h5 class="fw-bold">Reset Jadwal</h5>
                        <p class="text-muted mb-0" style="font-size:0.9rem;">
                            Hapus semua jadwal <strong>{{ ucfirst($semester) }} {{ $tahunAjaran }}</strong>?
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger px-4">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT SLOT --}}
    <div class="modal fade" id="modalSlot" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSlotTitle">Edit Slot Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2" style="font-size:0.85rem;" id="slotInfo">
                        Kelas: <strong><span id="slotKelasNama"></span></strong> <br>
                        Hari: <strong>{{ ucfirst($hariAktif) }}</strong>, Jam Ke: <strong><span id="slotJamKe"></span></strong>
                    </div>

                    <form id="formEditSlot">
                        <input type="hidden" id="editKelasId" name="kelas_id">
                        <input type="hidden" id="editHari" name="hari" value="{{ $hariAktif }}">
                        <input type="hidden" id="editJamKe" name="jam_ke">
                        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                        <input type="hidden" name="semester" value="{{ $semester }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mata Pelajaran</label>
                            <select class="form-select" id="editMapelId" name="mapel_id" required>
                                <option value="">-- Pilih Mapel --</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id_mapel }}" data-produktif="{{ $m->isProduktif() ? 1 : 0 }}">
                                        {{ $m->kode_mapel ?? $m->nama_mapel }} - {{ $m->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Guru Pengajar</label>
                            <select class="form-select" id="editGuruId" name="guru_id">
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guruList as $g)
                                    <option value="{{ $g->id_guru }}">
                                        {{ $g->nama }}
                                        @if(isset($guruKodes[$g->id_guru])) ({{ $guruKodes[$g->id_guru] }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-1 d-block" id="guruHelpText">Kosongkan jika mapel produktif (DDKV) dan guru belum ditentukan oleh Kaprog.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" id="btnHapusSlot">Hapus Slot</button>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSimpanSlot">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        // Styling on hover for cells
        document.querySelectorAll('.slot-cell').forEach(cell => {
            cell.addEventListener('mouseenter', function() {
                this.style.filter = 'brightness(0.9)';
            });
            cell.addEventListener('mouseleave', function() {
                this.style.filter = 'brightness(1)';
            });
        });

        // Open Modal Edit Slot
        const modalSlot = new bootstrap.Modal(document.getElementById('modalSlot'));

        function openSlotModal(cell) {
            document.getElementById('editKelasId').value = cell.dataset.kelas;
            document.getElementById('slotKelasNama').textContent = cell.dataset.kelasNama;
            document.getElementById('editJamKe').value = cell.dataset.jam;
            document.getElementById('slotJamKe').textContent = cell.dataset.jam;

            document.getElementById('editMapelId').value = cell.dataset.mapel;
            document.getElementById('editGuruId').value = cell.dataset.guru;

            modalSlot.show();
        }

        // Logic disable guru if not productive
        document.getElementById('editMapelId').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var isProduktif = selectedOption.dataset.produktif == 1;
            var guruSelect = document.getElementById('editGuruId');
            var helpText = document.getElementById('guruHelpText');

            if (!this.value) {
                // Do nothing
            } else if (!isProduktif) {
                // Mapel umum WAJIB ada guru
                guruSelect.required = true;
                helpText.textContent = 'Mapel umum harus memiliki guru pengajar.';
                helpText.classList.remove('text-muted');
                helpText.classList.add('text-danger');
            } else {
                // Mapel produktif opsional guru
                guruSelect.required = false;
                helpText.textContent = 'Kosongkan jika mapel produktif (DDKV) dan guru belum ditentukan oleh Kaprog.';
                helpText.classList.remove('text-danger');
                helpText.classList.add('text-muted');
            }
        });

        // Simpan Slot via AJAX
        document.getElementById('btnSimpanSlot').addEventListener('click', function() {
            var form = document.getElementById('formEditSlot');
            if(!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var formData = new FormData(form);
            var data = {};
            formData.forEach((value, key) => data[key] = value);

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

            fetch('{{ route("admin.jadwal.update-slot") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(res => {
                if(res.success) {
                    location.reload(); // Reload to see changes
                } else {
                    alert('Error: ' + res.message);
                    this.disabled = false;
                    this.innerHTML = 'Simpan';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan sistem.');
                this.disabled = false;
                this.innerHTML = 'Simpan';
            });
        });

        // Hapus Slot via AJAX
        document.getElementById('btnHapusSlot').addEventListener('click', function() {
            if(!confirm('Apakah Anda yakin ingin mengosongkan slot jadwal ini?')) return;

            var data = {
                kelas_id: document.getElementById('editKelasId').value,
                hari: document.getElementById('editHari').value,
                jam_ke: document.getElementById('editJamKe').value,
                tahun_ajaran: '{{ $tahunAjaran }}',
                semester: '{{ $semester }}'
            };

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menghapus...';

            fetch('{{ route("admin.jadwal.delete-slot") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(res => {
                if(res.success) {
                    location.reload();
                } else {
                    alert('Error: ' + res.message);
                    this.disabled = false;
                    this.innerHTML = 'Hapus Slot';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan sistem.');
                this.disabled = false;
                this.innerHTML = 'Hapus Slot';
            });
        });
    </script>
@endpush
