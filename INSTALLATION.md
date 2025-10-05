# Guide d'Installation - VPS Manager

## 📋 Prérequis

- **PHP 8.2** ou supérieur
- **Composer** 2.x
- **MySQL/MariaDB** 8.0+
- **Extensions PHP requises** :
  - pdo_mysql
  - intl
  - opcache
  - ctype
  - iconv

## 🚀 Installation Étape par Étape

### 1. Vérifier l'environnement

```bash
# Vérifier PHP
php -v

# Vérifier Composer
composer --version

# Vérifier les extensions PHP
php -m | findstr "pdo_mysql intl"
```

### 2. Configurer la base de données

Créez un fichier `.env.local` à la racine du projet :

```bash
# Copier le fichier .env
copy .env .env.local
```

Éditez `.env.local` et configurez votre connexion MySQL :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/vps_manager?serverVersion=8.0.32&charset=utf8mb4"
```

**Note** : Remplacez `root:` par vos identifiants MySQL si nécessaire.

### 3. Créer la base de données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

### 4. Créer un utilisateur administrateur

```bash
php bin/console app:create-user
```

Suivez les instructions pour créer votre compte :
- Nom complet : Guillaume Carlier
- Email : votre@email.com
- Mot de passe : (choisissez un mot de passe sécurisé)

### 5. Lancer le serveur de développement

#### Option A : Avec PHP Built-in Server

```bash
php -S localhost:8000 -t public/
```

Accédez à : http://localhost:8000

#### Option B : Avec WAMP (déjà configuré)

Le projet est déjà dans votre répertoire WAMP :
```
c:\wamp64\www\g-carlier\CascadeProjects\personal-website
```

Configurez un VirtualHost ou accédez via :
```
http://localhost/g-carlier/CascadeProjects/personal-website/public/
```

#### Option C : Avec Symfony CLI (si installé)

```bash
symfony server:start
```

## 🔐 Première Connexion

1. Ouvrez votre navigateur
2. Allez sur la page de login
3. Connectez-vous avec les identifiants créés à l'étape 4

## 📝 Configuration Avancée

### Générer une clé secrète APP_SECRET

Dans `.env.local`, remplacez la valeur de `APP_SECRET` :

```bash
# Générer une clé aléatoire
php -r "echo bin2hex(random_bytes(16));"
```

Copiez le résultat dans votre `.env.local` :

```env
APP_SECRET=votre_cle_secrete_generee
```

### Configuration pour la production

Pour un environnement de production :

1. Modifiez `.env.local` :
```env
APP_ENV=prod
APP_DEBUG=0
```

2. Videz le cache :
```bash
php bin/console cache:clear --env=prod
```

3. Optimisez l'autoloader :
```bash
composer install --no-dev --optimize-autoloader
```

## 🎨 Personnalisation

### Ajouter des données de test

Pour ajouter des serveurs VPS de test, utilisez l'interface web après connexion :
1. Cliquez sur "Ajouter un serveur"
2. Remplissez les informations
3. Enregistrez

### Ajouter des métriques de test

Vous pouvez créer une commande pour simuler des métriques ou les récupérer via SSH.

## 🐛 Dépannage

### Erreur de connexion à la base de données

```bash
# Vérifier que MySQL est démarré
# Dans WAMP, vérifier que le service MySQL est actif
```

### Erreur de permissions

```bash
# Windows : donner les permissions au dossier var/
icacls var /grant Users:F /T
```

### Erreur "Class not found"

```bash
# Régénérer l'autoload
composer dump-autoload
```

### Cache non vidé

```bash
# Vider le cache manuellement
php bin/console cache:clear
```

## 📚 Commandes Utiles

```bash
# Lister toutes les routes
php bin/console debug:router

# Vérifier la configuration
php bin/console debug:config

# Créer un nouvel utilisateur
php bin/console app:create-user

# Créer une nouvelle migration
php bin/console make:migration

# Voir le statut des migrations
php bin/console doctrine:migrations:status
```

## 🔒 Sécurité

- ✅ Mots de passe hashés avec Argon2id
- ✅ Protection CSRF activée
- ✅ Sessions sécurisées
- ✅ Validation des entrées
- ✅ Contrôle d'accès par utilisateur

## 📞 Support

Pour toute question ou problème :
- Consultez la documentation Symfony : https://symfony.com/doc
- Vérifiez les logs dans `var/log/`

---

**Bon développement ! 🚀**
