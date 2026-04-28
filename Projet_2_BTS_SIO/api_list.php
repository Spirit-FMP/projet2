<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$user_id = preg_replace('/[^a-zA-Z0-9_]/', '', trim($_GET['user_id'] ?? ''));

if (empty($user_id)) {
    echo json_encode(['success' => true, 'projets' => []]);
    exit;
}

$dir = __DIR__ . '/projets/' . $user_id;
if (!is_dir($dir)) {
    echo json_encode(['success' => true, 'projets' => []]);
    exit;
}

$base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

$projets = [];
foreach (glob($dir . '/*.json') as $file) {
    $meta = json_decode(file_get_contents($file), true);
    if ($meta) {
        $meta['url_image'] = $base_url . '/projets/' . $user_id . '/' . $meta['image'];
        unset($meta['elements']);
        $projets[] = $meta;
    }
}

usort($projets, fn($a, $b) => strcmp($b['image'], $a['image']));
echo json_encode(['success' => true, 'projets' => $projets]);
