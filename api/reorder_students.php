<?php
// api/reorder_students.php — ПЕРВАЯ СТРОКА, без BOM!
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

if (empty($data['order']) || !is_array($data['order'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing order array']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Обновляем позиции в транзакции
    $db->beginTransaction();
    
    foreach ($data['order'] as $item) {
        if (!empty($item['id']) && isset($item['position'])) {
            $stmt = $db->prepare("UPDATE students SET position = ? WHERE id = ?");
            $stmt->execute([$item['position'], $item['id']]);
        }
    }
    
    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Порядок обновлён']);
    
} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log("Reorder students error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}