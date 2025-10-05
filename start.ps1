# Script de démarrage rapide pour VPS Manager
# Exécutez ce script avec: .\start.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   VPS Manager - Démarrage Rapide" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si .env.local existe
if (-not (Test-Path ".env.local")) {
    Write-Host "[INFO] Création du fichier .env.local..." -ForegroundColor Yellow
    Copy-Item ".env" ".env.local"
    Write-Host "[OK] Fichier .env.local créé. Veuillez le configurer avec vos paramètres." -ForegroundColor Green
    Write-Host ""
}

# Vérifier si la base de données existe
Write-Host "[INFO] Vérification de la base de données..." -ForegroundColor Yellow
$dbCheck = php bin/console doctrine:database:create --if-not-exists 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Base de données prête" -ForegroundColor Green
} else {
    Write-Host "[ERREUR] Problème avec la base de données" -ForegroundColor Red
    Write-Host $dbCheck
    exit 1
}

# Exécuter les migrations
Write-Host "[INFO] Exécution des migrations..." -ForegroundColor Yellow
$migrations = php bin/console doctrine:migrations:migrate --no-interaction 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Migrations exécutées" -ForegroundColor Green
} else {
    Write-Host "[ERREUR] Problème avec les migrations" -ForegroundColor Red
    Write-Host $migrations
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   Serveur de développement" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Démarrage du serveur sur http://localhost:8000" -ForegroundColor Green
Write-Host "Appuyez sur Ctrl+C pour arrêter le serveur" -ForegroundColor Yellow
Write-Host ""

# Démarrer le serveur
php -S localhost:8000 -t public/
