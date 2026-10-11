<?php
// api/get_users.php — Получение списка пользователей
session_start();
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->checkAuth() || !$auth->hasRole(['principal', 'methodologist'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT u.id, u.username, u.role, u.full_name, u.email,
               u.subject_id, u.class_id,
               s.name as subject_name, c.name as class_name
        FROM users u
        LEFT JOIN subjects s ON u.subject_id = s.id
        LEFT JOIN classes c ON u.class_id = c.id
        WHERE u.role IN ('teacher', 'methodologist')
        ORDER BY u.role DESC, u.full_name
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();

    // Загружаем назначения для каждого учителя
    foreach ($users as &$u) {
        if ($u['role'] === 'teacher') {
            $stmt = $db->prepare("
                SELECT ta.class_id, ta.subject_id, c.name as class_name, s.name as subject_name
                FROM teacher_assignments ta
                JOIN classes c ON ta.class_id = c.id
                JOIN subjects s ON ta.subject_id = s.id
                WHERE ta.teacher_id = ?
            ");
            $stmt->execute([$u['id']]);
            $u['assignments'] = $stmt->fetchAll();
        }
    }
    unset($u);

    // Получаем списки для селектов
    $subjects = $db->query("SELECT id, name, class_id FROM subjects ORDER BY name")->fetchAll();
    $classes = $db->query("SELECT id, name FROM classes ORDER BY name")->fetchAll();

    echo json_encode([
        'success' => true,
        'users' => $users,
        'subjects' => $subjects,
        'classes' => $classes
    ]);

} catch (PDOException $e) {
    error_log("Get users error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
