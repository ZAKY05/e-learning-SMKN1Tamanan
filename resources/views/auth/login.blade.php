<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SMK Negeri 1 Tamanan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .bg-emerald-600 {
            background-color: #046C00 !important;
        }
        
        .bg-emerald-700 {
            background-color: #046C00 !important;
        }
        
        .bg-emerald-800 {
            background-color: #046C00 !important;
        }
        
        .text-emerald-600 {
            color: #046C00 !important;
        }
        
        .text-emerald-700 {
            color: #046C00 !important;
        }
        
        .btn-emerald {
            background-color: #046C00;
            border-color: #046C00;
            color: white;
        }
        
        .btn-emerald:hover {
            background-color: #046C00;
            border-color: #046C00;
            color: white;
        }
        
        .form-control:focus {
            border-color: #046C00;
            box-shadow: 0 0 0 0.25rem rgba(5, 150, 105, 0.25);
        }
        
        .form-check-input:checked {
            background-color: #046C00;
            border-color: #046C00;
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
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        
        .divider span {
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
                            <!-- Ganti dengan logo sekolah -->
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

            <!-- Right Panel - Login Form -->
            <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center bg-light p-4">
                <div class="w-100" style="max-width: 448px;">
                    
                    <div class="mb-5">
                        <h2 class="h4 fw-bold text-dark mb-2">Selamat Datang Kembali, Admin</h2>
                        <p class="text-secondary small">Masuk untuk mengakses dashboard admin e-learning.</p>
                    </div>

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium text-dark small">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-envelope text-secondary"></i>
                                </span>
                                <input 
                                    id="email" 
                                    type="email" 
                                    name="email" 
                                    value="{{ old('email') }}" 
                                    required 
                                    autofocus
                                    class="form-control border-start-0 ps-0"
                                    placeholder="nama@email.com"
                                >
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label fw-medium text-dark small mb-0">Kata Sandi</label>
                                <a href="{{ route('password.request') }}" class="text-emerald-600 text-decoration-none small fw-medium">Lupa Kata Sandi?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-lock text-secondary"></i>
                                </span>
                                <input 
                                    id="password" 
                                    type="password" 
                                    name="password" 
                                    required
                                    class="form-control border-start-0 ps-0"
                                    placeholder="••••••••"
                                >
                                <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="eye-icon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="remember_me" 
                                    name="remember"
                                >
                                <label class="form-check-label text-secondary small" for="remember_me">
                                    Ingat saya di perangkat ini
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-emerald w-100 py-3 mb-3 fw-semibold">
                            Masuk Sekarang
                        </button>

                        <!-- Register Link -->
                        <p class="text-center text-secondary small mb-0">
                            Belum punya akun? 
                            <a href="{{ route('register') }}" class="text-emerald-600 text-decoration-none fw-semibold">Registrasi</a>
                        </p>
                    </form>

                    <!-- Footer Links -->
                    <div class="mt-5 pt-4 border-top">
                        <div class="d-flex justify-content-center gap-4 small">
                            <a href="#" class="text-secondary text-decoration-none">Bantuan</a>
                            <a href="#" class="text-secondary text-decoration-none">Kebijakan Privasi</a>
                            <a href="#" class="text-secondary text-decoration-none">Ketentuan Layanan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>