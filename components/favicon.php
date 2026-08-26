<?php
$iconPath = '/NEXAR/public/assets/images/Icone.png';
$publicIconPath = '/assets/images/Icone.png';
$defaultSvg = '/NEXAR/public/assets/images/favicon.svg';
$defaultPublicSvg = '/assets/images/favicon.svg';

if (isset($_SERVER['SCRIPT_FILENAME'])) {
    $scriptRealPath = realpath($_SERVER['SCRIPT_FILENAME']);
    if ($scriptRealPath !== false && str_contains($scriptRealPath, DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR)) {
        $iconPath = $publicIconPath;
    }
}

$iconFile = realpath(__DIR__ . '/../public/assets/images/Icone.png');
if ($iconFile === false) {
    $iconPath = isset($_SERVER['SCRIPT_FILENAME']) && str_contains(realpath($_SERVER['SCRIPT_FILENAME']) ?: '', DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR)
        ? $defaultPublicSvg
        : $defaultSvg;
}

$type = strtolower(pathinfo($iconPath, PATHINFO_EXTENSION)) === 'svg' ? 'image/svg+xml' : 'image/png';
?>
<link rel="icon" type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') ?>">
<link rel="shortcut icon" type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') ?>">
<link rel="apple-touch-icon" href="<?= htmlspecialchars($iconPath, ENT_QUOTES, 'UTF-8') ?>">
