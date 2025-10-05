# Script d'installation complète pour VPS Manager
# Exécutez ce script avec: .\setup.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   VPS Manager - Installation" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Étape 1: Vérifier les prérequis
Write-Host "[1/6] Vérification des prérequis..." -ForegroundColor Yellow
Write-Host ""

# Vérifier PHP
$phpVersion = php -v 2>&1 | Select-String "PHP (\d+\.\d+)" | ForEach-Object { $_.Matches.Groups[1].Value }
if ($phpVersion) {
    Write-Host "  [OK] PHP $phpVersion détecté" -ForegroundColor Green
} else {
    Write-Host "  [ERREUR] PHP non trouvé" -ForegroundColor Red
    exit 1
}

# Vérifier Composer
$composerVersion = composer --version 2>&1 | Select-String "Composer version (\d+\.\d+\.\d+)" | ForEach-Object { $_.Matches.Groups[1].Value }
if ($composerVersion) {
    Write-Host "  [OK] Composer $composerVersion détecté" -ForegroundColor Green
} else {
    Write-Host "  [ERREUR] Composer non trouvé" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Étape 2: Configuration
Write-Host "[2/6] Configuration de l'environnement..." -ForegroundColor Yellow
if (-not (Test-Path ".env.local")) {
    Copy-Item ".env" ".env.local"
    Write-Host "  [OK] Fichier .env.local créé" -ForegroundColor Green
} else {
    Write-Host "  [INFO] .env.local existe déjà" -ForegroundColor Cyan
}
Write-Host ""

# Étape 3: Installation des dépendances
Write-Host "[3/6] Installation des dépendances Composer..." -ForegroundColor Yellow
composer install --no-interaction
if ($LASTEXITCODE -eq 0) {
    Write-Host "  [OK] Dépendances installées" -ForegroundColor Green
} else {
    Write-Host "  [ERREUR] Échec de l'installation des dépendances" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Étape 4: Création de la base de données
Write-Host "[4/6] Création de la base de données..." -ForegroundColor Yellow
php bin/console doctrine:database:create --if-not-exists
if ($LASTEXITCODE -eq 0) {
    Write-Host "  [OK] Base de données créée" -ForegroundColor Green
} else {
    Write-Host "  [AVERTISSEMENT] La base de données existe peut-être déjà" -ForegroundColor Yellow
}
Write-Host ""

# Étape 5: Exécution des migrations
Write-Host "[5/6] Exécution des migrations..." -ForegroundColor Yellow
php bin/console doctrine:migrations:migrate --no-interaction
if ($LASTEXITCODE -eq 0) {
    Write-Host "  [OK] Migrations exécutées" -ForegroundColor Green
} else {
    Write-Host "  [ERREUR] Échec des migrations" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Étape 6: Création d'un utilisateur
Write-Host "[6/6] Création d'un utilisateur administrateur..." -ForegroundColor Yellow
Write-Host ""
Write-Host "Veuillez entrer les informations de l'utilisateur:" -ForegroundColor Cyan
php bin/console app:create-user

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   Installation terminée!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Pour démarrer le serveur, exécutez:" -ForegroundColor Yellow
Write-Host "  .\start.ps1" -ForegroundColor White
Write-Host ""
Write-Host "Ou manuellement:" -ForegroundColor Yellow
Write-Host "  php -S localhost:8000 -t public/" -ForegroundColor White
Write-Host ""
Write-Host "Puis ouvrez: http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
