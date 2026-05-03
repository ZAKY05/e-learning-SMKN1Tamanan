 <nav class="nxl-navigation">
     <div class="navbar-wrapper">
         <div class="m-header">
             <a href="{{ route('guru.dashboard') }}" class="b-brand">
                 {{-- <img src="assets/images/logo-full.png" alt="" class="logo logo-lg" />
                    <img src="assets/images/logo-abbr.png" alt="" class="logo logo-sm" /> --}}
                 <h3>GURU</h3>
             </a>
         </div>
         <div class="navbar-content">
             <ul class="nxl-navbar">
                 <li class="nxl-item nxl-caption">
                     <label>Navigation</label>
                 </li>

                 {{-- Dashboard --}}
                 <li class="nxl-item">
                     <a href="{{ route('guru.dashboard') }}" class="nxl-link">
                         <span class="nxl-micon"><i class="feather-home"></i></span>
                         <span class="nxl-mtext">Dashboard</span>
                     </a>
                 </li>
                 <li class="nxl-item">
                     <a href="{{ route('guru.dashboard') }}" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-user-clock"></i></span>
                         <span class="nxl-mtext">Presensi</span>
                     </a>
                 </li>

                 {{-- Menu Data --}}
                 <li class="nxl-item nxl-hasmenu mt-2">
                     <a href="javascript:void(0);" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-copy"></i></span>
                         <span class="nxl-mtext">Rekap</span><span class="nxl-arrow"><i
                                 class="feather-chevron-right"></i></span>
                     </a>
                     <ul class="nxl-submenu">
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.siswa.index') }}">Rekap Presensi</a></li>
                         <li class="nxl-item"><a class="nxl-link" href="reports-leads.html">Rekap Nilai</a></li>
                         <li class="nxl-item"><a class="nxl-link" href="reports-project.html">Rekap UTS & UAS</a></li>
                     </ul>
                 </li>

                 {{-- Akademik --}}
                 <li class="nxl-item nxl-hasmenu mt-2">
                     <a href="javascript:void(0);" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-graduation-cap"></i></span>
                         <span class="nxl-mtext">Pembelajaran</span><span class="nxl-arrow"><i
                                 class="feather-chevron-right"></i></span>
                     </a>
                     <ul class="nxl-submenu">
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.kelas.index') }}">Upload Tugas</a>
                         </li>
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('guru.materi.index') }}">Upload Materi</a>
                         </li>
                        
                     </ul>
                 </li>
                 <li class="nxl-item nxl-hasmenu mt-2">
                     <a href="javascript:void(0);" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-chalkboard-user"></i></span>
                         <span class="nxl-mtext">Pokok Ujian</span><span class="nxl-arrow"><i
                                 class="feather-chevron-right"></i></span>
                     </a>
                     <ul class="nxl-submenu">
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.kelas.index') }}">Ujian Tengah Semester</a>
                         </li>
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.jurusan.index') }}">Ujian Akhir Semester</a>
                         </li>
                         <li class="nxl-item"><a class="nxl-link" href="apps-tasks.html">Kuis Harian</a></li>
                     </ul>
                 </li>
                 <li class="nxl-item">
                     <a href="{{ route('guru.bank-lokasi-guru.index') }}" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-map-location-dot"></i></span>
                         <span class="nxl-mtext">Bank-Lokasi</span>
                     </a>
                 </li>
             </ul>
         </div>
     </div>
 </nav>
