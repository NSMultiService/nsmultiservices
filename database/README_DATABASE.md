# Configuration Base de Données MySQL - NSM Website

## 📋 Prérequis

- MySQL 5.7+ ou MariaDB 10.2+
- PHP 7.4+
- Accès administrateur à MySQL
- phpMyAdmin (optionnel mais recommandé)

## 🚀 Étapes d'Installation

### 1. Créer l'utilisateur MySQL

Connectez-vous à MySQL avec l'utilisateur root:

```bash
mysql -u root -p
```

Puis exécutez:

```sql
-- Créer l'utilisateur NSM
CREATE USER 'nsm_user'@'localhost' IDENTIFIED BY 'nsm_secure_pass';

-- Accorder les permissions
GRANT ALL PRIVILEGES ON nsm_website.* TO 'nsm_user'@'localhost';

-- Appliquer les modifications
FLUSH PRIVILEGES;
```

### 2. Importer la Base de Données

#### Option A: Via la ligne de commande

```bash
mysql -u nsm_user -p nsm_website < database/nsm_database.sql
```

Puis entrez le mot de passe: `nsm_secure_pass`

#### Option B: Via phpMyAdmin

1. Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
2. Cliquez sur "Nouvelle base de données"
3. Nommez-la `nsm_website`
4. Allez dans l'onglet "Importer"
5. Sélectionnez le fichier `database/nsm_database.sql`
6. Cliquez sur "Exécuter"

#### Option C: Via MySQL Workbench

1. Ouvrez MySQL Workbench
2. Connectez-vous avec root
3. Allez à File > Open SQL Script
4. Sélectionnez `database/nsm_database.sql`
5. Cliquez sur Execute

### 3. Configurer le fichier PHP

Éditez le fichier `includes/config.php`:

```php
// Modifier ces lignes avec vos paramètres
define('DB_USER', 'nsm_user');              // Utilisateur MySQL
define('DB_PASSWORD', 'nsm_secure_pass');   // Mot de passe MySQL
define('SMTP_USER', 'your-email@gmail.com');     // Email pour notifications
define('SMTP_PASSWORD', 'your-app-password');    // Mot de passe app Gmail
define('SITE_URL', 'http://localhost/nsm-website/');
```

### 4. Vérifier la Connexion

Créez un fichier `test_db.php`:

```php
<?php
require_once 'includes/config.php';

try {
    $query = new Query($db_connection);
    $result = $query->getOne("SELECT COUNT(*) as count FROM services");
    echo "✓ Connexion réussie!";
    echo "<br>Nombre de services: " . $result['count'];
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage();
}
?>
```

Puis accédez à: `http://localhost/nsm-website/test_db.php`

## 🗂️ Structure des Tables

### Utilisateurs
- `users`: Informations des clients et administrateurs
- `admins`: Rôles et permissions des administrateurs

### Services
- `service_categories`: Catégories (Admin, Impression, Informatique)
- `services`: Liste des services disponibles
- `required_documents`: Documents requis par service

### Demandes et Paiements
- `service_requests`: Demandes de service
- `payments`: Enregistrements de paiement

### Avis et Feedback
- `reviews`: Avis et ratings des clients
- `contact_messages`: Messages de contact

### Système
- `company_info`: Informations de l'entreprise
- `gallery_images`: Images de galerie
- `system_settings`: Paramètres du système
- `activity_logs`: Logs d'activité

## 🔐 Sécurité

### Bonnes Pratiques

1. **Changer les Mots de Passe**
   ```sql
   ALTER USER 'nsm_user'@'localhost' IDENTIFIED BY 'your_strong_password';
   ```

2. **Restreindre les Permissions**
   ```sql
   REVOKE ALL PRIVILEGES ON *.* FROM 'nsm_user'@'localhost';
   GRANT SELECT, INSERT, UPDATE, DELETE ON nsm_website.* TO 'nsm_user'@'localhost';
   ```

3. **Sauvegardes Régulières**
   ```bash
   mysqldump -u nsm_user -p nsm_website > backup_$(date +%Y%m%d).sql
   ```

4. **Utiliser des Requêtes Paramétrées** (fait dans config.php)
   ```php
   $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->execute([$email]);
   ```

## 📊 Vues SQL Disponibles

1. `v_requests_summary`: Résumé des demandes par statut
2. `v_approved_reviews`: Avis approuvés avec détails
3. `v_services_stats`: Statistiques des services

Utilisation:
```php
$stats = $query->getAll("SELECT * FROM v_services_stats");
```

## 🔧 Maintenance

### Vérifier la Santé de la Base de Données

```sql
-- Vérifier les tables corrompues
CHECK TABLE users, services, service_requests, payments, reviews;

-- Optimiser les tables
OPTIMIZE TABLE users, services, service_requests, payments, reviews;

-- Voir la taille de la base
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = 'nsm_website'
ORDER BY size_mb DESC;
```

### Ajouter des Données de Test

```sql
-- Insérer un utilisateur test
INSERT INTO users (first_name, last_name, email, phone, password_hash) VALUES
('Test', 'User', 'test@example.com', '+50912345678', SHA2('password123', 256));

-- Insérer une demande de service
INSERT INTO service_requests (user_id, service_id, request_number, status, quoted_price) VALUES
(1, 1, 'NSM-20240112-TEST', 'pending', 150.00);
```

## 📞 Support

Pour toute question ou problème:
- Email: contact@nsm-haiti.com
- WhatsApp: +50940317399

## 📝 Notes

- La base de données est prête pour la production
- Tous les statuts sont énumérés (ENUM) pour la sécurité
- Les indexes sont optimisés pour les recherches rapides
- Les relations de clé étrangère assurent l'intégrité des données

---
**Dernière mise à jour**: 12 Janvier 2026
