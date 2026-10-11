<?php
// api/edit_user.php — Редактирование пользователя
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
    echo json_encode(['success' => false, 'error' => 'Missing user id']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $updateFields = [];
    $params = [];

    if (isset($data['full_name'])) {
        $updateFields[] = 'full_name = ?';
        $params[] = $data['full_name'];
    }

    if (isset($data['email'])) {
        $updateFields[] = 'email = ?';
        $params[] = $data['email'];
    }

    if (isset($data['subject_id'])) {
        $updateFields[] = 'subject_id = ?';
        $params[] = $data['subject_id'] ? (int)$data['subject_id'] : null;
    }

    if (isset($data['class_id'])) {
        $updateFields[] = 'class_id = ?';
        $params[] = $data['class_id'] ? (int)$data['class_id'] : null;
    }

    if (!empty($data['password'])) {
        $updateFields[] = 'password_hash = ?';
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit;
    }

    $params[] = (int)$data['id'];

    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Пользователь обновлён']);

} catch (PDOException $e) {
    error_log("Edit user error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
