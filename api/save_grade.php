<?php
// api/save_grade.php
session_start();
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->checkAuth() || !$auth->hasRole(['principal', 'methodologist', 'teacher'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data['student_id']) || empty($data['lesson_id']) || !isset($data['grade'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// ✅ Проверка 0-100
$grade = floatval($data['grade']);
if ($grade < 0 || $grade > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Grade must be between 0 and 100']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id FROM grades WHERE student_id = ? AND lesson_id = ?");
    $stmt->execute([$data['student_id'], $data['lesson_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $db->prepare("UPDATE grades SET grade = ?, comment = ?, updated_at = NOW() WHERE student_id = ? AND lesson_id = ?");
        $stmt->execute([$grade, $data['comment'] ?? null, $data['student_id'], $data['lesson_id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO grades (student_id, lesson_id, grade, comment, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$data['student_id'], $data['lesson_id'], $grade, $data['comment'] ?? null]);
    }

    echo json_encode(['success' => true, 'message' => 'Оценка сохранена']);

} catch (PDOException $e) {
    error_log("Save grade error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}