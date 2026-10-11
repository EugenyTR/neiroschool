<?php
// api/delete_lesson.php
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

if (empty($data['lesson_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing lesson_id']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Удаляем оценки, связанные с уроком
    $stmt = $db->prepare("DELETE FROM grades WHERE lesson_id = ?");
    $stmt->execute([(int)$data['lesson_id']]);

    // Удаляем урок
    $stmt = $db->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->execute([(int)$data['lesson_id']]);

    echo json_encode(['success' => true, 'message' => 'Урок удалён']);

} catch (PDOException $e) {
    error_log("Delete lesson error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}
