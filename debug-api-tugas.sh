#!/bin/bash
# Debug script untuk test API tugas

DOMAIN="https://elearning-smkn1-tamanan.my.id"
# Atau jika masih test lokal:
DOMAIN="http://localhost:8000"

echo "=== API TUGAS DEBUG SCRIPT ==="
echo ""

# Test 1: Login untuk mendapat token
echo "1. Login ke API..."
LOGIN_RESPONSE=$(curl -s -X POST "$DOMAIN/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "siswa@example.com",
    "password": "password"
  }')

echo "Login Response:"
echo "$LOGIN_RESPONSE" | jq '.' 2>/dev/null || echo "$LOGIN_RESPONSE"

# Extract token
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token' 2>/dev/null)

if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
  echo ""
  echo "❌ Login gagal! Tidak bisa mendapat token."
  echo "Cek: email dan password harus sesuai di database"
  exit 1
fi

echo ""
echo "✅ Token diperoleh: ${TOKEN:0:30}..."
echo ""

# Test 2: Get Tugas
echo "2. Ambil daftar tugas..."
TUGAS_RESPONSE=$(curl -s -X GET "$DOMAIN/api/tugas" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Tugas Response:"
echo "$TUGAS_RESPONSE" | jq '.' 2>/dev/null || echo "$TUGAS_RESPONSE"

# Parse tugas data
TUGAS_COUNT=$(echo "$TUGAS_RESPONSE" | jq '.data | length' 2>/dev/null)

echo ""
if [ "$TUGAS_COUNT" -gt 0 ]; then
  echo "✅ Tugas ditemukan: $TUGAS_COUNT"
  
  # Get first tugas ID
  FIRST_TUGAS_ID=$(echo "$TUGAS_RESPONSE" | jq '.data[0].id_tugas' 2>/dev/null)
  echo "First Tugas ID: $FIRST_TUGAS_ID"
else
  echo "⚠️  Tidak ada tugas ditemukan"
  echo ""
  echo "Kemungkinan penyebab:"
  echo "1. Siswa belum di-assign ke kelas"
  echo "2. Tidak ada tugas untuk kelas siswa"
  echo "3. Tugas status bukan 'published'"
  echo ""
  echo "Debugging: Cek database"
  echo "SELECT siswa_id, kelas_id FROM student WHERE user_id = (SELECT id FROM users WHERE email = 'siswa@example.com');"
  echo "SELECT id_tugas, kelas_id, status FROM tugas WHERE kelas_id = {kelas_id};"
fi

echo ""
echo "3. Info User..."
USER_RESPONSE=$(curl -s -X GET "$DOMAIN/api/user" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "User Response:"
echo "$USER_RESPONSE" | jq '.' 2>/dev/null || echo "$USER_RESPONSE"

echo ""
echo "=== END DEBUG ==="
