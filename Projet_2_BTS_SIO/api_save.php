<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');          
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

$nom      = preg_replace('/[^a-zA-Z0-9_\- ]/', '', trim($body['nom'] ?? ''));
$user_id  = preg_replace('/[^a-zA-Z0-9_]/',    '', trim($body['user_id'] ?? 'guest'));
$imageB64 = $body['image']    ?? '';
$elements = $body['elements'] ?? [];

if (empty($nom) || empty($imageB64)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Nom ou image manquant']);
    exit;
}

$dir = __DIR__ . '/projets/' . $user_id;
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$slug     = str_replace(' ', '_', $nom);
$basename = $slug;

foreach (glob($dir . '/' . $basename . '.*') as $old) {
    unlink($old);
}

$imgData = $imageB64;
if (strpos($imgData, ',') !== false) {
    $imgData = explode(',', $imgData)[1];
}
$imgBytes = base64_decode($imgData);
if ($imgBytes === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Image base64 invalide']);
    exit;
}
file_put_contents($dir . '/' . $basename . '.png', $imgBytes);

$meta = [
    'nom'      => $nom,
    'user_id'  => $user_id,
    'date'     => date('d/m/Y H:i'),
    'image'    => $basename . '.png',
    'elements' => $elements,   
];
file_put_contents($dir . '/' . $basename . '.json', json_encode($meta, JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'id' => $basename]);