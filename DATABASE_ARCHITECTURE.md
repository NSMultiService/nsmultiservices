# Architecture de la Base de Données NSM

## 📊 Diagramme de Relations

```
┌─────────────────┐
│     USERS       │
├─────────────────┤
│ id (PK)         │
│ first_name      │
│ last_name       │
│ email (UNIQUE)  │
│ phone (UNIQUE)  │
│ password_hash   │
│ address         │
│ city            │
│ country         │
│ is_verified     │
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         │ 1:N
         │
    ┌────┴──────────────────────┐
    │                           │
    ▼                           ▼
┌──────────────────┐  ┌──────────────────┐
│ SERVICE_REQUESTS │  │     ADMINS       │
├──────────────────┤  ├──────────────────┤
│ id (PK)          │  │ id (PK)          │
│ user_id (FK)     │  │ user_id (FK)     │
│ service_id (FK)  │  │ role             │
│ request_number   │  │ permissions      │
│ status           │  │ is_active        │
│ quoted_price     │  │ created_at       │
│ final_price      │  └──────────────────┘
│ notes            │
│ created_at       │
└────────┬─────────┘
         │
         │ 1:N
         │
         ▼
    ┌─────────────┐
    │  PAYMENTS   │
    ├─────────────┤
    │ id (PK)     │
    │ request_id  │
    │ amount      │
    │ method      │
    │ status      │
    │ created_at  │
    └─────────────┘

┌──────────────────────┐
│SERVICE_CATEGORIES    │
├──────────────────────┤
│ id (PK)              │
│ name (UNIQUE)        │
│ description          │
│ icon                 │
│ display_order        │
│ is_active            │
│ created_at           │
└────────┬─────────────┘
         │
         │ 1:N
         │
         ▼
    ┌──────────────┐
    │  SERVICES    │
    ├──────────────┤
    │ id (PK)      │
    │ category_id  │
    │ name         │
    │ description  │
    │ base_price   │
    │ processing   │
    │   _time_min  │
    │ processing   │
    │   _time_max  │
    │ is_active    │
    └────┬─────────┘
         │
         │ 1:N
         │
    ┌────┴──────────────────────┐
    │                           │
    ▼                           ▼
┌──────────────────┐  ┌──────────────────┐
│REQUIRED_DOCUMENTS│  │    REVIEWS       │
├──────────────────┤  ├──────────────────┤
│ id (PK)          │  │ id (PK)          │
│ service_id (FK)  │  │ user_id (FK)     │
│ document_name    │  │ request_id (FK)  │
│ is_required      │  │ service_id (FK)  │
│ notes            │  │ rating (1-5)     │
│ display_order    │  │ comment          │
│ created_at       │  │ is_approved      │
└──────────────────┘  │ created_at       │
                      └──────────────────┘

┌──────────────────────┐
│  CONTACT_MESSAGES    │
├──────────────────────┤
│ id (PK)              │
│ name                 │
│ email                │
│ phone                │
│ subject              │
│ message              │
│ status               │
│ response             │
│ responded_by (FK)    │
│ created_at           │
└──────────────────────┘

┌──────────────────────┐
│  GALLERY_IMAGES      │
├──────────────────────┤
│ id (PK)              │
│ title                │
│ description          │
│ image_url            │
│ thumbnail_url        │
│ category             │
│ display_order        │
│ is_active            │
│ created_at           │
└──────────────────────┘

┌──────────────────────┐
│    COMPANY_INFO      │
├──────────────────────┤
│ id (PK)              │
│ company_name         │
│ company_short_name   │
│ email                │
│ phone1-4             │
│ address1-2           │
│ moncash_number       │
│ natcash_number       │
│ facebook_url         │
│ whatsapp_number      │
│ about_text           │
│ updated_at           │
└──────────────────────┘

┌──────────────────────┐
│  SYSTEM_SETTINGS     │
├──────────────────────┤
│ id (PK)              │
│ setting_key (UNIQUE) │
│ setting_value        │
│ data_type            │
│ description          │
│ updated_at           │
└──────────────────────┘

┌──────────────────────┐
│   ACTIVITY_LOGS      │
├──────────────────────┤
│ id (PK)              │
│ user_id (FK)         │
│ action               │
│ entity_type          │
│ entity_id            │
│ details              │
│ ip_address           │
│ user_agent           │
│ created_at           │
└──────────────────────┘
```

## 🔗 Relations Principales

### Users → Service Requests
- **Type:** One-to-Many (1:N)
- **Clé étrangère:** `service_requests.user_id`
- **Description:** Un utilisateur peut créer plusieurs demandes

### Service Requests → Payments
- **Type:** One-to-Many (1:N)
- **Clé étrangère:** `payments.request_id`
- **Description:** Une demande peut avoir plusieurs paiements

### Services → Service Requests
- **Type:** One-to-Many (1:N)
- **Clé étrangère:** `service_requests.service_id`
- **Description:** Un service peut être demandé plusieurs fois

### Services → Required Documents
- **Type:** One-to-Many (1:N)
- **Clé étrangère:** `required_documents.service_id`
- **Description:** Un service a plusieurs documents requis

### Services → Reviews
- **Type:** One-to-Many (1:N)
- **Clé étrangère:** `reviews.service_id`
- **Description:** Un service peut avoir plusieurs avis

### Service Categories → Services
- **Type:** One-to-Many (1:N)
- **Clé étrangère:** `services.category_id`
- **Description:** Une catégorie contient plusieurs services

## 📈 Indexes Principaux

```sql
-- Indexes pour les recherches rapides
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_services_name ON services(name);
CREATE INDEX idx_service_requests_user ON service_requests(user_id);
CREATE INDEX idx_service_requests_status ON service_requests(status);
CREATE INDEX idx_payments_status ON payments(payment_status);
CREATE INDEX idx_reviews_service ON reviews(service_id);
CREATE INDEX idx_reviews_approved ON reviews(is_approved);
```

## 🔍 Vues SQL

### v_requests_summary
Résumé des demandes groupées par statut:
```sql
SELECT 
    status,
    COUNT(*) as total,
    SUM(final_price) as total_revenue,
    AVG(final_price) as avg_price
FROM service_requests
GROUP BY status;
```

### v_approved_reviews
Avis approuvés avec les détails:
```sql
SELECT 
    r.id, r.rating, r.comment, r.created_at,
    u.first_name, u.last_name,
    s.name as service_name
FROM reviews r
LEFT JOIN users u ON r.user_id = u.id
LEFT JOIN services s ON r.service_id = s.id
WHERE r.is_approved = TRUE
ORDER BY r.created_at DESC;
```

### v_services_stats
Statistiques par service:
```sql
SELECT 
    s.id, s.name,
    COUNT(sr.id) as request_count,
    AVG(r.rating) as avg_rating
FROM services s
LEFT JOIN service_requests sr ON s.id = sr.service_id
LEFT JOIN reviews r ON s.id = r.service_id
GROUP BY s.id, s.name;
```

## 💾 Taille Estimée de la Base

Pour 10,000 demandes de service:

| Table | Taille Estimée |
|-------|----------------|
| users | 2-3 MB |
| services | < 1 MB |
| service_requests | 5-10 MB |
| payments | 3-5 MB |
| reviews | 2-4 MB |
| activity_logs | 10-20 MB |
| **TOTAL** | **25-45 MB** |

## 🔄 Stratégies de Sauvegarde

### Sauvegarde Complète
```bash
# Tous les jours
mysqldump -u nsm_user -p nsm_website > backup_complete_$(date +%Y%m%d).sql
```

### Sauvegarde Incrémentale
```bash
# Toutes les 6 heures
mysqldump -u nsm_user -p --single-transaction --quick nsm_website | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

## 🛡️ Intégrité des Données

### Contraintes de Clé Étrangère
```sql
-- Services
ALTER TABLE services 
ADD CONSTRAINT fk_services_category 
FOREIGN KEY (category_id) REFERENCES service_categories(id);

-- Service Requests
ALTER TABLE service_requests 
ADD CONSTRAINT fk_requests_user 
FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE service_requests 
ADD CONSTRAINT fk_requests_service 
FOREIGN KEY (service_id) REFERENCES services(id);

-- Payments
ALTER TABLE payments 
ADD CONSTRAINT fk_payments_request 
FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE;
```

### Contraintes de Vérification
```sql
-- Rating entre 1 et 5
ALTER TABLE reviews 
ADD CONSTRAINT check_rating 
CHECK (rating >= 1 AND rating <= 5);

-- Statuts valides
ALTER TABLE service_requests 
ADD CONSTRAINT check_status 
CHECK (status IN ('pending', 'paid', 'in_progress', 'completed', 'cancelled'));
```

## 🚀 Optimisation

### Partitioning par Date (Futur)
```sql
-- Partitionner les activity_logs par mois
ALTER TABLE activity_logs PARTITION BY RANGE (YEAR_MONTH(created_at)) (
  PARTITION p_202401 VALUES LESS THAN (202402),
  PARTITION p_202402 VALUES LESS THAN (202403),
  ...
);
```

## 📊 Métriques de Monitoring

Requêtes utiles pour le monitoring:

```sql
-- Nombre de demandes par jour
SELECT DATE(created_at) as date, COUNT(*) as count
FROM service_requests
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Revenu par jour
SELECT DATE(p.confirmed_at) as date, SUM(p.amount) as revenue
FROM payments p
WHERE p.payment_status = 'confirmed'
GROUP BY DATE(p.confirmed_at)
ORDER BY date DESC;

-- Services les plus demandés
SELECT s.name, COUNT(*) as request_count
FROM service_requests sr
JOIN services s ON sr.service_id = s.id
GROUP BY s.id, s.name
ORDER BY request_count DESC
LIMIT 10;

-- Taux de completion
SELECT 
  SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
  COUNT(*) as total,
  ROUND(100.0 * SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*), 2) as completion_rate
FROM service_requests;
```

---
**Version:** 1.0
**Dernière mise à jour:** 12 Janvier 2026
