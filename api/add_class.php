<?php
// api/add_class.php — Добавление нового класса
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

if (empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing class name']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Проверяем уникальность названия
    $stmt = $db->prepare("SELECT id FROM classes WHERE name = ?");
    $stmt->execute([trim($data['name'])]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Class already exists']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO classes (name, teacher_id) VALUES (?, ?)");
    $stmt->execute([trim($data['name']), !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null]);

    echo json_encode([
        'success' => true,
        'id' => $db->lastInsertId(),
        'message' => 'Класс создан'
    ]);

} catch (PDOException $e) {
    error_log("Add class error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
