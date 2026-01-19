<?php
/**
 * Configuration Base de Données - EXEMPLE
 * 
 * ⚠️ IMPORTANT: Ce fichier est un EXEMPLE
 * Copiez-le en "db.php" et remplissez vos paramètres
 * 
 * Commandes:
 * - Linux/Mac: cp config/db.example.php config/db.php
 * - Windows: copy config\db.example.php config\db.php
 */

// ============================================
// 📝 CONFIGURATION DE LA BASE DE DONNÉES
// ============================================

// Serveur MySQL
define('DB_HOST', 'localhost');

// Utilisateur MySQL
define('DB_USER', 'root');

// Mot de passe MySQL (laisser vide si pas de mot de passe)
define('DB_PASS', '');

// Nom de la base de données
define('DB_NAME', 'nsm_website');

// Port MySQL (optionnel, 3306 par défaut)
define('DB_PORT', 3306);

// ============================================
// 🔗 CRÉATION DE LA CONNEXION
// ============================================

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    // Vérifier la connexion
    if ($conn->connect_error) {
        die("❌ Erreur de connexion: " . $conn->connect_error);
    }
    
    // Définir le charset UTF-8
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("❌ Erreur: " . $e->getMessage());
}

// ============================================
// 📋 NOTES DE CONFIGURATION
// ============================================

/*
 * INSTRUCTIONS D'INSTALLATION:
 * 
 * 1. Copiez ce fichier en "db.php"
 * 2. Modifiez les paramètres selon votre environnement
 * 3. Assurez-vous que MySQL est en cours d'exécution
 * 4. La base de données sera créée automatiquement
 * 
 * PARAMÈTRES PAR DÉFAUT (XAMPP):
 * - Host: localhost
 * - User: root
 * - Password: (vide)
 * - Database: nsm_website
 * - Port: 3306
 * 
 * ENVIRONNEMENTS DIFFÉRENTS:
 * 
 * Développement:
 * define('DB_HOST', 'localhost');
 * define('DB_USER', 'root');
 * define('DB_PASS', '');
 * 
 * Production (exemple):
 * define('DB_HOST', 'db.example.com');
 * define('DB_USER', 'nsm_user');
 * define('DB_PASS', 'PASSWORD_SECURE_HERE');
 */

?>
