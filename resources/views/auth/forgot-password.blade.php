<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - SMK Negeri 1 Tamanan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        .bg-emerald-600 {
            background-color: #046C00 !important;
        }
        
        .bg-emerald-100 {
            background-color: #e6f2e6 !important;
        }
        
        .text-emerald-600 {
            color: #046C00 !important;
        }
        
        .btn-emerald {
            background-color: #046C00;
            border-color: #046C00;
            color: white;
        }
        
        .btn-emerald:hover {
            background-color: #035200;
            border-color: #035200;
            color: white;
        }
        
        .form-control:focus {
            border-color: #046C00;
            box-shadow: 0 0 0 0.25rem rgba(4, 108, 0, 0.25);
        }
        
        .left-panel {
            min-height: 100vh;
            background: linear-gradient(180deg, #046C00 0%, #046C00 100%);
        }
        
        .floating-badge {
            position: absolute;
            bottom: -16px;
            right: 16px;
            background: white;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background-color: #e6f2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
        }

        .icon-circle i {
            font-size: 1.8rem;
            color: #046C00;
        }

        .info-box {
            background-color: #f3f4f6;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            gap: 12px;
            margin-top: 1.5rem;
            align-items: flex-start;
        }

        .info-box i {
            color: #046C00;
            font-size: 1.2rem;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            
            <!-- Left Panel - Green -->
            <div class="col-lg-6 d-none d-lg-flex left-panel text-white p-5 flex-column justify-content-between">
                
                <!-- Logo & Header -->
                <div>
                    <div class="d-flex align-items-center gap-3 mb-5">
                        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <img src="{{ asset('img/logo-smk.png') }}" alt="" style="width: 70%; height: auto;">
                        </div>
                        <span class="fw-semibold fs-5">SMK NEGERI 1 TAMANAN</span>
                    </div>

                    <h1 class="display-5 fw-bold mb-4 lh-sm">
                        Kelola Sistem<br>Pembelajaran<br>dengan Mudah
                    </h1>
                    <p class="text-white-50 fs-6 lh-base mb-5" style="max-width: 400px;">
                        Atur materi, pantau perkembangan siswa, dan kelola<br>
                        aktivitas belajar dalam satu platform.
                    </p>
                </div>

                <!-- Image Section -->
                <div class="position-relative mb-4">
                    <div class="bg-dark bg-opacity-25 rounded-4 overflow-hidden">
                        <img 
                            src="{{ asset('img/smk-tamanan-1.jpg') }}" 
                            alt="Students in lab" 
                            class="w-100" 
                            style="height: 256px; object-fit: cover; opacity: 0.9;"
                        >
                    </div>
                    <!-- Floating Badge -->
                    <div class="floating-badge d-flex align-items-center gap-2">
                        <div class="bg-emerald-100 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-people-fill text-emerald-600"></i>
                        </div>
                        <span class="fw-semibold text-dark">100+ Siswa</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-white-50 small">
                    © 2025 SMK Negeri 1 Tamanan. Seluruh Hak Dilindungi.
                </div>
            </div>

            <!-- Right Panel - Forgot Password Form -->
            <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center p-4" style="background-color: #fafafa;">
                <div class="w-100" style="max-width: 420px;">
                    
                    <div class="text-center mb-4">
                        <div class="icon-circle">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </div>
                        <h2 class="h4 fw-bold text-dark mb-2">Lupa Kata Sandi?</h2>
                        <p class="text-secondary small px-3">Masukkan alamat email Anda yang terdaftar untuk menerima instruksi pemulihan kata sandi.</p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success small py-2 mb-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Forgot Password Form -->
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold text-dark" style="font-size: 0.8rem;">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-secondary">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input 
                                    id="email" 
                                    type="email" 
                                    name="email" 
                                    value="{{ old('email') }}" 
                                    required 
                                    autofocus
                                    class="form-control border-start-0 ps-0"
                                    placeholder="contoh@smkn1tamanan.sch.id"
                                >
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-emerald w-100 py-3 fw-semibold rounded-3">
                            Kirim Email Pemulihan
                        </button>
                    </form>

                    <!-- Info Box -->
                    <div class="info-box">
                        <i class="bi bi-info-circle"></i>
                        <div>
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">Informasi Bantuan:</h6>
                            <p class="text-secondary mb-0" style="font-size: 0.75rem;">
                                Jika Anda tidak lagi memiliki akses ke email ini, silakan hubungi pusat bantuan kami untuk verifikasi identitas manual.
                            </p>
                        </div>
                    </div>

                    <!-- Back to Login Link -->
                    <div class="text-center mt-5">
                        <a href="{{ route('login') }}" class="text-secondary text-decoration-none small fw-medium">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
