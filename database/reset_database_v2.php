<?php
/**
 * Script de Réinitialisation de la Base de Données NSM - VERSION 2
 * ⚠️  ATTENTION: Ce script supprime TOUT et recrée à zéro
 * Version améliorée avec meilleure gestion des erreurs
 */

// Configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "nsm_website";

// Créer connexion
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("❌ Erreur de connexion: " . $conn->connect_error);
}

echo "<h2>🔄 Réinitialisation de la Base de Données NSM (v2)</h2>";

// Étape 1: Supprimer la BD existante
echo "<p><strong>Étape 1:</strong> Suppression de la base de données existante...</p>";
if ($conn->query("DROP DATABASE IF EXISTS nsm_website") === TRUE) {
    echo "✅ Base de données supprimée (ou n'existait pas)<br>";
} else {
    die("❌ Erreur: " . $conn->error);
}

// Étape 2: Créer nouvelle BD
echo "<p><strong>Étape 2:</strong> Création de la nouvelle base de données...</p>";
if ($conn->query("CREATE DATABASE nsm_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === TRUE) {
    echo "✅ Base de données créée<br>";
} else {
    die("❌ Erreur: " . $conn->error);
}

// Sélectionner la base de données
if (!$conn->select_db("nsm_website")) {
    die("❌ Impossible de sélectionner nsm_website: " . $conn->error);
}

echo "<p><strong>Étape 3:</strong> Création des tables et données initiales...</p>";

// Créer les tables directement (approche plus fiable)
$tables_sql = [
    // TABLE USERS
    "CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        phone VARCHAR(20) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        address VARCHAR(255),
        city VARCHAR(100),
        country VARCHAR(100) DEFAULT 'Haiti',
        is_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_phone (phone)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // TABLE ADMINS
    "CREATE TABLE admins (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        permissions TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_admin_user (user_id),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // TABLE SERVICE_CATEGORIES
    "CREATE TABLE service_categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(50),
        display_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_name (name),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // TABLE SERVICES
    "CREATE TABLE services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        base_price DECIMAL(10, 2),
        processing_time_min INT,
        processing_time_max INT,
        processing_time_unit VARCHAR(20) DEFAULT 'jours',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE CASCADE,
        INDEX idx_category (category_id),
        INDEX idx_name (name),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // TABLE REQUIRED_DOCUMENTS
    "CREATE TABLE required_documents (
        id INT PRIMARY KEY AUTO_INCREMENT,
        service_id INT NOT NULL,
        document_name VARCHAR(200) NOT NULL,
        is_required BOOLEAN DEFAULT TRUE,
        notes TEXT,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
        INDEX idx_service (service_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // TABLE SERVICE_REQUESTS
    "CREATE TABLE service_requests (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        service_id INT NOT NULL,
        request_number VARCHAR(20) UNIQUE NOT NULL,
        status ENUM('pending', 'paid', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
        quoted_price DECIMAL(10, 2),
        final_price DECIMAL(10, 2),
        notes TEXT,
        admin_notes TEXT,
        expected_completion_date DATE,
        completed_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id),
        INDEX idx_user (user_id),
        INDEX idx_service (service_id),
        INDEX idx_status (status),
        INDEX idx_request_number (request_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // TABLE PAYMENTS
    "CREATE TABLE payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        request_id INT NOT NULL,
        user_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        payment_method ENUM('credit_card', 'bank_transfer', 'cash', 'paypal') DEFAULT 'credit_card',
        status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
        transaction_id VARCHAR(100),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id),
        INDEX idx_request (request_id),
        INDEX idx_user (user_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // TABLE REVIEWS
    "CREATE TABLE reviews (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        request_id INT NOT NULL,
        service_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        is_approved BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id),
        INDEX idx_service (service_id),
        INDEX idx_user (user_id),
        INDEX idx_approved (is_approved)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // TABLE BLOG_POSTS
    "CREATE TABLE blog_posts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        content LONGTEXT NOT NULL,
        excerpt VARCHAR(500),
        author VARCHAR(100),
        featured_image VARCHAR(255),
        category VARCHAR(100),
        is_published BOOLEAN DEFAULT FALSE,
        view_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published_at TIMESTAMP NULL,
        INDEX idx_published (is_published),
        INDEX idx_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

$tables_created = 0;

// Créer toutes les tables
foreach ($tables_sql as $sql) {
    if ($conn->query($sql) === TRUE) {
        $tables_created++;
    } else {
        echo "⚠️ Erreur création table: " . $conn->error . "<br>";
    }
}

echo "✅ $tables_created tables créées<br>";

// Insérer les données initiales
echo "<p><strong>Étape 4:</strong> Insertion des données initiales...</p>";

// Catégories
$insert_categories = [
    "INSERT INTO service_categories (name, description, icon, display_order) VALUES ('Services Légaux', 'Documents et services juridiques', 'legal', 1)",
    "INSERT INTO service_categories (name, description, icon, display_order) VALUES ('Services Administratifs', 'Documents administratifs et gouvernementaux', 'file', 2)",
    "INSERT INTO service_categories (name, description, icon, display_order) VALUES ('Services Commerciaux', 'Services pour entreprises et commerces', 'business', 3)",
    "INSERT INTO service_categories (name, description, icon, display_order) VALUES ('Services Traduction', 'Traduction de documents', 'translate', 4)",
    "INSERT INTO service_categories (name, description, icon, display_order) VALUES ('Services de Nettoyage', 'Services de nettoyage et maintenance', 'clean', 5)",
    "INSERT INTO service_categories (name, description, icon, display_order) VALUES ('Services IT', 'Services informatiques et techniques', 'computer', 6)"
];

$rows_inserted = 0;

foreach ($insert_categories as $sql) {
    if ($conn->query($sql) === TRUE) {
        $rows_inserted++;
    } else {
        echo "⚠️ " . $conn->error . "<br>";
    }
}

echo "✅ $rows_inserted catégories insérées<br>";

// Services
$insert_services = [
    // Services Légaux
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (1, 'Consultation Juridique', 'Consultation avec un avocat professionnel', 150.00, 1, 3)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (1, 'Rédaction de Contrat', 'Rédaction et révision de contrats', 250.00, 3, 7)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (1, 'Aide Demande de Passeport', 'Assistance pour demande de passeport', 100.00, 5, 10)",
    // Services Administratifs
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (2, 'Acte de Naissance', 'Obtention/certification acte de naissance', 50.00, 3, 5)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (2, 'Certificat de Résidence', 'Certificat de résidence attesté', 30.00, 2, 4)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (2, 'Aide Déclaration Fiscale', 'Assistance pour déclaration fiscale', 200.00, 5, 10)",
    // Services Commerciaux
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (3, 'Création Entreprise', 'Création et enregistrement entreprise', 500.00, 10, 20)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (3, 'Comptabilité', 'Services comptables pour PME', 300.00, 1, 5)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (3, 'Audit Financier', 'Audit et vérification comptable', 400.00, 7, 14)",
    // Services Traduction
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (4, 'Traduction Simple', 'Traduction de documents simples', 2.00, 1, 3)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (4, 'Traduction Certifiée', 'Traduction certifiée conforme', 5.00, 2, 5)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (4, 'Traduction Urgente', 'Traduction express 24h', 10.00, 0, 1)",
    // Services Nettoyage
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (5, 'Nettoyage Bureau', 'Nettoyage complet bureau/espace de travail', 80.00, 0, 1)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (5, 'Nettoyage Résidentiel', 'Nettoyage complet maison/appartement', 120.00, 0, 1)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (5, 'Maintenance Régulière', 'Service de nettoyage mensuel', 350.00, 1, 5)",
    // Services IT
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (6, 'Support Informatique', 'Support technique et assistance', 50.00, 0, 1)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (6, 'Création Site Web', 'Conception et développement site internet', 2000.00, 14, 30)",
    "INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max) VALUES (6, 'Maintenance Serveur', 'Maintenance et sauvegarde serveur', 200.00, 1, 7)"
];

$services_inserted = 0;

foreach ($insert_services as $sql) {
    if ($conn->query($sql) === TRUE) {
        $services_inserted++;
    } else {
        echo "⚠️ " . $conn->error . "<br>";
    }
}

echo "✅ $services_inserted services insérés<br>";

// Documents requis
$insert_docs = [
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (1, 'Pièce d\\'identité valide', TRUE, 1)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (1, 'Description du problème juridique', TRUE, 2)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (2, 'Détails des parties (entreprise/personne)', TRUE, 1)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (2, 'Conditions principales du contrat', TRUE, 2)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (3, 'Acte de naissance', TRUE, 1)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (3, 'Pièce d\\'identité', TRUE, 2)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (3, 'Certificat de résidence', TRUE, 3)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (4, 'Pièce d\\'identité du demandeur', TRUE, 1)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (7, 'Plan d\\'affaires', TRUE, 1)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (7, 'Statuts juridiques préliminaires', TRUE, 2)",
    "INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES (7, 'Pièce d\\'identité propriétaire', TRUE, 3)"
];

$docs_inserted = 0;

foreach ($insert_docs as $sql) {
    if ($conn->query($sql) === TRUE) {
        $docs_inserted++;
    } else {
        echo "⚠️ " . $conn->error . "<br>";
    }
}

echo "✅ $docs_inserted documents insérés<br>";

// Vérifications finales
echo "<h3>📊 Vérification Finale:</h3>";

$result = $conn->query("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'nsm_website'");
$row = $result->fetch_assoc();
echo "✅ Tables: " . $row['count'] . "<br>";

$result = $conn->query("SELECT COUNT(*) as count FROM service_categories");
$row = $result->fetch_assoc();
echo "✅ Catégories: " . $row['count'] . "<br>";

$result = $conn->query("SELECT COUNT(*) as count FROM services");
$row = $result->fetch_assoc();
echo "✅ Services: " . $row['count'] . "<br>";

$result = $conn->query("SELECT COUNT(*) as count FROM required_documents");
$row = $result->fetch_assoc();
echo "✅ Documents requis: " . $row['count'] . "<br>";

echo "<h3>🎉 Réinitialisation Réussie!</h3>";
echo "<p><strong>La base de données NSM est maintenant prête à l'emploi.</strong></p>";

echo "<h3>📋 Services par Catégorie:</h3>";
$result = $conn->query("
    SELECT sc.name, COUNT(s.id) as count 
    FROM service_categories sc 
    LEFT JOIN services s ON sc.id = s.category_id 
    GROUP BY sc.id, sc.name
    ORDER BY sc.display_order
");

if ($result) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['name']) . ": " . $row['count'] . " service(s)</li>";
    }
    echo "</ul>";
}

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
    h2, h3 { color: #333; }
    p { line-height: 1.6; }
    ul { list-style-type: none; padding-left: 0; }
    li { padding: 5px 0; }
</style>
