<?php
// api/delete_class.php — Удаление класса
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

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing class id']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Удаляем связанные данные
    // 1. Удаляем оценки через уроки
    $stmt = $db->prepare("DELETE FROM grades WHERE lesson_id IN (SELECT id FROM lessons WHERE subject_id IN (SELECT id FROM subjects WHERE class_id = ?))");
    $stmt->execute([(int)$data['id']]);

    // 2. Удаляем уроки через предметы
    $stmt = $db->prepare("DELETE FROM lessons WHERE subject_id IN (SELECT id FROM subjects WHERE class_id = ?)");
    $stmt->execute([(int)$data['id']]);

    // 3. Удаляем предметы
    $stmt = $db->prepare("DELETE FROM subjects WHERE class_id = ?");
    $stmt->execute([(int)$data['id']]);

    // 4. Удаляем учеников
    $stmt = $db->prepare("DELETE FROM students WHERE class_id = ?");
    $stmt->execute([(int)$data['id']]);

    // 5. Удаляем класс
    $stmt = $db->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([(int)$data['id']]);

    echo json_encode(['success' => true, 'message' => 'Класс удалён']);

} catch (PDOException $e) {
    error_log("Delete class error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
