<?php
session_start();
require_once __DIR__.'/auth.php';
$auth=new Auth();
if($auth->checkAuth()){header('Location: index.php');exit;}
$error=$_SESSION['login_error']??'';unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Вход - Электронный журнал</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-container">
<div class="login-box">
<h1>📚 Электронный журнал</h1>
<h2>Вход</h2>
<?php if($error):?><div class="error-message"><?=$error?></div><?php endif;?>
<form method="POST" action="do_login.php">
<div class="form-group"><label>Логин</label><input type="text" name="username" required></div>
<div class="form-group"><label>Пароль</label><input type="password" name="password" required></div>
<button type="submit" class="btn btn-primary">Войти</button>
</form>
</div>
</div>
</body>
</html>