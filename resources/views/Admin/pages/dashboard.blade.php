@extends('Admin.layout.master')

@section('content')
    <div class="main-content">
        <div class="row">
            <div class="col-12 mb-4">
                <h4 class="mb-0">Selamat Datang di Dashboard Admin</h4>
                <p class="text-muted">Ringkasan data E-Learning SMKN 1 Tamanan</p>
            </div>
            
            <div class="col-12">
                <div class="row g-4">
                    <!-- Widget Total Siswa -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card stretch stretch-full border border-dashed rounded">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fs-12 text-muted mb-1">Total Siswa</div>
                                        <h4 class="fw-bold text-dark mb-0">{{ $totalSiswa }}</h4>
                                    </div>
                                    <div class="avatar-text avatar-lg bg-primary-subtle text-primary rounded-circle">
                                        <i class="feather-users fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.siswa.index') }}" class="fs-12 fw-medium text-primary d-flex align-items-center">
                                        Lihat Data <i class="feather-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Widget Total Guru -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card stretch stretch-full border border-dashed rounded">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fs-12 text-muted mb-1">Total Guru</div>
                                        <h4 class="fw-bold text-dark mb-0">{{ $totalGuru }}</h4>
                                    </div>
                                    <div class="avatar-text avatar-lg bg-success-subtle text-success rounded-circle">
                                        <i class="feather-user-check fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.guru.index') }}" class="fs-12 fw-medium text-success d-flex align-items-center">
                                        Lihat Data <i class="feather-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Widget Total Kelas -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card stretch stretch-full border border-dashed rounded">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fs-12 text-muted mb-1">Total Kelas</div>
                                        <h4 class="fw-bold text-dark mb-0">{{ $totalKelas }}</h4>
                                    </div>
                                    <div class="avatar-text avatar-lg bg-warning-subtle text-warning rounded-circle">
                                        <i class="feather-layers fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.kelas.index') }}" class="fs-12 fw-medium text-warning d-flex align-items-center">
                                        Lihat Data <i class="feather-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Widget Total Mapel -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card stretch stretch-full border border-dashed rounded">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fs-12 text-muted mb-1">Total Mata Pelajaran</div>
                                        <h4 class="fw-bold text-dark mb-0">{{ $totalMapel }}</h4>
                                    </div>
                                    <div class="avatar-text avatar-lg bg-info-subtle text-info rounded-circle">
                                        <i class="feather-book-open fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.mapel.index') }}" class="fs-12 fw-medium text-info d-flex align-items-center">
                                        Lihat Data <i class="feather-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
@endsection
