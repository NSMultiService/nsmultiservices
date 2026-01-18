<?php
header('Content-Type: application/json');

// Simple file-based reviews API for quick persistence
$dataFile = __DIR__ . '/data/reviews.json';

// Ensure data folder exists
$dataDir = dirname($dataFile);
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Read existing reviews
function read_reviews($file){
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    $arr = json_decode($json, true);
    return is_array($arr) ? $arr : [];
}

// Save reviews atomically
function save_reviews($file, $arr){
    $tmp = $file . '.tmp';
    file_put_contents($tmp, json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    rename($tmp, $file);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET'){
    $reviews = read_reviews($dataFile);
    // sort by date desc
    usort($reviews, function($a,$b){
        return ($b['date'] ?? 0) - ($a['date'] ?? 0);
    });
    echo json_encode(array_values($reviews));
    exit;
}

if ($method === 'POST'){
    // Accept JSON body
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)){
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    $name = isset($data['name']) ? trim($data['name']) : '';
    $service = isset($data['service']) ? trim($data['service']) : '';
    $rating = isset($data['rating']) ? intval($data['rating']) : 0;
    $message = isset($data['message']) ? trim($data['message']) : '';

    if ($name === '' || $rating < 1 || $rating > 5 || $message === ''){
        http_response_code(422);
        echo json_encode(['error' => 'Missing or invalid fields']);
        exit;
    }

    $reviews = read_reviews($dataFile);
    $new = [
        'id' => (count($reviews) ? max(array_column($reviews, 'id')) + 1 : 1),
        'name' => $name,
        'service' => $service,
        'rating' => $rating,
        'message' => $message,
        'date' => time() * 1000 // ms to match JS
    ];

    $reviews[] = $new;
    save_reviews($dataFile, $reviews);

    http_response_code(201);
    echo json_encode(['success' => true, 'review' => $new]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

?>
