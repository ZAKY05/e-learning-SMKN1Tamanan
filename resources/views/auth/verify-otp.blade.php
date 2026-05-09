<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - SMK Negeri 1 Tamanan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .btn-emerald { background-color: #046C00; border-color: #046C00; color: white; }
        .btn-emerald:hover { background-color: #035200; border-color: #035200; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-4 p-4">
                    <div class="text-center mb-4">
                        <h2 class="h4 fw-bold text-dark mb-2">Verifikasi OTP</h2>
                        <p class="text-secondary small">Masukkan 4 digit kode OTP yang telah dikirim ke email <strong>{{ session('reset_email') }}</strong></p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success small">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.otp') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="otp" class="form-label fw-medium small">Kode OTP</label>
                            <input id="otp" type="text" name="otp" class="form-control text-center fs-4 letter-spacing-2 py-2" required autofocus placeholder="1234" maxlength="4">
                            @error('otp')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-emerald w-100 py-2 mb-3 fw-semibold rounded-3">
                            Verifikasi Kode
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <a href="{{ route('password.request') }}" class="text-decoration-none text-secondary small">Kembali ke Lupa Password</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
