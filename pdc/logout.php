<?php
require_once __DIR__ . '/includes/bootstrap.php';

Auth::logout();
header('Location: ' . APP_URL . '/login.php');
exit;