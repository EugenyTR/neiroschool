<?php
session_start();
require_once __DIR__ . '/config.php';

// =====================================================================
// НАСТРОЙКИ ЯНДЕКС OAUTH
// Замените эти значения на данные из https://oauth.yandex.ru/
// =====================================================================
$clientId = 'aafb0f59f4934a27a68196c2f567eb2c';
$clientSecret = 'bc68a090291d497396dfdc333c4e2efe';
$redirectUri = 'https://neiroschool.com/yandex_auth.php';

if (!isset($_GET['code'])) {
    // Шаг 1: Перенаправление на страницу авторизации Яндекса
    $authUrl = "https://oauth.yandex.ru/authorize?response_type=code&client_id={$clientId}&redirect_uri=" . urlencode($redirectUri);
    header("Location: {$authUrl}");
    exit;
}

$code = $_GET['code'];

// Шаг 2: Обмен кода на токен
$tokenUrl = "https://oauth.yandex.ru/token";
$postData = http_build_query([
    'grant_type' => 'authorization_code',
    'code' => $code,
    'client_id' => $clientId,
    'client_secret' => $clientSecret
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $postData,
        'ignore_errors' => true
    ]
]);

$tokenResponse = @file_get_contents($tokenUrl, false, $context);
$tokenData = json_decode($tokenResponse, true);

if (!isset($tokenData['access_token'])) {
    die("Ошибка получения токена Яндекс: " . ($tokenData['error_description'] ?? 'Неизвестная ошибка. Проверьте Client ID и Secret.'));
}

$accessToken = $tokenData['access_token'];

// Шаг 3: Получение данных пользователя
$userInfoUrl = "https://login.yandex.ru/info?format=json";
$userContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: OAuth {$accessToken}\r\n",
        'ignore_errors' => true
    ]
]);

$userResponse = @file_get_contents($userInfoUrl, false, $userContext);
$userData = json_decode($userResponse, true);

if (!isset($userData['id'])) {
    die("Ошибка получения данных пользователя Яндекс. Убедитесь, что в настройках приложения выданы права: login:email, login:info");
}

$yandexId = $userData['id'];
$email = $userData['default_email'] ?? ($userData['emails'][0] ?? 'yandex_' . $yandexId . '@yandex.ru');
$fullName = $userData['real_name'] ?? $userData['display_name'] ?? 'Пользователь Яндекс';
$username = 'yandex_' . $yandexId;

$db = Database::getInstance()->getConnection();

// Шаг 4: Проверка существования пользователя
$stmt = $db->prepare("SELECT id, username, role, full_name, email FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $email]);
$user = $stmt->fetch();

if (!$user) {
    // Шаг 5: Регистрация нового пользователя (по умолчанию роль 'parent')
    // Вы можете изменить 'parent' на 'teacher', если это необходимо для вашей логики
    $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $role = 'parent';

    $stmt = $db->prepare("INSERT INTO users (username, password_hash, email, role, full_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $passwordHash, $email, $role, $fullName]);

    $userId = $db->lastInsertId();
    $user = [
        'id' => $userId,
        'username' => $username,
        'role' => $role,
        'full_name' => $fullName,
        'email' => $email
    ];
}

// Шаг 6: Создание сессии и cookie (аналогично логике в auth.php)
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
$stmt = $db->prepare("INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$user['id'], $token, $expires]);

setcookie('session_token', $token, [
    'expires' => time() + 86400,
    'path' => '/',
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']),
    'samesite' => 'Lax'
]);

$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['username'] = $user['username'];
$_SESSION['full_name'] = $user['full_name'];

// Шаг 7: Автоматический редирект на системную страницу
header('Location: https://neiroschool.com/index.php');
exit;