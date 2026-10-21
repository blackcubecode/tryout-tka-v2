powershell

# Matikan Dapodik saat mau ujian:
Stop-Service DapodikWebSrv
Stop-Service DapodikDB

# Nyalakan Dapodik lagi setelah ujian selesai:
Start-Service DapodikDB
Start-Service DapodikWebSrv