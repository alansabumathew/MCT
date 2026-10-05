<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
admin_session();
$_SESSION = [];
session_destroy();
header('Location: login.php');
