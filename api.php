<?php
/**
 * API REST - NSM Website
 * Gère les requêtes API pour les services
 */

header('Content-Type: application/json; charset=utf-8');

require_once 'includes/config.php';
require_once 'includes/ServiceManager.php';

// Initialiser le gestionnaire de services
$serviceManager = new ServiceManager($db_connection);

// Obtenir la méthode HTTP et le endpoint
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Endpoint: /api/services
if (isset($path_parts[1]) && $path_parts[1] === 'api' && isset($path_parts[2])) {
    $endpoint = $path_parts[2];
    $id = $path_parts[3] ?? null;

    try {
        switch ($endpoint) {
            // ===== SERVICES =====
            case 'services':
                if ($method === 'GET') {
                    // Obtenir tous les services
                    $services = $serviceManager->getAllServices();
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'data' => $services,
                        'count' => count($services)
                    ]);
                } elseif ($method === 'POST') {
                    // Créer un service (admin only)
                    $data = json_decode(file_get_contents('php://input'), true);
                    $result = $serviceManager->createService($data);
                    
                    if ($result['success']) {
                        http_response_code(201);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Service créé avec succès',
                            'id' => $result['lastInsertId']
                        ]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Erreur lors de la création']);
                    }
                }
                break;

            case 'services-detail':
                if ($method === 'GET' && $id) {
                    // Obtenir un service spécifique avec ses documents
                    $service = $serviceManager->getService($id);
                    $documents = $serviceManager->getRequiredDocuments($id);
                    $rating = $serviceManager->getServiceAverageRating($id);
                    
                    if ($service) {
                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'data' => array_merge($service, [
                                'documents' => $documents,
                                'rating' => $rating
                            ])
                        ]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Service non trouvé']);
                    }
                }
                break;

            case 'categories':
                if ($method === 'GET') {
                    // Obtenir toutes les catégories avec leurs services
                    $categories = $serviceManager->getAllCategories();
                    
                    foreach ($categories as &$category) {
                        $category['services'] = $serviceManager->getServicesByCategory($category['id']);
                    }
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'data' => $categories
                    ]);
                }
                break;

            // ===== DEMANDES =====
            case 'requests':
                if ($method === 'GET') {
                    // Obtenir les demandes par utilisateur
                    $userId = $_GET['user_id'] ?? null;
                    if ($userId) {
                        $requests = $serviceManager->getUserRequests($userId);
                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'data' => $requests
                        ]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'user_id requis']);
                    }
                } elseif ($method === 'POST') {
                    // Créer une demande
                    $data = json_decode(file_get_contents('php://input'), true);
                    $result = $serviceManager->createServiceRequest(
                        $data['user_id'],
                        $data['service_id'],
                        $data['quoted_price'] ?? null,
                        $data['notes'] ?? null
                    );
                    
                    if ($result['success']) {
                        http_response_code(201);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Demande créée',
                            'id' => $result['lastInsertId']
                        ]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Erreur']);
                    }
                }
                break;

            case 'requests-detail':
                if ($method === 'GET' && $id) {
                    // Obtenir une demande spécifique
                    $request = $serviceManager->getServiceRequest($id);
                    $payments = $serviceManager->getRequestPayments($id);
                    
                    if ($request) {
                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'data' => array_merge($request, ['payments' => $payments])
                        ]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Demande non trouvée']);
                    }
                }
                break;

            // ===== PAIEMENTS =====
            case 'payments':
                if ($method === 'POST') {
                    // Créer un paiement
                    $data = json_decode(file_get_contents('php://input'), true);
                    $result = $serviceManager->createPayment(
                        $data['request_id'],
                        $data['amount'],
                        $data['payment_method'],
                        $data['transaction_id'] ?? null,
                        $data['notes'] ?? null
                    );
                    
                    if ($result['success']) {
                        http_response_code(201);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Paiement enregistré',
                            'id' => $result['lastInsertId']
                        ]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Erreur']);
                    }
                }
                break;

            // ===== AVIS =====
            case 'reviews':
                if ($method === 'GET') {
                    // Obtenir les avis approuvés
                    $reviews = $serviceManager->getApprovedReviews();
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'data' => $reviews
                    ]);
                } elseif ($method === 'POST') {
                    // Créer un avis
                    $data = json_decode(file_get_contents('php://input'), true);
                    $result = $serviceManager->createReview(
                        $data['user_id'],
                        $data['request_id'],
                        $data['service_id'],
                        $data['rating'],
                        $data['comment']
                    );
                    
                    if ($result['success']) {
                        http_response_code(201);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Avis créé',
                            'id' => $result['lastInsertId']
                        ]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => $result['error']]);
                    }
                }
                break;

            // ===== STATISTIQUES =====
            case 'stats':
                if ($method === 'GET') {
                    // Obtenir les statistiques
                    $requestsStats = $serviceManager->getRequestsStats();
                    $servicesStats = $serviceManager->getServiceStats();
                    $totalRevenue = $serviceManager->getTotalRevenue();
                    $totalRequests = $serviceManager->getTotalRequests();
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'total_requests' => $totalRequests,
                            'total_revenue' => $totalRevenue['total_revenue'],
                            'requests_stats' => $requestsStats,
                            'services_stats' => $servicesStats
                        ]
                    ]);
                }
                break;

            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Endpoint non trouvé']);
                break;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => DEBUG_MODE ? $e->getMessage() : 'Erreur serveur'
        ]);
    }
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'API non trouvée']);
}
