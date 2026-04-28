<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

define('API_SECRET', 'MON_SECRET_12345');

$body = json_decode(file_get_contents('php://input'), true);

if (!$body || ($body['secret'] ?? '') !== API_SECRET) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$nom     = preg_replace('/[^a-zA-Z0-9_\- ]/', '', trim($body['nom']     ?? ''));
$user_id = preg_replace('/[^a-zA-Z0-9_]/',    '', trim($body['user_id'] ?? ''));

if (empty($nom) || empty($user_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
    exit;
}

$slug = str_replace(' ', '_', $nom);
$dir  = __DIR__ . '/projets/' . $user_id;

$deleted = 0;
foreach (glob($dir . '/' . $slug . '.*') as $file) {
    unlink($file);
    $deleted++;
}

if ($deleted === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Projet introuvable']);
    exit;
}

echo json_encode(['success' => true]);
