<#
PowerShell helper to import src/biowell_insurance.sql into a remote MySQL database.
Usage: run from project root in PowerShell:
  .\scripts\import-db.ps1

It will prompt for host, port, user, database name and password.
If mysql.exe isn't in PATH, the script will try common XAMPP/MySQL install locations and prompt for the path.
#>

# Ensure script runs from project root where src/biowell_insurance.sql is located
 $scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Push-Location $scriptDir\.. | Out-Null

Write-Host "BioWell DB import helper"
# Use variable names that won't conflict with PowerShell automatic variables
$dbHost = Read-Host "DB host (e.g. db.example.com)"
$dbPort = Read-Host "DB port (default 3306)"; if ([string]::IsNullOrWhiteSpace($dbPort)) { $dbPort = '3306' }
$dbName = Read-Host "Database name (e.g. biowell_insurance)"
$dbUser = Read-Host "DB user"
# Securely read password
$dbPasswordSecure = Read-Host "DB password (leave blank for no password)" -AsSecureString
# convert secure string to plain (used only for calling mysql client)
$ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPasswordSecure)
$dbPassword = [Runtime.InteropServices.Marshal]::PtrToStringAuto($ptr)
[Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)

# Locate mysql.exe
$mysqlExe = Get-Command mysql.exe -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Source -ErrorAction SilentlyContinue
if (-not $mysqlExe) {
    $candidates = @(
        "C:\\xampp\\mysql\\bin\\mysql.exe",
        "C:\\xampp\\php\\mysql.exe",
        "C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe",
        "C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysql.exe"
    )
    foreach ($c in $candidates) {
        if (Test-Path $c) { $mysqlExe = $c; break }
    }
}

if (-not $mysqlExe) {
    $mysqlExe = Read-Host "mysql.exe not found. Enter full path to mysql.exe (or press Enter to cancel)"
    if ([string]::IsNullOrWhiteSpace($mysqlExe) -or -not (Test-Path $mysqlExe)) {
        Write-Error "mysql.exe not found. Install MySQL client or provide path to mysql.exe. Aborting."
        Pop-Location | Out-Null
        exit 1
    }
}

 $sqlFile = Join-Path -Path (Get-Location) -ChildPath "src\\biowell_insurance.sql"
if (-not (Test-Path $sqlFile)) {
    Write-Error "SQL file not found at $sqlFile. Run this script from project root. Aborting."
    Pop-Location | Out-Null
    exit 1
}

# Build args for mysql client
$args = @()
$args += "-h"; $args += $dbHost
$args += "-P"; $args += $dbPort
$args += "-u"; $args += $dbUser
if (-not [string]::IsNullOrWhiteSpace($dbPassword)) {
    $args += "-p$dbPassword"
}
$args += $dbName

Write-Host "Running import using:`n  $mysqlExe $($args -join ' ')"

# Pipe the SQL file into mysql.exe (this avoids shell redirect issues)
try {
    Get-Content -Raw -Path $sqlFile | & $mysqlExe @args
    $exit = $LASTEXITCODE
} catch {
    Write-Error "Import failed: $_"
    Pop-Location | Out-Null
    exit 1
}

if ($exit -eq 0) {
    Write-Host "Import completed successfully."
} else {
    Write-Error "Import failed with exit code $exit. Check credentials, network access, and that the DB exists."
}

Pop-Location | Out-Null
