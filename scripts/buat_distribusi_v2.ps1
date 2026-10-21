$source = "D:\Tryout TKA v2"
$target = "D:\Tryout TKA v2-distribusi"
$zip = "D:\Tryout TKA v2-distribusi.zip"

Write-Host "1. Hapus folder target lama..." -ForegroundColor Yellow
if (Test-Path $target) { Remove-Item $target -Recurse -Force }
if (Test-Path $zip) { Remove-Item $zip -Force }

Write-Host "2. Copy file dasar dari folder proyek asli..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path $target | Out-Null
# Copy file utama (README, LICENSE, .gitignore, dll)
Get-ChildItem $source -File | Copy-Item -Destination $target -Force
# Copy folder pendukung
"config","db","docs","scripts","soal","tools" | ForEach-Object {
    if (Test-Path "$source\$_") { Copy-Item "$source\$_" "$target\$_" -Recurse -Force }
}

Write-Host "3. Copy folder web dari XAMPP..." -ForegroundColor Yellow
if (Test-Path "C:\xampp\htdocs\tka") {
    Copy-Item "C:\xampp\htdocs\tka" "$target\web" -Recurse -Force
    # Bersihkan file config asli, simpan sebagai example
    if (Test-Path "$target\web\config.php") {
        Copy-Item "$target\web\config.php" "$target\web\config.example.php" -Force
        Remove-Item "$target\web\config.php" -Force
    }
} else {
    Write-Host "   [WARNING] Folder web di XAMPP tidak ditemukan!" -ForegroundColor Red
}

Write-Host "4. Copy folder admin dari XAMPP..." -ForegroundColor Yellow
if (Test-Path "C:\xampp\htdocs\tka-admin") {
    Copy-Item "C:\xampp\htdocs\tka-admin" "$target\admin" -Recurse -Force
    # Bersihkan file config asli, simpan sebagai example
    if (Test-Path "$target\admin\config_admin.php") {
        Copy-Item "$target\admin\config_admin.php" "$target\admin\config_admin.example.php" -Force
        Remove-Item "$target\admin\config_admin.php" -Force
    }
} else {
    Write-Host "   [WARNING] Folder tka-admin di XAMPP tidak ditemukan!" -ForegroundColor Red
}

Write-Host "5. Hapus data sensitif dari folder peserta & db..." -ForegroundColor Yellow
Remove-Item "$target\peserta_contoh\kartu_peserta.*" -Force -ErrorAction SilentlyContinue
Remove-Item "$target\peserta_contoh\*.csv" -Force -ErrorAction SilentlyContinue
Remove-Item "$target\db\seed_peserta.sql" -Force -ErrorAction SilentlyContinue
Remove-Item "$target\db\backup_*.sql" -Force -ErrorAction SilentlyContinue
Remove-Item "$target\seb\*.seb" -Force -ErrorAction SilentlyContinue
Remove-Item "$target\analisis\*" -Recurse -Force -ErrorAction SilentlyContinue

Write-Host "6. Kompresi ke ZIP..." -ForegroundColor Yellow
Compress-Archive -Path "$target\*" -DestinationPath $zip -Force

Write-Host "SUKSES! Paket distribusi siap di: $zip" -ForegroundColor Green
