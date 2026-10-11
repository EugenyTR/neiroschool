<?php
// api/add_lesson.php
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

if (empty($data['subject_id']) || empty($data['date'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing subject_id or date']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT COALESCE(MAX(lesson_number), 0) + 1 as next_num FROM lessons WHERE subject_id = ?");
    $stmt->execute([$data['subject_id']]);
    $nextNum = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(MAX(position), 0) + 1 as next_pos FROM lessons WHERE subject_id = ?");
    $stmt->execute([$data['subject_id']]);
    $nextPos = $stmt->fetchColumn();

    $stmt = $db->prepare("
        INSERT INTO lessons (subject_id, lesson_number, date, topic, homework, individual_task, quarter, position)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        (int)$data['subject_id'],
        (int)$nextNum,
        $data['date'],
        $data['topic'] ?? 'Урок ' . $nextNum,
        $data['homework'] ?? '',
        $data['individual_task'] ?? '',
        (int)($data['quarter'] ?? 1),
        (int)$nextPos
    ]);

    echo json_encode([
        'success' => true,
        'id' => $db->lastInsertId(),
        'lesson_number' => $nextNum,
        'message' => 'Урок добавлен'
    ]);

} catch (PDOException $e) {
    error_log("Add lesson error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}