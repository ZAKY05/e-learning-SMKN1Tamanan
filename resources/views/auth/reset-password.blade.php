<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru - SMK Negeri 1 Tamanan</title>
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
                        <h2 class="h4 fw-bold text-dark mb-2">Buat Password Baru</h2>
                        <p class="text-secondary small">Masukkan password baru untuk akun <strong>{{ $email ?? session('reset_email') }}</strong></p>
                    </div>

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium small">Password Baru</label>
                            <input id="password" type="password" name="password" class="form-control" required autofocus placeholder="••••••••">
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-medium small">Konfirmasi Password Baru</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required placeholder="••••••••">
                            @error('password_confirmation')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-emerald w-100 py-2 fw-semibold rounded-3">
                            Simpan Password Baru
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>