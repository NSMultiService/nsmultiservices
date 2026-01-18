# 📚 Résumé de l'Installation de la Base de Données MySQL

## ✅ Fichiers Créés

### 1. **database/nsm_database.sql** 
   - Script SQL complet pour créer la base de données
   - 13 tables principales
   - 3 vues SQL utiles
   - 20 services pré-insérés
   - Informations de l'entreprise
   - Paramètres système

### 2. **includes/config.php**
   - Configuration de la connexion à MySQL
   - Classe `Database` pour la connexion PDO
   - Classe `Query` pour les requêtes
   - Fonctions utilitaires (validation, logs, etc.)
   - Configuration des emails et du site

### 3. **includes/ServiceManager.php**
   - Classe pour gérer toutes les opérations
   - CRUD pour services, demandes, paiements, avis
   - Statistiques et rapports
   - Gestion des documents requis

### 4. **api.php**
   - API REST complète
   - 5 endpoints principaux
   - Gestion des erreurs
   - Validation des données

### 5. **assets/js/api-client.js**
   - Client JavaScript pour l'API
   - Fonctions async/await
   - Exemples d'utilisation
   - Intégration facile au frontend

### 6. **API_DOCUMENTATION.md**
   - Documentation complète de l'API
   - Tous les endpoints détaillés
   - Exemples de requêtes
   - Codes de réponse

### 7. **DATABASE_ARCHITECTURE.md**
   - Diagramme de relations
   - Structure de chaque table
   - Vues SQL
   - Stratégies d'optimisation

### 8. **database/README_DATABASE.md**
   - Guide d'installation étape par étape
   - Création d'utilisateur MySQL
   - Commandes d'import
   - Bonnes pratiques de sécurité

## 📊 Tables de la Base de Données

| Table | Description |
|-------|-------------|
| `users` | Clients et administrateurs |
| `service_categories` | Catégories de services (Admin, Impression, Informatique) |
| `services` | Services disponibles (19 au départ) |
| `required_documents` | Documents requis par service |
| `service_requests` | Demandes de service des clients |
| `payments` | Paiements (Moncash/Natcash) |
| `reviews` | Avis et évaluations |
| `contact_messages` | Messages de contact |
| `admins` | Administrateurs et leurs rôles |
| `company_info` | Informations de l'entreprise |
| `gallery_images` | Images de la galerie |
| `system_settings` | Paramètres du système |
| `activity_logs` | Logs d'activité |

## 🚀 Étapes d'Installation

### 1️⃣ Créer l'utilisateur MySQL
```bash
mysql -u root -p

CREATE USER 'nsm_user'@'localhost' IDENTIFIED BY 'nsm_secure_pass';
GRANT ALL PRIVILEGES ON nsm_website.* TO 'nsm_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2️⃣ Importer la base de données
```bash
mysql -u nsm_user -p nsm_website < database/nsm_database.sql
# Entrez le mot de passe: nsm_secure_pass
```

### 3️⃣ Configurer config.php
- Modifier les identifiants MySQL
- Configurer les emails SMTP
- Mettre à jour l'URL du site

### 4️⃣ Tester la connexion
```php
<?php
require_once 'includes/config.php';
$query = new Query($db_connection);
$count = $query->count("SELECT * FROM services");
echo "Services: " . $count; // Doit afficher: Services: 19
?>
```

## 🔌 Utilisation de l'API

### Obtenir les services
```javascript
const apiClient = new NSMApiClient('/api/');
const services = await apiClient.getServices();
console.log(services.data);
```

### Créer une demande
```javascript
const result = await apiClient.createRequest(
  userId,
  serviceId,
  quotedPrice,
  notes
);
```

### Enregistrer un paiement
```javascript
const payment = await apiClient.createPayment(
  requestId,
  amount,
  'moncash',
  transactionId
);
```

### Soumettre un avis
```javascript
const review = await apiClient.createReview(
  userId,
  requestId,
  serviceId,
  rating,  // 1-5
  comment
);
```

## 📁 Structure des Fichiers

```
nsm-website/
├── database/
│   ├── nsm_database.sql          ← Script SQL
│   └── README_DATABASE.md        ← Guide installation
├── includes/
│   ├── config.php                ← Configuration
│   └── ServiceManager.php         ← Gestion des services
├── api.php                        ← API REST
├── assets/js/
│   └── api-client.js             ← Client JavaScript
├── API_DOCUMENTATION.md          ← Doc API
├── DATABASE_ARCHITECTURE.md      ← Architecture BD
└── ... (autres fichiers)
```

## 🔐 Sécurité

✅ **Implémenté:**
- Mots de passe hashés SHA2
- Requêtes paramétrées (PDO)
- Validation des entrées
- Logs d'activité
- CORS (à configurer)

⏳ **À ajouter:**
- Authentification JWT
- Rate limiting
- HTTPS obligatoire
- 2FA pour les admins
- Chiffrement des données sensibles

## 📊 Données Pré-insérées

### Services (19)
- **Admin (10):** Passeport, Visa, etc.
- **Impression (6):** Photocopie, Plastification, etc.
- **Informatique (3):** Dépannage, Caméras, etc.

### Informations Entreprise
```
- Nom: Naderplus Solution Multi-Services
- Adresses: Baie-Tortue, La Gonâve & Léogâne
- Téléphones: 4 numéros
- Moncash: +509 34 80 4456 (Wilnader Jean)
- Natcash: +509 34 80 4456 (Wilnader Jean)
```

### Paramètres Système
- Devise: HTG (Gourde haïtienne)
- Paiement obligatoire avant service
- Notifications par email activées
- Taille max upload: 5MB

## 📈 Endpoints API Disponibles

```
GET    /api/services              → Tous les services
GET    /api/services-detail/{id}  → Service avec détails
GET    /api/categories            → Catégories avec services
POST   /api/requests              → Créer une demande
GET    /api/requests?user_id=     → Demandes d'un user
GET    /api/requests-detail/{id}  → Détails d'une demande
POST   /api/payments              → Enregistrer un paiement
GET    /api/reviews               → Avis approuvés
POST   /api/reviews               → Créer un avis
GET    /api/stats                 → Statistiques
```

## 🎯 Prochaines Étapes

1. **Développement du Frontend**
   - Formulaire de demande de service
   - Enregistrement client
   - Page de paiement

2. **Fonctionnalités Admin**
   - Dashboard de gestion
   - Approval des avis
   - Gestion des demandes
   - Rapports et statistiques

3. **Améliorations**
   - Système d'authentification JWT
   - Notifications par email
   - SMS pour confirmations
   - Intégration réelle Moncash/Natcash

4. **Sécurité**
   - Tests de pénétration
   - Sauvegardes automatiques
   - Monitoring 24/7

## 💡 Conseils d'Utilisation

### En Développement
```php
define('DEBUG_MODE', true);    // Afficher les erreurs
define('ENVIRONMENT', 'development');
```

### En Production
```php
define('DEBUG_MODE', false);   // Cacher les erreurs
define('ENVIRONMENT', 'production');
define('DB_HOST', 'db.nsm-haiti.com'); // IP du serveur BD
```

### Sauvegardes Quotidiennes
```bash
# Crontab (tous les jours à 2:00 AM)
0 2 * * * mysqldump -u nsm_user -p'password' nsm_website | gzip > /backups/nsm_$(date +\%Y\%m\%d).sql.gz
```

## 📞 Support

**Questions sur la BD?**
- Consultez: `database/README_DATABASE.md`
- Consultez: `DATABASE_ARCHITECTURE.md`

**Questions sur l'API?**
- Consultez: `API_DOCUMENTATION.md`

**Support technique:**
- Email: contact@nsm-haiti.com
- WhatsApp: +50940317399

## 📝 Contrôle de Version

```bash
# Faire un backup avant modifications
mysqldump -u nsm_user -p nsm_website > backup_pre_update.sql

# Faire des commits réguliers
git add database/
git commit -m "Update database schema"

# Versionner les changements de schéma
# v1.0 - Initial setup
# v1.1 - Add phone validation
# v1.2 - Add activity logs
```

## 🎓 Ressources Utiles

- [MySQL Documentation](https://dev.mysql.com/doc/)
- [PDO PHP](https://www.php.net/manual/en/book.pdo.php)
- [REST API Best Practices](https://restfulapi.net/)
- [SQL Optimization](https://use-the-index-luke.com/)

---

**Base de données prête! 🚀**

Pour des questions: contact@nsm-haiti.com

Dernière mise à jour: 12 Janvier 2026
