<?php
// api/save_lesson_date.php
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

if (empty($data['student_id']) || empty($data['lesson_id']) || empty($data['lesson_date'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Проверяем, существует ли запись
    $stmt = $db->prepare("SELECT id FROM student_lesson_dates WHERE student_id = ? AND lesson_id = ?");
    $stmt->execute([$data['student_id'], $data['lesson_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Обновляем
        $stmt = $db->prepare("UPDATE student_lesson_dates SET lesson_date = ? WHERE student_id = ? AND lesson_id = ?");
        $stmt->execute([$data['lesson_date'], $data['student_id'], $data['lesson_id']]);
    } else {
        // Создаем
        $stmt = $db->prepare("INSERT INTO student_lesson_dates (student_id, lesson_id, lesson_date) VALUES (?, ?, ?)");
        $stmt->execute([$data['student_id'], $data['lesson_id'], $data['lesson_date']]);
    }

    echo json_encode(['success' => true, 'message' => 'Дата сохранена']);

} catch (PDOException $e) {
    error_log("Save lesson date error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}