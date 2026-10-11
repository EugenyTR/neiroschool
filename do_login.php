<?php
session_start();
require_once __DIR__.'/auth.php';
$auth = new Auth();
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$result = $auth->login($username, $password);
if ($result['success']) {
    header('Location: index.php');
    exit;
} else {
    $_SESSION['login_error'] = $result['message'];
    header('Location: login.php');
    exit;
}