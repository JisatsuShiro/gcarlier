# VPS Manager - Dashboard de Gestion VPS

Application web sécurisée développée avec **Symfony 7.3** pour gérer et monitorer vos serveurs VPS.

![Symfony](https://img.shields.io/badge/Symfony-7.3-black?style=flat-square&logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql)

## 🚀 Fonctionnalités

- ✅ **Authentification sécurisée** : Système de login avec protection CSRF et gestion des sessions
- ✅ **Dashboard VPS** : Vue d'ensemble de tous vos serveurs avec statistiques en temps réel
- ✅ **Monitoring** : Suivi des métriques (CPU, RAM, disque, réseau) avec graphiques
- ✅ **Gestion multi-serveurs** : Ajout, modification et suppression de serveurs VPS
- ✅ **Interface moderne** : UI responsive avec TailwindCSS et Alpine.js
- ✅ **Sécurité renforcée** : Mots de passe hashés (Argon2id), contrôle d'accès, validation des entrées

## 📋 Prérequis

- **PHP 8.2** ou supérieur
- **Composer** 2.x
- **MySQL/MariaDB** 8.0+
- **Extensions PHP** : pdo_mysql, intl, opcache, ctype, iconv

## ⚡ Installation Rapide

### Option 1 : Script automatique (Recommandé)

```powershell
# Installation complète
.\setup.ps1

# Démarrer le serveur
.\start.ps1
```

### Option 2 : Installation manuelle

1. **Configurer la base de données**
   ```powershell
   copy .env .env.local
   # Éditez .env.local et configurez DATABASE_URL
   ```

2. **Installer et configurer**
   ```bash
   composer install
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console app:create-user
   ```

3. **Lancer le serveur**
   ```bash
   php -S localhost:8000 -t public/
   ```

4. **Accéder à l'application**
   ```
   http://localhost:8000
   ```

📖 **Guide détaillé** : Consultez [INSTALLATION.md](INSTALLATION.md)

## 🔐 Sécurité

- Mots de passe hashés avec Argon2id
- Protection CSRF sur tous les formulaires
- Sessions sécurisées avec HttpOnly cookies
- Rate limiting sur les tentatives de connexion
- Validation et sanitization des entrées

## 📁 Structure du Projet

```
├── config/             # Configuration Symfony
├── src/
│   ├── Controller/     # Contrôleurs
│   ├── Entity/         # Entités Doctrine
│   ├── Repository/     # Repositories
│   ├── Security/       # Authentification
│   └── Service/        # Services métier
├── templates/          # Templates Twig
├── public/             # Fichiers publics (CSS, JS, images)
└── migrations/         # Migrations de base de données
```

## 🎨 Technologies Utilisées

- **Backend** : Symfony 7.1
- **Base de données** : MySQL avec Doctrine ORM
- **Frontend** : Twig, TailwindCSS, Alpine.js
- **Sécurité** : Symfony Security Bundle

## 📝 Licence

Projet personnel - Guillaume Carlier
