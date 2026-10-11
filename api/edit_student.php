<?php
// api/edit_student.php — ПЕРВАЯ СТРОКА, без BOM!
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

if (empty($data['id']) || empty($data['full_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing id or full_name']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("UPDATE students SET full_name = ?, updated_at = NOW() WHERE id = ? AND class_id = ?");
    $stmt->execute([trim($data['full_name']), $data['id'], $data['class_id'] ?? 1]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Ученик обновлён']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ученик не найден или не изменён']);
    }
    
} catch (PDOException $e) {
    error_log("Edit student error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}