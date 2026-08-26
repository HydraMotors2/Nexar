<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../php/database.php';
require_once __DIR__ . '/../../php/auth.php';
header('Content-Type: application/json');
try {
    $auth = Auth::getInstance();
    if (!$auth->check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
    $db = db();
    $userId = $auth->id();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$csrfHeader || $csrfHeader !== Auth::csrfToken()) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']); exit; }
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $action = $input['action'] ?? null;
        if ($action === 'mark_read') {
            $id = isset($input['id']) ? (int)$input['id'] : null;
            if ($id) {
                $db->query('UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
                echo json_encode(['success' => true]);
                exit;
            }
            $db->query('UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE user_id = :uid', ['uid' => $userId]);
            echo json_encode(['success' => true]);
            exit;
        }
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
    $db->query('SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0', ['uid' => $userId]);
    $unread = (int) $db->fetchColumn();
    $db->query('SELECT id, type, title, message, action_url, is_read, created_at FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 50', ['uid' => $userId]);
    $notifications = $db->fetchAll();
    echo json_encode(['success' => true, 'unread' => $unread, 'data' => $notifications]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
