$ErrorActionPreference = 'Stop'

# Tunables
$ftpUsePassive = $true
$ftpEnableSsl = $true
$ftpKeepAlive = $true
$ftpDisableProxy = $true
$ftpTimeoutMs = 900000           # 15 minutes
$ftpReadWriteTimeoutMs = 900000  # 15 minutes
$ftpMaxRetries = 3
$ftpRetryDelaySeconds = 3
$ftpUploadBufferSize = 65536     # 64 KiB

function New-FtpRequest {
    param(
        [string]$Uri,
        [string]$Method,
        [string]$User,
        [string]$Pass
    )
    # Ensure TLS 1.2
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12
    [System.Net.ServicePointManager]::Expect100Continue = $false
    $req = [System.Net.FtpWebRequest]::Create($Uri)
    $req.Method = $Method
    $req.Credentials = New-Object System.Net.NetworkCredential($User, $Pass)
    if ($ftpDisableProxy) { $req.Proxy = [System.Net.GlobalProxySelection]::GetEmptyWebProxy() }
    $req.UseBinary = $true
    $req.KeepAlive = $ftpKeepAlive
    $req.UsePassive = $ftpUsePassive
    $req.EnableSsl = $ftpEnableSsl  # Explicit FTPS
    $req.ReadWriteTimeout = $ftpReadWriteTimeoutMs
    $req.Timeout = $ftpTimeoutMs
    return $req
}

function Ensure-FtpDirectory {
    param(
        [string]$BaseUri,
        [string]$RemoteDir,
        [string]$User,
        [string]$Pass
    )
    $parts = $RemoteDir.Trim('/').Split('/') | Where-Object { $_ -ne '' }
    $path = ''
    foreach ($p in $parts) {
        $path = $path + '/' + $p
        $uri = ($BaseUri.TrimEnd('/') + $path)
        try {
            $req = New-FtpRequest -Uri $uri -Method ([System.Net.WebRequestMethods+Ftp]::MakeDirectory) -User $User -Pass $Pass
            $res = $req.GetResponse()
            $res.Close()
        } catch {
            # ignore errors (directory may already exist)
        }
    }
}

function Upload-FtpFile {
    param(
        [string]$BaseUri,
        [string]$RemotePath,
        [string]$LocalFile,
        [string]$User,
        [string]$Pass
    )
    $remoteDir = [System.IO.Path]::GetDirectoryName($RemotePath).Replace('\','/')
    if ($remoteDir -and $remoteDir -ne '.') {
        Ensure-FtpDirectory -BaseUri $BaseUri -RemoteDir $remoteDir -User $User -Pass $Pass
    }
    $uri = ($BaseUri.TrimEnd('/') + '/' + $RemotePath.TrimStart('/'))
    $attempt = 0
    $usePassiveLocal = $ftpUsePassive
    while ($true) {
        $attempt += 1
        try {
            $req = New-FtpRequest -Uri $uri -Method ([System.Net.WebRequestMethods+Ftp]::UploadFile) -User $User -Pass $Pass
            $req.UsePassive = $usePassiveLocal

            $fileStream = [System.IO.File]::OpenRead($LocalFile)
            try {
                $req.ContentLength = $fileStream.Length
                $outStream = $req.GetRequestStream()
                try {
                    $buffer = New-Object byte[] $ftpUploadBufferSize
                    while (($bytesRead = $fileStream.Read($buffer, 0, $buffer.Length)) -gt 0) {
                        $outStream.Write($buffer, 0, $bytesRead)
                    }
                } finally {
                    if ($outStream) { $outStream.Close() }
                }
            } finally {
                if ($fileStream) { $fileStream.Close() }
            }

            $res = $req.GetResponse()
            try { } finally { if ($res) { $res.Close() } }
            break
        } catch {
            if ($attempt -ge $ftpMaxRetries) { throw $_ }
            Start-Sleep -Seconds $ftpRetryDelaySeconds
            if ($attempt -eq ($ftpMaxRetries - 1)) { $usePassiveLocal = -not $ftpUsePassive }
        }
    }
}

# Config
$ftpHost = 'ftp.fasthosts.co.uk'
$baseRemote = 'ftp://' + $ftpHost + ':21/htdocs'
$user = 'bindayadmin'
$pass = '9XZwda@2SqZxXzk'

# Sanity check: list /htdocs
try {
    $testReq = New-FtpRequest -Uri ($baseRemote) -Method ([System.Net.WebRequestMethods+Ftp]::ListDirectory) -User $user -Pass $pass
    $testRes = $testReq.GetResponse()
    Write-Host "Connected. Listing /htdocs OK" -ForegroundColor Green
    $testRes.Close()
} catch {
    Write-Error "FTP connect/list failed. Verify host/user/pass and that FTP and passive mode are allowed. Details: $($_.Exception.Message)"
    exit 1
}

# 1) Upload root index.php that routes to subfolder public
$rootIndex = Join-Path $PSScriptRoot 'root_index.php'
if (-not (Test-Path $rootIndex)) { throw "Missing root_index.php next to this script." }
Upload-FtpFile -BaseUri $baseRemote -RemotePath 'index.php' -LocalFile $rootIndex -User $user -Pass $pass

# 2) Upload the Laravel app into /htdocs/binday/
$projectRoot = Get-Location
$files = Get-ChildItem -Path $projectRoot -Recurse -File -Force |
    Where-Object {
        # Exclude git and this deploy script itself and root_index
        $_.FullName -notmatch '\\.git\\' -and
        $_.FullName -notmatch '\\node_modules\\' -and
        $_.FullName -notmatch '\\storage\\framework\\cache\\data\\' -and
        $_.FullName -notmatch '\\deploy_ftp.ps1$' -and
        $_.FullName -notmatch '\\root_index.php$'
    }

foreach ($f in $files) {
    $full = [System.IO.Path]::GetFullPath($f.FullName)
    $root = [System.IO.Path]::GetFullPath($projectRoot.Path)
    $rel = $full.Substring($root.Length)
    $rel = ($rel -replace '^[\\/]+','')
    $rel = ($rel -replace '\\','/')
    $remote = 'binday/' + $rel
    Write-Host "Uploading $rel -> $remote"
    Upload-FtpFile -BaseUri $baseRemote -RemotePath $remote -LocalFile $f.FullName -User $user -Pass $pass
}

Write-Host 'Deploy finished.'


