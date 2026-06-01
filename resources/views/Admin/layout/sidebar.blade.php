<nav class="nxl-navigation">
     <div class="navbar-wrapper">
         <div class="m-header">
            <a href="{{ route('admin.dashboard') }}" class="b-brand d-flex align-items-center gap-2">
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
                     <a href="{{ route('admin.dashboard') }}" class="nxl-link">
                         <span class="nxl-micon"><i class="feather-home"></i></span>
                         <span class="nxl-mtext">Dashboard</span>
                     </a>
                 </li>

                 {{-- Menu Data --}}
                 <li class="nxl-item nxl-hasmenu">
                     <a href="javascript:void(0);" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-database"></i></span>
                         <span class="nxl-mtext">Data</span><span class="nxl-arrow"><i
                                 class="feather-chevron-right"></i></span>
                     </a>
                     <ul class="nxl-submenu">
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.siswa.index') }}">Data Siswa</a>
                         </li>
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.guru.index') }}">Data Guru</a>
                         </li>
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.mapel.index') }}">Data Mapel</a></li>
                     </ul>
                 </li>

                 {{-- Akademik --}}
                 <li class="nxl-item nxl-hasmenu">
                     <a href="javascript:void(0);" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-graduation-cap"></i></span>
                         <span class="nxl-mtext">Akademik</span><span class="nxl-arrow"><i
                                 class="feather-chevron-right"></i></span>
                     </a>
                     <ul class="nxl-submenu">
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.kelas.index') }}">Kelas</a></li>
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.jurusan.index') }}">Jurusan</a>
                         </li>
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.jadwal.index') }}">Jadwal</a></li>
                     </ul>
                 </li>
                 <li class="nxl-item nxl-hasmenu">
                     <a href="javascript:void(0);" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-circle-user"></i></span>
                         <span class="nxl-mtext">Manajemen Akun</span><span class="nxl-arrow"><i
                                 class="feather-chevron-right"></i></span>
                     </a>
                     <ul class="nxl-submenu">
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.akun-guru.index') }}">Guru</a></li>
                         <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.akun-siswa.index') }}">Siswa</a></li>
                     </ul>
                 </li>
                 <li class="nxl-item">
                     <a href="{{ route('admin.bank-lokasi.index') }}" class="nxl-link">
                         <span class="nxl-micon"><i class="fa-solid fa-map-location-dot"></i></span>
                         <span class="nxl-mtext">Bank Lokasi</span>
                     </a>
                 </li>


             </ul>
         </div>
     </div>
 </nav>
