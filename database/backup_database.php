<?php
/**
 * Script de Sauvegarde de la Base de Données Actuelle
 * À exécuter AVANT la réinitialisation pour conserver les données
 */

echo "<h2>💾 Sauvegarde de la Base de Données NSM</h2>";

// Configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "nsm_website";

// Créer connexion
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo "<p style='color: orange;'>⚠️ Aucune base existante à sauvegarder ou erreur de connexion</p>";
    echo "<p>Message: " . $conn->connect_error . "</p>";
    echo "<p>Vous pouvez procéder à la réinitialisation.</p>";
    exit;
}

// Vérifier que la base de données existe
$dbCheck = $conn->query("SELECT DATABASE()");
if (!$dbCheck) {
    echo "<p style='color: orange;'>⚠️ Base de données NSM n'existe pas encore</p>";
    echo "<p>Vous pouvez procéder à la réinitialisation.</p>";
    exit;
}

echo "<p>✅ Connexion établie à la base existante</p>";

// Créer le dossier backups s'il n'existe pas
$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Générer le nom du fichier de sauvegarde
$timestamp = date('Y-m-d_H-i-s');
$backupFile = $backupDir . '/nsm_database_backup_' . $timestamp . '.sql';

echo "<p><strong>Création de la sauvegarde...</strong></p>";
echo "<p>Fichier: <code>$backupFile</code></p>";

// Utiliser mysqldump pour créer la sauvegarde
$command = "mysqldump --user={$username} --password={$password} {$database} > \"{$backupFile}\"";

// Sur Windows, ajuster la commande
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Essayer de trouver mysqldump
    $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    if (file_exists($mysqldump)) {
        $command = "\"{$mysqldump}\" --user={$username} --password={$password} {$database} > \"{$backupFile}\"";
    }
}

// Exécuter la commande de sauvegarde
$output = [];
$returnVar = 0;
exec($command, $output, $returnVar);

if ($returnVar === 0 && file_exists($backupFile)) {
    $fileSize = filesize($backupFile);
    echo "<p style='color: green;'>✅ Sauvegarde créée avec succès!</p>";
    echo "<p>Taille du fichier: " . round($fileSize / 1024, 2) . " KB</p>";
    
    // Afficher les statistiques de la base avant réinitialisation
    echo "<h3>📊 Statistiques de la Base Sauvegardée:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Table</th><th>Enregistrements</th></tr>";
    
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
    
    foreach ($tables as $table) {
        // Vérifier que la table existe
        $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
        if ($checkTable && $checkTable->num_rows > 0) {
            $result = $conn->query("SELECT COUNT(*) as count FROM $table");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<tr><td>$table</td><td>" . $row['count'] . "</td></tr>";
            }
        } else {
            echo "<tr><td>$table</td><td style='color: orange;'>Table n'existe pas</td></tr>";
        }
    }
    echo "</table>";
    
} else {
    // Alternative: créer une sauvegarde SQL manuelle
    echo "<p style='color: orange;'>⚠️ Sauvegarde automatique non disponible, création manuelle...</p>";
    
    $sqlContent = "-- Sauvegarde de nsm_website - " . date('Y-m-d H:i:s') . "\n\n";
    
    // Exporter les tables et données
    $result = $conn->query("SHOW TABLES");
    
    while ($table = $result->fetch_row()) {
        $tableName = $table[0];
        
        // Obtenir la structure CREATE TABLE
        $createResult = $conn->query("SHOW CREATE TABLE $tableName");
        $createRow = $createResult->fetch_row();
        $sqlContent .= "\n\n-- Table: $tableName\n";
        $sqlContent .= $createRow[1] . ";\n";
        
        // Obtenir les données
        $dataResult = $conn->query("SELECT * FROM $tableName");
        
        if ($dataResult && $dataResult->num_rows > 0) {
            while ($dataRow = $dataResult->fetch_assoc()) {
                $values = array_map(function($v) use ($conn) {
                    if ($v === null) return 'NULL';
                    return "'" . $conn->real_escape_string($v) . "'";
                }, $dataRow);
                
                $columns = implode(', ', array_keys($dataRow));
                $sqlContent .= "INSERT INTO $tableName ($columns) VALUES (" . implode(', ', $values) . ");\n";
            }
        }
    }
    
    file_put_contents($backupFile, $sqlContent);
    
    if (file_exists($backupFile)) {
        echo "<p style='color: green;'>✅ Sauvegarde créée avec succès (mode manuel)!</p>";
        echo "<p>Taille: " . round(filesize($backupFile) / 1024, 2) . " KB</p>";
    } else {
        echo "<p style='color: red;'>❌ Impossible de créer la sauvegarde</p>";
    }
}

// Lister tous les fichiers de sauvegarde
echo "<h3>📂 Fichiers de Sauvegarde Existants:</h3>";
echo "<ul>";

$files = glob($backupDir . '/*.sql');
if (count($files) > 0) {
    rsort($files); // Trier par date (plus récent en premier)
    
    foreach ($files as $file) {
        $fileName = basename($file);
        $fileSize = round(filesize($file) / 1024, 2);
        $fileDate = date('d/m/Y H:i', filemtime($file));
        echo "<li><strong>$fileName</strong> - $fileSize KB - $fileDate</li>";
    }
} else {
    echo "<li>Aucune sauvegarde trouvée</li>";
}
echo "</ul>";

// Instructions pour la restauration
echo "<h3>🔄 Pour Restaurer une Sauvegarde:</h3>";
echo "<pre>
-- Via phpMyAdmin:
1. Allez dans la base nsm_website
2. Cliquez sur 'Importer'
3. Sélectionnez le fichier de sauvegarde
4. Cliquez sur 'Exécuter'

-- Ou en ligne de commande:
mysql -u root nsm_website < " . str_replace("\\", "/", $backupFile) . "
</pre>";

echo "<h3>✅ Prochaines Étapes:</h3>";
echo "<ol>";
echo "<li>Sauvegarde complétée ✅</li>";
echo "<li>Vous pouvez maintenant exécuter <strong>reset_database.php</strong></li>";
echo "<li>Votre sauvegarde sera disponible dans <strong>database/backups/</strong></li>";
echo "</ol>";

$conn->close();
?>

<style>
    body { 
        font-family: Arial, sans-serif; 
        margin: 20px; 
        background-color: #f5f5f5;
    }
    h2 { color: #333; border-bottom: 3px solid #28a745; padding-bottom: 10px; }
    h3 { color: #555; margin-top: 20px; }
    p { line-height: 1.6; }
    ul, ol, li { margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; background-color: white; margin: 15px 0; }
    th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #28a745; color: white; }
    pre { 
        background-color: #f4f4f4; 
        padding: 15px; 
        border-radius: 5px;
        border-left: 4px solid #28a745;
        overflow-x: auto;
    }
    code { 
        background-color: #f4f4f4; 
        padding: 2px 6px; 
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }
</style>
