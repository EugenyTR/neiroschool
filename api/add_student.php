<?php
// api/add_student.php — ПЕРВАЯ СТРОКА, без BOM!
session_start();
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

$auth = new Auth();

// Проверка авторизации и прав
if (!$auth->checkAuth()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!$auth->hasRole('principal')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden: only principal can add students']);
    exit;
}

// Получаем данные
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['full_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing full_name']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Получаем максимальную позицию для нового ученика
    $stmt = $db->prepare("SELECT COALESCE(MAX(position), 0) + 1 as next_pos FROM students WHERE class_id = ?");
    $stmt->execute([$data['class_id'] ?? 1]);
    $nextPos = $stmt->fetchColumn();
    
    // Вставляем нового ученика
    $stmt = $db->prepare("INSERT INTO students (full_name, class_id, position) VALUES (?, ?, ?)");
    $stmt->execute([
        trim($data['full_name']),
        $data['class_id'] ?? 1,
        $nextPos
    ]);
    
    echo json_encode([
        'success' => true, 
        'id' => $db->lastInsertId(),
        'message' => 'Ученик добавлен'
    ]);
    
} catch (PDOException $e) {
    error_log("Add student error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}