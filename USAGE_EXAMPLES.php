<?php
/**
 * EXEMPLES D'UTILISATION - NSM Website
 * Intégration de la base de données et API
 */

// ============================================
// 1. INITIALISER LA CONNEXION
// ============================================

require_once 'includes/config.php';
require_once 'includes/ServiceManager.php';

$serviceManager = new ServiceManager($db_connection);

// ============================================
// 2. AFFICHER LES SERVICES SUR LA PAGE
// ============================================

// Exemple pour services.html
function displayServicesByCategory() {
    global $serviceManager;
    
    $categories = $serviceManager->getAllCategories();
    
    foreach ($categories as $category) {
        echo '<h2>' . escape($category['name']) . '</h2>';
        
        $services = $serviceManager->getServicesByCategory($category['id']);
        echo '<ul>';
        
        foreach ($services as $service) {
            echo '<li>';
            echo escape($service['name']);
            if ($service['base_price']) {
                echo ' - ' . number_format($service['base_price'], 2, ',', ' ') . ' HTG';
            }
            echo '</li>';
        }
        
        echo '</ul>';
    }
}

// ============================================
// 3. AFFICHER LES AVIS SUR LA PAGE
// ============================================

// Exemple pour avis.html ou galerie
function displayApprovedReviews() {
    global $serviceManager;
    
    $reviews = $serviceManager->getApprovedReviews(5);
    
    foreach ($reviews as $review) {
        echo '<div class="review-card">';
        echo '<div class="rating">';
        echo str_repeat('⭐', $review['rating']);
        echo '</div>';
        echo '<p>' . escape($review['comment']) . '</p>';
        echo '<strong>' . escape($review['first_name'] . ' ' . $review['last_name']) . '</strong>';
        echo '<small>' . $review['service_name'] . '</small>';
        echo '</div>';
    }
}

// ============================================
// 4. TRAITER LA CRÉATION DE DEMANDE (AJAX)
// ============================================

// Route POST /api/requests ou handler direct
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_request'])) {
    $userId = intval($_POST['user_id']);
    $serviceId = intval($_POST['service_id']);
    
    // Validation
    if (!validateEmail($_POST['email']) || !validatePhone($_POST['phone'])) {
        http_response_code(400);
        die(json_encode(['error' => 'Email ou téléphone invalide']));
    }
    
    // Créer la demande
    $result = $serviceManager->createServiceRequest(
        $userId,
        $serviceId,
        $_POST['quoted_price'] ?? null,
        $_POST['notes'] ?? null
    );
    
    if ($result['success']) {
        // Envoyer un email de confirmation
        sendConfirmationEmail($userId, $serviceId, $result['lastInsertId']);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Votre demande a été créée avec succès!',
            'request_id' => $result['lastInsertId']
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Erreur lors de la création']);
    }
    exit;
}

// ============================================
// 5. TRAITER LE PAIEMENT
// ============================================

// Route POST /api/payments ou handler direct
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    $requestId = intval($_POST['request_id']);
    $amount = floatval($_POST['amount']);
    $paymentMethod = $_POST['payment_method']; // 'moncash' ou 'natcash'
    $transactionId = $_POST['transaction_id'] ?? null;
    
    // Vérifier que la demande existe
    $request = $serviceManager->getServiceRequest($requestId);
    if (!$request) {
        http_response_code(404);
        die(json_encode(['error' => 'Demande non trouvée']));
    }
    
    // Créer le paiement
    $result = $serviceManager->createPayment(
        $requestId,
        $amount,
        $paymentMethod,
        $transactionId,
        'Paiement soumis par le client'
    );
    
    if ($result['success']) {
        // Envoyer un email au client et à l'admin
        notifyPaymentSubmitted($requestId, $amount, $paymentMethod);
        
        echo json_encode([
            'success' => true,
            'message' => 'Paiement enregistré avec succès!',
            'instruction' => 'Votre paiement est en attente de confirmation. Vous recevrez une notification bientôt.'
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Erreur lors de l\'enregistrement du paiement']);
    }
    exit;
}

// ============================================
// 6. TRAITER LES AVIS
// ============================================

// Route POST /api/reviews ou handler direct
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $userId = intval($_POST['user_id']);
    $requestId = intval($_POST['request_id']);
    $serviceId = intval($_POST['service_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    // Validation du rating
    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        die(json_encode(['error' => 'La note doit être entre 1 et 5']));
    }
    
    // Créer l'avis
    $result = $serviceManager->createReview($userId, $requestId, $serviceId, $rating, $comment);
    
    if ($result['success']) {
        // Logger l'action
        logActivity($userId, 'create_review', 'reviews', $result['lastInsertId']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Merci pour votre avis! Il sera approuvé dans les 24h.'
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Erreur lors de l\'envoi de l\'avis']);
    }
    exit;
}

// ============================================
// 7. AFFICHER LES STATISTIQUES (ADMIN)
// ============================================

function displayAdminStats() {
    global $serviceManager;
    
    $requestsStats = $serviceManager->getRequestsStats();
    $serviceStats = $serviceManager->getServiceStats();
    $revenue = $serviceManager->getTotalRevenue();
    
    echo '<h2>Statistiques</h2>';
    echo '<p>Revenu total: ' . number_format($revenue['total_revenue'], 2) . ' HTG</p>';
    
    echo '<h3>Demandes par statut:</h3>';
    echo '<table>';
    echo '<tr><th>Statut</th><th>Nombre</th><th>Revenu</th></tr>';
    
    foreach ($requestsStats as $stat) {
        echo '<tr>';
        echo '<td>' . ucfirst($stat['status']) . '</td>';
        echo '<td>' . $stat['total'] . '</td>';
        echo '<td>' . number_format($stat['total_revenue'], 2) . ' HTG</td>';
        echo '</tr>';
    }
    
    echo '</table>';
}

// ============================================
// 8. OBTENIR UN SERVICE AVEC DOCUMENTS
// ============================================

function getServiceWithDocuments($serviceId) {
    global $serviceManager;
    
    $service = $serviceManager->getService($serviceId);
    $documents = $serviceManager->getRequiredDocuments($serviceId);
    $rating = $serviceManager->getServiceAverageRating($serviceId);
    
    return array_merge($service, [
        'documents' => $documents,
        'average_rating' => $rating['average_rating'],
        'review_count' => $rating['review_count']
    ]);
}

// ============================================
// 9. FONCTIONS DE NOTIFICATION
// ============================================

function sendConfirmationEmail($userId, $serviceId, $requestId) {
    global $serviceManager;
    
    $service = $serviceManager->getService($serviceId);
    
    $subject = "Confirmation de votre demande - NSM";
    $message = "
    Merci pour votre demande de: {$service['name']}
    
    Numéro de demande: NSM-$requestId
    
    Délai estimé: {$service['processing_time_min']}-{$service['processing_time_max']} {$service['processing_time_unit']}
    
    Étapes suivantes:
    1. Effectuez le paiement via Moncash ou Natcash
    2. Confirmez votre paiement
    3. Nous traiterons votre demande
    
    Questions? Contactez-nous via WhatsApp: +50940317399
    ";
    
    // TODO: Implémenter l'envoi d'email
    // mail($email, $subject, $message);
    
    logActivity($userId, 'send_email', 'service_requests', $requestId, $subject);
}

function notifyPaymentSubmitted($requestId, $amount, $paymentMethod) {
    $subject = "Paiement soumis - NSM";
    $message = "
    Paiement reçu: $amount HTG
    Méthode: " . ucfirst($paymentMethod) . "
    
    Votre paiement est en attente de confirmation.
    Vous recevrez une notification dès que c'est confirmé.
    ";
    
    // TODO: Implémenter l'envoi d'email
    logActivity(null, 'payment_submitted', 'payments', $requestId, $paymentMethod);
}

// ============================================
// 10. EXEMPLE D'UTILISATION EN HTML
// ============================================
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exemple NSM</title>
</head>
<body>

<!-- AFFICHER LES SERVICES -->
<section>
    <h1>Nos Services</h1>
    <?php displayServicesByCategory(); ?>
</section>

<!-- FORMULAIRE DE DEMANDE (JavaScript) -->
<section>
    <h2>Demander un Service</h2>
    <form id="requestForm">
        <select id="service" required>
            <option value="">Choisir un service...</option>
            <!-- Rempli par JavaScript via l'API -->
        </select>
        <input type="email" id="email" placeholder="Email" required>
        <input type="tel" id="phone" placeholder="Téléphone" required>
        <textarea id="notes" placeholder="Notes (optionnel)"></textarea>
        <button type="submit">Soumettre la demande</button>
    </form>
</section>

<!-- AFFICHER LES AVIS -->
<section>
    <h2>Avis de nos Clients</h2>
    <?php displayApprovedReviews(); ?>
</section>

<!-- CHARGER L'API CLIENT -->
<script src="assets/js/api-client.js"></script>
<script>
    // Initialiser le client API
    const api = new NSMApiClient('/api/');

    // Charger les services dans le formulaire
    async function loadServices() {
        const response = await api.getServices();
        const select = document.getElementById('service');
        
        response.data.forEach(service => {
            const option = document.createElement('option');
            option.value = service.id;
            option.textContent = service.name + ' - ' + service.base_price + ' HTG';
            select.appendChild(option);
        });
    }

    // Soumettre une demande
    document.getElementById('requestForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const serviceId = document.getElementById('service').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const notes = document.getElementById('notes').value;
        
        try {
            const result = await api.createRequest(1, serviceId, null, notes);
            alert(result.message);
        } catch (error) {
            alert('Erreur: ' + error.message);
        }
    });

    // Charger au démarrage
    document.addEventListener('DOMContentLoaded', loadServices);
</script>

</body>
</html>

<?php

// ============================================
// 11. REQUÊTE SQL PERSONNALISÉE
// ============================================

// Si vous avez besoin d'une requête custom non couverte par ServiceManager
$query = new Query($db_connection);

// Exemple: Obtenir les demandes en attente de paiement depuis 7 jours
$pendingRequests = $query->getAll(
    "SELECT sr.*, s.name FROM service_requests sr
     LEFT JOIN services s ON sr.service_id = s.id
     WHERE sr.status = 'pending' 
     AND sr.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     ORDER BY sr.created_at DESC"
);

foreach ($pendingRequests as $request) {
    echo "Demande: " . $request['request_number'] . " - " . $request['name'] . "\n";
}

// ============================================
// FIN DES EXEMPLES
// ============================================
