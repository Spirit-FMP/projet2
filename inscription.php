<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input {
            width: 100%; padding: 10px; border: 1px solid #ddd;
            border-radius: 4px; box-sizing: border-box; font-size: 14px;
        }
        input:focus { outline: none; border-color: #667eea; }
        button {
            width: 100%; padding: 12px; background: #667eea;
            color: white; border: none; border-radius: 4px;
            font-size: 16px; font-weight: bold; cursor: pointer;
        }
        button:hover { background: #764ba2; }
        .message { text-align: center; margin-top: 15px; color: #666; }
        .message a { color: #667eea; text-decoration: none; }
        .alert {
            padding: 10px 15px; border-radius: 4px;
            margin-bottom: 20px; text-align: center; font-size: 14px;
        }
        .alert-error  { background: #fdecea; color: #c0392b; border: 1px solid #e74c3c; }
        .alert-success { background: #eafaf1; color: #1e8449; border: 1px solid #2ecc71; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Créer un compte</h1>

        <?php if (!empty($_SESSION['erreur'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['erreur']) ?></div>
            <?php unset($_SESSION['erreur']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['succes'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['succes']) ?></div>
            <?php unset($_SESSION['succes']); ?>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="mail">Email :</label>
                <input type="email" id="mail" name="mail" required>
            </div>
            <div class="form-group">
                <label for="mdp">Mot de passe :</label>
                <input type="password" id="mdp" name="mdp" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">S'inscrire</button>
        </form>

        <div class="message">
            Vous avez déjà un compte ? <a href="connexion.php">Se connecter</a>
        </div>
    </div>
</body>
</html>
