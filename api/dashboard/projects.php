<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../php/database.php';
require_once __DIR__ . '/../../php/auth.php';
header('Content-Type: application/json');
try {
    $auth = Auth::getInstance();
    if (!$auth->check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
    $db = db();
    $sql = "SELECT COALESCE(c.name, 'Uncategorized') AS label, COUNT(p.id) AS value FROM projects p LEFT JOIN categories c ON p.category_id = c.id GROUP BY p.category_id ORDER BY value DESC";
    $db->query($sql);
    $rows = $db->fetchAll();
    $labels = array_column($rows, 'label');
    $data = array_map(function($r){ return (int)$r['value']; }, $rows);
    echo json_encode(['success' => true, 'labels' => $labels, 'data' => $data]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
