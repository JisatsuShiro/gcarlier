# Architecture - VPS Manager

## 📐 Vue d'ensemble

VPS Manager est une application web construite avec Symfony 7.3 suivant l'architecture MVC et les meilleures pratiques de développement.

## 🏗️ Structure des Dossiers

```
personal-website/
├── bin/                    # Scripts exécutables (console)
├── config/                 # Configuration de l'application
│   ├── packages/          # Configuration des bundles
│   ├── routes.yaml        # Routes de l'application
│   ├── services.yaml      # Configuration des services
│   └── bundles.php        # Bundles activés
├── migrations/            # Migrations de base de données
├── public/                # Point d'entrée web
│   ├── index.php         # Front controller
│   ├── css/              # Fichiers CSS
│   └── js/               # Fichiers JavaScript
├── src/                   # Code source de l'application
│   ├── Command/          # Commandes console
│   ├── Controller/       # Contrôleurs
│   ├── Entity/           # Entités Doctrine
│   ├── Form/             # Types de formulaires
│   ├── Repository/       # Repositories Doctrine
│   ├── Security/         # Voters et authenticateurs
│   └── Kernel.php        # Kernel de l'application
├── templates/             # Templates Twig
│   ├── base.html.twig    # Template de base
│   ├── dashboard/        # Templates du dashboard
│   ├── security/         # Templates d'authentification
│   └── vps_server/       # Templates de gestion VPS
├── var/                   # Fichiers générés (cache, logs)
├── vendor/                # Dépendances Composer
├── .env                   # Variables d'environnement
├── composer.json          # Dépendances PHP
└── README.md             # Documentation principale
```

## 🔄 Flux de l'Application

### 1. Authentification

```
Utilisateur → /login → SecurityController
                         ↓
                    Formulaire de connexion
                         ↓
                    Validation des identifiants
                         ↓
                    Création de session
                         ↓
                    Redirection vers /dashboard
```

### 2. Affichage du Dashboard

```
Utilisateur authentifié → /dashboard → DashboardController
                                           ↓
                                    VpsServerRepository
                                           ↓
                                    Récupération des serveurs
                                           ↓
                                    Calcul des statistiques
                                           ↓
                                    Rendu du template
```

### 3. Gestion d'un serveur VPS

```
/vps/new → VpsServerController::new()
              ↓
         Création du formulaire
              ↓
         Validation
              ↓
         Sauvegarde en BDD
              ↓
         Redirection vers dashboard
```

## 🗄️ Modèle de Données

### Entités

#### User
- **id** : Identifiant unique
- **email** : Email de connexion (unique)
- **name** : Nom complet
- **password** : Mot de passe hashé
- **roles** : Rôles de l'utilisateur (JSON)
- **createdAt** : Date de création
- **lastLoginAt** : Dernière connexion
- **isActive** : Compte actif ou non

**Relations** :
- `OneToMany` avec VpsServer

#### VpsServer
- **id** : Identifiant unique
- **name** : Nom du serveur
- **ipAddress** : Adresse IP
- **sshPort** : Port SSH
- **sshUser** : Utilisateur SSH
- **location** : Localisation géographique
- **provider** : Fournisseur (OVH, AWS, etc.)
- **status** : Statut (active, inactive, maintenance)
- **notes** : Notes personnelles
- **createdAt** : Date de création
- **updatedAt** : Dernière modification

**Relations** :
- `ManyToOne` avec User
- `OneToMany` avec VpsMetric

#### VpsMetric
- **id** : Identifiant unique
- **cpuUsage** : Utilisation CPU (%)
- **memoryUsage** : Utilisation RAM (%)
- **diskUsage** : Utilisation disque (%)
- **networkIn** : Trafic entrant (octets)
- **networkOut** : Trafic sortant (octets)
- **uptime** : Temps de fonctionnement (secondes)
- **recordedAt** : Date d'enregistrement

**Relations** :
- `ManyToOne` avec VpsServer

## 🔐 Sécurité

### Authentification
- Utilisation de `SecurityBundle` de Symfony
- Formulaire de login avec protection CSRF
- Sessions sécurisées (HttpOnly, SameSite)
- Mots de passe hashés avec Argon2id

### Autorisation
- Firewall configuré dans `security.yaml`
- Voters personnalisés pour les permissions granulaires
- Contrôle d'accès par route

### Protection des données
- Validation des entrées avec Symfony Validator
- Sanitization des données
- Requêtes préparées (Doctrine)
- Protection contre les injections SQL

## 🎨 Frontend

### Technologies
- **TailwindCSS** : Framework CSS utility-first
- **Alpine.js** : Framework JavaScript léger pour l'interactivité
- **Chart.js** : Bibliothèque de graphiques
- **Font Awesome** : Icônes

### Templates
- **Twig** : Moteur de templates
- Héritage de templates avec `base.html.twig`
- Composants réutilisables
- Responsive design (mobile-first)

## 🔌 API et Extensions

### Commandes Console

#### `app:create-user`
Crée un nouvel utilisateur administrateur.

```bash
php bin/console app:create-user
```

### Futures Extensions Possibles

1. **API REST** : Exposer les données via une API
2. **Collecte automatique de métriques** : Script SSH pour récupérer les métriques
3. **Alertes** : Notifications par email/SMS en cas de problème
4. **Backup** : Gestion des sauvegardes
5. **Multi-tenant** : Support de plusieurs organisations

## 🧪 Tests (À implémenter)

### Tests unitaires
- Tests des entités
- Tests des services
- Tests des repositories

### Tests fonctionnels
- Tests des contrôleurs
- Tests des formulaires
- Tests d'authentification

### Tests d'intégration
- Tests de bout en bout
- Tests de l'API

## 📊 Performance

### Optimisations
- Cache Doctrine (query cache, result cache)
- Opcache PHP activé
- Assets minifiés en production
- Lazy loading des relations Doctrine

### Monitoring
- Profiler Symfony en développement
- Logs structurés (Monolog)
- Métriques de performance

## 🚀 Déploiement

### Environnements

#### Développement
```env
APP_ENV=dev
APP_DEBUG=1
```

#### Production
```env
APP_ENV=prod
APP_DEBUG=0
```

### Checklist de déploiement
- [ ] Configurer `.env.local` avec les bonnes valeurs
- [ ] Exécuter `composer install --no-dev --optimize-autoloader`
- [ ] Vider le cache : `php bin/console cache:clear --env=prod`
- [ ] Exécuter les migrations : `php bin/console doctrine:migrations:migrate`
- [ ] Configurer le serveur web (Apache/Nginx)
- [ ] Activer HTTPS
- [ ] Configurer les sauvegardes de la base de données

## 📚 Ressources

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/)
- [Documentation Twig](https://twig.symfony.com/doc/)
- [TailwindCSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev/)

---

**Développé par Guillaume Carlier** 🚀
