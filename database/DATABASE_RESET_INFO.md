# 🗄️ Réinitialisation Complète de la Base de Données NSM

## 📚 Vue d'ensemble

Ce dossier contient tous les fichiers nécessaires pour **réinitialiser complètement la base de données NSM à zéro**. 

> **⚠️ Important:** La réinitialisation supprime TOUTES les données actuelles. Utilisez `backup_database.php` d'abord si vous avez des données importantes !

---

## 🚀 Démarrage Rapide

### 1️⃣ **Méthode la Plus Simple - Interface Web**
```
Ouvrez dans votre navigateur:
http://localhost/nsm-website/database/
```
✅ Interface complète pour gérer la base de données

### 2️⃣ **Sauvegarder d'abord (Recommandé)**
```
http://localhost/nsm-website/database/backup_database.php
```
💾 Crée une sauvegarde de la base actuelle

### 3️⃣ **Réinitialiser la Base**
```
http://localhost/nsm-website/database/reset_database.php
```
🔄 Supprime tout et recrée à zéro

### 4️⃣ **Tester la Configuration**
```
http://localhost/nsm-website/database/test_database.php
```
✅ Vérifie que tout fonctionne correctement

---

## 📁 Fichiers Inclus

| Fichier | Description |
|---------|-------------|
| **index.php** | 🌐 Interface d'accueil pour gérer la BD |
| **reset_database.sql** | 📝 Script SQL complet (pour phpMyAdmin) |
| **reset_database.php** | 🔄 Script PHP pour réinitialiser automatiquement |
| **backup_database.php** | 💾 Crée une sauvegarde avant modifications |
| **test_database.php** | ✅ Teste et affiche les statistiques |
| **RESET_GUIDE.md** | 📖 Guide détaillé avec instructions |
| **DATABASE_RESET_INFO.md** | 📄 Ce fichier |

---

## 🎯 Ce qui est Créé

Après la réinitialisation, vous aurez:

### ✅ 9 Tables Principales
- `users` - Clients/Utilisateurs
- `admins` - Administrateurs  
- `service_categories` - Catégories de services
- `services` - Services disponibles
- `required_documents` - Documents par service
- `service_requests` - Demandes de service
- `payments` - Paiements
- `reviews` - Avis clients
- `blog_posts` - Articles de blog

### ✅ 6 Catégories de Services
1. Services Légaux (3 services)
2. Services Administratifs (3 services)
3. Services Commerciaux (3 services)
4. Services Traduction (3 services)
5. Services Nettoyage (3 services)
6. Services IT (3 services)

### ✅ 19 Services Complètement Configurés
Avec prix, durée de traitement, et documents requis

### ✅ 13 Documents Requis
Liés aux services appropriés

---

## 📖 Guide Détaillé

Pour les instructions étape par étape, consultez:
➡️ **[RESET_GUIDE.md](RESET_GUIDE.md)**

---

## 🔐 Identifiants de Connexion

```
Host: localhost
User: root
Password: (laissé vide)
Database: nsm_website
Charset: utf8mb4
```

Fichier de configuration: `../config/db.php`

---

## ⚡ Commandes SQL Utiles

```sql
-- Vérifier si la base existe
SHOW DATABASES;

-- Voir toutes les tables
USE nsm_website;
SHOW TABLES;

-- Compter les services
SELECT COUNT(*) FROM services;

-- Lister les catégories
SELECT * FROM service_categories;

-- Voir les documents requis pour un service
SELECT * FROM required_documents WHERE service_id = 1;
```

---

## 🛡️ Sécurité et Sauvegarde

### Avant de Réinitialiser

1. **Toujours créer une sauvegarde d'abord:**
   ```
   http://localhost/nsm-website/database/backup_database.php
   ```

2. **Les sauvegardes sont stockées dans:**
   ```
   database/backups/nsm_database_backup_YYYY-MM-DD_HH-MM-SS.sql
   ```

3. **Pour restaurer une sauvegarde:**
   - Ouvrez phpMyAdmin
   - Allez dans nsm_website
   - Cliquez sur "Importer"
   - Sélectionnez le fichier .sql

---

## 🔧 Dépannage

### Problème: "Access Denied"
→ Vérifiez les identifiants dans `../config/db.php`

### Problème: "Database cannot be created"
→ Supprimez d'abord la base existante dans phpMyAdmin

### Problème: Erreur d'encodage UTF-8
→ Assurez-vous que MySQL utilise utf8mb4

### Problème: Table manquante après import
→ Vérifiez que tout le script SQL a été exécuté

---

## 📊 Vérification Post-Réinitialisation

Après avoir réinitialisé, vérifiez que:

```
✅ 9 tables créées
✅ 6 catégories de services
✅ 19 services configurés
✅ 13 documents requis
✅ Pas de données utilisateur (normal, BD vierge)
✅ Toutes les relations (clés étrangères) en place
```

Utilisez pour cela: `test_database.php`

---

## 🎓 Étapes Recommandées

1. 💾 **Sauvegarde** - `backup_database.php`
2. 🔄 **Réinitialisation** - `reset_database.php`
3. ✅ **Test** - `test_database.php`
4. 🚀 **Utilisation** - Vous êtes prêt !

---

## 📞 Ressources Supplémentaires

- [Architecture de la Base de Données](../DATABASE_ARCHITECTURE.md)
- [Documentation API](../API_DOCUMENTATION.md)
- [Guide d'Installation](../INSTALLATION_SUMMARY.md)

---

**Dernière mise à jour:** 18 Janvier 2026  
**Version:** 1.0  
**Statut:** ✅ Prêt à l'emploi
