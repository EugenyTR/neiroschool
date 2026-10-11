<?php
// api/delete_user.php — Удаление пользователя
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

// Запрещаем удалять самого себя
if ((int)$data['id'] === (int)$_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cannot delete yourself']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Проверяем, что удаляем не директора
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([(int)$data['id']]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    if ($user['role'] === 'principal') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Cannot delete principal']);
        exit;
    }

    // Удаляем сессии пользователя
    $stmt = $db->prepare("DELETE FROM sessions WHERE user_id = ?");
    $stmt->execute([(int)$data['id']]);

    // Удаляем пользователя
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([(int)$data['id']]);

    echo json_encode(['success' => true, 'message' => 'Пользователь удалён']);

} catch (PDOException $e) {
    error_log("Delete user error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
