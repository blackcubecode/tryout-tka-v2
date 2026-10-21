# Script untuk membuat paket distribusi bersih
$source = "D:\Tryout TKA v2"
$target = "D:\Tryout TKA v2-distribusi"
$zip = "D:\Tryout TKA v2-distribusi.zip"

Write-Host "1. Membersihkan folder target..." -ForegroundColor Yellow
if (Test-Path $target) { Remove-Item $target -Recurse -Force }
if (Test-Path $zip) { Remove-Item $zip -Force }

Write-Host "2. Copy folder proyek..." -ForegroundColor Yellow
Copy-Item $source $target -Recurse -Force

Write-Host "3. Menghapus data sensitif & file sampah..." -ForegroundColor Yellow
# Hapus file token & peserta asli
Remove-Item "$target\peserta_contoh\kartu_peserta.*" -Force -ErrorAction SilentlyContinue
Remove-Item "$target\peserta_contoh\*.csv" -Force -ErrorAction SilentlyContinue
Remove-Item "$target\db\seed_peserta.sql" -Force -ErrorAction SilentlyContinue
Remove-Item "$target\db\backup_*.sql" -Force -ErrorAction SilentlyContinue

# Hapus file SEB asli (sekolah lain harus buat sendiri)
Remove-Item "$target\seb\*.seb" -Force -ErrorAction SilentlyContinue

# Hapus file config asli (sekolah lain harus isi sendiri)
Remove-Item "$target\web\config.php" -Force -ErrorAction SilentlyContinue
Remove-Item "$target\admin\config_admin.php" -Force -ErrorAction SilentlyContinue

# Hapus output analitik
if (Test-Path "$target\analisis") { Remove-Item "$target\analisis\*" -Recurse -Force -ErrorAction SilentlyContinue }

# Hapus cache Python
Get-ChildItem "$target" -Recurse -Directory -Filter "__pycache__" | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue

Write-Host "4. Membuat file config contoh..." -ForegroundColor Yellow
if (Test-Path "$source\web\config.php") {
    Copy-Item "$source\web\config.php" "$target\web\config.example.php" -Force
}
if (Test-Path "$source\admin\config_admin.php") {
    Copy-Item "$source\admin\config_admin.php" "$target\admin\config_admin.example.php" -Force
}

Write-Host "5. Kompresi ke ZIP..." -ForegroundColor Yellow
Compress-Archive -Path "$target\*" -DestinationPath $zip -Force

Write-Host "6. Verifikasi..." -ForegroundColor Yellow
if (Test-Path $zip) {
    $size = (Get-Item $zip).Length / 1MB
    Write-Host "SUKSES! Paket distribusi siap: $zip" -ForegroundColor Green
    Write-Host ("Ukuran: {0:N2} MB" -f $size) -ForegroundColor Green
} else {
    Write-Host "GAGAL membuat ZIP." -ForegroundColor Red
}
