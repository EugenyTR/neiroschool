<?php
// api/get_classes.php — Получение списка классов
session_start();
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->checkAuth()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = $auth->getCurrentUser();

try {
    $db = Database::getInstance()->getConnection();

    // Учитель видит только классы, где он назначен
    if ($user['role'] === 'teacher') {
        $stmt = $db->prepare("
            SELECT DISTINCT c.id, c.name, c.teacher_id
            FROM classes c
            INNER JOIN teacher_assignments ta ON c.id = ta.class_id
            WHERE ta.teacher_id = ?
            ORDER BY c.name
        ");
        $stmt->execute([$user['id']]);
    } else {
        $stmt = $db->query("SELECT id, name, teacher_id FROM classes ORDER BY name");
    }
    $classes = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'classes' => $classes,
        'user_role' => $user['role']
    ]);

} catch (PDOException $e) {
    error_log("Get classes error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
