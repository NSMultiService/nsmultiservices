-- ============================================
-- Base de Données NSM - Naderplus Solution Multi-Services
-- ============================================
-- Cette base de données gère les services, les demandes, les paiements et les avis

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS nsm_website;
USE nsm_website;

-- ============================================
-- 1. TABLE: UTILISATEURS (CLIENTS)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
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
);

-- ============================================
-- 2. TABLE: CATEGORIES DE SERVICES
-- ============================================
CREATE TABLE IF NOT EXISTS service_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(10),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- ============================================
-- 3. TABLE: SERVICES
-- ============================================
CREATE TABLE IF NOT EXISTS services (
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
    FOREIGN KEY (category_id) REFERENCES service_categories(id),
    INDEX idx_category (category_id),
    INDEX idx_name (name)
);

-- ============================================
-- 4. TABLE: DOCUMENTS REQUIS PAR SERVICE
-- ============================================
CREATE TABLE IF NOT EXISTS required_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT NOT NULL,
    document_name VARCHAR(200) NOT NULL,
    is_required BOOLEAN DEFAULT TRUE,
    notes TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service (service_id)
);

-- ============================================
-- 5. TABLE: DEMANDES DE SERVICE
-- ============================================
CREATE TABLE IF NOT EXISTS service_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    request_number VARCHAR(20) UNIQUE,
    status ENUM('pending', 'paid', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    quoted_price DECIMAL(10, 2),
    final_price DECIMAL(10, 2),
    notes TEXT,
    admin_notes TEXT,
    expected_completion_date DATE,
    completed_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_request_number (request_number)
);

-- ============================================
-- 6. TABLE: PAIEMENTS
-- ============================================
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('moncash', 'natcash', 'bank_transfer', 'other') DEFAULT 'moncash',
    payment_status ENUM('pending', 'confirmed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    payment_proof_url VARCHAR(255),
    notes TEXT,
    confirmed_by INT,
    confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES service_requests(id),
    FOREIGN KEY (confirmed_by) REFERENCES users(id),
    INDEX idx_request (request_id),
    INDEX idx_status (payment_status),
    INDEX idx_transaction (transaction_id)
);

-- ============================================
-- 7. TABLE: AVIS/REVIEWS
-- ============================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    request_id INT,
    service_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (request_id) REFERENCES service_requests(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX idx_service (service_id),
    INDEX idx_approved (is_approved),
    INDEX idx_rating (rating)
);

-- ============================================
-- 8. TABLE: MESSAGES DE CONTACT
-- ============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'responded', 'closed') DEFAULT 'new',
    response TEXT,
    responded_by INT,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responded_by) REFERENCES users(id),
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- ============================================
-- 9. TABLE: ADMINISTRATEURS
-- ============================================
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    role ENUM('admin', 'manager', 'support') DEFAULT 'support',
    permissions VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_role (role)
);

-- ============================================
-- 10. TABLE: LOGS D'ACTIVITÉ
-- ============================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

-- ============================================
-- 11. TABLE: GALERIE/IMAGES
-- ============================================
CREATE TABLE IF NOT EXISTS gallery_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(150),
    description TEXT,
    image_url VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(255),
    category VARCHAR(50),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category)
);

-- ============================================
-- 12. TABLE: INFORMATIONS ENTREPRISE
-- ============================================
CREATE TABLE IF NOT EXISTS company_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(200) NOT NULL,
    company_short_name VARCHAR(50),
    email VARCHAR(150),
    phone1 VARCHAR(20),
    phone2 VARCHAR(20),
    phone3 VARCHAR(20),
    phone4 VARCHAR(20),
    address1 VARCHAR(255),
    city1 VARCHAR(100),
    country1 VARCHAR(100),
    address2 VARCHAR(255),
    city2 VARCHAR(100),
    country2 VARCHAR(100),
    website VARCHAR(255),
    moncash_number VARCHAR(50),
    moncash_name VARCHAR(100),
    natcash_number VARCHAR(50),
    natcash_name VARCHAR(100),
    facebook_url VARCHAR(255),
    whatsapp_number VARCHAR(20),
    instagram_url VARCHAR(255),
    about_text TEXT,
    terms_conditions TEXT,
    privacy_policy TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- 13. TABLE: PARAMÈTRES DU SYSTÈME
-- ============================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    data_type VARCHAR(20) DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);

-- ============================================
-- INSERTIONS DE DONNÉES INITIALES
-- ============================================

-- Insérer les catégories de services
INSERT INTO service_categories (name, description, icon, display_order) VALUES
('Services administratifs', 'Demandes et obtention de documents officiels', '📋', 1),
('Impression & bureautique', 'Services d\'impression et traitement de documents', '📄', 2),
('Informatique & sécurité', 'Services informatiques et de sécurité', '💻', 3);

-- Insérer les services administratifs
INSERT INTO services (category_id, name, description, base_price, processing_time_min, processing_time_max, processing_time_unit) VALUES
(1, 'Passeport', 'Obtention et renouvellement de passeport', 150.00, 5, 10, 'jours'),
(1, 'Extrait d\'archive', 'Demande d\'extrait d\'archive officiel', 50.00, 3, 7, 'jours'),
(1, 'Casier judiciaire TPI / DCPJ', 'Casier judiciaire officiel', 75.00, 4, 8, 'jours'),
(1, 'Permis de conduire', 'Demande de permis de conduire', 200.00, 7, 15, 'jours'),
(1, 'Demande de diplôme fin d\'études', 'Demande de diplôme scolaire', 60.00, 5, 10, 'jours'),
(1, 'Demande visa Brésil (R-V)', 'Assistance pour demande de visa Brésil', 300.00, 15, 30, 'jours'),
(1, 'Légalisation des pièces', 'Légalisation officielle de documents', 80.00, 3, 5, 'jours'),
(1, 'Acte de mariage', 'Extrait d\'acte de mariage', 45.00, 2, 5, 'jours'),
(1, 'Acte de naissance', 'Extrait d\'acte de naissance', 40.00, 3, 7, 'jours'),
(1, 'Légalisation ambassade Chili et Brésil', 'Légalisation auprès des ambassades', 250.00, 10, 20, 'jours'),
(2, 'Impression noir & blanc', 'Impression en noir et blanc', 0.50, 1, 1, 'heures'),
(2, 'Impression couleur', 'Impression en couleur', 1.50, 1, 1, 'heures'),
(2, 'Plastification', 'Service de plastification', 5.00, 1, 1, 'heures'),
(2, 'Reliure', 'Service de reliure de documents', 10.00, 1, 2, 'heures'),
(2, 'Scanner', 'Service de numérisation', 1.00, 1, 1, 'heures'),
(2, 'Saisie et traitement de texte', 'Saisie et mise en forme de documents', 15.00, 2, 4, 'heures'),
(3, 'Dépannage ordinateur (PC)', 'Réparation et maintenance informatique', 100.00, 1, 3, 'jours'),
(3, 'Installation caméra de surveillance', 'Installation de systèmes de surveillance', 500.00, 1, 5, 'jours'),
(3, 'Services cartes (Mastercard / carte de débit)', 'Assistance pour cartes bancaires', 50.00, 2, 7, 'jours');

-- Insérer les documents requis pour le Passeport
INSERT INTO required_documents (service_id, document_name, is_required, display_order) VALUES
(1, 'Acte de naissance original', TRUE, 1),
(1, 'Carte d\'identité ou passeport actuel', FALSE, 2),
(1, 'Formulaire de demande rempli', TRUE, 3),
(1, '2 photos 4x4 récentes', TRUE, 4),
(1, 'Certificat de résidence', TRUE, 5);

-- Insérer les informations de l'entreprise
INSERT INTO company_info (
    company_name, company_short_name, email, phone1, phone2, phone3, phone4,
    address1, city1, address2, city2,
    moncash_number, moncash_name, natcash_number, natcash_name,
    whatsapp_number
) VALUES (
    'Naderplus Solution Multi-Services', 'NSM',
    'contact@nsm-haiti.com',
    '+509 40 31 7399', '+1 818 650 9746', '+509 47 81 2457', '+509 41 81 9394',
    'Baie-Tortue, Anse-à-Galets', 'La Gonâve',
    'Léogâne, Rue Lavandière #24', 'Léogâne',
    '+509 34 80 4456', 'Wilnader Jean',
    '+509 34 80 4456', 'Wilnader Jean',
    '+50940317399'
);

-- Insérer les paramètres système
INSERT INTO system_settings (setting_key, setting_value, data_type, description) VALUES
('site_name', 'NSM - Naderplus Solution Multi-Services', 'string', 'Nom du site'),
('site_url', 'https://nsm-haiti.com', 'string', 'URL du site'),
('currency', 'HTG', 'string', 'Devise monétaire'),
('min_service_price', '40', 'decimal', 'Prix minimum d\'un service'),
('max_service_price', '500', 'decimal', 'Prix maximum d\'un service'),
('payment_required', 'true', 'boolean', 'Le paiement est-il obligatoire avant la prestation?'),
('allow_registration', 'true', 'boolean', 'Autoriser l\'enregistrement des clients?'),
('email_notifications', 'true', 'boolean', 'Envoyer des notifications par email?'),
('max_upload_size', '5242880', 'integer', 'Taille maximale des uploads en bytes');

-- ============================================
-- VUES UTILES (Optional mais recommandées)
-- ============================================

-- Vue: Résumé des demandes par statut
CREATE OR REPLACE VIEW v_requests_summary AS
SELECT 
    status,
    COUNT(*) as total,
    SUM(final_price) as total_revenue,
    AVG(final_price) as avg_price
FROM service_requests
GROUP BY status;

-- Vue: Avis approuvés avec détails
CREATE OR REPLACE VIEW v_approved_reviews AS
SELECT 
    r.id,
    r.rating,
    r.comment,
    r.created_at,
    u.first_name,
    u.last_name,
    s.name as service_name
FROM reviews r
LEFT JOIN users u ON r.user_id = u.id
LEFT JOIN services s ON r.service_id = s.id
WHERE r.is_approved = TRUE
ORDER BY r.created_at DESC;

-- Vue: Services avec nombre de demandes
CREATE OR REPLACE VIEW v_services_stats AS
SELECT 
    s.id,
    s.name,
    sc.name as category,
    COUNT(sr.id) as request_count,
    AVG(r.rating) as avg_rating
FROM services s
LEFT JOIN service_categories sc ON s.category_id = sc.id
LEFT JOIN service_requests sr ON s.id = sr.service_id
LEFT JOIN reviews r ON s.id = r.service_id
GROUP BY s.id, s.name, sc.name;

-- ============================================
-- INDEXES SUPPLÉMENTAIRES POUR PERFORMANCE
-- ============================================
CREATE INDEX idx_payments_status ON payments(payment_status);
CREATE INDEX idx_requests_date ON service_requests(created_at);
CREATE INDEX idx_reviews_date ON reviews(created_at);

-- ============================================
-- FIN DU SCRIPT
-- ============================================
-- Cette base de données est prête pour une utilisation en production
-- N'oubliez pas de:
-- 1. Créer des utilisateurs MySQL avec les permissions appropriées
-- 2. Configurer les sauvegardes régulières
-- 3. Mettre à jour les informations de l'entreprise
-- 4. Tester toutes les fonctionnalités
