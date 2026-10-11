<?php
// api/add_user.php — Создание нового пользователя (учителя/методиста)
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

if (empty($data['username']) || empty($data['password']) || empty($data['role']) || empty($data['full_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

if (!in_array($data['role'], ['teacher', 'methodologist'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid role']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Проверяем уникальность логина
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Username already exists']);
        exit;
    }

    $stmt = $db->prepare("
        INSERT INTO users (username, password_hash, role, subject_id, class_id, full_name, email)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['username'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['role'],
        !empty($data['subject_id']) ? (int)$data['subject_id'] : null,
        !empty($data['class_id']) ? (int)$data['class_id'] : null,
        $data['full_name'],
        $data['email'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'id' => $db->lastInsertId(),
        'message' => 'Пользователь создан'
    ]);

} catch (PDOException $e) {
    error_log("Add user error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
