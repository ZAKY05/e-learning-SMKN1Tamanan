<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\GuruKode;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\JamPelajaran;
use App\Models\JadwalPelajaran;
use App\Models\SettingJadwal;
use App\Models\JadwalImportMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    /**
     * Halaman utama jadwal — tampilan grid mirip PDF per hari
     */
    public function index(Request $request)
    {
        $tahunAjaran = $request->input('tahun_ajaran', date('Y') . '/' . (date('Y') + 1));
        $semester    = $request->input('semester', 'ganjil');
        $hariAktif   = $request->input('hari', 'senin');

        $kelasList = Kelas::with('jurusan')->orderBy('tingkat')->get();
        $guruList  = Guru::orderBy('nama')->get();

        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

        // Load setting jadwal
        $setting = SettingJadwal::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->first();

        // Jika belum ada setting, buat default
        if (!$setting) {
            $setting = new SettingJadwal([
                'tahun_ajaran'        => $tahunAjaran,
                'semester'            => $semester,
                'total_jam_per_minggu' => 48,
                'jam_mulok_tambahan'  => 2,
                'jam_senin'           => 10,
                'jam_selasa'          => 10,
                'jam_rabu'            => 10,
                'jam_kamis'           => 10,
                'jam_jumat'           => 8,
                'waktu_mulai'         => '07:00',
                'durasi_jam_menit'    => 45,
                'slot_khusus'         => [],
            ]);
        }

        // Hitung waktu slot untuk hari aktif
        $timeSlots = $setting->hitungWaktuSlot($hariAktif);

        // Jumlah JP hari ini
        $jamHariIni = $setting->getJamPerHari()[$hariAktif] ?? 0;

        // Ambil jadwal untuk hari aktif
        $jadwal = JadwalPelajaran::with(['guru', 'mapel', 'kelas.jurusan'])
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->where('hari', $hariAktif)
            ->get();

        // Build matrix: [kelas_id][jam_ke] => jadwal
        $jadwalMatrix = [];
        foreach ($jadwal as $j) {
            $jadwalMatrix[$j->kelas_id][$j->jam_ke] = $j;
        }

        // Load kode guru untuk semester ini
        $guruKodes = GuruKode::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->pluck('kode', 'guru_id')
            ->toArray();

        // Cek apakah sudah ada jadwal
        $hasJadwal = JadwalPelajaran::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->exists();

        // Hitung total jam per guru untuk summary
        $guruJamCount = JadwalPelajaran::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->whereNotNull('guru_id')
            ->select('guru_id', DB::raw('COUNT(*) as total_jam'))
            ->groupBy('guru_id')
            ->pluck('total_jam', 'guru_id')
            ->toArray();

        // Load mapel list for manual edit modal
        $mapelList = Mapel::with('gurus')->orderBy('nama_mapel')->get();

        // Generate warna per mapel
        $mapelColors = $this->generateMapelColors($mapelList);

        return view('Admin.pages.jadwal', compact(
            'kelasList', 'guruList', 'hariList', 'hariAktif',
            'setting', 'timeSlots', 'jamHariIni',
            'jadwal', 'jadwalMatrix', 'guruKodes',
            'tahunAjaran', 'semester', 'hasJadwal',
            'guruJamCount', 'mapelList', 'mapelColors'
        ));
    }

    /**
     * Form setting jadwal
     */
    public function settingForm(Request $request)
    {
        $tahunAjaran = $request->input('tahun_ajaran', date('Y') . '/' . (date('Y') + 1));
        $semester    = $request->input('semester', 'ganjil');

        $setting = SettingJadwal::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->first();

        if (!$setting) {
            $setting = new SettingJadwal([
                'tahun_ajaran'        => $tahunAjaran,
                'semester'            => $semester,
                'total_jam_per_minggu' => 48,
                'jam_mulok_tambahan'  => 2,
                'jam_senin'           => 10,
                'jam_selasa'          => 10,
                'jam_rabu'            => 10,
                'jam_kamis'           => 10,
                'jam_jumat'           => 8,
                'waktu_mulai'         => '07:00',
                'durasi_jam_menit'    => 45,
                'slot_khusus'         => [],
            ]);
        }

        return view('Admin.pages.jadwal-setting', compact(
            'setting', 'tahunAjaran', 'semester'
        ));
    }

    /**
     * Simpan setting jadwal
     */
    public function settingSave(Request $request)
    {
        $request->validate([
            'tahun_ajaran'         => 'required|string|max:9',
            'semester'             => 'required|in:ganjil,genap',
            'waktu_mulai'          => 'required|string',
            'durasi_jam_menit'     => 'required|integer|min:30|max:60',
        ]);

        // Clean waktu_mulai to H:i
        $waktuMulai = \Carbon\Carbon::parse($request->waktu_mulai)->format('H:i');

        SettingJadwal::updateOrCreate(
            [
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester'     => $request->semester,
            ],
            [
                'total_jam_per_minggu' => 48,
                'jam_mulok_tambahan'   => 2,
                'waktu_mulai'          => $waktuMulai,
                'durasi_jam_menit'     => $request->durasi_jam_menit,
                'slot_khusus'          => [],
            ]
        );

        return redirect()
            ->route('admin.jadwal.setting', [
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester'     => $request->semester,
            ])
            ->with('success', 'Pengaturan jadwal berhasil disimpan!');
    }


    /**
     * Simpan / update satu slot jadwal secara manual
     */
    public function updateSlot(Request $request)
    {
        $request->validate([
            'kelas_id'     => 'required|exists:kelas,id_kelas',
            'hari'         => 'required|in:senin,selasa,rabu,kamis,jumat',
            'jam_ke'       => 'required|integer|min:1',
            'mapel_id'     => 'required|exists:mapel,id_mapel',
            'guru_id'      => 'nullable|exists:guru,id_guru',
            'tahun_ajaran' => 'required|string|max:9',
            'semester'     => 'required|in:ganjil,genap',
        ]);

        // Cek konflik guru
        if ($request->guru_id) {
            $conflict = JadwalPelajaran::where('guru_id', $request->guru_id)
                ->where('hari', $request->hari)
                ->where('jam_ke', $request->jam_ke)
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->where('semester', $request->semester)
                ->where('kelas_id', '!=', $request->kelas_id)
                ->first();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru sudah mengajar di kelas lain pada jam dan hari yang sama!',
                ], 422);
            }
        }

        // Update or create
        $jadwal = JadwalPelajaran::updateOrCreate(
            [
                'kelas_id'     => $request->kelas_id,
                'hari'         => $request->hari,
                'jam_ke'       => $request->jam_ke,
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester'     => $request->semester,
            ],
            [
                'mapel_id' => $request->mapel_id,
                'guru_id'  => $request->guru_id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Slot jadwal berhasil diperbarui!',
            'data'    => $jadwal->load(['guru', 'mapel', 'kelas.jurusan']),
        ]);
    }
    /**
     * Hapus satu slot jadwal
     */
    public function deleteSlot(Request $request)
    {
        $request->validate([
            'kelas_id'     => 'required',
            'hari'         => 'required',
            'jam_ke'       => 'required',
            'tahun_ajaran' => 'required',
            'semester'     => 'required',
        ]);

        JadwalPelajaran::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('jam_ke', $request->jam_ke)
            ->where('tahun_ajaran', $request->tahun_ajaran)
            ->where('semester', $request->semester)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Slot dihapus!']);
    }

    public function downloadTemplateExcel()
    {
        $rows = [
            ['Hari', 'Jam Ke', 'Nama Kelas', 'Nama Mata Pelajaran', 'Kode Guru (Opsional)'],
            ['Senin', 1, '10 RPL 1', 'Matematika', 'A'],
            ['Senin', 2, '10 RPL 1', 'Matematika', 'A'],
            ['Selasa', 3, '11 DKV 2', 'IPAS', 'B'],
        ];
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($rows);
        $xlsx->downloadAs('Template_Import_Jadwal_ASC.xlsx');
        exit;
    }

    /**
     * Halaman mapping singkatan ASC
     */
    public function mappingIndex(Request $request)
    {
        $mapelMappings = JadwalImportMapping::where('tipe', 'mapel')->get();
        $kelasMappings = JadwalImportMapping::where('tipe', 'kelas')->get();
        $allMapel = Mapel::orderBy('nama_mapel')->get();
        $allKelas = Kelas::with('jurusan')->get();

        return view('Admin.pages.jadwal-mapping', compact(
            'mapelMappings', 'kelasMappings', 'allMapel', 'allKelas'
        ));
    }

    public function mappingSave(Request $request)
    {
        $request->validate([
            'tipe'        => 'required|in:mapel,kelas',
            'singkatan'   => 'required|string|max:100',
            'target_id'   => 'required|integer',
        ]);

        JadwalImportMapping::updateOrCreate(
            ['tipe' => $request->tipe, 'singkatan' => strtoupper(trim($request->singkatan))],
            ['target_id' => $request->target_id]
        );

        return redirect()->back()->with('success', 'Mapping berhasil disimpan!');
    }

    public function mappingDelete($id)
    {
        JadwalImportMapping::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Mapping berhasil dihapus!');
    }

    /**
     * Import Excel - mendukung format GRID ASC dan format LIST template
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel'   => 'required|mimes:xlsx,xls|max:5120',
            'tahun_ajaran' => 'required|string|max:9',
            'semester'     => 'required|in:ganjil,genap',
        ]);

        $tahunAjaran = $request->tahun_ajaran;
        $semester    = $request->semester;

        $xlsx = \Shuchkin\SimpleXLSX::parse($request->file('file_excel')->getPathname());
        if (!$xlsx) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel: ' . \Shuchkin\SimpleXLSX::parseError());
        }

        $rows = $xlsx->rows();
        if (empty($rows)) {
            return redirect()->back()->with('error', 'File Excel kosong!');
        }

        // Deteksi format: GRID ASC atau LIST template
        $isGrid = $this->detectASCGrid($rows);

        if ($isGrid) {
            return $this->importFromASCGrid($rows, $tahunAjaran, $semester);
        } else {
            return $this->importFromList($rows, $tahunAjaran, $semester);
        }
    }

    private function detectASCGrid(array $rows): bool
    {
        foreach ($rows as $row) {
            foreach ($row as $cell) {
                $val = strtolower(trim($cell));
                if (in_array($val, ['senin', 'selasa', 'rabu', 'kamis', 'jumat', "jum'at"])) {
                    // Check nearby for "jam ke" pattern
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Parser GRID ASC TimeTables
     */
    private function importFromASCGrid(array $rows, string $tahunAjaran, string $semester)
    {
        // 1. Bangun cache mapping
        $mapelMap = [];
        $kelasMap = [];
        foreach (JadwalImportMapping::all() as $m) {
            if ($m->tipe === 'mapel') $mapelMap[strtoupper(trim($m->singkatan))] = $m->target_id;
            if ($m->tipe === 'kelas') $kelasMap[strtoupper(trim($m->singkatan))] = $m->target_id;
        }
        // Tambah nama asli mapel & kelas ke cache
        foreach (Mapel::all() as $m) {
            $mapelMap[strtoupper(trim($m->nama_mapel))] = $m->id_mapel;
        }
        foreach (Kelas::all() as $k) {
            $kelasMap[strtoupper(trim($k->nama_kelas))] = $k->id_kelas;
            $kelasMap[strtoupper(trim($k->nama_kelas_lengkap))] = $k->id_kelas;
        }

        $guruKodes = DB::table('guru_kode')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->pluck('guru_id', 'kode')->toArray();

        // 2. Scan untuk menemukan semua section hari
        $daySections = [];
        $foundDays = []; // Track hari yang sudah ditemukan
        foreach ($rows as $rIdx => $row) {
            foreach ($row as $cIdx => $cell) {
                $val = strtolower(trim($cell));
                $hari = null;
                if ($val === 'senin') $hari = 'senin';
                elseif ($val === 'selasa') $hari = 'selasa';
                elseif ($val === 'rabu') $hari = 'rabu';
                elseif ($val === 'kamis') $hari = 'kamis';
                elseif ($val === 'jumat' || $val === "jum'at" || $val === 'jumat') $hari = 'jumat';

                if ($hari && !isset($foundDays[$hari])) {
                    $daySections[] = ['hari' => $hari, 'row' => $rIdx, 'col' => $cIdx];
                    $foundDays[$hari] = true;
                }
            }
        }

        if (empty($daySections)) {
            return redirect()->back()->with('error', 'Tidak ditemukan header hari (Senin/Selasa/Rabu/Kamis/Jumat) di file Excel.');
        }

        $jadwalBaru = [];
        $inserted = 0;
        $failed = 0;
        $unmatchedMapel = [];
        $unmatchedKelas = [];
        $jamPerHariFromExcel = []; // Track max jam per hari dari Excel

        foreach ($daySections as $section) {
            $hari = $section['hari'];
            $dayRow = $section['row'];
            $dayCol = $section['col'];

            // 3. Cari baris "Jam ke" (1-3 baris di bawah header hari)
            $jamKeRow = null;
            for ($r = $dayRow; $r <= min($dayRow + 4, count($rows) - 1); $r++) {
                foreach ($rows[$r] as $c => $cell) {
                    $v = strtolower(trim($cell));
                    if (strpos($v, 'jam ke') !== false || strpos($v, 'jam_ke') !== false) {
                        $jamKeRow = $r;
                        break 2;
                    }
                }
            }
            if (!$jamKeRow) {
                // Coba row tepat di bawah hari
                $jamKeRow = $dayRow + 1;
            }

            // 4. Baca nomor Jam Ke dari baris tersebut, batasi hanya untuk section ini
            $jamKeCols = []; // col => jam_ke_number
            $lastJam = 0;
            // Mulai dari kolom hari atau 1-2 kolom sebelumnya karena kolom Hari bisa di-merge
            $startCol = max(0, $dayCol - 2); 
            for ($c = $startCol; $c < count($rows[$jamKeRow]); $c++) {
                $v = trim($rows[$jamKeRow][$c] ?? '');
                if (is_numeric($v) && (int)$v >= 1 && (int)$v <= 15) {
                    $jam = (int)$v;
                    // Jika jam tiba-tiba kembali ke 1 (atau lebih kecil dari jam sebelumnya), 
                    // berarti ini sudah masuk jadwal hari lain (side-by-side)
                    if ($jam <= $lastJam && $lastJam >= 3) {
                        break;
                    }
                    $jamKeCols[$c] = $jam;
                    $lastJam = $jam;
                }
            }
            if (empty($jamKeCols)) continue;

            // Track jumlah jam per hari dari Excel
            $maxJamHari = max($jamKeCols);
            $jamPerHariFromExcel[$hari] = max($jamPerHariFromExcel[$hari] ?? 0, $maxJamHari);

            // 5. Tentukan kolom Nama Kelas
            $minDataCol = min(array_keys($jamKeCols));
            // Cari kolom kelas: cek beberapa kolom di kiri data untuk menemukan yg berisi teks
            $kelasCol = max(0, $minDataCol - 1);
            for ($c = $minDataCol - 1; $c >= max(0, $minDataCol - 3); $c--) {
                // Cek apakah kolom ini berisi teks kelas di baris data
                for ($testR = $jamKeRow + 2; $testR <= min($jamKeRow + 5, count($rows) - 1); $testR++) {
                    $testVal = trim($rows[$testR][$c] ?? '');
                    if (!empty($testVal) && !is_numeric($testVal)) {
                        $kelasCol = $c;
                        break 2;
                    }
                }
            }

            // 6. Baca data kelas mulai dari 2-3 baris di bawah jamKeRow
            $dataStart = $jamKeRow + 2;
            $emptyCount = 0;
            // Log section start info

            for ($r = $dataStart; $r < count($rows); $r++) {
                $namaKelas = strtoupper(trim($rows[$r][$kelasCol] ?? ''));
                
                if (empty($namaKelas)) {
                    $emptyCount++;
                    if ($emptyCount > 3) break; // Stop setelah 3 baris kosong berturut-turut
                    continue;
                }
                $emptyCount = 0;

                // Stop jika menemukan header hari lain atau teks struktural
                $namaKelasLower = strtolower($namaKelas);
                if (in_array($namaKelasLower, ['senin','selasa','rabu','kamis','jumat',"jum'at",'hari'])) break;
                if (strpos($namaKelasLower, 'ditetapkan') !== false) break;
                if (strpos($namaKelasLower, 'kepala') !== false) break;

                // Normalisasi: coba cari dengan dan tanpa spasi/dash
                $kId = $kelasMap[$namaKelas] ?? null;
                if (!$kId) {
                    // Coba variasi: strip dash, normalize spaces
                    $normalized = preg_replace('/[\s\-]+/', ' ', $namaKelas);
                    $kId = $kelasMap[$normalized] ?? null;
                }
                if (!$kId) {
                    // Coba tanpa spasi sama sekali
                    $noSpace = preg_replace('/[\s\-]+/', '', $namaKelas);
                    foreach ($kelasMap as $mapKey => $mapVal) {
                        if (preg_replace('/[\s\-]+/', '', $mapKey) === $noSpace) {
                            $kId = $mapVal;
                            break;
                        }
                    }
                }
                if (!$kId) {

                    if (!in_array($namaKelas, $unmatchedKelas)) $unmatchedKelas[] = $namaKelas;
                    continue;
                }


                // 7. Baca setiap kolom jam - sort kolom agar urut
                $sortedJamCols = $jamKeCols;
                ksort($sortedJamCols);
                $prevCellValue = '';
                $prevCol = -99;
                foreach ($sortedJamCols as $c => $jamKe) {
                    $cellValue = trim($rows[$r][$c] ?? '');
                    
                    // DEBUG: log raw cell for K3R

                    
                    // Fill-forward: jika kosong dan kolom bersebelahan, kemungkinan merged cell
                    if (empty($cellValue) || $cellValue === '-') {
                        $isAdjacent = ($c - $prevCol) <= 1; // Kolom bersebelahan
                        if (!empty($prevCellValue) && $cellValue !== '-' && $isAdjacent) {
                            $cellValue = $prevCellValue; // Gunakan data sebelumnya
                        } else {
                            $prevCellValue = '';
                            $prevCol = $c;
                            continue;
                        }
                    }
                    $prevCellValue = $cellValue;
                    $prevCol = $c;

                    // Parse "MAPEL / KODE" atau "MAPEL"
                    $parts = preg_split('/\s*\/\s*/', $cellValue, 2);
                    $mapelSingkatan = strtoupper(trim($parts[0] ?? ''));
                    $kodeGuru = strtoupper(trim($parts[1] ?? ''));

                    // Skip slot khusus
                    $skipWords = ['upacara','istirahat','sholat','dhuhur','istirah'];
                    $isSkip = false;
                    foreach ($skipWords as $sw) {
                        if (stripos($mapelSingkatan, $sw) !== false) { $isSkip = true; break; }
                    }
                    if ($isSkip) continue;

                    $mId = $mapelMap[$mapelSingkatan] ?? null;
                    if (!$mId) {
                        if (!in_array($mapelSingkatan, $unmatchedMapel)) $unmatchedMapel[] = $mapelSingkatan;
                        $failed++;
                        continue;
                    }

                    $gId = null;
                    if ($kodeGuru) {
                        $gId = $guruKodes[$kodeGuru] ?? null;
                        // Fallback: cari case-insensitive
                        if (!$gId) {
                            foreach ($guruKodes as $kode => $guruId) {
                                if (strtoupper($kode) === $kodeGuru) {
                                    $gId = $guruId;
                                    break;
                                }
                            }
                        }

                    }

                    $jadwalBaru[] = [
                        'guru_id'      => $gId,
                        'mapel_id'     => $mId,
                        'kelas_id'     => $kId,
                        'hari'         => $hari,
                        'jam_ke'       => $jamKe,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester'     => $semester,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];

                    $inserted++;
                }
            }
        }

        // Hapus jadwal lama lalu insert baru
        JadwalPelajaran::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)->delete();



        // Deduplikasi kelas: jika ada kelas+hari+jam_ke yang sama, prioritaskan yang punya guru
        $uniqueJadwal = [];
        foreach ($jadwalBaru as $j) {
            $key = $j['kelas_id'] . '-' . $j['hari'] . '-' . $j['jam_ke'];
            if (!isset($uniqueJadwal[$key])) {
                // Belum ada, simpan langsung
                $uniqueJadwal[$key] = $j;
            } else {
                // Sudah ada: simpan yang punya guru_id (prioritas)
                if ($j['guru_id'] && !$uniqueJadwal[$key]['guru_id']) {
                    $uniqueJadwal[$key] = $j;
                }
            }
        }
        

        
        $jadwalBaru = array_values($uniqueJadwal);
        $inserted = count($jadwalBaru);

        if (!empty($jadwalBaru)) {
            foreach (array_chunk($jadwalBaru, 100) as $chunk) {
                JadwalPelajaran::insert($chunk);
            }
        }

        // Auto-update setting jadwal dengan jumlah jam per hari dari Excel
        if (!empty($jamPerHariFromExcel)) {
            $settingData = [
                'tahun_ajaran' => $tahunAjaran,
                'semester'     => $semester,
            ];
            foreach (['senin','selasa','rabu','kamis','jumat'] as $h) {
                $settingData['jam_' . $h] = $jamPerHariFromExcel[$h] ?? 0;
            }
            $totalJam = array_sum(array_intersect_key($jamPerHariFromExcel, array_flip(['senin','selasa','rabu','kamis','jumat'])));
            $settingData['total_jam_per_minggu'] = $totalJam;

            SettingJadwal::updateOrCreate(
                ['tahun_ajaran' => $tahunAjaran, 'semester' => $semester],
                $settingData
            );
        }

        $msg = "Berhasil mengimpor $inserted slot jadwal (format Grid ASC).";
        $warnings = [];
        if ($failed > 0) $msg .= " $failed sel gagal.";
        if (!empty($unmatchedMapel)) {
            $warnings[] = "Mapel tidak dikenali: " . implode(', ', $unmatchedMapel) . ". Silakan tambahkan di menu Mapping Singkatan.";
        }
        if (!empty($unmatchedKelas)) {
            $warnings[] = "Kelas tidak dikenali: " . implode(', ', $unmatchedKelas) . ". Silakan tambahkan di menu Mapping Singkatan.";
        }

        return redirect()->back()->with('success', $msg)->with('warnings', $warnings);
    }

    /**
     * Import dari format List (template standar)
     */
    private function importFromList(array $rows, string $tahunAjaran, string $semester)
    {
        JadwalPelajaran::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)->delete();

        $mapelMap = [];
        foreach (JadwalImportMapping::where('tipe','mapel')->get() as $m) {
            $mapelMap[strtolower(trim($m->singkatan))] = $m->target_id;
        }
        foreach (Mapel::all() as $m) {
            $mapelMap[strtolower(trim($m->nama_mapel))] = $m->id_mapel;
        }

        $kelasMap = [];
        foreach (JadwalImportMapping::where('tipe','kelas')->get() as $m) {
            $kelasMap[strtolower(trim($m->singkatan))] = $m->target_id;
        }
        foreach (Kelas::all() as $k) {
            $kelasMap[strtolower(trim($k->nama_kelas))] = $k->id_kelas;
            $kelasMap[strtolower(trim($k->nama_kelas_lengkap))] = $k->id_kelas;
        }

        $guruKodes = DB::table('guru_kode')
            ->where('tahun_ajaran', $tahunAjaran)->where('semester', $semester)
            ->pluck('guru_id', 'kode')->toArray();
        $guruNamaCache = Guru::all()->pluck('id_guru', 'nama')->toArray();

        array_shift($rows);
        $inserted = 0; $failed = 0;
        $jadwalBaru = [];

        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            $hari = strtolower(trim($row[0] ?? ''));
            $jamKe = (int)($row[1] ?? 0);
            $namaKelas = strtolower(trim($row[2] ?? ''));
            $namaMapel = strtolower(trim($row[3] ?? ''));
            $kodeGuru = trim($row[4] ?? '');

            if (!$hari || !$jamKe || !$namaKelas || !$namaMapel) { $failed++; continue; }

            $kId = $kelasMap[$namaKelas] ?? null;
            $mId = $mapelMap[$namaMapel] ?? null;
            if (!$kId || !$mId) { $failed++; continue; }

            $gId = null;
            if ($kodeGuru) {
                $gId = $guruKodes[$kodeGuru] ?? ($guruNamaCache[$kodeGuru] ?? null);
            }

            $jadwalBaru[] = [
                'guru_id' => $gId, 'mapel_id' => $mId, 'kelas_id' => $kId,
                'hari' => $hari, 'jam_ke' => $jamKe,
                'tahun_ajaran' => $tahunAjaran, 'semester' => $semester,
                'created_at' => now(), 'updated_at' => now(),
            ];
            $inserted++;
        }

        if (!empty($jadwalBaru)) {
            foreach (array_chunk($jadwalBaru, 100) as $chunk) {
                JadwalPelajaran::insert($chunk);
            }
        }

        $msg = "Berhasil mengimpor $inserted slot jadwal.";
        if ($failed > 0) $msg .= " $failed baris gagal.";
        return redirect()->back()->with('success', $msg);
    }
    /**
     * Halaman kelola Kode Guru per semester
     */
    public function guruKodeIndex(Request $request)
    {
        $tahunAjaran = $request->input('tahun_ajaran', date('Y') . '/' . (date('Y') + 1));
        $semester    = $request->input('semester', 'ganjil');

        $guruKodes = GuruKode::with('guru')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->orderBy('kode')
            ->get();

        $allGuru = Guru::orderBy('nama')->get();

        return view('Admin.pages.jadwal-guru-kode', compact(
            'guruKodes', 'allGuru', 'tahunAjaran', 'semester'
        ));
    }

    public function guruKodeSave(Request $request)
    {
        $request->validate([
            'guru_id'      => 'required|exists:guru,id_guru',
            'kode'         => 'required|string|max:5',
            'tahun_ajaran' => 'required|string|max:9',
            'semester'     => 'required|in:ganjil,genap',
        ]);

        // Cek duplikat kode di semester yang sama
        $existing = GuruKode::where('kode', strtoupper(trim($request->kode)))
            ->where('tahun_ajaran', $request->tahun_ajaran)
            ->where('semester', $request->semester)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', "Kode '{$request->kode}' sudah digunakan oleh guru lain di semester ini!");
        }

        GuruKode::create([
            'guru_id'      => $request->guru_id,
            'kode'         => strtoupper(trim($request->kode)),
            'tahun_ajaran' => $request->tahun_ajaran,
            'semester'     => $request->semester,
        ]);

        return redirect()->back()->with('success', "Kode guru berhasil ditambahkan!");
    }

    public function guruKodeDelete($id)
    {
        GuruKode::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Kode guru berhasil dihapus!');
    }

    /**
     * Hapus semua jadwal di tahun ajaran & semester tertentu
     */
    public function reset(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string|max:9',
            'semester'     => 'required|in:ganjil,genap',
        ]);

        $deleted = JadwalPelajaran::where('tahun_ajaran', $request->tahun_ajaran)
            ->where('semester', $request->semester)
            ->delete();

        return redirect()
            ->route('admin.jadwal.index', [
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester'     => $request->semester,
            ])
            ->with('success', "Jadwal berhasil di-reset! ({$deleted} data dihapus)");
    }

    /**
     * Hapus satu jadwal (legacy)
     */
    public function destroy($id)
    {
        $jadwal = JadwalPelajaran::findOrFail($id);
        $tahunAjaran = $jadwal->tahun_ajaran;
        $semester = $jadwal->semester;
        $jadwal->delete();

        return redirect()
            ->route('admin.jadwal.index', [
                'tahun_ajaran' => $tahunAjaran,
                'semester'     => $semester,
            ])
            ->with('success', 'Jadwal berhasil dihapus');
    }

    /**
     * Generate warna konsisten per mapel
     */
    private function generateMapelColors($mapelList): array
    {
        $palette = [
            '#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4',
            '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE',
            '#85C1E9', '#F0B27A', '#82E0AA', '#F1948A', '#AED6F1',
            '#D7BDE2', '#A3E4D7', '#FAD7A0', '#A9CCE3', '#D5F5E3',
            '#FADBD8', '#E8DAEF', '#D4EFDF', '#FCF3CF', '#D6EAF8',
            '#F9E79F', '#A9DFBF', '#F5B7B1', '#AEB6BF', '#D2B4DE',
        ];

        $colors = [];
        $i = 0;
        foreach ($mapelList as $m) {
            $colors[$m->id_mapel] = $palette[$i % count($palette)];
            $i++;
        }
        return $colors;
    }
}
