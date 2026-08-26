<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/php/database.php';
$db = Database::getInstance();
$rows = $db->query('SELECT id,email,first_name,last_name,account_type,role FROM users ORDER BY id DESC LIMIT 10')->fetchAll();
echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
