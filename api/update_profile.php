<?php
// api/update_profile.php — Редактирование профиля текущего пользователя
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
$input = file_get_contents('php://input');
$data = json_decode($input, true);

try {
    $db = Database::getInstance()->getConnection();

    $updateFields = [];
    $params = [];

    if (isset($data['full_name']) && trim($data['full_name'])) {
        $updateFields[] = 'full_name = ?';
        $params[] = trim($data['full_name']);
    }

    if (isset($data['email'])) {
        $updateFields[] = 'email = ?';
        $params[] = $data['email'];
    }

    if (!empty($data['password'])) {
        if (strlen($data['password']) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
            exit;
        }
        $updateFields[] = 'password_hash = ?';
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    // Проверка текущего пароля при смене пароля
    if (!empty($data['current_password'])) {
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $dbUser = $stmt->fetch();
        if (!$dbUser || !password_verify($data['current_password'], $dbUser['password_hash'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
            exit;
        }
    }

    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit;
    }

    $params[] = $user['id'];
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Профиль обновлён']);

} catch (PDOException $e) {
    error_log("Update profile error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
