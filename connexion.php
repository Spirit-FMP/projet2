<?php
session_start();

$host = 'localhost';
$dbname = 'projet_2';
$user = 'root_copy';
$password = 'Spirit-FMP';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = htmlspecialchars($_POST['email'] ?? '');
    $mdp      = $_POST['password'] ?? '';

    if (empty($email) || empty($mdp)) {
        $_SESSION['erreur'] = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erreur'] = "Adresse mail invalide.";
    } else {
        $stmt = $pdo->prepare("SELECT compte_id, Mdp FROM compte WHERE Mail = ?");
        $stmt->execute([$email]);
        $compte = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$compte || !password_verify($mdp, $compte['Mdp'])) {
            $_SESSION['erreur'] = "Email ou mot de passe incorrect.";
        } else {
            $_SESSION['user_id'] = $compte['compte_id'];
            $_SESSION['user_email'] = $email;
            header('Location: index.php');
            exit;
        }
    }

    header('Location: connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
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
            width: 300px;
        }
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input {
            width: 100%; padding: 10px; border: 1px solid #ddd;
            border-radius: 4px; box-sizing: border-box;
        }
        button {
            width: 100%; padding: 10px; background: #667eea;
            color: white; border: none; border-radius: 4px;
            cursor: pointer; font-size: 16px;
        }
        button:hover { background: #764ba2; }
        .link { text-align: center; margin-top: 15px; }
        .link a { color: #667eea; text-decoration: none; }
        .link a:hover { text-decoration: underline; }
        .alert {
            padding: 10px 15px; border-radius: 4px;
            margin-bottom: 20px; text-align: center; font-size: 14px;
        }
        .alert-error { background: #fdecea; color: #c0392b; border: 1px solid #e74c3c; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Connexion</h1>

        <?php if (!empty($_SESSION['erreur'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['erreur']) ?></div>
            <?php unset($_SESSION['erreur']); ?>
        <?php endif; ?>

        <form action="connexion.php" method="POST">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>

        <div class="link">
            <a href="inscription.php">Créer un compte</a>
        </div>
    </div>
</body>
</html>
