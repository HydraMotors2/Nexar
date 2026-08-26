<?php
if (file_exists(__DIR__ . '/../php/auth.php')) {
    require_once __DIR__ . '/../php/auth.php';
}

$auth = Auth::getInstance();
if ($auth->check()) {
    $auth->logout();
}

header('Location: /NEXAR/');
exit;
