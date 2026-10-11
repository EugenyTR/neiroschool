<?php
// api/delete_student.php — ПЕРВАЯ СТРОКА, без BOM!
session_start();
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->checkAuth() || !$auth->hasRole('principal')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing student id']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Сначала удаляем привязки к родителям
    $stmt = $db->prepare("DELETE FROM parent_student WHERE student_id = ?");
    $stmt->execute([$data['id']]);
    
    // Удаляем оценки
    $stmt = $db->prepare("DELETE FROM grades WHERE student_id = ?");
    $stmt->execute([$data['id']]);
    
    // Удаляем ученика
    $stmt = $db->prepare("DELETE FROM students WHERE id = ? AND class_id = ?");
    $stmt->execute([$data['id'], $data['class_id'] ?? 1]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Ученик удалён']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ученик не найден']);
    }
    
} catch (PDOException $e) {
    error_log("Delete student error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}