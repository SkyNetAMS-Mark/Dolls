<?php
require_once '../init.php';

$adminModel = new Admin();
$adminModel->logout();

redirect('/admin/login.php');
