# Documentation API REST - NSM Website

## 🚀 Vue d'ensemble

L'API REST NSM permet de gérer les services, les demandes, les paiements et les avis du site.

**URL de base:** `/api/`

**Format des réponses:** JSON

## 📋 Endpoints

### 1. Services

#### Obtenir tous les services
```
GET /api/services
```

**Réponse (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "name": "Passeport",
      "description": "Obtention et renouvellement de passeport",
      "base_price": "150.00",
      "processing_time_min": 5,
      "processing_time_max": 10,
      "processing_time_unit": "jours",
      "category_name": "Services administratifs"
    }
  ],
  "count": 19
}
```

#### Obtenir un service avec détails
```
GET /api/services-detail/{serviceId}
```

**Paramètres:**
- `serviceId` (int): ID du service

**Réponse (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Passeport",
    "base_price": "150.00",
    "documents": [
      {
        "id": 1,
        "document_name": "Acte de naissance original",
        "is_required": true
      }
    ],
    "rating": {
      "average_rating": 4.5,
      "review_count": 12
    }
  }
}
```

#### Obtenir les catégories
```
GET /api/categories
```

**Réponse (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Services administratifs",
      "display_order": 1,
      "services": [
        {
          "id": 1,
          "name": "Passeport",
          "base_price": "150.00"
        }
      ]
    }
  ]
}
```

### 2. Demandes de Service

#### Créer une demande
```
POST /api/requests
```

**Corps de la requête:**
```json
{
  "user_id": 1,
  "service_id": 1,
  "quoted_price": 150.00,
  "notes": "Demande urgente"
}
```

**Réponse (201):**
```json
{
  "success": true,
  "message": "Demande créée",
  "id": 5
}
```

#### Obtenir les demandes d'un utilisateur
```
GET /api/requests?user_id={userId}
```

**Paramètres:**
- `user_id` (int): ID de l'utilisateur

**Réponse (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "service_id": 1,
      "request_number": "NSM-20240112-ABC1",
      "status": "pending",
      "quoted_price": "150.00",
      "service_name": "Passeport",
      "created_at": "2024-01-12T10:30:00"
    }
  ]
}
```

#### Obtenir une demande spécifique
```
GET /api/requests-detail/{requestId}
```

**Paramètres:**
- `requestId` (int): ID de la demande

**Réponse (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "request_number": "NSM-20240112-ABC1",
    "status": "paid",
    "payments": [
      {
        "id": 1,
        "amount": "150.00",
        "payment_method": "moncash",
        "payment_status": "confirmed",
        "confirmed_at": "2024-01-12T11:00:00"
      }
    ]
  }
}
```

### 3. Paiements

#### Créer un paiement
```
POST /api/payments
```

**Corps de la requête:**
```json
{
  "request_id": 1,
  "amount": 150.00,
  "payment_method": "moncash",
  "transaction_id": "MONCASH123456",
  "notes": "Paiement reçu via Moncash"
}
```

**Valeurs acceptées pour `payment_method`:**
- `moncash`
- `natcash`
- `bank_transfer`
- `other`

**Réponse (201):**
```json
{
  "success": true,
  "message": "Paiement enregistré",
  "id": 3
}
```

### 4. Avis

#### Obtenir les avis approuvés
```
GET /api/reviews
```

**Réponse (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "rating": 5,
      "comment": "Excellent service! Très rapide et professionnel.",
      "first_name": "Jean",
      "last_name": "Dupont",
      "service_name": "Passeport",
      "created_at": "2024-01-10T14:30:00"
    }
  ]
}
```

#### Créer un avis
```
POST /api/reviews
```

**Corps de la requête:**
```json
{
  "user_id": 1,
  "request_id": 1,
  "service_id": 1,
  "rating": 5,
  "comment": "Excellent service!"
}
```

**Validations:**
- `rating`: doit être entre 1 et 5
- `comment`: peut être nul mais recommandé

**Réponse (201):**
```json
{
  "success": true,
  "message": "Avis créé",
  "id": 5
}
```

### 5. Statistiques

#### Obtenir les statistiques
```
GET /api/stats
```

**Réponse (200):**
```json
{
  "success": true,
  "data": {
    "total_requests": 45,
    "total_revenue": 5250.00,
    "requests_stats": [
      {
        "status": "completed",
        "total": 30,
        "total_revenue": "4500.00",
        "avg_price": "150.00"
      }
    ],
    "services_stats": [
      {
        "id": 1,
        "name": "Passeport",
        "request_count": 12,
        "avg_rating": 4.7
      }
    ]
  }
}
```

## 🔄 Flux de Demande de Service

1. **Créer une demande** (POST `/api/requests`)
   - User crée une demande de service
   - Status: `pending`

2. **Enregistrer un paiement** (POST `/api/payments`)
   - User effectue un paiement via Moncash/Natcash
   - Payment status: `pending`

3. **Confirmer le paiement** (Admin only)
   - Admin confirme la réception du paiement
   - Request status: `paid`
   - Payment status: `confirmed`

4. **Traiter la demande** (Admin only)
   - Admin met à jour le statut à `in_progress`
   - Travail sur la demande

5. **Terminer la demande** (Admin only)
   - Admin met à jour le statut à `completed`
   - Client peut laisser un avis

6. **Créer un avis** (POST `/api/reviews`)
   - Client laisse un avis
   - Admin doit approuver l'avis

## 📊 Statuts des Demandes

| Statut | Description |
|--------|-------------|
| `pending` | Demande créée, en attente de paiement |
| `paid` | Paiement reçu et confirmé |
| `in_progress` | En cours de traitement |
| `completed` | Terminée |
| `cancelled` | Annulée |

## 💳 Statuts des Paiements

| Statut | Description |
|--------|-------------|
| `pending` | Paiement reçu, en attente de confirmation |
| `confirmed` | Confirmé par l'admin |
| `failed` | Paiement échoué |
| `refunded` | Remboursé |

## ⚡ Gestion des Erreurs

### Réponses d'erreur

**400 - Bad Request**
```json
{
  "success": false,
  "error": "user_id requis"
}
```

**404 - Not Found**
```json
{
  "success": false,
  "error": "Service non trouvé"
}
```

**500 - Server Error**
```json
{
  "success": false,
  "error": "Erreur serveur"
}
```

## 🔐 Sécurité

### Authentification (À implémenter)
À l'avenir, ajouter un système de tokens JWT:
```
Authorization: Bearer {token}
```

### Validation des données
- Tous les inputs sont validés côté serveur
- Utilisez des requêtes paramétrées pour éviter les injections SQL
- Échappez les données en sortie

### CORS
À configurer pour les domaines autorisés:
```php
header('Access-Control-Allow-Origin: https://nsm-haiti.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
```

## 📝 Exemples d'utilisation

### JavaScript/Fetch

```javascript
// Créer une demande
const response = await fetch('/api/requests', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    user_id: 1,
    service_id: 1,
    quoted_price: 150.00
  })
});

const result = await response.json();
console.log('Demande créée:', result.id);
```

### cURL

```bash
# Obtenir tous les services
curl -X GET "http://localhost/api/services"

# Créer une demande
curl -X POST "http://localhost/api/requests" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "service_id": 1,
    "quoted_price": 150.00
  }'
```

### Python

```python
import requests

# Obtenir les services
response = requests.get('http://localhost/api/services')
services = response.json()['data']

# Créer une demande
data = {
    'user_id': 1,
    'service_id': 1,
    'quoted_price': 150.00
}
response = requests.post('http://localhost/api/requests', json=data)
print(response.json())
```

## 📈 Limites et Quotas (À implémenter)

- 100 requêtes par minute par IP
- Taille maximum des uploads: 5MB
- Limite de 1000 demandes par utilisateur par mois

## 🔄 Versioning (Futur)

Pour les futures versions, utiliser:
- `/api/v2/services`
- `/api/v2/requests`

## 📞 Support

Pour des questions sur l'API:
- Email: contact@nsm-haiti.com
- WhatsApp: +50940317399

---
**Version:** 1.0
**Dernière mise à jour:** 12 Janvier 2026
