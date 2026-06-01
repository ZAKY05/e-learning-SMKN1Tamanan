 <nav class="nxl-navigation">
     <div class="navbar-wrapper">
         <div class="m-header">
              <a href="{{ route('guru.dashboard') }}" class="b-brand d-flex align-items-center gap-2">
                  <!-- Large Logo (visible when sidebar is expanded) -->
                  <div class="logo logo-lg d-flex align-items-center gap-2">
                      <img src="{{ asset('img/logo-smk.png') }}" alt="Logo SMK" style="max-height: 45px; width: auto;" />
                      <div class="d-flex flex-column justify-content-center text-start">
                          <span class="fw-bold text-dark fs-12 lh-1 text-uppercase text-spacing-1">E-Learning</span>
                          <span class="fw-bold text-dark fs-10 lh-1 mt-1">SMKN 1 Tamanan</span>
                      </div>
                  </div>
                  <!-- Small Logo (visible when sidebar is collapsed) -->
                  <img src="{{ asset('img/logo-smk.png') }}" alt="Logo SMK" class="logo logo-sm" style="max-height: 40px; width: auto;" />
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
                     <a href="{{ route('guru.presensi.index') }}" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-user-clock"></i></span>
                         <span class="nxl-mtext">Presensi</span>
                     </a>
                 </li>

                 
                 <li class="nxl-item nxl-hasmenu mt-2">
                     <a href="javascript:void(0);" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-copy"></i></span>
                         <span class="nxl-mtext">Rekap</span><span class="nxl-arrow"><i
                                 class="feather-chevron-right"></i></span>
                     </a>
                     <ul class="nxl-submenu">
                         <li class="nxl-item">
                             <a class="nxl-link" href="{{ route('guru.rekap.absensi') }}">
                                 <i class="feather-calendar me-2"></i> Rekap Absensi
                             </a>
                         </li>
                         <li class="nxl-item">
                             <a class="nxl-link" href="{{ route('guru.rekap.nilai') }}">
                                 <i class="feather-award me-2"></i> Rekap Nilai
                             </a>
                         </li>
                         <li class="nxl-item">
                             <a class="nxl-link" href="{{ route('guru.rekap.uts_uas') }}">
                                 <i class="feather-file-text me-2"></i> Rekap UTS & UAS
                             </a>
                         </li>
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
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('guru.tugas.index') }}">Pengumpulan Tugas</a>
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
                         </li>
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('guru.kuis.index') }}">Manajemen Kuis / Ujian</a></li>
                     </ul>
                     
                 </li>

             </ul>
         </div>
     </div>
 </nav>
