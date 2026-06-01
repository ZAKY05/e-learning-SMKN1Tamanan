@extends('Guru.layout.master')

@section('page_title', 'Profile Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@push('styles')
<style>
    .profile-cover {
        position: relative;
        height: 200px;
        background: linear-gradient(135deg, #046C00 0%, #069e02 100%);
        border-radius: 12px 12px 0 0;
        overflow: hidden;
    }
    .profile-cover::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.5;
    }
    .profile-avatar-wrapper {
        margin-top: -55px;
        z-index: 2;
    }
    .profile-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        object-fit: cover;
        background: #f0f0f0;
    }
    .profile-info-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 12px;
        font-size: 15px;
        background: rgba(4, 108, 0, 0.1);
        color: #046C00;
    }
    .info-label {
        font-size: 11px;
        color: #8c8c8c;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }
    .info-value {
        font-size: 14px;
        color: #2d3436;
        font-weight: 600;
    }
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        background: #046C00;
        color: #fff;
    }
    .mapel-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(4, 108, 0, 0.08);
        color: #046C00;
        border: 1px solid rgba(4, 108, 0, 0.2);
        margin: 3px;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    @php
        $guru = auth()->user()->guru;
        $fotoPath = $guru->foto_profil ?? null;
        $avatarUrl = ($fotoPath && file_exists(public_path($fotoPath)))
            ? asset($fotoPath)
            : asset('Template/assets/images/avatar/1.png');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card profile-info-card">
                <div class="profile-cover"></div>
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4" style="margin-top: -55px; position: relative; z-index: 2;">
                        <img src="{{ $guru->foto_profil ?? auth()->user()->foto_profil }}" alt="Avatar" class="profile-avatar" />
                        <div class="text-center text-md-start" style="margin-top: 55px;">
                            <h4 class="fw-bold mb-1">{{ $guru->nama ?? auth()->user()->name }}</h4>
                            <p class="text-muted mb-2">{{ auth()->user()->email }}</p>
                            <span class="role-badge">
                                <i class="feather-book-open" style="font-size: 13px;"></i>
                                Guru
                            </span>
                        </div>
                    </div>

                    @if($guru && $guru->mapels && $guru->mapels->count() > 0)
                        <hr class="my-3">
                        <div>
                            <div class="info-label mb-2">Mata Pelajaran yang Diampu</div>
                            <div class="d-flex flex-wrap">
                                @foreach($guru->mapels as $mapel)
                                    <span class="mapel-badge">
                                        <i class="feather-book" style="font-size: 11px;"></i>
                                        {{ $mapel->nama_mapel }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <div class="info-icon"><i class="feather-user"></i></div>
                                <div>
                                    <div class="info-label">Nama Lengkap</div>
                                    <div class="info-value">{{ $guru->nama ?? auth()->user()->name }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <div class="info-icon"><i class="feather-hash"></i></div>
                                <div>
                                    <div class="info-label">NIP</div>
                                    <div class="info-value">{{ $guru->nip ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <div class="info-icon"><i class="feather-mail"></i></div>
                                <div>
                                    <div class="info-label">Email</div>
                                    <div class="info-value">{{ auth()->user()->email }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <div class="info-icon"><i class="feather-phone"></i></div>
                                <div>
                                    <div class="info-label">No. Telepon</div>
                                    <div class="info-value">{{ $guru->no_telp ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <div class="info-icon"><i class="feather-map-pin"></i></div>
                                <div>
                                    <div class="info-label">Alamat</div>
                                    <div class="info-value">{{ $guru->alamat ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <div class="info-icon"><i class="feather-calendar"></i></div>
                                <div>
                                    <div class="info-label">Bergabung Sejak</div>
                                    <div class="info-value">{{ auth()->user()->created_at ? auth()->user()->created_at->format('d M Y') : '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
