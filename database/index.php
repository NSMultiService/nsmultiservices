<?php
/**
 * Centre de Gestion des Bases de Données NSM
 * Interface pour gérer les opérations de base de données
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🗄️ Gestion Base de Données NSM</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .card-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }
        
        .card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.4em;
        }
        
        .card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95em;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: #ff6b6b;
            color: white;
        }
        
        .btn-danger:hover {
            background: #ff5252;
        }
        
        .btn-success {
            background: #51cf66;
            color: white;
        }
        
        .btn-success:hover {
            background: #40c057;
        }
        
        .btn-info {
            background: #4dabf7;
            color: white;
        }
        
        .btn-info:hover {
            background: #339af0;
        }
        
        .info-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #4dabf7;
        }
        
        .info-box h4 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .info-box p {
            color: #666;
            margin: 5px 0;
            line-height: 1.6;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
        }
        
        .warning-box strong {
            color: #721c24;
        }
        
        footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @media (max-width: 768px) {
            header h1 { font-size: 1.8em; }
            .cards-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <header>
            <h1>🗄️ Gestion de la Base de Données NSM</h1>
            <p>Centre de contrôle pour les opérations de base de données</p>
        </header>

        <!-- Avertissement -->
        <div class="warning-box">
            <strong>⚠️ Attention!</strong> Les opérations de réinitialisation suppriment toutes les données actuelles. 
            Assurez-vous d'avoir une sauvegarde avant de procéder.
        </div>

        <!-- Informations actuelles -->
        <?php
            $conn = new mysqli('localhost', 'root', '', 'nsm_website');
            
            if ($conn->connect_error) {
                echo '<div class="info-box" style="border-left-color: #ff6b6b;">';
                echo '<h4>❌ État de la Connexion</h4>';
                echo '<p>La base de données est actuellement <strong>inaccessible</strong></p>';
                echo '</div>';
            } else {
                $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'nsm_website'");
                $tableCount = $result->fetch_assoc()['count'];
                
                echo '<div class="info-box">';
                echo '<h4>✅ État de la Connexion</h4>';
                echo '<p><strong>Serveur:</strong> localhost</p>';
                echo '<p><strong>Base de données:</strong> nsm_website</p>';
                echo '<p><strong>Tables créées:</strong> ' . $tableCount . '</p>';
                
                if ($tableCount > 0) {
                    $result = $conn->query("
                        SELECT 
                            (SELECT COUNT(*) FROM users) as users,
                            (SELECT COUNT(*) FROM services) as services,
                            (SELECT COUNT(*) FROM service_requests) as requests,
                            (SELECT COUNT(*) FROM reviews) as reviews
                    ");
                    $stats = $result->fetch_assoc();
                    echo '<p><strong>Utilisateurs:</strong> ' . $stats['users'] . '</p>';
                    echo '<p><strong>Services:</strong> ' . $stats['services'] . '</p>';
                    echo '<p><strong>Demandes:</strong> ' . $stats['requests'] . '</p>';
                    echo '<p><strong>Avis:</strong> ' . $stats['reviews'] . '</p>';
                }
                
                echo '</div>';
                $conn->close();
            }
        ?>

        <!-- Cartes d'actions -->
        <div class="cards-grid">
            <!-- Sauvegarde -->
            <div class="card">
                <div class="card-icon">💾</div>
                <h3>Sauvegarder</h3>
                <p>Créer une sauvegarde complète de la base de données actuelle avant d'effectuer des modifications.</p>
                <a href="backup_database.php" class="btn btn-success">Créer une Sauvegarde</a>
            </div>

            <!-- Réinitialisation -->
            <div class="card">
                <div class="card-icon">🔄</div>
                <h3>Réinitialiser</h3>
                <p>Supprimer complètement la base de données et la recréer à zéro avec les données initiales.</p>
                <a href="reset_database.php" class="btn btn-danger">Réinitialiser la BD</a>
            </div>

            <!-- Test -->
            <div class="card">
                <div class="card-icon">✅</div>
                <h3>Tester</h3>
                <p>Vérifier que la base de données est correctement configurée et afficher les statistiques.</p>
                <a href="test_database.php" class="btn btn-info">Tester la BD</a>
            </div>
        </div>

        <!-- Information supplémentaire -->
        <div class="info-box">
            <h4>📋 Fichiers Disponibles</h4>
            <p><strong>reset_database.sql:</strong> Script SQL complet (peut être utilisé directement avec phpMyAdmin)</p>
            <p><strong>reset_database.php:</strong> Interface pour réinitialiser automatiquement</p>
            <p><strong>backup_database.php:</strong> Créer une sauvegarde de la base actuelle</p>
            <p><strong>test_database.php:</strong> Vérifier l'état et les statistiques de la base</p>
            <p><strong>RESET_GUIDE.md:</strong> Guide complet avec instructions détaillées</p>
        </div>

        <!-- Instructions phpMyAdmin -->
        <div class="info-box">
            <h4>🌐 Utiliser phpMyAdmin Directement</h4>
            <p>Vous pouvez également utiliser <a href="http://localhost/phpmyadmin" target="_blank" style="color: #667eea; font-weight: bold;">phpMyAdmin</a> pour:</p>
            <p>1. Accéder à <strong>http://localhost/phpmyadmin</strong></p>
            <p>2. Importer le fichier <strong>reset_database.sql</strong> directement</p>
            <p>3. Ou exécuter les commandes SQL manuellement</p>
        </div>

        <!-- Raccourcis -->
        <div class="info-box">
            <h4>⚡ Raccourcis Rapides</h4>
            <p>
                <a href="http://localhost/phpmyadmin" target="_blank" class="btn btn-info">Ouvrir phpMyAdmin</a>
                <a href="../" class="btn btn-primary">Retour au Site</a>
            </p>
        </div>

        <footer>
            <p>🔐 Gestion sécurisée des bases de données | NSM Website v1.0</p>
            <p>Dernière mise à jour: <?php echo date('d/m/Y H:i'); ?></p>
        </footer>
    </div>
</body>
</html>
