@extends('Guru.layout.master')

@section('page_title', 'Dashboard Guru')

@section('content')
    <style>
        /* ===== Dashboard Custom Styles ===== */

        /* Greeting Header */
        .dashboard-greeting {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 28px;
        }

        .dashboard-greeting h4 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }

        .dashboard-greeting p {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }

        .btn-export-laporan {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            text-decoration: none;
        }

        .btn-export-laporan:hover {
            background: #16a34a;
            color: #fff;
            border-color: #16a34a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
        }

        /* ===== Stat Cards ===== */
        .stat-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        @media (max-width: 992px) {
            .stat-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .stat-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: 16px 0 0 16px;
            transition: width 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .stat-card-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .stat-card-icon.green {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #16a34a;
        }

        .stat-card.green::before { background: #16a34a; }

        .stat-card-icon.blue {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #2563eb;
        }

        .stat-card.blue::before { background: #2563eb; }

        .stat-card-icon.amber {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #d97706;
        }

        .stat-card.amber::before { background: #d97706; }

        .stat-card-icon.rose {
            background: linear-gradient(135deg, #fce7f3, #fbcfe8);
            color: #e11d48;
        }

        .stat-card.rose::before { background: #e11d48; }

        .stat-card-info h6 {
            font-size: 0.82rem;
            color: #6b7280;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .stat-card-info .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1a1a2e;
            line-height: 1;
        }

        /* ===== Attendance Banner ===== */
        .attendance-banner {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 40%, #166534 100%);
            border-radius: 20px;
            padding: 36px 40px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            min-height: 200px;
        }

        .attendance-banner::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -40px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .attendance-banner::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: 30%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .attendance-left {
            position: relative;
            z-index: 1;
            max-width: 55%;
        }

        .attendance-left h3 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .attendance-left p {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .btn-detail-kehadiran {
            background: #ffffff;
            color: #16a34a;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-detail-kehadiran:hover {
            background: #f0fdf4;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: #16a34a;
        }

        .attendance-right {
            position: relative;
            z-index: 1;
        }

        .attendance-circle {
            width: 150px;
            height: 150px;
            position: relative;
        }

        .attendance-circle svg {
            width: 150px;
            height: 150px;
            transform: rotate(-90deg);
        }

        .attendance-circle .circle-bg {
            fill: none;
            stroke: rgba(255, 255, 255, 0.15);
            stroke-width: 10;
        }

        .attendance-circle .circle-progress {
            fill: none;
            stroke: #ffffff;
            stroke-width: 10;
            stroke-linecap: round;
            transition: stroke-dashoffset 1.5s ease-in-out;
        }

        .attendance-circle-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .attendance-circle-text .percent-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            display: block;
        }

        .attendance-circle-text .percent-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.8;
            display: block;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .attendance-banner {
                flex-direction: column;
                text-align: center;
                padding: 28px 24px;
            }

            .attendance-left {
                max-width: 100%;
                margin-bottom: 24px;
            }

            .attendance-left h3 {
                font-size: 1.3rem;
            }
        }

        /* ===== Schedule Section ===== */
        .schedule-section {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            overflow: hidden;
        }

        .schedule-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 28px;
            border-bottom: 1px solid #f0f0f0;
        }

        .schedule-header h5 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0;
        }

        .schedule-header a {
            color: #16a34a;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .schedule-header a:hover {
            color: #15803d;
            gap: 10px;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table thead th {
            background: #f9fafb;
            padding: 14px 28px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .schedule-table tbody tr {
            border-bottom: 1px solid #f5f5f5;
            transition: background 0.15s ease;
        }

        .schedule-table tbody tr:last-child {
            border-bottom: none;
        }

        .schedule-table tbody tr:hover {
            background: #f0fdf4;
        }

        .schedule-table tbody td {
            padding: 18px 28px;
            font-size: 0.88rem;
            color: #374151;
            vertical-align: middle;
        }

        .schedule-time {
            font-weight: 600;
            color: #1a1a2e;
            white-space: nowrap;
        }

        .schedule-mapel-name {
            font-weight: 600;
            color: #1a1a2e;
            display: block;
        }

        .schedule-mapel-sub {
            font-size: 0.78rem;
            color: #9ca3af;
            display: block;
            margin-top: 2px;
        }

        .schedule-kelas-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #16a34a;
            letter-spacing: 0.3px;
        }

        .schedule-room {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .schedule-room i {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .schedule-empty {
            text-align: center;
            padding: 48px 28px !important;
            color: #9ca3af;
        }

        .schedule-empty i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            display: block;
            color: #d1d5db;
        }

        .schedule-empty span {
            font-size: 0.92rem;
            display: block;
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .animate-in:nth-child(1) { animation-delay: 0.05s; }
        .animate-in:nth-child(2) { animation-delay: 0.1s; }
        .animate-in:nth-child(3) { animation-delay: 0.15s; }
        .animate-in:nth-child(4) { animation-delay: 0.2s; }

        .animate-banner { animation: fadeInUp 0.6s ease 0.25s forwards; opacity: 0; }
        .animate-schedule { animation: fadeInUp 0.6s ease 0.35s forwards; opacity: 0; }
    </style>

    <div class="main-content">
        {{-- Greeting Header --}}
        <div class="dashboard-greeting">
            <div>
                <h4>Dashboard Guru</h4>
                <p>
                    Selamat {{ \Carbon\Carbon::now()->hour < 12 ? 'pagi' : (\Carbon\Carbon::now()->hour < 15 ? 'siang' : (\Carbon\Carbon::now()->hour < 18 ? 'sore' : 'malam')) }},
                    {{ $guru ? $guru->nama : (Auth::user()->name ?? 'Guru') }}.
                    Mari kita lihat perkembangan siswa hari ini.
                </p>
            </div>
            <a href="javascript:void(0);" class="btn-export-laporan" onclick="window.print()">
                <i class="feather-download"></i> Ekspor Laporan
            </a>
        </div>

        {{-- Stat Cards --}}
        <div class="stat-cards-grid">
            <div class="stat-card green animate-in">
                <div class="stat-card-icon green">
                    <i class="feather-layers"></i>
                </div>
                <div class="stat-card-info">
                    <h6>Jumlah Kelas</h6>
                    <div class="stat-number">{{ $totalKelas }}</div>
                </div>
            </div>

            <div class="stat-card blue animate-in">
                <div class="stat-card-icon blue">
                    <i class="feather-upload-cloud"></i>
                </div>
                <div class="stat-card-info">
                    <h6>Materi Unggahan</h6>
                    <div class="stat-number">{{ $totalMateri }}</div>
                </div>
            </div>

            <div class="stat-card amber animate-in">
                <div class="stat-card-icon amber">
                    <i class="feather-clipboard"></i>
                </div>
                <div class="stat-card-info">
                    <h6>Tugas Aktif</h6>
                    <div class="stat-number">{{ $totalTugasAktif }}</div>
                </div>
            </div>

            <div class="stat-card rose animate-in">
                <div class="stat-card-icon rose">
                    <i class="feather-alert-circle"></i>
                </div>
                <div class="stat-card-info">
                    <h6>Tugas Belum Dinilai</h6>
                    <div class="stat-number">{{ $totalTugasBelumDinilai }}</div>
                </div>
            </div>
        </div>

        {{-- Attendance Banner --}}
        <div class="attendance-banner animate-banner">
            <div class="attendance-left">
                <h3>Statistik Kehadiran Siswa</h3>
                <p>
                    Rata-rata kehadiran seluruh kelas Anda bulan ini
                    {{ $rataKehadiran >= 80 ? 'menunjukkan tren positif.' : 'perlu perhatian lebih.' }}
                    Terus pantau kedisiplinan siswa untuk hasil belajar maksimal.
                </p>
                <a href="{{ route('guru.presensi.index') }}" class="btn-detail-kehadiran">
                    <i class="feather-eye"></i> Lihat Detail Kehadiran
                </a>
            </div>
            <div class="attendance-right">
                @php
                    $radius = 60;
                    $circumference = 2 * 3.14159 * $radius;
                    $offset = $circumference - ($rataKehadiran / 100) * $circumference;
                @endphp
                <div class="attendance-circle">
                    <svg viewBox="0 0 150 150">
                        <circle class="circle-bg" cx="75" cy="75" r="{{ $radius }}" />
                        <circle class="circle-progress" cx="75" cy="75" r="{{ $radius }}"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $offset }}" />
                    </svg>
                    <div class="attendance-circle-text">
                        <span class="percent-value">{{ $rataKehadiran }}%</span>
                        <span class="percent-label">Rata-rata</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Schedule Section --}}
        <div class="schedule-section animate-schedule">
            <div class="schedule-header">
                <h5>Jadwal Mengajar</h5>
                <a href="javascript:void(0);">
                    Lihat Jadwal Mingguan <i class="feather-arrow-right"></i>
                </a>
            </div>

            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Ruangan</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($jadwalGrouped) && count($jadwalGrouped) > 0)
                        @foreach($jadwalGrouped as $item)
                            <tr>
                                <td class="schedule-time">{{ $item['waktu'] }}</td>
                                <td>
                                    <span class="schedule-mapel-name">{{ $item['mapel'] }}</span>
                                    @if($item['sub_mapel'])
                                        <span class="schedule-mapel-sub">{{ $item['sub_mapel'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="schedule-kelas-badge">{{ $item['kelas'] }}</span>
                                </td>
                                <td>
                                    <div class="schedule-room">
                                        <i class="feather-map-pin"></i> {{ $item['ruangan'] }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="schedule-empty">
                                <i class="feather-coffee"></i>
                                <span>Tidak ada jadwal mengajar hari {{ ucfirst($hariIni) }}</span>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
