<?php
/**
 * Fichier de Configuration - Connexion Base de Données
 * NSM - Naderplus Solution Multi-Services
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
// Pour environnement de développement local (XAMPP), utiliser l'utilisateur root
define('DB_USER', 'root');
define('DB_PASSWORD', ''); // mot de passe vide par défaut pour XAMPP
define('DB_NAME', 'nsm_website');
define('DB_PORT', 3306);

// Configuration du site
define('SITE_URL', 'http://localhost/nsm-website/');
define('SITE_NAME', 'NSM - Naderplus Solution Multi-Services');

// Configuration des emails
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'contact@nsm-haiti.com');
define('FROM_NAME', 'NSM Services');

// Configuration des paiements
define('MONCASH_ACCOUNT', '+509 34 80 4456');
define('MONCASH_NAME', 'Wilnader Jean');
define('NATCASH_ACCOUNT', '+509 34 80 4456');
define('NATCASH_NAME', 'Wilnader Jean');

// Configuration de session
define('SESSION_TIMEOUT', 3600); // 1 heure en secondes

// Modes et environnement
define('ENVIRONMENT', 'production'); // 'development' ou 'production'
define('DEBUG_MODE', false);

// Classe de connexion à la base de données
class Database {
    private $connection;
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $user = DB_USER;
    private $password = DB_PASSWORD;
    private $port = DB_PORT;

    public function connect() {
        try {
            $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->db_name . ';charset=utf8mb4';
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, $this->user, $this->password, $options);
            
            if (DEBUG_MODE) {
                error_log('Database connected successfully');
            }
            
            return $this->connection;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                error_log('Database connection error: ' . $e->getMessage());
                die('Error: Database connection failed. ' . $e->getMessage());
            } else {
                die('Error: Database connection failed. Please contact the administrator.');
            }
        }
    }

    public function getConnection() {
        if (!$this->connection) {
            $this->connect();
        }
        return $this->connection;
    }
}

// Créer une instance globale de la base de données
$db = new Database();
$db_connection = $db->connect();

// Classe utilitaire pour les requêtes
class Query {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupérer un enregistrement
    public function getOne($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Query error: ' . $e->getMessage());
            return null;
        }
    }

    // Récupérer tous les enregistrements
    public function getAll($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Query error: ' . $e->getMessage());
            return [];
        }
    }

    // Exécuter une requête (INSERT, UPDATE, DELETE)
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            return [
                'success' => $result,
                'lastInsertId' => $this->pdo->lastInsertId(),
                'rowCount' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            error_log('Query error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Vérifier si un enregistrement existe
    public function exists($sql, $params = []) {
        $result = $this->getOne($sql, $params);
        return $result !== null;
    }

    // Compter les enregistrements
    public function count($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Query error: ' . $e->getMessage());
            return 0;
        }
    }
}

// Fonction utilitaire pour échapper les données
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Fonction utilitaire pour valider les emails
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction utilitaire pour valider les numéros de téléphone
function validatePhone($phone) {
    // Format haïtien: +509 XX XX XXXX
    $pattern = '/^\+509\s?[0-9]{8}$/';
    return preg_match($pattern, preg_replace('/\s+/', ' ', $phone));
}

// Fonction pour générer un numéro de demande unique
function generateRequestNumber() {
    $date = date('Ymd');
    $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    return 'NSM-' . $date . '-' . $random;
}

// Configuration des logs
function logActivity($user_id, $action, $entity_type = null, $entity_id = null, $details = null, $pdo = null) {
    global $db_connection;
    
    if (!$pdo) {
        $pdo = $db_connection;
    }

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $sql = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $action, $entity_type, $entity_id, $details, $ip_address, $user_agent]);
    } catch (PDOException $e) {
        error_log('Activity log error: ' . $e->getMessage());
    }
}

// Définir le fuseau horaire
date_default_timezone_set('America/Port-au-Prince');
