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
                'slot_khusus'         => [
                    ['sebelum_jam' => 1, 'durasi' => 30, 'label' => 'Upacara', 'hari' => 'senin'],
                    ['setelah_jam' => 4, 'durasi' => 15, 'label' => 'Istirahat', 'hari' => null],
                    ['setelah_jam' => 7, 'durasi' => 45, 'label' => 'Istirahat / Sholat Dhuhur', 'hari' => null],
                ],
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
                'slot_khusus'         => [
                    ['sebelum_jam' => 1, 'durasi' => 30, 'label' => 'Upacara', 'hari' => 'senin'],
                    ['setelah_jam' => 4, 'durasi' => 15, 'label' => 'Istirahat', 'hari' => null],
                    ['setelah_jam' => 7, 'durasi' => 45, 'label' => 'Istirahat / Sholat Dhuhur', 'hari' => null],
                ],
            ]);
        }

        $guruList = Guru::orderBy('nama')->get();

        // Load existing kode guru
        $guruKodes = GuruKode::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->pluck('kode', 'guru_id')
            ->toArray();

        return view('Admin.pages.jadwal-setting', compact(
            'setting', 'tahunAjaran', 'semester', 'guruList', 'guruKodes'
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
            'total_jam_per_minggu' => 'required|integer|min:1|max:60',
            'jam_mulok_tambahan'   => 'required|integer|min:0|max:10',
            'jam_senin'            => 'required|integer|min:0|max:14',
            'jam_selasa'           => 'required|integer|min:0|max:14',
            'jam_rabu'             => 'required|integer|min:0|max:14',
            'jam_kamis'            => 'required|integer|min:0|max:14',
            'jam_jumat'            => 'required|integer|min:0|max:14',
            'waktu_mulai'          => 'required|string',
            'durasi_jam_menit'     => 'required|integer|min:30|max:60',
        ]);

        DB::transaction(function () use ($request) {
            // Clean waktu_mulai to H:i
            $waktuMulai = \Carbon\Carbon::parse($request->waktu_mulai)->format('H:i');

            // Save/update setting
            $setting = SettingJadwal::updateOrCreate(
                [
                    'tahun_ajaran' => $request->tahun_ajaran,
                    'semester'     => $request->semester,
                ],
                [
                    'total_jam_per_minggu' => $request->total_jam_per_minggu,
                    'jam_mulok_tambahan'   => $request->jam_mulok_tambahan,
                    'jam_senin'            => $request->jam_senin,
                    'jam_selasa'           => $request->jam_selasa,
                    'jam_rabu'             => $request->jam_rabu,
                    'jam_kamis'            => $request->jam_kamis,
                    'jam_jumat'            => $request->jam_jumat,
                    'waktu_mulai'          => $waktuMulai,
                    'durasi_jam_menit'     => $request->durasi_jam_menit,
                    'slot_khusus'          => json_decode($request->input('slot_khusus_json', '[]'), true),
                ]
            );

            // Save kode guru
            $kodeGuru = $request->input('kode_guru', []);
            $maxJam   = $request->input('max_jam_guru', []);

            // Delete existing kode guru for this semester
            GuruKode::where('tahun_ajaran', $request->tahun_ajaran)
                ->where('semester', $request->semester)
                ->delete();

            foreach ($kodeGuru as $guruId => $kode) {
                if (!empty(trim($kode))) {
                    GuruKode::create([
                        'guru_id'      => $guruId,
                        'kode'         => strtoupper(trim($kode)),
                        'tahun_ajaran' => $request->tahun_ajaran,
                        'semester'     => $request->semester,
                    ]);
                }
            }

            // Update max jam guru
            foreach ($maxJam as $guruId => $max) {
                Guru::where('id_guru', $guruId)->update([
                    'max_jam_per_minggu' => (int) $max ?: 24,
                ]);
            }
        });

        return redirect()
            ->route('admin.jadwal.setting', [
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester'     => $request->semester,
            ])
            ->with('success', 'Pengaturan jadwal berhasil disimpan!');
    }

    /**
     * Generate jadwal otomatis
     */
    public function generate(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string|max:9',
            'semester'     => 'required|in:ganjil,genap',
        ]);

        $tahunAjaran = $request->tahun_ajaran;
        $semester    = $request->semester;

        // Load setting
        $setting = SettingJadwal::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->first();

        if (!$setting) {
            return redirect()->route('admin.jadwal.setting', [
                'tahun_ajaran' => $tahunAjaran,
                'semester'     => $semester,
            ])->with('error', 'Silakan atur pengaturan jadwal terlebih dahulu!');
        }

        // Hapus jadwal lama
        JadwalPelajaran::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->delete();

        $hariList   = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $jamPerHari = $setting->getJamPerHari();

        // Kelas yang tidak PKL
        $kelasList = Kelas::with('jurusan')->where('is_pkl', false)->get();

        // Track occupancy
        $guruOccupied  = []; // [guru_id][hari][jam_ke] = true
        $kelasOccupied = []; // [kelas_id][hari][jam_ke] = true
        $guruTotalJam  = []; // [guru_id] = total jam minggu ini
        
        // Optimasi: Preload max jam guru
        $guruMaxJam = Guru::pluck('max_jam_per_minggu', 'id_guru')->toArray();

        $jadwalBaru = [];
        $warnings   = [];

        // 1. Siapkan kebutuhan mapel tiap kelas
        $kelasMapelNeeds = [];
        foreach ($kelasList as $kelas) {
            $mapels = Mapel::where(function ($q) use ($kelas) {
                $q->where('jenis', 'umum')
                  ->orWhere(function ($q2) use ($kelas) {
                      $q2->where('jenis', 'jurusan')
                         ->where('jurusan_id', $kelas->jurusan_id);
                  });
            })->get();
            
            $needs = [];
            foreach ($mapels as $mapel) {
                $guruIds = DB::table('guru_mapel')
                    ->where('mapel_id', $mapel->id_mapel)
                    ->pluck('guru_id')
                    ->toArray();
                    
                if (empty($guruIds) && !$mapel->isProduktif()) {
                    $warnings[] = "Tidak ada guru untuk mapel '{$mapel->nama_mapel}' di kelas {$kelas->nama_kelas}";
                    continue;
                }
                
                $needs[] = [
                    'mapel'    => $mapel,
                    'guru_ids' => $guruIds,
                    'sisa_jam' => $mapel->jam_per_minggu ?? 2,
                ];
            }
            $kelasMapelNeeds[$kelas->id_kelas] = $needs;
        }

        // MATRIX MEMORI UNTUK SWAP & RELOCATE
        $matrix = []; // $matrix[$hari][$jamKe][$kId] = ['mapel_id', 'guru_id', 'terpilihIdx']

        // Helper Closures
        $assignSlot = function($hari, $jamKe, $kId, $mapel, $guruId, $idx) use (&$matrix, &$guruOccupied, &$kelasOccupied, &$guruTotalJam) {
            $matrix[$hari][$jamKe][$kId] = [
                'mapel' => $mapel,
                'guru_id' => $guruId,
                'idx' => $idx
            ];
            $kelasOccupied[$kId][$hari][$jamKe] = true;
            if ($guruId !== 'null' && $guruId !== null) {
                $guruOccupied[$guruId][$hari][$jamKe] = $kId; // store which class the guru is teaching
                $guruTotalJam[$guruId] = ($guruTotalJam[$guruId] ?? 0) + 1;
            }
        };

        $unassignSlot = function($hari, $jamKe, $kId) use (&$matrix, &$guruOccupied, &$kelasOccupied, &$guruTotalJam) {
            if (!isset($matrix[$hari][$jamKe][$kId])) return null;
            $slot = $matrix[$hari][$jamKe][$kId];
            unset($matrix[$hari][$jamKe][$kId]);
            unset($kelasOccupied[$kId][$hari][$jamKe]);
            if ($slot['guru_id'] !== 'null' && $slot['guru_id'] !== null) {
                unset($guruOccupied[$slot['guru_id']][$hari][$jamKe]);
                $guruTotalJam[$slot['guru_id']]--;
            }
            return $slot;
        };

        // FASE 1: GREEDY ASSIGNMENT
        $lastMapelIdx = []; 
        $unresolvedConflicts = []; // Array of needs that couldn't be scheduled
        
        foreach ($hariList as $hari) {
            $maxJamHari = $jamPerHari[$hari] ?? 0;
            for ($jamKe = 1; $jamKe <= $maxJamHari; $jamKe++) {
                foreach ($kelasList as $kelas) {
                    $kId = $kelas->id_kelas;
                    $needs = &$kelasMapelNeeds[$kId];
                    if (empty($needs)) continue;
                    
                    $assigned = false;
                    $terpilihIdx = null;
                    $guruTersedia = null;

                    if (isset($lastMapelIdx[$kId]) && isset($needs[$lastMapelIdx[$kId]])) {
                        $idx = $lastMapelIdx[$kId];
                        if ($needs[$idx]['sisa_jam'] > 0) {
                            $mapel = $needs[$idx]['mapel'];
                            $guruIds = $needs[$idx]['guru_ids'];
                            
                            if ($mapel->isProduktif() && empty($guruIds)) {
                                $guruTersedia = 'null'; $terpilihIdx = $idx;
                            } else {
                                foreach ($guruIds as $gId) {
                                    if (isset($guruOccupied[$gId][$hari][$jamKe])) continue;
                                    $maxJam = $guruMaxJam[$gId] ?? 24;
                                    if (($guruTotalJam[$gId] ?? 0) >= $maxJam) continue;
                                    $guruTersedia = $gId; $terpilihIdx = $idx; break;
                                }
                            }
                        }
                    }

                    if ($guruTersedia === null) {
                        foreach ($needs as $idx => $need) {
                            if ($need['sisa_jam'] <= 0) continue;
                            $mapel = $need['mapel'];
                            $guruIds = $need['guru_ids'];
                            
                            if ($mapel->isProduktif() && empty($guruIds)) {
                                $guruTersedia = 'null'; $terpilihIdx = $idx; break;
                            } else {
                                foreach ($guruIds as $gId) {
                                    if (isset($guruOccupied[$gId][$hari][$jamKe])) continue;
                                    $maxJam = $guruMaxJam[$gId] ?? 24;
                                    if (($guruTotalJam[$gId] ?? 0) >= $maxJam) continue;
                                    $guruTersedia = $gId; $terpilihIdx = $idx; break;
                                }
                            }
                            if ($guruTersedia !== null) break;
                        }
                    }

                    if ($guruTersedia !== null) {
                        $assignSlot($hari, $jamKe, $kId, $needs[$terpilihIdx]['mapel'], $guruTersedia, $terpilihIdx);
                        $needs[$terpilihIdx]['sisa_jam']--;
                        $lastMapelIdx[$kId] = $terpilihIdx;
                        if ($needs[$terpilihIdx]['sisa_jam'] <= 0) {
                            unset($needs[$terpilihIdx]); unset($lastMapelIdx[$kId]);
                        }
                        $assigned = true;
                    } else {
                        // Conflict occurred! Log for Phase 2
                        unset($lastMapelIdx[$kId]);
                    }
                }
            }
        }

        // Collect remaining needs for Phase 2
        foreach ($kelasMapelNeeds as $kId => $needs) {
            foreach ($needs as $idx => $need) {
                while ($need['sisa_jam'] > 0) {
                    $unresolvedConflicts[] = [
                        'kId' => $kId,
                        'mapel' => $need['mapel'],
                        'guru_ids' => $need['guru_ids'],
                        'idx' => $idx
                    ];
                    $need['sisa_jam']--;
                }
            }
        }

        // FASE 2: REPAIR HEURISTIC (SWAP & RELOCATE)
        $resolveConflict = function($kId, $mapel, $guruIds, $idx) use (&$matrix, &$guruOccupied, &$kelasOccupied, &$assignSlot, &$unassignSlot, $hariList, $jamPerHari, $guruMaxJam, &$guruTotalJam) {
            // Find a slot where class K is empty
            foreach ($hariList as $hari) {
                $maxJamHari = $jamPerHari[$hari] ?? 0;
                for ($jamKe = 1; $jamKe <= $maxJamHari; $jamKe++) {
                    if (isset($kelasOccupied[$kId][$hari][$jamKe])) continue; // K must be empty

                    // Try to place it here. We need one of the $guruIds to be available.
                    foreach ($guruIds as $gId) {
                        // Is $gId free?
                        if (!isset($guruOccupied[$gId][$hari][$jamKe])) {
                            // If free, why wasn't it assigned in phase 1? Maybe maxJam exceeded.
                            $maxJam = $guruMaxJam[$gId] ?? 24;
                            if (($guruTotalJam[$gId] ?? 0) < $maxJam) {
                                $assignSlot($hari, $jamKe, $kId, $mapel, $gId, $idx);
                                return true;
                            }
                        } else {
                            // $gId is BUSY. They are teaching $kClash.
                            $kClash = $guruOccupied[$gId][$hari][$jamKe];
                            
                            // === ATTEMPT RELOCATE ===
                            // Can we move ($kClash, mapelClash, $gId) to a different empty slot for $kClash?
                            foreach ($hariList as $rHari) {
                                $rMaxJam = $jamPerHari[$rHari] ?? 0;
                                for ($rJam = 1; $rJam <= $rMaxJam; $rJam++) {
                                    if ($rHari === $hari && $rJam === $jamKe) continue;
                                    
                                    if (!isset($kelasOccupied[$kClash][$rHari][$rJam]) && !isset($guruOccupied[$gId][$rHari][$rJam])) {
                                        // Relocate success!
                                        $clashSlot = $unassignSlot($hari, $jamKe, $kClash);
                                        $assignSlot($rHari, $rJam, $kClash, $clashSlot['mapel'], $clashSlot['guru_id'], $clashSlot['idx']);
                                        
                                        // Now slot ($hari, $jamKe) is free for $gId and $kId!
                                        $assignSlot($hari, $jamKe, $kId, $mapel, $gId, $idx);
                                        return true;
                                    }
                                }
                            }

                            // === ATTEMPT SWAP ===
                            // We need to swap $kClash's ($hari, $jamKe, $gId) with $kClash's ($sHari, $sJam, $gOther)
                            foreach ($hariList as $sHari) {
                                $sMaxJam = $jamPerHari[$sHari] ?? 0;
                                for ($sJam = 1; $sJam <= $sMaxJam; $sJam++) {
                                    if ($sHari === $hari && $sJam === $jamKe) continue;
                                    
                                    if (isset($matrix[$sHari][$sJam][$kClash])) {
                                        $otherSlot = $matrix[$sHari][$sJam][$kClash];
                                        $gOther = $otherSlot['guru_id'];
                                        
                                        if ($gOther === 'null' || $gOther === null) continue;

                                        // To swap, $gId must be free at ($sHari, $sJam) and $gOther must be free at ($hari, $jamKe)
                                        if (!isset($guruOccupied[$gId][$sHari][$sJam]) && !isset($guruOccupied[$gOther][$hari][$jamKe])) {
                                            // Swap success!
                                            $clashSlot = $unassignSlot($hari, $jamKe, $kClash);
                                            $otherSlotUn = $unassignSlot($sHari, $sJam, $kClash);
                                            
                                            $assignSlot($sHari, $sJam, $kClash, $clashSlot['mapel'], $clashSlot['guru_id'], $clashSlot['idx']);
                                            $assignSlot($hari, $jamKe, $kClash, $otherSlotUn['mapel'], $otherSlotUn['guru_id'], $otherSlotUn['idx']);
                                            
                                            // Now slot ($hari, $jamKe) is free for $gId and $kId!
                                            $assignSlot($hari, $jamKe, $kId, $mapel, $gId, $idx);
                                            return true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return false;
        };

        // Run Phase 2
        foreach ($unresolvedConflicts as $conflict) {
            $success = false;
            // Limit attempts if needed, but here it's O(Slots * Slots), which is ~50*50 = 2500 iterations per conflict. Very fast.
            if ($conflict['mapel']->isProduktif() && empty($conflict['guru_ids'])) {
                // Just find any empty slot for K
                foreach ($hariList as $hari) {
                    $maxJamHari = $jamPerHari[$hari] ?? 0;
                    for ($jamKe = 1; $jamKe <= $maxJamHari; $jamKe++) {
                        if (!isset($kelasOccupied[$conflict['kId']][$hari][$jamKe])) {
                            $assignSlot($hari, $jamKe, $conflict['kId'], $conflict['mapel'], 'null', $conflict['idx']);
                            $success = true;
                            break 2;
                        }
                    }
                }
            } else {
                $success = $resolveConflict($conflict['kId'], $conflict['mapel'], $conflict['guru_ids'], $conflict['idx']);
            }

            if (!$success) {
                $kelas = $kelasList->firstWhere('id_kelas', $conflict['kId']);
                $warnings[] = "Mapel '{$conflict['mapel']->nama_mapel}' di kelas {$kelas->nama_kelas} kurang 1 JP (Bentrok permanen/Guru habis jam).";
            }
        }

        // FASE 3: CONVERT MATRIX TO DB INSERT FORMAT
        foreach ($matrix as $hari => $jams) {
            foreach ($jams as $jamKe => $kelasSlots) {
                foreach ($kelasSlots as $kId => $slot) {
                    $realGuruId = $slot['guru_id'] === 'null' ? null : $slot['guru_id'];
                    $jadwalBaru[] = [
                        'guru_id'      => $realGuruId,
                        'mapel_id'     => $slot['mapel']->id_mapel,
                        'kelas_id'     => $kId,
                        'hari'         => $hari,
                        'jam_ke'       => $jamKe,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester'     => $semester,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
            }
        }

        // Insert semua jadwal
        if (!empty($jadwalBaru)) {
            foreach (array_chunk($jadwalBaru, 100) as $chunk) {
                JadwalPelajaran::insert($chunk);
            }
        }

        $msg = 'Jadwal berhasil di-generate! Total: ' . count($jadwalBaru) . ' slot.';
        if (!empty($warnings)) {
            $msg .= ' (Ada ' . count($warnings) . ' peringatan)';
        }

        return redirect()
            ->route('admin.jadwal.index', [
                'tahun_ajaran' => $tahunAjaran,
                'semester'     => $semester,
            ])
            ->with('success', $msg)
            ->with('warnings', $warnings);
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

            // 4. Baca nomor Jam Ke dari baris tersebut
            $jamKeCols = []; // col => jam_ke_number
            foreach ($rows[$jamKeRow] as $c => $cell) {
                $v = trim($cell);
                if (is_numeric($v) && (int)$v >= 1 && (int)$v <= 15) {
                    $jamKeCols[$c] = (int)$v;
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
                    $kodeGuru = trim($parts[1] ?? '');

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

        // Deduplikasi kelas: jika ada kelas+hari+jam_ke yang sama, ambil yang terakhir
        $uniqueJadwal = [];
        foreach ($jadwalBaru as $j) {
            $key = $j['kelas_id'] . '-' . $j['hari'] . '-' . $j['jam_ke'];
            $uniqueJadwal[$key] = $j;
        }
        
        // Deduplikasi guru: jika ada guru+hari+jam_ke yang sama (guru mengajar 2 kelas bersamaan), 
        // kosongkan guru_id pada entri duplikat agar tidak melanggar constraint
        $guruUsed = [];
        foreach ($uniqueJadwal as $key => &$j) {
            if ($j['guru_id']) {
                $gKey = $j['guru_id'] . '-' . $j['hari'] . '-' . $j['jam_ke'];
                if (isset($guruUsed[$gKey])) {
                    $j['guru_id'] = null; // Kosongkan guru duplikat, bisa diisi manual nanti
                } else {
                    $guruUsed[$gKey] = true;
                }
            }
        }
        unset($j);
        
        $jadwalBaru = array_values($uniqueJadwal);
        $inserted = count($jadwalBaru);

        if (!empty($jadwalBaru)) {
            foreach (array_chunk($jadwalBaru, 100) as $chunk) {
                JadwalPelajaran::insertOrIgnore($chunk);
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
