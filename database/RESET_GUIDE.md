# Guide Complet - Réinitialisation de la Base de Données NSM

## ⚠️ ATTENTION IMPORTANTE
Ce guide supprime **COMPLÈTEMENT** votre base de données actuelle et la recrée à zéro. Assurez-vous d'avoir une sauvegarde si vous avez des données importantes !

---

## 🎯 Objectifs de la Réinitialisation

✅ Créer une base de données vierge et structurée  
✅ Implémenter toutes les tables correctement  
✅ Initialiser les données de démonstration (catégories, services, documents)  
✅ S'assurer que toutes les contraintes et relations sont correctes  

---

## 📋 Étapes de Réinitialisation

### Option 1: Via Interface phpMyAdmin (Recommandé - Plus Simple)

#### 1. Ouvrir phpMyAdmin
- Allez dans votre navigateur: **http://localhost/phpmyadmin**
- Connectez-vous avec vos identifiants (généralement root / vide)

#### 2. Supprimer l'ancienne base
- Cliquez sur **"nsm_website"** dans la liste de gauche
- Cliquez sur l'onglet **"Opérations"**
- Cliquez sur **"Supprimer la base de données"** (icône de poubelle)
- Confirmez la suppression

#### 3. Créer une nouvelle base vierge
- Dans la colonne de gauche, cliquez sur **"Nouvelle base de données"**
- Nom: **nsm_website**
- Classement: **utf8mb4_unicode_ci**
- Cliquez sur **"Créer"**

#### 4. Importer le fichier SQL
- Sélectionnez la base **nsm_website**
- Cliquez sur l'onglet **"Importer"**
- Cliquez sur **"Choisir un fichier"**
- Sélectionnez: **database/reset_database.sql**
- Cliquez sur **"Exécuter"**

**✅ Voilà ! Votre base de données est réinitialisée !**

---

### Option 2: Via Script PHP (Automatisé)

#### 1. Accéder au script
- Ouvrez votre navigateur: **http://localhost/nsm-website/database/reset_database.php**

#### 2. Laisser le script s'exécuter
- Le script va:
  1. Supprimer la base existante
  2. Créer une nouvelle base
  3. Créer toutes les tables
  4. Insérer les données initiales
  5. Afficher les statistiques

**✅ Résultat instantané avec confirmation !**

---

### Option 3: Via Ligne de Commande MySQL (Avancé)

```bash
# 1. Ouvrir le terminal/invite de commande
# 2. Naviguer jusqu'à votre dossier MySQL
cd "C:\xampp\mysql\bin"

# 3. Connecter-vous à MySQL
mysql -u root -p

# 4. Dans l'invite MySQL, exécuter:
SOURCE C:/xampp/htdocs/nsm-website/database/reset_database.sql;

# 5. Vérifier la création
USE nsm_website;
SHOW TABLES;
```

---

## 📊 Structure de la Base Créée

### Tables Principales

| Table | Description | Enregistrements |
|-------|-------------|-----------------|
| **users** | Clients/Utilisateurs | 0 (à ajouter) |
| **admins** | Administrateurs | 0 (à ajouter) |
| **service_categories** | Catégories de services | 6 |
| **services** | Services disponibles | 19 |
| **required_documents** | Documents requis par service | 13 |
| **service_requests** | Demandes de service | 0 (à ajouter) |
| **payments** | Paiements | 0 (à ajouter) |
| **reviews** | Avis clients | 0 (à ajouter) |
| **blog_posts** | Articles de blog | 0 (à ajouter) |

### Catégories de Services Créées

1. **Services Légaux** (3 services)
   - Consultation Juridique (150 HTG)
   - Rédaction de Contrat (250 HTG)
   - Aide Demande de Passeport (100 HTG)

2. **Services Administratifs** (3 services)
   - Acte de Naissance (50 HTG)
   - Certificat de Résidence (30 HTG)
   - Aide Déclaration Fiscale (200 HTG)

3. **Services Commerciaux** (3 services)
   - Création Entreprise (500 HTG)
   - Comptabilité (300 HTG)
   - Audit Financier (400 HTG)

4. **Services Traduction** (3 services)
   - Traduction Simple (2 HTG/mot)
   - Traduction Certifiée (5 HTG/mot)
   - Traduction Urgente (10 HTG/mot)

5. **Services de Nettoyage** (3 services)
   - Nettoyage Bureau (80 HTG)
   - Nettoyage Résidentiel (120 HTG)
   - Maintenance Régulière (350 HTG)

6. **Services IT** (3 services)
   - Support Informatique (50 HTG)
   - Création Site Web (2000 HTG)
   - Maintenance Serveur (200 HTG)

---

## ✅ Vérification Post-Réinitialisation

Après la réinitialisation, vérifiez que tout fonctionne :

### 1. Via phpMyAdmin
- Connectez-vous à **nsm_website**
- Vérifiez que les 9 tables existent
- Cliquez sur chaque table pour voir les données

### 2. Via PHP Test Script
Créez un fichier `test_db.php`:

```php
<?php
require 'config/db.php';

// Compter les services
$result = $conn->query("SELECT COUNT(*) as count FROM services");
$row = $result->fetch_assoc();
echo "Services créés: " . $row['count'] . "<br>";

// Lister les catégories
$result = $conn->query("SELECT name FROM service_categories");
echo "<br>Catégories:<br>";
while ($row = $result->fetch_assoc()) {
    echo "- " . $row['name'] . "<br>";
}
?>
```

---

## 🔄 Ajouter Manuellement des Utilisateurs de Test

Après la réinitialisation, vous voudrez peut-être ajouter des utilisateurs de test.

Via phpMyAdmin, insérez dans la table `users`:

```sql
INSERT INTO users (first_name, last_name, email, phone, password_hash, address, city, country, is_verified)
VALUES 
('Jean', 'Client', 'jean@example.com', '509-2123-4567', MD5('password123'), '123 Rue Test', 'Port-au-Prince', 'Haiti', TRUE),
('Marie', 'Dupont', 'marie@example.com', '509-3456-7890', MD5('password123'), '456 Ave Test', 'Port-au-Prince', 'Haiti', FALSE);
```

---

## 📝 Checklist de Réinitialisation

- [ ] Sauvegarde de l'ancienne base (si nécessaire)
- [ ] Exécution du script de réinitialisation
- [ ] Vérification des 9 tables créées
- [ ] Vérification des 6 catégories
- [ ] Vérification des 19 services
- [ ] Test de connexion PHP (vérifier config/db.php)
- [ ] Vérification du site web
- [ ] Ajout d'utilisateurs de test si nécessaire

---

## 🆘 Dépannage

### Problème: "Access Denied" dans phpMyAdmin
**Solution:** Vérifiez vos identifiants dans [config/db.php](config/db.php)

### Problème: "Database cannot be created"
**Solution:** Supprimez d'abord la base existante dans phpMyAdmin

### Problème: Les tables ne se créent pas
**Solution:** Vérifiez que MySQL fonctionne
```bash
# Vérifier le statut de MySQL (Windows)
netstat -an | find "3306"
```

### Problème: Erreur d'encodage UTF-8
**Solution:** Assurez-vous que MySQL utilise utf8mb4
```sql
ALTER DATABASE nsm_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 📞 Informations de Connexion à la BD

```
Host: localhost
User: root
Password: (vide)
Database: nsm_website
Charset: utf8mb4
```

Mise à jour dans [config/db.php](config/db.php)

---

## ✨ Prochaines Étapes

Après la réinitialisation:

1. ✅ Testez le site web
2. ✅ Vérifiez que les formulaires fonctionnent
3. ✅ Testez les API (API_DOCUMENTATION.md)
4. ✅ Créez des comptes de test
5. ✅ Testez les services et demandes

---

**Créé:** 18 Janvier 2026  
**Dernière mise à jour:** 18 Janvier 2026
