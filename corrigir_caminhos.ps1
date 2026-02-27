# Script para corrigir caminhos nos ficheiros API (voltar ao original)
$apiDir = "c:\Users\HP EliteBook 650\restaurante-saas\public\api"

Get-ChildItem -Path $apiDir -Filter *.php | ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    # Corrigir de volta para ../../config/ e ../../app/
    $novo = $content -replace '\.\./config/', '../../config/' -replace '\.\./app/', '../../app/'
    Set-Content -Path $_.FullName -Value $novo
    Write-Host "Corrigido: $($_.Name)"
}

Write-Host "Concluído!"
