<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../php/database.php';
require_once __DIR__ . '/../../php/auth.php';
header('Content-Type: application/json');
try {
    $auth = Auth::getInstance();
    if (!$auth->check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
    $db = db();
    $period = $_GET['period'] ?? 'week';
    if ($period === 'week') {
        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = new DateTimeImmutable("-$i days");
            $labels[] = $d->format('d/m');
            $start = $d->setTime(0,0,0)->format('Y-m-d H:i:s');
            $end = $d->setTime(23,59,59)->format('Y-m-d H:i:s');
            $sql = "SELECT COALESCE(SUM(COALESCE(budget_max, budget_min, 0)),0) FROM projects WHERE status = 'completed' AND updated_at BETWEEN :start AND :end";
            $db->query($sql, ['start' => $start, 'end' => $end]);
            $data[] = (float) $db->fetchColumn();
        }
    } elseif ($period === 'month') {
        $labels = [];
        $data = [];
        $today = new DateTimeImmutable('now');
        for ($i = 29; $i >= 0; $i--) {
            $d = $today->modify("-$i days");
            $labels[] = $d->format('d/m');
            $start = $d->setTime(0,0,0)->format('Y-m-d H:i:s');
            $end = $d->setTime(23,59,59)->format('Y-m-d H:i:s');
            $sql = "SELECT COALESCE(SUM(COALESCE(budget_max, budget_min, 0)),0) FROM projects WHERE status = 'completed' AND updated_at BETWEEN :start AND :end";
            $db->query($sql, ['start' => $start, 'end' => $end]);
            $data[] = (float) $db->fetchColumn();
        }
    } else {
        $labels = [];
        $data = [];
        $now = new DateTimeImmutable('first day of this month');
        for ($i = 11; $i >= 0; $i--) {
            $m = $now->modify("-$i months");
            $labels[] = $m->format('M Y');
            $start = $m->modify('first day of this month')->setTime(0,0,0)->format('Y-m-d H:i:s');
            $end = $m->modify('last day of this month')->setTime(23,59,59)->format('Y-m-d H:i:s');
            $sql = "SELECT COALESCE(SUM(COALESCE(budget_max, budget_min, 0)),0) FROM projects WHERE status = 'completed' AND updated_at BETWEEN :start AND :end";
            $db->query($sql, ['start' => $start, 'end' => $end]);
            $data[] = (float) $db->fetchColumn();
        }
    }
    echo json_encode(['success' => true, 'labels' => $labels, 'data' => $data]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
