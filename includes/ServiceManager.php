<?php
/**
 * Classe Service Manager
 * Gère toutes les opérations relatives aux services
 */

require_once 'config.php';

class ServiceManager {
    private $pdo;
    private $query;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->query = new Query($pdo);
    }

    // ===== SERVICES =====

    /**
     * Obtenir tous les services
     */
    public function getAllServices($activeOnly = true) {
        $where = $activeOnly ? "WHERE s.is_active = TRUE" : "";
        $sql = "SELECT s.*, sc.name as category_name 
                FROM services s
                LEFT JOIN service_categories sc ON s.category_id = sc.id
                $where
                ORDER BY sc.display_order, s.name";
        return $this->query->getAll($sql);
    }

    /**
     * Obtenir les services par catégorie
     */
    public function getServicesByCategory($categoryId, $activeOnly = true) {
        $where = $activeOnly ? "AND s.is_active = TRUE" : "";
        $sql = "SELECT s.* FROM services s 
                WHERE s.category_id = ? $where 
                ORDER BY s.name";
        return $this->query->getAll($sql, [$categoryId]);
    }

    /**
     * Obtenir un service spécifique
     */
    public function getService($serviceId) {
        $sql = "SELECT s.*, sc.name as category_name 
                FROM services s
                LEFT JOIN service_categories sc ON s.category_id = sc.id
                WHERE s.id = ?";
        return $this->query->getOne($sql, [$serviceId]);
    }

    /**
     * Créer un nouveau service
     */
    public function createService($data) {
        $sql = "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max, processing_time_unit)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        return $this->query->execute($sql, [
            $data['category_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['base_price'] ?? null,
            $data['processing_time_min'] ?? null,
            $data['processing_time_max'] ?? null,
            $data['processing_time_unit'] ?? 'jours'
        ]);
    }

    /**
     * Mettre à jour un service
     */
    public function updateService($serviceId, $data) {
        $updates = [];
        $params = [];

        foreach (['name', 'description', 'base_price', 'processing_time_min', 'processing_time_max', 'processing_time_unit'] as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) return ['success' => false, 'error' => 'No data to update'];

        $params[] = $serviceId;
        $sql = "UPDATE services SET " . implode(', ', $updates) . " WHERE id = ?";
        return $this->query->execute($sql, $params);
    }

    /**
     * Supprimer un service
     */
    public function deleteService($serviceId) {
        $sql = "DELETE FROM services WHERE id = ?";
        return $this->query->execute($sql, [$serviceId]);
    }

    // ===== CATÉGORIES =====

    /**
     * Obtenir toutes les catégories
     */
    public function getAllCategories($activeOnly = true) {
        $where = $activeOnly ? "WHERE is_active = TRUE" : "";
        $sql = "SELECT * FROM service_categories $where ORDER BY display_order";
        return $this->query->getAll($sql);
    }

    /**
     * Obtenir une catégorie
     */
    public function getCategory($categoryId) {
        $sql = "SELECT * FROM service_categories WHERE id = ?";
        return $this->query->getOne($sql, [$categoryId]);
    }

    /**
     * Créer une catégorie
     */
    public function createCategory($data) {
        $sql = "INSERT INTO service_categories (name, description, icon, display_order)
                VALUES (?, ?, ?, ?)";
        return $this->query->execute($sql, [
            $data['name'],
            $data['description'] ?? null,
            $data['icon'] ?? null,
            $data['display_order'] ?? 0
        ]);
    }

    // ===== DOCUMENTS REQUIS =====

    /**
     * Obtenir les documents requis pour un service
     */
    public function getRequiredDocuments($serviceId) {
        $sql = "SELECT * FROM required_documents 
                WHERE service_id = ? 
                ORDER BY display_order";
        return $this->query->getAll($sql, [$serviceId]);
    }

    /**
     * Ajouter un document requis
     */
    public function addRequiredDocument($serviceId, $documentName, $isRequired = true, $notes = null) {
        $sql = "INSERT INTO required_documents (service_id, document_name, is_required, notes)
                VALUES (?, ?, ?, ?)";
        return $this->query->execute($sql, [$serviceId, $documentName, $isRequired ? 1 : 0, $notes]);
    }

    /**
     * Supprimer un document requis
     */
    public function deleteRequiredDocument($documentId) {
        $sql = "DELETE FROM required_documents WHERE id = ?";
        return $this->query->execute($sql, [$documentId]);
    }

    // ===== DEMANDES DE SERVICE =====

    /**
     * Créer une demande de service
     */
    public function createServiceRequest($userId, $serviceId, $quotedPrice = null, $notes = null) {
        $requestNumber = generateRequestNumber();
        $sql = "INSERT INTO service_requests (user_id, service_id, request_number, status, quoted_price, notes)
                VALUES (?, ?, ?, 'pending', ?, ?)";
        return $this->query->execute($sql, [$userId, $serviceId, $requestNumber, $quotedPrice, $notes]);
    }

    /**
     * Obtenir une demande de service
     */
    public function getServiceRequest($requestId) {
        $sql = "SELECT sr.*, s.name as service_name, u.first_name, u.last_name, u.email, u.phone
                FROM service_requests sr
                LEFT JOIN services s ON sr.service_id = s.id
                LEFT JOIN users u ON sr.user_id = u.id
                WHERE sr.id = ?";
        return $this->query->getOne($sql, [$requestId]);
    }

    /**
     * Obtenir les demandes d'un utilisateur
     */
    public function getUserRequests($userId) {
        $sql = "SELECT sr.*, s.name as service_name, s.category_id
                FROM service_requests sr
                LEFT JOIN services s ON sr.service_id = s.id
                WHERE sr.user_id = ?
                ORDER BY sr.created_at DESC";
        return $this->query->getAll($sql, [$userId]);
    }

    /**
     * Mettre à jour le statut d'une demande
     */
    public function updateRequestStatus($requestId, $status) {
        $validStatuses = ['pending', 'paid', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'error' => 'Invalid status'];
        }

        $sql = "UPDATE service_requests SET status = ? WHERE id = ?";
        return $this->query->execute($sql, [$status, $requestId]);
    }

    /**
     * Obtenir les demandes par statut
     */
    public function getRequestsByStatus($status) {
        $sql = "SELECT sr.*, s.name as service_name, u.first_name, u.last_name
                FROM service_requests sr
                LEFT JOIN services s ON sr.service_id = s.id
                LEFT JOIN users u ON sr.user_id = u.id
                WHERE sr.status = ?
                ORDER BY sr.created_at DESC";
        return $this->query->getAll($sql, [$status]);
    }

    // ===== PAIEMENTS =====

    /**
     * Créer un paiement
     */
    public function createPayment($requestId, $amount, $paymentMethod, $transactionId = null, $notes = null) {
        $sql = "INSERT INTO payments (request_id, amount, payment_method, transaction_id, notes, payment_status)
                VALUES (?, ?, ?, ?, ?, 'pending')";
        return $this->query->execute($sql, [$requestId, $amount, $paymentMethod, $transactionId, $notes]);
    }

    /**
     * Confirmer un paiement
     */
    public function confirmPayment($paymentId, $confirmedBy) {
        $sql = "UPDATE payments SET payment_status = 'confirmed', confirmed_by = ?, confirmed_at = NOW()
                WHERE id = ?";
        $result = $this->query->execute($sql, [$confirmedBy, $paymentId]);

        if ($result['success']) {
            // Mettre à jour le statut de la demande en "paid"
            $payment = $this->getPayment($paymentId);
            $this->updateRequestStatus($payment['request_id'], 'paid');
        }

        return $result;
    }

    /**
     * Obtenir un paiement
     */
    public function getPayment($paymentId) {
        $sql = "SELECT * FROM payments WHERE id = ?";
        return $this->query->getOne($sql, [$paymentId]);
    }

    /**
     * Obtenir les paiements d'une demande
     */
    public function getRequestPayments($requestId) {
        $sql = "SELECT * FROM payments WHERE request_id = ? ORDER BY created_at DESC";
        return $this->query->getAll($sql, [$requestId]);
    }

    // ===== AVIS =====

    /**
     * Créer un avis
     */
    public function createReview($userId, $requestId, $serviceId, $rating, $comment) {
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'error' => 'Rating must be between 1 and 5'];
        }

        $sql = "INSERT INTO reviews (user_id, request_id, service_id, rating, comment)
                VALUES (?, ?, ?, ?, ?)";
        return $this->query->execute($sql, [$userId, $requestId, $serviceId, $rating, $comment]);
    }

    /**
     * Obtenir les avis approuvés
     */
    public function getApprovedReviews($limit = 10) {
        $sql = "SELECT r.*, u.first_name, u.last_name, s.name as service_name
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN services s ON r.service_id = s.id
                WHERE r.is_approved = TRUE
                ORDER BY r.created_at DESC
                LIMIT ?";
        return $this->query->getAll($sql, [$limit]);
    }

    /**
     * Obtenir la note moyenne d'un service
     */
    public function getServiceAverageRating($serviceId) {
        $sql = "SELECT AVG(rating) as average_rating, COUNT(*) as review_count
                FROM reviews
                WHERE service_id = ? AND is_approved = TRUE";
        return $this->query->getOne($sql, [$serviceId]);
    }

    /**
     * Approuver un avis
     */
    public function approveReview($reviewId) {
        $sql = "UPDATE reviews SET is_approved = TRUE, approved_at = NOW() WHERE id = ?";
        return $this->query->execute($sql, [$reviewId]);
    }

    // ===== STATISTIQUES =====

    /**
     * Obtenir les statistiques des services
     */
    public function getServiceStats() {
        $sql = "SELECT * FROM v_services_stats ORDER BY request_count DESC";
        return $this->query->getAll($sql);
    }

    /**
     * Obtenir les statistiques des demandes
     */
    public function getRequestsStats() {
        $sql = "SELECT * FROM v_requests_summary";
        return $this->query->getAll($sql);
    }

    /**
     * Obtenir le revenu total
     */
    public function getTotalRevenue($startDate = null, $endDate = null) {
        $where = "";
        $params = [];

        if ($startDate && $endDate) {
            $where = "WHERE p.confirmed_at BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }

        $sql = "SELECT SUM(p.amount) as total_revenue, COUNT(DISTINCT p.request_id) as total_payments
                FROM payments p
                WHERE p.payment_status = 'confirmed' $where";
        
        return $this->query->getOne($sql, $params);
    }

    /**
     * Obtenir le nombre de demandes
     */
    public function getTotalRequests() {
        $sql = "SELECT COUNT(*) as total FROM service_requests";
        $result = $this->query->getOne($sql);
        return $result['total'] ?? 0;
    }

    /**
     * Obtenir les demandes en attente de paiement
     */
    public function getPendingPaymentRequests() {
        $sql = "SELECT sr.*, s.name as service_name, u.first_name, u.last_name, u.email
                FROM service_requests sr
                LEFT JOIN services s ON sr.service_id = s.id
                LEFT JOIN users u ON sr.user_id = u.id
                WHERE sr.status = 'pending'
                ORDER BY sr.created_at DESC";
        return $this->query->getAll($sql);
    }
}

// Exemple d'utilisation
/*
require_once 'includes/config.php';
$serviceManager = new ServiceManager($db_connection);

// Obtenir tous les services
$services = $serviceManager->getAllServices();

// Obtenir les avis approuvés
$reviews = $serviceManager->getApprovedReviews();

// Créer une demande de service
$result = $serviceManager->createServiceRequest(1, 1, 150.00);
echo $result['lastInsertId']; // ID de la demande créée
*/
