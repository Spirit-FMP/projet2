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
    $email            = htmlspecialchars($_POST['mail'] ?? '');
    $mdp              = $_POST['mdp'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($mdp) || empty($confirm_password)) {
        $_SESSION['erreur'] = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erreur'] = "Adresse mail invalide.";
    } elseif ($mdp !== $confirm_password) {
        $_SESSION['erreur'] = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mdp) < 6) {
        $_SESSION['erreur'] = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $stmt = $pdo->prepare("SELECT compte_id FROM compte WHERE Mail = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['erreur'] = "Cet email est déjà enregistré.";
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO compte (Mail, Mdp) VALUES (?, ?)");
            if ($stmt->execute([$email, $hash])) {
                $_SESSION['succes'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $_SESSION['erreur'] = "Une erreur est survenue. Veuillez réessayer.";
            }
        }
    }
}

header('Location: inscription.php');
exit;
?>