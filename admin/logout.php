<?php
require_once __DIR__ . '/../inc/auth.php';
do_logout();
header('Location: index.php');
exit;
