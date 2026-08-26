<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../php/database.php';
require_once __DIR__ . '/../../php/auth.php';
header('Content-Type: application/json');
try {
    $auth = Auth::getInstance();
    if (!$auth->check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
    $q = trim($_GET['q'] ?? '');
    if ($q === '') { echo json_encode(['success' => true, 'results' => []]); exit; }
    $db = db();
    $like = '%' . $q . '%';
    $results = [];
    $db->query("SELECT id, name AS title, tagline || ' - ' || IFNULL(city, '') || ', ' || IFNULL(state, '') AS subtitle, 'supplier' AS type FROM companies WHERE name LIKE :q OR tagline LIKE :q OR city LIKE :q OR state LIKE :q OR country LIKE :q LIMIT 8", ['q' => $like]);
    foreach ($db->fetchAll() as $r) { $results[] = $r + ['url' => '/NEXAR/provider?id=' . $r['id']]; }
    $db->query('SELECT id, company_id, title AS title, description AS subtitle, "service" AS type FROM services WHERE title LIKE :q OR description LIKE :q LIMIT 8', ['q' => $like]);
    foreach ($db->fetchAll() as $r) { $results[] = $r + ['url' => '/NEXAR/provider?id=' . $r['company_id'] . '&service=' . $r['id']]; }
    echo json_encode(['success' => true, 'results' => array_slice($results, 0, 10)]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
