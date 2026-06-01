<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun Baru - SMK Negeri 1 Tamanan</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo-smk.png') }}">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
        }
        
        .bg-emerald-600 {
            background-color: #046C00 !important;
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
            background-color: #ffffff;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #046C00;
            background-color: #ffffff;
        }
        
        .left-panel {
            min-height: 100vh;
            background: linear-gradient(180deg, #046C00 0%, #046C00 100%);
        }

        .btn-google {
            background-color: white;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .btn-google:hover {
            background-color: #f9fafb;
            border-color: #d1d5db;
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
            border-bottom: 1px solid #e5e7eb;
        }
        
        .divider span {
            padding: 0 1rem;
            color: #9ca3af;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group-text {
            background-color: #f9fafb;
            border-right: none;
            color: #6b7280;
            border-color: #e5e7eb;
        }

        .form-control {
            background-color: #f9fafb;
            border-left: none;
            border-color: #e5e7eb;
            font-size: 0.9rem;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
        }

        .form-check-input:checked {
            background-color: #046C00;
            border-color: #046C00;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            
            <!-- Left Panel - Green -->
            <div class="col-lg-5 d-none d-lg-flex left-panel text-white p-5 flex-column">
                
                <!-- Logo & Header -->
                <div class="mb-5">
                    <div class="d-flex align-items-center gap-3 mb-5">
                        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <img src="{{ asset('img/logo-smk.png') }}" alt="" style="width: 70%; height: auto;">
                        </div>
                        <span class="fw-bold fs-6 tracking-wide">SMK NEGERI 1 TAMANAN</span>
                    </div>

                    <h1 class="display-6 fw-bold mb-4 lh-sm">
                        Kelola Sistem dalam<br>Satu Platform
                    </h1>
                    <p class="text-white-50 fs-6 lh-base" style="max-width: 400px;">
                        Daftar untuk mengakses dashboard admin dan mengatur<br>seluruh aktivitas pembelajaran.
                    </p>
                </div>

                <!-- Image Section -->
                <div class="mt-auto mb-5 position-relative">
                    <div class="bg-dark rounded-4 overflow-hidden shadow-lg" style="border: 2px solid rgba(255,255,255,0.1);">
                        <img 
                            src="{{ asset('img/smk-tamanan-2.jpg') }}" 
                            alt="Students in lab" 
                            class="w-100" 
                            style="height: 320px; object-fit: cover; opacity: 0.9;"
                        >
                    </div>
                </div>
            </div>

            <!-- Right Panel - Register Form -->
            <div class="col-12 col-lg-7 d-flex flex-column" style="background-color: #ffffff;">
                
                <!-- Back to Login Top Right -->
                <div class="text-end p-4">
                    <a href="{{ route('login') }}" class="text-dark text-decoration-none small fw-medium">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                    </a>
                </div>

                <div class="d-flex align-items-center justify-content-center flex-grow-1 p-4">
                    <div class="w-100" style="max-width: 440px;">
                        
                        <div class="mb-4">
                            <h2 class="h3 fw-bold text-dark mb-2">Buat Akun Baru</h2>
                            <p class="text-secondary small">Daftar untuk mengakses dashboard admin e-learning.</p>
                        </div>

                    

                        <!-- Register Form -->
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="form-control rounded-end" placeholder="Joko Widodo">
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- NIP -->
                            <div class="mb-3">
                                <label for="nip" class="form-label">NIP (Nomor Induk Pegawai)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                    <input id="nip" type="text" name="nip" value="{{ old('nip') }}" required autocomplete="nip" class="form-control rounded-end" placeholder="Masukkan NIP Anda">
                                </div>
                                @error('nip') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Email Address -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="form-control rounded-end" placeholder="name@example.com">
                                </div>
                                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input id="password" type="password" name="password" required autocomplete="new-password" class="form-control rounded-end" placeholder="••••••••">
                                </div>
                                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control rounded-end" placeholder="••••••••">
                                </div>
                                @error('password_confirmation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Terms & Conditions -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label text-secondary" for="terms" style="font-size: 0.75rem; line-height: 1.4;">
                                        Saya setuju dengan <a href="#" class="text-emerald-600 text-decoration-none fw-semibold">Syarat & Ketentuan</a> serta <a href="#" class="text-emerald-600 text-decoration-none fw-semibold">Kebijakan Privasi SMKN 1 Tamanan</a>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-emerald w-100 py-2 mb-3 fw-semibold rounded-3">
                                Daftar Sekarang
                            </button>

                            <!-- Login Link -->
                            <p class="text-center text-secondary small mb-4">
                                Sudah punya akun? 
                                <a href="{{ route('login') }}" class="text-emerald-600 text-decoration-none fw-semibold">Masuk Sekarang</a>
                            </p>
                        </form>

                        <!-- Help Link -->
                        <div class="text-center mt-3 pt-3">
                            <a href="#" class="text-secondary text-decoration-none small">
                                <i class="bi bi-question-circle me-1"></i> Butuh bantuan teknis?
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
