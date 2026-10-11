<?php
// api/edit_lesson.php
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

    $updateFields = [];
    $params = [];

    if (isset($data['topic'])) {
        $updateFields[] = 'topic = ?';
        $params[] = $data['topic'];
    }

    if (isset($data['date'])) {
        $updateFields[] = 'date = ?';
        $params[] = $data['date'];
    }

    if (isset($data['homework'])) {
        $updateFields[] = 'homework = ?';
        $params[] = $data['homework'];
    }

    if (isset($data['individual_task'])) {
        $updateFields[] = 'individual_task = ?';
        $params[] = $data['individual_task'];
    }

    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit;
    }

    $params[] = $data['lesson_id'];

    $sql = "UPDATE lessons SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Урок обновлён']);

} catch (PDOException $e) {
    error_log("Edit lesson error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}