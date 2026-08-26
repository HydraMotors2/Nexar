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
    $db->query('SELECT COUNT(*) FROM messages WHERE recipient_id = :uid AND is_deleted_recipient = 0', ['uid' => $userId]);
    $inboxCount = (int) $db->fetchColumn();
    $db->query('SELECT COUNT(*) FROM messages WHERE recipient_id = :uid AND is_read = 0 AND is_deleted_recipient = 0', ['uid' => $userId]);
    $unreadCount = (int) $db->fetchColumn();
    $sql = 'SELECT m.id, m.conversation_id, m.sender_id, m.subject, m.body, m.is_read, m.created_at, u.first_name, u.last_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE (m.recipient_id = :uid OR m.sender_id = :uid) AND m.is_deleted_recipient = 0 ORDER BY m.created_at DESC LIMIT 10';
    $db->query($sql, ['uid' => $userId]);
    $messages = array_map(function ($row) {
        return [
            'id' => (int) $row['id'],
            'conversation_id' => $row['conversation_id'],
            'subject' => $row['subject'] ?: 'Sem assunto',
            'preview' => mb_substr(strip_tags($row['body']), 0, 120),
            'sender' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'Sistema',
            'is_read' => (bool) $row['is_read'],
            'created_at' => $row['created_at'],
        ];
    }, $db->fetchAll());
    echo json_encode(['success' => true, 'data' => ['inbox_count' => $inboxCount, 'unread_count' => $unreadCount, 'messages' => $messages]]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
