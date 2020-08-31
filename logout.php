<?php
require_once 'functions/init.php';

if (isset($_COOKIE['login_token'])) {
    unset($_COOKIE['login_token']);
    setcookie('login_token', '', time() - 86400);
}
session_destroy();

redirect('login.php');