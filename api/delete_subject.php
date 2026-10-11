<?php
// api/delete_subject.php — Удаление предмета
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
    echo json_encode(['success' => false, 'error' => 'Missing subject id']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Удаляем оценки через уроки
    $stmt = $db->prepare("DELETE FROM grades WHERE lesson_id IN (SELECT id FROM lessons WHERE subject_id = ?)");
    $stmt->execute([(int)$data['id']]);

    // Удаляем уроки
    $stmt = $db->prepare("DELETE FROM lessons WHERE subject_id = ?");
    $stmt->execute([(int)$data['id']]);

    // Удаляем предмет
    $stmt = $db->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->execute([(int)$data['id']]);

    echo json_encode(['success' => true, 'message' => 'Предмет удалён']);

} catch (PDOException $e) {
    error_log("Delete subject error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
