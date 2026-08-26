<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../php/database.php';
require_once __DIR__ . '/../../php/auth.php';
header('Content-Type: application/json');
try {
    $auth = Auth::getInstance();
    if (!$auth->check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
    $db = db();
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
    $sql = "SELECT a.id, a.user_id, a.action, a.model_type, a.model_id, a.properties, a.ip_address, a.created_at, u.first_name, u.last_name FROM activity_log a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT :limit";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $items = array_map(function($r){ return ['id' => (int)$r['id'], 'user' => $r['first_name'] ? trim($r['first_name'] . ' ' . $r['last_name']) : null, 'action' => $r['action'], 'timestamp' => $r['created_at'], 'properties' => $r['properties']]; }, $rows);
    echo json_encode(['success' => true, 'data' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
