<?php
// api/add_subject.php — Добавление нового предмета
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

if (empty($data['name']) || empty($data['class_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing name or class_id']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        INSERT INTO subjects (name, class_id, teacher_id)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        trim($data['name']),
        (int)$data['class_id'],
        !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null
    ]);

    echo json_encode([
        'success' => true,
        'id' => $db->lastInsertId(),
        'message' => 'Предмет создан'
    ]);

} catch (PDOException $e) {
    error_log("Add subject error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}