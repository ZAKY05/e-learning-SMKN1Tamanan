# Debug script untuk test API tugas (PowerShell untuk Windows)
# Usage: .\debug-api-tugas.ps1

$DOMAIN = "http://localhost:8000"
# Untuk production: $DOMAIN = "https://elearning-smkn1-tamanan.my.id"

$EMAIL = "siswa@example.com"
$PASSWORD = "password"

Write-Host "=== API TUGAS DEBUG SCRIPT ===" -ForegroundColor Cyan
Write-Host ""

# Test 1: Login untuk mendapat token
Write-Host "1. Login ke API..." -ForegroundColor Yellow

$LoginBody = @{
    email = $EMAIL
    password = $PASSWORD
} | ConvertTo-Json

try {
    $LoginResponse = Invoke-WebRequest -Uri "$DOMAIN/api/auth/login" `
        -Method POST `
        -Headers @{
            "Content-Type" = "application/json"
            "Accept" = "application/json"
        } `
        -Body $LoginBody

    $LoginData = $LoginResponse.Content | ConvertFrom-Json
    
    Write-Host "Login Response:"
    Write-Host ($LoginData | ConvertTo-Json -Depth 10)
    
    $TOKEN = $LoginData.data.token
    
    if (-not $TOKEN) {
        Write-Host ""
        Write-Host "❌ Login gagal! Tidak bisa mendapat token." -ForegroundColor Red
        Write-Host "Cek: email dan password harus sesuai di database"
        exit 1
    }
    
    Write-Host ""
    Write-Host "✅ Token diperoleh: $($TOKEN.Substring(0, 30))..." -ForegroundColor Green
    
} catch {
    Write-Host "❌ Login error: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Kemungkinan penyebab:" -ForegroundColor Yellow
    Write-Host "1. Server tidak aktif"
    Write-Host "2. Domain/URL salah"
    Write-Host "3. Email/password tidak sesuai"
    exit 1
}

Write-Host ""

# Test 2: Get Tugas
Write-Host "2. Ambil daftar tugas..." -ForegroundColor Yellow

try {
    $TugasResponse = Invoke-WebRequest -Uri "$DOMAIN/api/tugas" `
        -Method GET `
        -Headers @{
            "Authorization" = "Bearer $TOKEN"
            "Accept" = "application/json"
        }
    
    $TugasData = $TugasResponse.Content | ConvertFrom-Json
    
    Write-Host "Tugas Response:"
    Write-Host ($TugasData | ConvertTo-Json -Depth 10)
    
    $TugasCount = @($TugasData.data).Count
    
    Write-Host ""
    if ($TugasCount -gt 0) {
        Write-Host "✅ Tugas ditemukan: $TugasCount" -ForegroundColor Green
        
        $FirstTugasId = $TugasData.data[0].id_tugas
        Write-Host "First Tugas ID: $FirstTugasId"
    } else {
        Write-Host "⚠️  Tidak ada tugas ditemukan" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Kemungkinan penyebab:" -ForegroundColor Yellow
        Write-Host "1. Siswa belum di-assign ke kelas"
        Write-Host "2. Tidak ada tugas untuk kelas siswa"
        Write-Host "3. Tugas status bukan 'published'"
        Write-Host ""
        Write-Host "Debugging: Cek database dengan query:" -ForegroundColor Cyan
        Write-Host "mysql> SELECT siswa_id, kelas_id FROM student WHERE id_siswa = (SELECT siswa_id FROM users WHERE email = '$EMAIL');"
        Write-Host "mysql> SELECT id_tugas, kelas_id, status FROM tugas WHERE kelas_id = {kelas_id};"
    }
    
} catch {
    Write-Host "❌ Error ambil tugas: $_" -ForegroundColor Red
}

Write-Host ""

# Test 3: Get User Info
Write-Host "3. Info User..." -ForegroundColor Yellow

try {
    $UserResponse = Invoke-WebRequest -Uri "$DOMAIN/api/user" `
        -Method GET `
        -Headers @{
            "Authorization" = "Bearer $TOKEN"
            "Accept" = "application/json"
        }
    
    $UserData = $UserResponse.Content | ConvertFrom-Json
    
    Write-Host "User Response:"
    Write-Host ($UserData | ConvertTo-Json -Depth 10)
    
    Write-Host ""
    Write-Host "User Role: $($UserData.data.role)" -ForegroundColor Cyan
    Write-Host "User Siswa ID: $($UserData.data.siswa.id_siswa)" -ForegroundColor Cyan
    Write-Host "User Kelas ID: $($UserData.data.siswa.kelas_id)" -ForegroundColor Cyan
    
} catch {
    Write-Host "❌ Error ambil user: $_" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== END DEBUG ===" -ForegroundColor Cyan

# Additional debugging checks
Write-Host ""
Write-Host "📋 ADDITIONAL CHECKS" -ForegroundColor Cyan
Write-Host ""
Write-Host "Jika tugas tidak muncul, jalankan query di database:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Cek siswa sudah ter-assign ke kelas:" -ForegroundColor Cyan
Write-Host "   mysql> SELECT id_siswa, nama, kelas_id FROM student LIMIT 5;"
Write-Host ""
Write-Host "2. Cek tugas yang ada:" -ForegroundColor Cyan
Write-Host "   mysql> SELECT id_tugas, judul_tugas, kelas_id, status FROM tugas LIMIT 5;"
Write-Host ""
Write-Host "3. Cek tugas untuk kelas tertentu:" -ForegroundColor Cyan
Write-Host "   mysql> SELECT id_tugas, judul_tugas, status FROM tugas WHERE kelas_id = 1 AND status = 'published';"
Write-Host ""
Write-Host "4. Cek role user:" -ForegroundColor Cyan
Write-Host "   mysql> SELECT id, email, role, siswa_id FROM users WHERE email = '$EMAIL';"
