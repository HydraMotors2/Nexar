<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../php/database.php';
require_once __DIR__ . '/../../php/auth.php';
header('Content-Type: application/json');
try {
    $auth = Auth::getInstance();
    if (!$auth->check()) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
    $q = trim((string)($_GET['q'] ?? ''));
    if (mb_strlen($q) < 2) { echo json_encode(['success' => true, 'results' => []]); exit; }
    $db = db();
    $like = '%' . $q . '%';
    $results = [];
    $supplierColumns = [
        's.trade_name', 's.legal_name', 's.category', 's.business_segment',
        's.description', 's.main_products', 's.service_region', 's.city', 's.state',
    ];
    $supplierConditions = [];
    $supplierParams = [];
    foreach ($supplierColumns as $index => $column) {
        $parameter = 'term' . $index;
        $supplierConditions[] = "$column LIKE :$parameter";
        $supplierParams[$parameter] = $like;
    }

    $db->query(
        "SELECT s.id, COALESCE(NULLIF(s.trade_name, ''), s.legal_name) AS title,
            COALESCE(NULLIF(s.category, ''), s.business_segment, 'Fornecedor') || ' • ' ||
                COALESCE(NULLIF(s.city, ''), '') || CASE WHEN s.state IS NOT NULL AND s.state <> '' THEN ' - ' || s.state ELSE '' END AS subtitle,
            'supplier' AS type, s.plan
        FROM suppliers s
        JOIN users u ON u.id = s.user_id AND u.status = 'active'
        WHERE " . implode(' OR ', $supplierConditions) . "
        ORDER BY CASE s.plan WHEN 'promoted' THEN 1 WHEN 'boost' THEN 2 ELSE 3 END, s.id
        LIMIT 20",
        $supplierParams
    );
    foreach ($db->fetchAll() as $r) {
        $r['plan_label'] = plan_label('supplier', $r['plan']);
        $r['plan_price'] = plan_price('supplier', $r['plan']);
        $r['url'] = '/NEXAR/provider?id=' . $r['id'] . '&type=supplier';
        $results[] = $r;
    }

    $db->query("SELECT id, name AS title,
        COALESCE(tagline, '') || ' - ' || IFNULL(city, '') || ', ' || IFNULL(state, '') AS subtitle,
        'company' AS type FROM companies
        WHERE name LIKE :q OR tagline LIKE :q OR city LIKE :q OR state LIKE :q OR country LIKE :q LIMIT 8", ['q' => $like]);
    foreach ($db->fetchAll() as $r) { $results[] = $r + ['url' => '/NEXAR/provider?id=' . $r['id']]; }

    $db->query('SELECT id, company_id, title AS title, description AS subtitle, "service" AS type FROM services WHERE title LIKE :q OR description LIKE :q LIMIT 8', ['q' => $like]);
    foreach ($db->fetchAll() as $r) { $results[] = $r + ['url' => '/NEXAR/provider?id=' . $r['company_id'] . '&service=' . $r['id']]; }

    $results = array_slice($results, 0, 10);
    foreach ($results as $index => &$result) {
        $result['position'] = $index + 1;
    }
    unset($result);

    echo json_encode(['success' => true, 'results' => $results], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
