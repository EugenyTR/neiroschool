<?php
// api/get_subjects.php — Получение списка предметов по классу
session_start();
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->checkAuth()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = $auth->getCurrentUser();
$classId = $_GET['class_id'] ?? null;

if (!$classId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing class_id']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Учитель видит только свои предметы в этом классе
    if ($user['role'] === 'teacher') {
        $stmt = $db->prepare("
            SELECT s.id, s.name, s.class_id, c.name as class_name, u.full_name as teacher_name
            FROM subjects s
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN users u ON s.teacher_id = u.id
            INNER JOIN teacher_assignments ta ON s.id = ta.subject_id AND s.class_id = ta.class_id
            WHERE s.class_id = ? AND ta.teacher_id = ?
            ORDER BY s.name
        ");
        $stmt->execute([$classId, $user['id']]);
    } else {
        $stmt = $db->prepare("
            SELECT s.id, s.name, s.class_id, c.name as class_name, u.full_name as teacher_name
            FROM subjects s
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN users u ON s.teacher_id = u.id
            WHERE s.class_id = ?
            ORDER BY s.name
        ");
        $stmt->execute([$classId]);
    }
    $subjects = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'subjects' => $subjects
    ]);

} catch (PDOException $e) {
    error_log("Get subjects error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
