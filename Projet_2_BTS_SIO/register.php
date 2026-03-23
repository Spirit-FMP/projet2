<?php
session_start();

// Connexion à la base de données
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
    $email = htmlspecialchars($_POST['mail'] ?? '');
    $mdp = $_POST['mdp'] ?? '';


    if (empty($email) || empty($mdp)) {
        $erreur = "Mail et mot de passe obligatoires";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Mail invalide";
    } else {
   
        $stmt = $pdo->prepare("SELECT id FROM compte WHERE Mail = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $erreur = "Cet email est déjà enregistré";
        } else {
         
            $hash = password_hash($mdp, PASSWORD_DEFAULT);


            $stmt = $pdo->prepare("INSERT INTO compte (Mail, Mdp) VALUES (?, ?)");
            if ($stmt->execute([$email, $hash])) {
                $succes = "Inscription réussie !";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
</head>
<body>
    <h1>Formulaire d'inscription</h1>
    <?php if (isset($erreur)) echo "<p style='color:red'>$erreur</p>"; ?>
    <?php if (isset($succes)) echo "<p style='color:green'>$succes</p>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="mdp" placeholder="Mot de passe" required>
        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>