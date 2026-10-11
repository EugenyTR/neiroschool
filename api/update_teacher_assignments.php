<?php
// api/update_teacher_assignments.php — Обновление назначений учителя
session_start();
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->checkAuth() || !$auth->hasRole(['principal', 'methodologist'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data['teacher_id']) || !isset($data['assignments'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing teacher_id or assignments']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $teacherId = (int)$data['teacher_id'];

    // Удаляем старые назначения
    $stmt = $db->prepare("DELETE FROM teacher_assignments WHERE teacher_id = ?");
    $stmt->execute([$teacherId]);

    // Добавляем новые
    $stmt = $db->prepare("INSERT INTO teacher_assignments (teacher_id, class_id, subject_id) VALUES (?, ?, ?)");
    foreach ($data['assignments'] as $a) {
        if (!empty($a['class_id']) && !empty($a['subject_id'])) {
            $stmt->execute([$teacherId, (int)$a['class_id'], (int)$a['subject_id']]);
        }
    }

    // Обновляем legacy поля в users (первое назначение)
    $stmt = $db->prepare("SELECT class_id, subject_id FROM teacher_assignments WHERE teacher_id = ? LIMIT 1");
    $stmt->execute([$teacherId]);
    $first = $stmt->fetch();
    if ($first) {
        $stmt = $db->prepare("UPDATE users SET class_id = ?, subject_id = ? WHERE id = ?");
        $stmt->execute([$first['class_id'], $first['subject_id'], $teacherId]);
    }

    echo json_encode(['success' => true, 'message' => 'Назначения обновлены']);

} catch (PDOException $e) {
    error_log("Update assignments error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
