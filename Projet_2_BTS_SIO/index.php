<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 { color: #333; }
        .message { margin-top: 20px; font-size: 18px; color: #555; }
        .link a {
            display: inline-block; margin-top: 20px; padding: 10px 20px;
            background: #667eea; color: white; text-decoration: none;
            border-radius: 4px; font-size: 16px;
        }
        .link a:hover { background: #764ba2; }
    </style>
</head>
<body>
    <div class="container">
        <h1> Sélectionnez un projet</h1>
        <p class="message">Vous êtes connecté en tant que <?= htmlspecialchars($_SESSION['user_email'] ?? 'Invité') ?>.</p>
        <h1> Créer un projet</h1>
        <div class="link">
            <a href="creer_projet.php">Créer un projet</a>
        </div>
        </form>
        <div class="link">
            <?php if (isset($_SESSION['user_email'])): ?>
                <a href="connexion.php">Se déconnecter</a>
            <?php endif; ?>
        </div>
        
    </div>
</body>
</html>