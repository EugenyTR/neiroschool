<?php
session_start();
require_once __DIR__.'/auth.php';
$auth = new Auth();
if ($auth->checkAuth()) {
    header('Location: index.php');
    exit;
}
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Вход - Электронный журнал</title>
<link rel="stylesheet" href="css/style.css">
<style>
  .yandex-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    width: 100%; padding: 12px; background-color: #fc3f1d; color: white;
    border: none; border-radius: 8px; font-size: 16px; font-weight: 600;
    cursor: pointer; text-decoration: none; margin-top: 15px; transition: background-color 0.2s;
  }
  .yandex-btn:hover { background-color: #e03515; }
  .divider { display: flex; align-items: center; text-align: center; margin: 20px 0; color: #888; }
  .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #ddd; }
  .divider span { padding: 0 10px; font-size: 14px; }
</style>
</head>
<body>
<div class="login-container">
  <div class="login-box">
    <h1>📚 Электронный журнал</h1>
    <h2>Вход в систему</h2>
    <?php if($error): ?><div class="error-message"><?=$error?></div><?php endif; ?>

    <form method="POST" action="do_login.php">
      <div class="form-group"><label>Логин</label><input type="text" name="username" required></div>
      <div class="form-group"><label>Пароль</label><input type="password" name="password" required></div>
      <button type="submit" class="btn btn-primary">Войти</button>
    </form>

    <div class="divider"><span>или</span></div>

    <a href="yandex_auth.php" class="yandex-btn">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M13.93 24h-3.72l5.64-13.08h-5.47v-2.64h8.82v2.64h-3.51L13.93 24zM2.16 0h4.32l7.56 24h-4.32L2.16 0z"/></svg>
      Войти через Яндекс
    </a>

    <div style="margin-top: 20px; text-align: center;">
      <a href="index.html" style="color: #666; text-decoration: none; font-size: 14px;">← Вернуться на главную</a>
    </div>
  </div>
</div>
</body>
</html>