<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../php/database.php';
require_once __DIR__ . '/../../php/auth.php';
header('Content-Type: application/json');
try {
    $auth = Auth::getInstance();
    if (!$auth->check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
    $db = db();
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $startCurrentMonth = $now->modify('first day of this month')->setTime(0,0,0)->format('Y-m-d H:i:s');
    $endCurrent = $now->format('Y-m-d H:i:s');
    $startPrevMonth = $now->modify('first day of last month')->setTime(0,0,0)->format('Y-m-d H:i:s');
    $endPrevMonth = $now->modify('last day of last month')->setTime(23,59,59)->format('Y-m-d H:i:s');

    $sql = "SELECT COUNT(*) FROM projects WHERE status IN ('open','in_progress')";
    $db->query($sql);
    $activeTotal = (int) $db->fetchColumn();

    $sql = "SELECT COUNT(*) FROM projects WHERE status IN ('open','in_progress') AND created_at BETWEEN :start AND :end";
    $db->query($sql, ['start' => $startCurrentMonth, 'end' => $endCurrent]);
    $activeThisMonth = (int) $db->fetchColumn();
    $db->query($sql, ['start' => $startPrevMonth, 'end' => $endPrevMonth]);
    $activePrevMonth = (int) $db->fetchColumn();
    $activeGrowth = $activePrevMonth == 0 ? ($activeThisMonth ? 100.0 : 0.0) : round((($activeThisMonth - $activePrevMonth) / max(1, $activePrevMonth)) * 100, 1);

    $sql = "SELECT COALESCE(SUM(COALESCE(budget_max, budget_min, 0)),0) FROM projects WHERE status = 'completed'";
    $db->query($sql);
    $revenueTotal = (float) $db->fetchColumn();
    $sql = "SELECT COALESCE(SUM(COALESCE(budget_max, budget_min, 0)),0) FROM projects WHERE status = 'completed' AND updated_at BETWEEN :start AND :end";
    $db->query($sql, ['start' => $startCurrentMonth, 'end' => $endCurrent]);
    $revenueThisMonth = (float) $db->fetchColumn();
    $db->query($sql, ['start' => $startPrevMonth, 'end' => $endPrevMonth]);
    $revenuePrevMonth = (float) $db->fetchColumn();
    $revenueGrowth = $revenuePrevMonth == 0 ? ($revenueThisMonth ? 100.0 : 0.0) : round((($revenueThisMonth - $revenuePrevMonth) / max(1, $revenuePrevMonth)) * 100, 1);

    $sql = "SELECT COUNT(*) FROM users WHERE created_at BETWEEN :start AND :end";
    $db->query($sql, ['start' => $startCurrentMonth, 'end' => $endCurrent]);
    $newClientsThis = (int) $db->fetchColumn();
    $db->query($sql, ['start' => $startPrevMonth, 'end' => $endPrevMonth]);
    $newClientsPrev = (int) $db->fetchColumn();
    $newClientsGrowth = $newClientsPrev == 0 ? ($newClientsThis ? 100.0 : 0.0) : round((($newClientsThis - $newClientsPrev) / max(1, $newClientsPrev)) * 100, 1);

    $db->query("SELECT COUNT(*) FROM projects");
    $totalProjects = (int) $db->fetchColumn();
    $db->query("SELECT COUNT(*) FROM projects WHERE status = 'completed'");
    $completedProjects = (int) $db->fetchColumn();
    $completionRate = $totalProjects == 0 ? 0 : round(($completedProjects / $totalProjects) * 100, 1);

    $formatter = function(float $value) { return 'R$ ' . number_format($value, 2, ',', '.'); };

    echo json_encode([
        'success' => true,
        'data' => [
            'active_projects' => ['total' => $activeTotal, 'this_month' => $activeThisMonth, 'growth' => $activeGrowth],
            'total_revenue' => ['raw' => $revenueTotal, 'formatted' => $formatter($revenueTotal), 'this_month' => $revenueThisMonth, 'growth' => $revenueGrowth],
            'new_clients' => ['this_month' => $newClientsThis, 'previous_month' => $newClientsPrev, 'growth' => $newClientsGrowth],
            'completion_rate' => $completionRate
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
