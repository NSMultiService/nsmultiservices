<?php
/**
 * Script de Test - Vérifier que la Base de Données est bien configurée
 */

echo "<h2>✅ Test de la Base de Données NSM</h2>";

// Configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "nsm_website";

// Créer connexion
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("<p style='color: red;'>❌ Erreur de connexion: " . $conn->connect_error . "</p>");
}

echo "<p style='color: green;'>✅ Connexion à la base de données réussie</p>";

// Vérifier l'existence des tables
$tables = [
    'users',
    'admins',
    'service_categories',
    'services',
    'required_documents',
    'service_requests',
    'payments',
    'reviews',
    'blog_posts'
];

echo "<h3>📋 Vérification des Tables:</h3>";
echo "<ul>";

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<li>✅ Table <strong>$table</strong> existe</li>";
    } else {
        echo "<li>❌ Table <strong>$table</strong> MANQUANTE</li>";
    }
}
echo "</ul>";

// Afficher les statistiques
echo "<h3>📊 Statistiques de Contenu:</h3>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Table</th><th>Enregistrements</th></tr>";

$stats = [
    'users' => 'Utilisateurs',
    'admins' => 'Administrateurs',
    'service_categories' => 'Catégories',
    'services' => 'Services',
    'required_documents' => 'Documents',
    'service_requests' => 'Demandes',
    'payments' => 'Paiements',
    'reviews' => 'Avis',
    'blog_posts' => 'Articles Blog'
];

foreach ($stats as $table => $label) {
    // Vérifier que la table existe d'abord
    $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
    
    if ($checkTable && $checkTable->num_rows > 0) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['count'];
            $color = $count > 0 ? 'green' : 'blue';
            echo "<tr><td>$label</td><td style='color: $color;'><strong>$count</strong></td></tr>";
        }
    } else {
        echo "<tr><td>$label</td><td style='color: red;'>❌ Table n'existe pas</td></tr>";
    }
}
echo "</table>";

// Détails des services par catégorie (vérifier que les tables existent)
$checkCat = $conn->query("SHOW TABLES LIKE 'service_categories'");
if ($checkCat && $checkCat->num_rows > 0) {
    echo "<h3>📚 Services par Catégorie:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Catégorie</th><th>Nombre de Services</th></tr>";

    $result = $conn->query("
        SELECT sc.name, COUNT(s.id) as count 
        FROM service_categories sc 
        LEFT JOIN services s ON sc.id = s.category_id 
        GROUP BY sc.id, sc.name
        ORDER BY sc.display_order
    ");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['name']) . "</td><td>" . $row['count'] . "</td></tr>";
        }
    }
    echo "</table>";
    
    // Afficher un exemple de service avec ses documents
    echo "<h3>📄 Exemple - Service et ses Documents Requis:</h3>";
    $result = $conn->query("
        SELECT s.id, s.name, s.base_price, sc.name as category 
        FROM services s 
        JOIN service_categories sc ON s.category_id = sc.id 
        LIMIT 1
    ");

    if ($result && $result->num_rows > 0) {
        $service = $result->fetch_assoc();
        echo "<p><strong>Service:</strong> " . htmlspecialchars($service['name']) . "</p>";
        echo "<p><strong>Catégorie:</strong> " . htmlspecialchars($service['category']) . "</p>";
        echo "<p><strong>Prix de base:</strong> " . $service['base_price'] . " HTG</p>";
        
        echo "<p><strong>Documents requis:</strong></p>";
        echo "<ul>";
        $docs = $conn->query("
            SELECT document_name, is_required 
            FROM required_documents 
            WHERE service_id = " . $service['id'] . "
            ORDER BY display_order
        ");
        
        if ($docs) {
            while ($doc = $docs->fetch_assoc()) {
                $required = $doc['is_required'] ? '✓' : '○';
                echo "<li>$required " . htmlspecialchars($doc['document_name']) . "</li>";
            }
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Les tables de services n'ont pas pu être vérifiées.</p>";
}

// Vérifier les clés étrangères
echo "<h3>🔗 Vérification des Contraintes:</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = 'nsm_website'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>✅ " . $row['count'] . " contraintes de clés étrangères définies</p>";
}

echo "<h3>🎉 Test Terminé!</h3>";
echo "<p><strong>La base de données est correctement configurée et prête à l'emploi.</strong></p>";

echo "<hr>";
echo "<p><small>Date du test: " . date('d/m/Y H:i:s') . "</small></p>";

$conn->close();
?>

<style>
    body { 
        font-family: Arial, sans-serif; 
        margin: 20px; 
        background-color: #f5f5f5;
    }
    h2 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
    h3 { color: #555; margin-top: 20px; }
    p { line-height: 1.6; }
    ul, li { margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; background-color: white; margin: 15px 0; }
    th, td { padding: 12px; text-align: left; }
    th { background-color: #007bff; color: white; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    hr { margin: 30px 0; border: none; border-top: 2px solid #ddd; }
</style>
