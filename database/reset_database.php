<?php
/**
 * Script de Réinitialisation de la Base de Données NSM
 * ⚠️  ATTENTION: Ce script supprime TOUT et recrée à zéro
 */

// Configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "nsm_website";

// Créer connexion sans sélectionner la BD
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("❌ Erreur de connexion: " . $conn->connect_error);
}

echo "<h2>🔄 Réinitialisation de la Base de Données NSM</h2>";

// Étape 1: Supprimer la BD existante
echo "<p><strong>Étape 1:</strong> Suppression de la base de données existante...</p>";
if ($conn->query("DROP DATABASE IF EXISTS nsm_website") === TRUE) {
    echo "✅ Base de données supprimée<br>";
} else {
    echo "❌ Erreur: " . $conn->error . "<br>";
    exit;
}

// Étape 2: Créer nouvelle BD
echo "<p><strong>Étape 2:</strong> Création de la nouvelle base de données...</p>";
if ($conn->query("CREATE DATABASE nsm_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === TRUE) {
    echo "✅ Base de données créée<br>";
} else {
    echo "❌ Erreur: " . $conn->error . "<br>";
    exit;
}

// Sélectionner la base de données
$conn->select_db("nsm_website");

// Étape 3: Exécuter le script SQL
echo "<p><strong>Étape 3:</strong> Création des tables et données initiales...</p>";

$sqlFile = __DIR__ . '/reset_database.sql';

if (!file_exists($sqlFile)) {
    die("❌ Fichier SQL non trouvé: $sqlFile");
}

$sqlContent = file_get_contents($sqlFile);

// Diviser les requêtes SQL
$queries = array_filter(array_map('trim', explode(';', $sqlContent)));

$success = 0;
$errors = 0;
$baseCreated = false;

foreach ($queries as $query) {
    // Nettoyer la requête
    $query = trim($query);
    
    // Ignorer les requêtes vides et commentaires
    if (strlen($query) === 0 || strpos($query, '--') === 0 || strpos($query, '/*') === 0) {
        continue;
    }
    
    // Détecter DROP DATABASE
    if (stripos($query, 'DROP DATABASE') !== false) {
        if ($conn->query($query) === TRUE) {
            echo "✅ Base de données existante supprimée<br>";
            $success++;
        } else {
            echo "⚠️ Erreur DROP: " . $conn->error . "<br>";
        }
        continue;
    }
    
    // Détecter CREATE DATABASE
    if (stripos($query, 'CREATE DATABASE') !== false) {
        if ($conn->query($query) === TRUE) {
            echo "✅ Base de données créée<br>";
            $baseCreated = true;
            $success++;
        } else {
            echo "❌ Erreur CREATE DB: " . $conn->error . "<br>";
            $errors++;
        }
        continue;
    }
    
    // Après CREATE DATABASE, sélectionner la base
    if ($baseCreated) {
        if (!$conn->select_db("nsm_website")) {
            echo "❌ Erreur: Impossible de sélectionner nsm_website: " . $conn->error . "<br>";
            $baseCreated = false;
            $errors++;
            continue;
        }
    }
    
    // Ignorer les commandes USE
    if (stripos($query, 'USE nsm_website') !== false) {
        continue;
    }
    
    // Exécuter les autres requêtes (CREATE TABLE, INSERT, etc.)
    if (strlen($query) > 0) {
        if ($conn->query($query) === TRUE) {
            $success++;
        } else {
            // Afficher l'erreur mais continuer
            if ($conn->error) {
                echo "⚠️ Erreur: " . $conn->error . " (Requête: " . substr($query, 0, 50) . "...)<br>";
            }
            $errors++;
        }
    }
}

echo "<p>✅ $success requêtes exécutées avec succès</p>";
if ($errors > 0) {
    echo "<p>⚠️ $errors erreurs rencontrées</p>";
}

// Vérifier que la base a été créée et sélectionnée
if (!$conn->select_db("nsm_website")) {
    echo "<p style='color: red;'>❌ Erreur: La base de données n'a pas pu être sélectionnée: " . $conn->error . "</p>";
    exit;
}

// Afficher les statistiques
echo "<h3>📊 Statistiques de la Base de Données:</h3>";

$stats = [
    "Tables créées" => "SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'nsm_website'",
    "Catégories" => "SELECT COUNT(*) as count FROM service_categories",
    "Services" => "SELECT COUNT(*) as count FROM services",
    "Documents requis" => "SELECT COUNT(*) as count FROM required_documents"
];

foreach ($stats as $label => $query) {
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ $label: " . $row['count'] . "<br>";
    } else {
        echo "⚠️ $label: Impossible à vérifier - " . $conn->error . "<br>";
    }
}

echo "<h3>🎉 Réinitialisation Terminée!</h3>";
echo "<p><strong>La base de données est maintenant vierge et prête à l'emploi.</strong></p>";

// Afficher les services par catégorie (avec vérification)
if ($conn->query("SHOW TABLES LIKE 'service_categories'") && $conn->query("SHOW TABLES LIKE 'service_categories'")->num_rows > 0) {
    echo "<h3>📋 Services Créés:</h3>";
    $result = $conn->query("
        SELECT sc.name as category, COUNT(s.id) as count 
        FROM service_categories sc 
        LEFT JOIN services s ON sc.id = s.category_id 
        GROUP BY sc.id, sc.name
        ORDER BY sc.display_order
    ");

    if ($result) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['category']) . ": " . $row['count'] . " service(s)</li>";
        }
        echo "</ul>";
    }
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
