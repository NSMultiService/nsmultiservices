/**
 * NSM API Client
 * Gère les appels API REST vers le serveur
 */

class NSMApiClient {
    constructor(baseUrl = '/api/') {
        this.baseUrl = baseUrl;
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
    }

    /**
     * Effectuer une requête API
     */
    async request(endpoint, method = 'GET', data = null) {
        try {
            const url = this.baseUrl + endpoint;
            const options = {
                method: method,
                headers: this.headers
            };

            if (data && (method === 'POST' || method === 'PUT')) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || 'Erreur API');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // ===== SERVICES =====

    /**
     * Obtenir tous les services
     */
    async getServices() {
        return this.request('services');
    }

    /**
     * Obtenir un service avec ses détails
     */
    async getServiceDetail(serviceId) {
        return this.request(`services-detail/${serviceId}`);
    }

    /**
     * Obtenir les catégories avec leurs services
     */
    async getCategories() {
        return this.request('categories');
    }

    // ===== DEMANDES =====

    /**
     * Créer une demande de service
     */
    async createRequest(userId, serviceId, quotedPrice = null, notes = null) {
        return this.request('requests', 'POST', {
            user_id: userId,
            service_id: serviceId,
            quoted_price: quotedPrice,
            notes: notes
        });
    }

    /**
     * Obtenir les demandes d'un utilisateur
     */
    async getUserRequests(userId) {
        return this.request(`requests?user_id=${userId}`);
    }

    /**
     * Obtenir une demande spécifique
     */
    async getRequestDetail(requestId) {
        return this.request(`requests-detail/${requestId}`);
    }

    // ===== PAIEMENTS =====

    /**
     * Créer un paiement
     */
    async createPayment(requestId, amount, paymentMethod, transactionId = null, notes = null) {
        return this.request('payments', 'POST', {
            request_id: requestId,
            amount: amount,
            payment_method: paymentMethod,
            transaction_id: transactionId,
            notes: notes
        });
    }

    // ===== AVIS =====

    /**
     * Obtenir les avis approuvés
     */
    async getReviews() {
        return this.request('reviews');
    }

    /**
     * Créer un avis
     */
    async createReview(userId, requestId, serviceId, rating, comment) {
        return this.request('reviews', 'POST', {
            user_id: userId,
            request_id: requestId,
            service_id: serviceId,
            rating: rating,
            comment: comment
        });
    }

    // ===== STATISTIQUES =====

    /**
     * Obtenir les statistiques
     */
    async getStats() {
        return this.request('stats');
    }
}

// Initialiser le client API
const apiClient = new NSMApiClient('/api/');

// ===== EXEMPLES D'UTILISATION =====

/**
 * Charger et afficher tous les services
 */
async function loadAllServices() {
    try {
        const response = await apiClient.getServices();
        console.log('Services:', response.data);
        
        // Afficher les services
        response.data.forEach(service => {
            console.log(`${service.name} - ${service.base_price}HTG`);
        });
    } catch (error) {
        console.error('Erreur:', error);
    }
}

/**
 * Créer une demande de service
 */
async function submitServiceRequest(userId, serviceId) {
    try {
        const response = await apiClient.createRequest(userId, serviceId);
        console.log('Demande créée:', response);
        alert('Votre demande a été créée avec succès!');
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la création de la demande');
    }
}

/**
 * Enregistrer un paiement
 */
async function submitPayment(requestId, amount, paymentMethod, transactionId) {
    try {
        const response = await apiClient.createPayment(requestId, amount, paymentMethod, transactionId);
        console.log('Paiement enregistré:', response);
        alert('Votre paiement a été enregistré!');
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'enregistrement du paiement');
    }
}

/**
 * Soumettre un avis
 */
async function submitReview(userId, requestId, serviceId, rating, comment) {
    try {
        const response = await apiClient.createReview(userId, requestId, serviceId, rating, comment);
        console.log('Avis créé:', response);
        alert('Merci pour votre avis!');
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'envoi de l\'avis');
    }
}

/**
 * Charger et afficher les avis
 */
async function loadReviews() {
    try {
        const response = await apiClient.getReviews();
        const reviews = response.data;

        // Exemple d'affichage dans une liste
        const reviewsList = document.getElementById('reviews-list');
        if (reviewsList) {
            reviewsList.innerHTML = reviews.map(review => `
                <div class="review-item">
                    <div class="review-rating">
                        ${'⭐'.repeat(review.rating)}
                    </div>
                    <div class="review-text">
                        <strong>${review.first_name} ${review.last_name}</strong>
                        <p>${review.comment}</p>
                        <small>${new Date(review.created_at).toLocaleDateString('fr-FR')}</small>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Erreur lors du chargement des avis:', error);
    }
}

/**
 * Charger et afficher les catégories
 */
async function loadCategories() {
    try {
        const response = await apiClient.getCategories();
        const categories = response.data;

        categories.forEach(category => {
            console.log(`${category.name} (${category.services.length} services)`);
            category.services.forEach(service => {
                console.log(`  - ${service.name}: ${service.base_price}HTG`);
            });
        });
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Attacher les fonctions au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    // Charger les avis si l'élément existe
    const reviewsList = document.getElementById('reviews-list');
    if (reviewsList) {
        loadReviews();
    }

    // Charger les services si nécessaire
    const servicesList = document.getElementById('services-list');
    if (servicesList) {
        loadAllServices();
    }
});

// Exporter pour utilisation en modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NSMApiClient;
}
