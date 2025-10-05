# 🚀 Guide de Démarrage Rapide - VPS Manager

## ⚡ Démarrage en 3 minutes

### Étape 1 : Configuration (30 secondes)

Ouvrez PowerShell dans le dossier du projet et exécutez :

```powershell
.\setup.ps1
```

Ce script va :
- ✅ Vérifier PHP et Composer
- ✅ Créer le fichier `.env.local`
- ✅ Installer les dépendances
- ✅ Créer la base de données
- ✅ Exécuter les migrations
- ✅ Créer votre compte utilisateur

### Étape 2 : Démarrage (10 secondes)

```powershell
.\start.ps1
```

### Étape 3 : Connexion (5 secondes)

Ouvrez votre navigateur : **http://localhost:8000**

Connectez-vous avec les identifiants créés à l'étape 1.

---

## 🎯 Premiers Pas

### 1. Ajouter votre premier serveur VPS

1. Cliquez sur **"Ajouter un serveur"**
2. Remplissez les informations :
   - **Nom** : Mon serveur de production
   - **Adresse IP** : 192.168.1.100
   - **Port SSH** : 22
   - **Utilisateur SSH** : root
   - **Localisation** : Paris, France
   - **Fournisseur** : OVH
   - **Statut** : Actif
3. Cliquez sur **"Enregistrer"**

### 2. Voir les détails d'un serveur

- Cliquez sur l'icône 👁️ (œil) pour voir les détails
- Vous verrez les informations et les graphiques de métriques

### 3. Modifier un serveur

- Cliquez sur l'icône ✏️ (crayon)
- Modifiez les informations
- Enregistrez

### 4. Supprimer un serveur

- Cliquez sur l'icône 🗑️ (poubelle)
- Confirmez la suppression

---

## 📊 Fonctionnalités Principales

### Dashboard
- Vue d'ensemble de tous vos serveurs
- Statistiques globales (nombre de serveurs, CPU moyen, RAM moyenne)
- Liste de tous vos serveurs avec leur statut

### Gestion des Serveurs
- Ajout de nouveaux serveurs
- Modification des informations
- Suppression de serveurs
- Visualisation détaillée avec graphiques

### Métriques (À venir)
Les métriques (CPU, RAM, disque) peuvent être ajoutées :
- Manuellement via la base de données
- Automatiquement via un script de collecte SSH
- Via une API externe

---

## 🔧 Configuration Avancée

### Changer le port du serveur

Par défaut, le serveur démarre sur le port 8000. Pour changer :

```powershell
php -S localhost:3000 -t public/
```

### Utiliser avec WAMP

Le projet est déjà dans votre dossier WAMP. Accédez via :

```
http://localhost/g-carlier/CascadeProjects/personal-website/public/
```

### Ajouter un nouvel utilisateur

```bash
php bin/console app:create-user
```

---

## 🐛 Problèmes Courants

### "Erreur de connexion à la base de données"

**Solution** : Vérifiez que MySQL est démarré dans WAMP et que les identifiants dans `.env.local` sont corrects.

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/vps_manager?serverVersion=8.0.32&charset=utf8mb4"
```

### "Class not found"

**Solution** : Régénérez l'autoload

```bash
composer dump-autoload
```

### "Le serveur ne démarre pas"

**Solution** : Vérifiez que le port 8000 n'est pas déjà utilisé

```powershell
# Utiliser un autre port
php -S localhost:8080 -t public/
```

### "Page blanche après connexion"

**Solution** : Videz le cache

```bash
php bin/console cache:clear
```

---

## 📝 Commandes Utiles

### Gestion de la base de données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Voir le statut des migrations
php bin/console doctrine:migrations:status
```

### Gestion des utilisateurs

```bash
# Créer un utilisateur
php bin/console app:create-user
```

### Développement

```bash
# Vider le cache
php bin/console cache:clear

# Lister les routes
php bin/console debug:router

# Voir la configuration
php bin/console debug:config
```

---

## 📚 Documentation Complète

- **README.md** : Vue d'ensemble du projet
- **INSTALLATION.md** : Guide d'installation détaillé
- **ARCHITECTURE.md** : Architecture technique du projet

---

## 🎨 Personnalisation

### Changer les couleurs

Éditez `templates/base.html.twig` et modifiez les classes TailwindCSS :

```html
<!-- Remplacer la couleur primaire -->
<div class="bg-indigo-600">  <!-- Changer indigo-600 -->
```

### Ajouter des champs personnalisés

1. Modifiez l'entité `src/Entity/VpsServer.php`
2. Créez une migration : `php bin/console make:migration`
3. Exécutez la migration : `php bin/console doctrine:migrations:migrate`
4. Mettez à jour le formulaire `src/Form/VpsServerType.php`

---

## 🚀 Prochaines Étapes

### Fonctionnalités à implémenter

1. **Collecte automatique de métriques**
   - Script SSH pour récupérer CPU, RAM, disque
   - Commande console pour la collecte périodique

2. **Alertes**
   - Notifications par email si CPU > 90%
   - Alertes si serveur inactif

3. **API REST**
   - Endpoints pour récupérer les données
   - Authentification par token

4. **Export de données**
   - Export CSV des métriques
   - Génération de rapports PDF

5. **Multi-utilisateurs**
   - Gestion des équipes
   - Permissions granulaires

---

## 💡 Astuces

- **Raccourci clavier** : Utilisez `Ctrl+C` pour arrêter le serveur
- **Mode debug** : Le profiler Symfony est disponible en bas de page en mode dev
- **Logs** : Consultez `var/log/dev.log` pour les erreurs
- **Base de données** : Utilisez phpMyAdmin de WAMP pour voir les données

---

## 📞 Besoin d'aide ?

- Consultez la documentation Symfony : https://symfony.com/doc
- Vérifiez les logs dans `var/log/`
- Utilisez le profiler Symfony (barre en bas en mode dev)

---

**Bon développement ! 🎉**

*Créé par Guillaume Carlier*
