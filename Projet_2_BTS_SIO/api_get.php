<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$nom     = preg_replace('/[^a-zA-Z0-9_\- ]/', '', trim($_GET['nom']     ?? ''));
$user_id = preg_replace('/[^a-zA-Z0-9_]/',    '', trim($_GET['user_id'] ?? ''));

if (empty($nom) || empty($user_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
    exit;
}

$slug = str_replace(' ', '_', $nom);
$file = __DIR__ . '/projets/' . $user_id . '/' . $slug . '.json';

if (!file_exists($file)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Projet introuvable']);
    exit;
}

$meta = json_decode(file_get_contents($file), true);
if (!$meta) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Fichier JSON invalide']);
    exit;
}

echo json_encode(['success' => true, 'elements' => $meta['elements'] ?? [], 'nom' => $meta['nom'], 'date' => $meta['date']]);
